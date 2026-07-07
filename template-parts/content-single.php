<?php
/**
 * Template part for displaying single posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Acme Themes
 * @subpackage Read More
 */

global $read_more_customizer_all_values;
$read_more_single_image_layout = $read_more_customizer_all_values['read-more-single-image-layout'];
?>
<div class="entry-meta entry-meta-after-title">
	<?php read_more_posted_on( 120 ); ?>
</div><!-- .entry-meta -->

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	$sidebar_layout = read_more_sidebar_selection();
	if( has_post_thumbnail() && 'disable' != $read_more_single_image_layout ):
		echo '<div class="single-feat clearfix"><figure class="single-thumb single-thumb-full">';
		the_post_thumbnail( $read_more_single_image_layout );
		echo "</figure></div>";/*figure, .single-feat*/
	endif;
	$meta_float = 'pull-left';
	$content_float = 'pull-right';
	if( $sidebar_layout == "left-sidebar" ) :
		$meta_float = 'pull-right';
		$content_float = 'pull-left';
	endif;
	?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 <?php echo esc_attr( $content_float ); ?>">
                <div class="entry-content">
					<?php
					the_content();
					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wow-blog' ),
						'after'  => '</div>',
					) );
					read_more_entry_footer();
					?>
                </div><!-- .entry-content -->
            </div>
        </div>
	    <?php
	    /**
	     * read_more_related_posts hook
	     * @since Read More 1.0.0
	     *
	     * @hooked read_more_related_posts_left -  10
	     */
	    do_action( 'read_more_related_posts' ,get_the_ID() );
	    ?>
    </div>
</article><!-- #post-## -->