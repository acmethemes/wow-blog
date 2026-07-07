<?php
/**
 * WOW Blog functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Acme Themes
 * @subpackage WOW Blog
 */

if ( ! function_exists( 'wow_blog_setup' ) ) :
	function wow_blog_setup() {
		load_child_theme_textdomain( 'wow-blog', get_stylesheet_directory() . '/languages' );
	}
endif;
add_action( 'after_setup_theme', 'wow_blog_setup' );

/**
 * Enqueue Styles
 *
 * @package Acme Themes
 * @subpackage WOW Blog
 */
function wow_blog_enqueue_styles() {
	$parent_style = 'wow-blog-parent-style';
	wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
	wp_style_add_data( $parent_style, 'rtl', 'replace' );
	wp_enqueue_style(
		'wow-blog-style',
		get_stylesheet_uri(),
		array( $parent_style )
	);
	wp_style_add_data( 'wow-blog-style', 'rtl', 'replace' );
}
add_action( 'wp_enqueue_scripts', 'wow_blog_enqueue_styles', 98 );

/**
 * Add body class
 *
 * @since Read More 1.0.0
 *
 * @param null
 * @return null
 */
if ( ! function_exists( 'wow_blog_body_class' ) ) :

	function wow_blog_body_class( $read_more_body_classes ) {
		global $read_more_customizer_all_values;

		$read_more_header_logo_menu_display_position = $read_more_customizer_all_values['read-more-header-logo-ads-display-position'];
		$read_more_body_classes[]                    = esc_attr( $read_more_header_logo_menu_display_position );

		if ( is_active_sidebar( 'read-more-header' ) ) :
			$read_more_body_classes[] = 'read-more-active-header-sidebar';
		endif;
		return $read_more_body_classes;
	}
endif;
add_action( 'body_class', 'wow_blog_body_class', 10, 1 );

/**
 * Header Modification
 *
 * @package Acme Themes
 * @subpackage WOW Blog
 */

if ( ! function_exists( 'read_more_header' ) ) :
	function read_more_header() {
		global $read_more_customizer_all_values;
		$read_more_enable_header_top = $read_more_customizer_all_values['read-more-enable-header-top'];
		if ( 1 == $read_more_enable_header_top ) {
			?>
			<div class="top-header">
				<div class="container">
					<div class="row">
						<div class="col-sm-8 text-left">
							<?php
							$read_more_header_top_enable_menu   = $read_more_customizer_all_values['read-more-header-top-enable-menu'];
							$read_more_header_top_enable_social = $read_more_customizer_all_values['read-more-header-top-enable-social'];

							if ( has_nav_menu( 'top-menu' ) && 1 == $read_more_header_top_enable_menu ) {
								wp_nav_menu(
									array(
										'theme_location'  => 'top-menu',
										'container'       => 'div',
										'container_class' => 'acmethemes-top-nav top-block',
										'depth'           => 1,
									)
								);
							}
							if ( 1 == $read_more_header_top_enable_social ) {
								do_action( 'read_more_action_social_links' );
							}
							?>
						</div>
						<div class="col-sm-4 text-right">
							<?php
							$read_more_phone_number             = $read_more_customizer_all_values['read-more-phone-number'];
							$read_more_top_email                = $read_more_customizer_all_values['read-more-top-email'];
							$read_more_header_top_enable_search = $read_more_customizer_all_values['read-more-header-top-enable-search'];
							if ( ! empty( $read_more_phone_number ) ) {
								echo "<a class='top-phone' href='tel:" . esc_attr( esc_html( $read_more_phone_number ) ) . "'><i class='fa fa-phone'></i>" . esc_html( $read_more_phone_number ) . '</a>';
							}
							if ( ! empty( $read_more_top_email ) ) {
								echo "<a class='top-email' href='mailto:" . esc_attr( esc_html( $read_more_top_email ) ) . "'><i class='fa fa-envelope-o'></i>" . esc_html( $read_more_top_email ) . '</a>';
							}
							if ( 1 == $read_more_header_top_enable_search ) {
								echo '<div class="header-search top-block"><i class="fa fa-search icon-menu search-icon-menu"></i>';
								echo "<div class='menu-search-toggle'>";
								echo "<div class='menu-search-inner'>";
								get_search_form();
								echo '</div>';/*menu-search-inner*/
								echo '</div>';/*menu-search-toggle*/
								echo '</div>';/*menu-search-toggle*/
							}
							?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<div class="navbar at-navbar" id="navbar" role="navigation">
			<div class="container">
				<div class="row">
					<div class="col-sm-12 read-more-main-header">
						<div class="navbar-header">
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"><i class="fa fa-bars"></i></button>
							<?php
							if ( 'disable' != $read_more_customizer_all_values['read-more-header-id-display-opt'] ) :
								if ( 'logo-only' == $read_more_customizer_all_values['read-more-header-id-display-opt'] && function_exists( 'the_custom_logo' ) ) :
									the_custom_logo();
								else : /*else is title-only or title-and-tagline*/
									if ( is_front_page() && is_home() ) :
										?>
										<h1 class="site-title">
											<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
										</h1>
									<?php else : ?>
										<p class="site-title">
											<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
										</p>
										<?php
									endif;
									if ( 'title-and-tagline' == $read_more_customizer_all_values['read-more-header-id-display-opt'] ) :
										$description = get_bloginfo( 'description', 'display' );
										if ( $description || is_customize_preview() ) :
											?>
											<p class="site-description"><?php echo esc_html( $description ); ?></p>
											<?php
										endif;
									endif;
								endif;
							endif;
							?>
						</div>
						<?php
						if ( is_active_sidebar( 'read-more-header' ) ) :
							echo '<div class="read-more-header-sidebar">';
							dynamic_sidebar( 'read-more-header' );
							echo '</div>';
						endif;
						?>
					</div>
				</div>
			</div>
			<div class="at-navbar-wrapper">
				<div class="at-navbar-trigger-fix">
					<div class="container">
						<div class="row">
							<div class="col-sm-12">
								<div class="main-navigation navbar-collapse collapse">
									<?php
									wp_nav_menu(
										array(
											'theme_location' => 'primary',
											'menu_id'    => 'primary-menu',
											'menu_class' => 'nav navbar-nav',
										)
									);
									?>
								</div>
							</div>
						</div>
						<!--/.nav-collapse -->
					</div>
				</div>
			</div>
		</div>
		<?php
	}
endif;

/**
 * Footer content
 *
 * @since SuperMag 1.0.0
 *
 * @param null
 * @return null
 */
if ( ! function_exists( 'read_more_footer' ) ) :

	function read_more_footer() {
		global $read_more_customizer_all_values;
		?>
		<div class="clearfix"></div>
		<?php
		if ( is_active_sidebar( 'read-more-footer-full-width' ) ) {
			?>
			<div class="acme-full-width-sidebar">
				<div class="container acme-full-sidebar-wrapper read-more-footer-full-width">
					<?php dynamic_sidebar( 'read-more-footer-full-width' ); ?>
				</div>
			</div>
			<?php
		}
		?>
		<footer class="site-footer at-remove-width">
			<?php
			if (
				is_active_sidebar( 'read-more-footer-top-col-one' ) ||
				is_active_sidebar( 'read-more-footer-top-col-two' ) ||
				is_active_sidebar( 'read-more-footer-top-col-three' )
			) {
				$footer_top_col = 'col-sm-4';
				?>
				<div class="footer-top-wrapper">
					<div class="container">
						<div class="row">
							<div class="footer-columns">
								<?php if ( is_active_sidebar( 'read-more-footer-top-col-one' ) ) : ?>
									<div class="footer-sidebar <?php echo esc_attr( $footer_top_col ); ?>">
										<?php dynamic_sidebar( 'read-more-footer-top-col-one' ); ?>
									</div>
									<?php
								endif;
								if ( is_active_sidebar( 'read-more-footer-top-col-two' ) ) :
									?>
									<div class="footer-sidebar <?php echo esc_attr( $footer_top_col ); ?>">
										<?php dynamic_sidebar( 'read-more-footer-top-col-two' ); ?>
									</div>
									<?php
								endif;
								if ( is_active_sidebar( 'read-more-footer-top-col-three' ) ) :
									?>
									<div class="footer-sidebar <?php echo esc_attr( $footer_top_col ); ?>">
										<?php dynamic_sidebar( 'read-more-footer-top-col-three' ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div><!-- footer-top-wrapper-->
				<?php
			}
			?>
			<div class="footer-bottom-wrapper">
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
							<?php
							if ( 1 == $read_more_customizer_all_values['read-more-enable-social'] ) {
								/**
								 * Social Sharing
								 * read_more_action_social_links
								 *
								 * @since Read More 1.1.0
								 *
								 * @hooked read_more_social_links -  10
								 */
								echo "<div class='text-center'>";
								do_action( 'read_more_action_social_links' );
								echo '</div>';
							}
							?>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<?php
							if ( isset( $read_more_customizer_all_values['read-more-footer-copyright'] ) ) :
								?>
								<p class="text-center">
									<?php echo wp_kses_post( $read_more_customizer_all_values['read-more-footer-copyright'] ); ?>
								</p>
								<?php
							endif;
							?>
							<div class="footer-copyright border text-center">
								<div class="site-info">
									<?php printf( esc_html__( '%1$s by %2$s', 'wow-blog' ), 'WOW Blog', '<a href="http://www.acmethemes.com/" rel="designer">Acme Themes</a>' ); ?>
								</div><!-- .site-info -->
							</div>
						</div>
					</div>
					<a href="#page" class="sm-up-container"><i class="fa fa-angle-up sm-up"></i></a>
				</div>
			</div>
		</footer>
		<?php
	}
endif;

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function wow_blog_widgets_init() {

	if ( is_customize_preview() ) {
		$description = sprintf( __( ' Displays items on header area. Fit For Advertisement. You can put Advertisement from %1$s here %2$s too', 'wow-blog' ), '<a href="#" class="at-customizer" data-section="read-more-header-ad-option">', '</a>' );
	} else {
		$description = __( 'Displays items on header area. Fit For Advertisement', 'wow-blog' );
	}

	register_sidebar(
		array(
			'name'          => __( 'Header Area', 'wow-blog' ),
			'id'            => 'read-more-header',
			'description'   => $description,
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title"><span>',
			'after_title'   => '</span></h3>',
		)
	);
}
add_action( 'widgets_init', 'wow_blog_widgets_init' );

/**
 * Add options on customizer
 *
 * @package Acme Themes
 * @subpackage WOW Blog
 */

if ( ! function_exists( 'wow_blog_customize_register' ) ) :
	function wow_blog_customize_register( $wp_customize ) {

		/*saved options*/
		$options = read_more_get_theme_options();

		/*defaults options*/
		$defaults = read_more_get_default_theme_options();

		/**
		 * Header Site identity and ads display options
		 *
		 * @since Read More 2.0.0
		 *
		 * @param null
		 * @return array $read_more_header_logo_menu_display_position
		 */
		if ( ! function_exists( 'read_more_header_logo_menu_display_position' ) ) :
			function read_more_header_logo_menu_display_position() {
				$read_more_header_logo_menu_display_position = array(
					'left-logo-right-ads'     => __( 'Left Logo and Right Ads', 'wow-blog' ),
					'right-logo-left-ainfo'   => __( 'Right Logo and Left Ads', 'wow-blog' ),
					'center-logo-below-ainfo' => __( 'Center Logo and Below Ads', 'wow-blog' ),
				);
				return apply_filters( 'read_more_header_logo_menu_display_position', $read_more_header_logo_menu_display_position );
			}
		endif;

		/*adding sections for menu */
		$wp_customize->add_section(
			'read-more-site-identity-placement',
			array(
				'priority'       => 20,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '',
				'title'          => __( 'Header Placement', 'wow-blog' ),
				'panel'          => 'read-more-header-panel',
			)
		);

		/*header menu type*/
		$wp_customize->add_setting(
			'read_more_theme_options[read-more-header-logo-ads-display-position]',
			array(
				'capability'        => 'edit_theme_options',
				'default'           => $defaults['read-more-header-logo-ads-display-position'],
				'sanitize_callback' => 'read_more_sanitize_select',
			)
		);
		$choices = read_more_header_logo_menu_display_position();
		$wp_customize->add_control(
			'read_more_theme_options[read-more-header-logo-ads-display-position]',
			array(
				'choices'  => $choices,
				'label'    => __( 'Logo and Advertisement Position', 'wow-blog' ),
				'section'  => 'read-more-site-identity-placement',
				'settings' => 'read_more_theme_options[read-more-header-logo-ads-display-position]',
				'type'     => 'select',
				'priority' => 15,
			)
		);

		/*Related title*/
		$wp_customize->add_setting(
			'read_more_theme_options[read-more-related-title]',
			array(
				'capability'        => 'edit_theme_options',
				'default'           => $defaults['read-more-related-title'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'read_more_theme_options[read-more-related-title]',
			array(
				'label'           => __( 'Related Posts title', 'wow-blog' ),
				'section'         => 'read-more-related-posts',
				'settings'        => 'read_more_theme_options[read-more-related-title]',
				'type'            => 'text',
				'active_callback' => 'read_more_show_related_posts',
				'priority'        => 20,
			)
		);
	}
endif;
add_action( 'customize_register', 'wow_blog_customize_register' );

/**
 * require int.
 */
require trailingslashit( get_stylesheet_directory() ) . 'acmethemes/wow-blog-init.php';
