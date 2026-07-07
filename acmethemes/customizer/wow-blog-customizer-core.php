<?php
/**
 * Altering/Adding default theme options
 *
 * @since WOW Blog 1.0.0
 *
 * @param null
 * @return array $wow_blog_theme_layout
 *
 */
if ( !function_exists('wow_blog_added_default_theme_options') ) :
    function wow_blog_added_default_theme_options( $read_more_default_theme_options ) {

        $read_more_default_theme_options['read-more-primary-color'] = '#4fbe6e';
        $read_more_default_theme_options['read-more-button-design'] = 'normal';
        $read_more_default_theme_options['read-more-header-logo-ads-display-position'] = 'left-logo-right-ads';
        $read_more_default_theme_options['read-more-related-title'] = __( 'Related posts', 'wow-blog' );
        $read_more_default_theme_options['read-more-blog-archive-layout'] = 'left-image';


        return $read_more_default_theme_options;
    }
endif;
add_filter( 'read_more_default_theme_options', 'wow_blog_added_default_theme_options', 10, 1 );