---
name: skill-release-operations
description: >
  Full release pipeline for a WordPress theme: build the zip, upload it to
  WordPress Playground via Playwright, activate the theme, then run a
  structured battery of automated checks — front-page screenshot, admin
  dashboard, demo-import, theme-info page, and Theme Check plugin — all
  captured as screenshots and text artefacts.
allowed-tools: Bash(playwright-cli:*) Bash(npx:*) Bash(npm:*)
---

## Overview

This skill automates the end-to-end release-verification workflow:

1. **Deploy** – produce a distributable `.zip` of the theme.
2. **Upload** – install the zip on a fresh WordPress Playground instance.
3. **Activate** – switch the active theme to the uploaded one.
4. **Automated Checks** – screenshot key admin and front-end pages, trigger
   Demo Import, inspect the Theme Info page, and run the Theme Check plugin.

All steps share a single named Playwright session (`-s=mysession`) so the
browser stays open between commands.

> **Error handling**: The script uses `set -euo pipefail` and a cleanup trap
> that closes the browser session on exit or failure.

---

## Prerequisites

```bash
set -euo pipefail
trap 'playwright-cli -s=mysession close 2>/dev/null || true' EXIT

# Verify playwright-cli is available
playwright-cli --version

# Confirm npm build script exists
npm run --if-present deploy --dry-run

# Helper: wait for the WordPress admin iframe to be ready
wait_for_admin_frame() {
  playwright-cli -s=mysession --raw run-code "async page => {
    const frames = page.frames();
    const adminFrame = frames.find(f => f.url().includes('/wp-admin'));
    if (!adminFrame) throw new Error('Admin frame not found');
    await adminFrame.waitForSelector('body', { timeout: 30000 });
  }"
}
```

---

## Step 1 – Build the Theme Zip

```bash
# Build production assets (adjust script name if needed: build / bundle / dist)
npm run deploy
```

---

## Step 2 – Launch WordPress Playground & Open a Session

```bash
# Start a Playground instance and open a named browser session.
playwright-cli -s=mysession open \
  "https://playground.wordpress.net/?wp=latest&php=8.2&storage=none"

# Wait for Playground to fully boot — poll for the admin iframe body
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin'));
  if (adminFrame) {
    await adminFrame.waitForSelector('body', { timeout: 60000 });
  }
}"

# Confirm the page loaded
playwright-cli -s=mysession screenshot --filename=00-playground-ready.png
```

---

## Step 3 – Upload the Theme Zip

WordPress Playground exposes a URL bar in its outer shell. Navigate to the
upload page and use the file-input inside the WP admin iframe.

```bash
THEME_SLUG=$(basename "$PWD")

# Locate the built zip — prefer deploy/ dir, fall back to theme root
if ls deploy/*.zip 2>/dev/null; then
  ZIP_PATH=$(ls deploy/*.zip | head -1)
elif ls *.zip 2>/dev/null; then
  ZIP_PATH=$(ls *.zip | head -1)
else
  echo "ERROR: No .zip found in deploy/ or theme root" >&2
  exit 1
fi

# Navigate to Add New Theme → Upload Theme
playwright-cli -s=mysession fill "input[type=text]" "/wp-admin/theme-install.php"
playwright-cli -s=mysession press Enter
wait_for_admin_frame

# Click "Upload Theme" button
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
  await adminFrame.getByRole('link', { name: 'Upload Theme' }).click();
}"
# Wait for the upload form to render
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
  await adminFrame.waitForSelector('input[name=\"themezip\"]', { timeout: 10000 });
}"

# Set the file input and submit
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
  await adminFrame.locator('input[name=\"themezip\"]').setInputFiles('${ZIP_PATH}');
  await adminFrame.getByRole('button', { name: 'Install Now' }).click();
}"

# Wait for upload to complete
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
  await adminFrame.waitForSelector('a:has-text(\"Activate\"), .notice-success', { timeout: 30000 });
}"

playwright-cli -s=mysession screenshot --filename=01-upload-complete.png
```

---

## Step 4 – Activate the Theme

After upload, WordPress shows an "Activate" link on the install-success screen.

```bash
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
  // Prefer the 'Activate' link on the post-install page
  const activateLink = adminFrame.getByRole('link', { name: /^Activate/ });
  if (await activateLink.count() > 0) {
    await activateLink.click();
  } else {
    // Fallback: navigate to themes list and activate by theme name
    await adminFrame.goto('/wp-admin/themes.php');
    await adminFrame.locator('.theme:not(.active)').filter({ hasText: '${THEME_SLUG}' })
      .getByRole('link', { name: 'Activate' }).click();
  }
}"
wait_for_admin_frame

playwright-cli -s=mysession screenshot --filename=02-theme-activated.png
```

> **Note on iframe index**: The actual WordPress content lives in a
> cross-origin iframe. `page.frames()` returns all frames in creation order;
> `frames[2]` is the typical WP-admin frame in Playground, but this can vary.
> The snippets above use `.find(f => f.url().includes('/wp-admin'))` as the
> primary path, falling back to `frames[2]` only as a last resort. If both
> approaches fail, log all frame URLs for debugging:
>
> ```bash
> playwright-cli -s=mysession --raw run-code "async page => {
>   page.frames().forEach((f, i) => console.log(i, f.url()));
> }"
> ```

---

## Automated Checks

The Playground interface has a URL bar for navigating within WordPress. The
actual WordPress content is inside a cross-origin iframe accessible via
`page.frames()[2]` (this index may vary across Playground versions — if
`frames()[2]` fails, iterate all frames to find the WordPress admin frame).

### 1. Front Page Screenshot

```bash
playwright-cli -s=mysession fill "input[type=text]" "/"
playwright-cli -s=mysession press Enter
wait_for_admin_frame
playwright-cli -s=mysession screenshot --filename=03-front.png
```

### 2. Admin Dashboard Screenshot

```bash
playwright-cli -s=mysession fill "input[type=text]" "/wp-admin/"
playwright-cli -s=mysession press Enter
wait_for_admin_frame
playwright-cli -s=mysession screenshot --filename=04-dashboard.png
```

Check for the "Get started with {Theme Name}" welcome notification.

### 3. Install Advanced Import

```bash
# Click the "Get started" banner to install Advanced Import
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
  await adminFrame.getByRole('button', { name: /^Get started with [A-Z]/ }).click();
  // Wait for install to complete — poll for success notice or URL change
  await adminFrame.waitForFunction(() => {
    return document.querySelector('.notice-success')
      || document.querySelector('.updated')
      || window.location.href.includes('themes.php');
  }, { timeout: 30000 });
}"
```

### 4. Demo Import Page

```bash
playwright-cli -s=mysession fill "input[type=text]" "/wp-admin/themes.php?page=demo-import"
playwright-cli -s=mysession press Enter
wait_for_admin_frame
playwright-cli -s=mysession screenshot --filename=05-demoimport.png
```

### 5. Theme Info Page

```bash
THEME_SLUG=$(basename "$PWD")
playwright-cli -s=mysession fill "input[type=text]" "/wp-admin/admin.php?page=${THEME_SLUG}"
playwright-cli -s=mysession press Enter
wait_for_admin_frame
playwright-cli -s=mysession screenshot --filename=06-themeinfo.png
```

### 6. Run Theme Check

```bash
# Navigate to Theme Check page
playwright-cli -s=mysession fill "input[type=text]" "/wp-admin/admin.php?page=themecheck"
playwright-cli -s=mysession press Enter
wait_for_admin_frame

# Ensure Theme Check plugin is active; install if missing
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
  const currentUrl = adminFrame.url();
  if (!currentUrl.includes('page=themecheck')) {
    // Theme Check plugin not present — install it
    await adminFrame.goto('/wp-admin/plugin-install.php?s=theme+check&tab=search&type=term');
    await adminFrame.locator('.plugin-card-theme-check').getByRole('link', { name: 'Install Now' }).click();
    await adminFrame.waitForSelector('.plugin-card-theme-check .activate-now', { timeout: 30000 });
    await adminFrame.locator('.plugin-card-theme-check .activate-now').click();
    await adminFrame.waitForURL(/page=themecheck|plugins\.php/, { timeout: 15000 });
  }
}"

# Click "Check it!" button in the WordPress frame
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
  await adminFrame.getByRole('button', { name: 'Check it!' }).click();
}"

# Poll for results (up to 60 s)
for i in {1..6}; do
  sleep 10
  result=$(playwright-cli -s=mysession --raw run-code "async page => {
    const frames = page.frames();
    const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
    const h2 = await adminFrame.locator('h2:has-text(\"passed the tests\"), h2:has-text(\"REQUIRED\")').first();
    return await h2.innerText().catch(() => '');
  }" 2>/dev/null)
  if echo "$result" | grep -q "passed\|REQUIRED"; then
    echo "Theme Check result: $result"
    break
  fi
done

# Screenshot result
playwright-cli -s=mysession screenshot --filename=07-themecheck-result.png

# Extract full result text
playwright-cli -s=mysession --raw run-code "async page => {
  const frames = page.frames();
  const adminFrame = frames.find(f => f.url().includes('/wp-admin')) ?? frames[2];
  return await adminFrame.evaluate(() => document.querySelector('.wrap')?.innerText ?? '');
}" > theme-check-result.txt

echo "=== Theme Check Result ==="
cat theme-check-result.txt

# Close session
playwright-cli -s=mysession close
```

---

## Artefacts Summary

After the full run the following files are produced:

| File | Contents |
|------|----------|
| `00-playground-ready.png` | Playground shell after boot |
| `01-upload-complete.png` | Post-upload install screen |
| `02-theme-activated.png` | Themes list after activation |
| `03-front.png` | Front page of the live site |
| `04-dashboard.png` | WP Admin dashboard (welcome notice) |
| `05-demoimport.png` | Advanced Import / Demo Import page |
| `06-themeinfo.png` | Theme info/welcome page |
| `07-themecheck-result.png` | Theme Check plugin results |
| `theme-check-result.txt` | Full plain-text Theme Check output |

---

## Troubleshooting

**Wrong iframe index** – If interactions silently fail, log all frames:
```bash
playwright-cli -s=mysession --raw run-code "async page => {
  page.frames().forEach((f, i) => console.log(i, f.url()));
}"
```
Then replace `frames[2]` with the correct index or use the URL-based find pattern.

**Playground takes too long to boot** – The initial polling wait uses a 60 s
timeout on `waitForSelector('body')` inside the admin iframe. Increase this if
your network is slow.

**Theme Check plugin redirects** – The skill navigates to `page=themecheck`
first, then checks the resulting URL. If the plugin isn't installed, WordPress
will redirect away, and the install logic kicks in. If your test site already
has the plugin, the guard condition skips the install.

**Zip path issues on Windows/WSL2** – Use `wslpath -w` to convert Linux paths
to Windows paths when `playwright-cli` runs under a native Windows Node:
```bash
if ls deploy/*.zip 2>/dev/null; then
  ZIP_PATH=$(wslpath -w "$(ls deploy/*.zip | head -1)")
elif ls *.zip 2>/dev/null; then
  ZIP_PATH=$(wslpath -w "$(ls *.zip | head -1)")
fi
```

**Flaky navigation** – If `fill "input[type=text]"` fails to target the
Playground URL bar, take a snapshot first to verify the element:
```bash
playwright-cli -s=mysession snapshot --depth=2
```
Then use the ref instead of the CSS selector.
