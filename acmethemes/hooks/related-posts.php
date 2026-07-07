<?php
/**
 * Display related posts from same category
 *
 * @since Read More 1.0.0
 *
 * @param int $post_id
 * @return void
 *
 */
if ( !function_exists('read_more_related_posts_left') ) :

    function read_more_related_posts_left( $post_id ) {

        global $read_more_customizer_all_values;
        if( 0 == $read_more_customizer_all_values['read-more-show-related'] ){
            return;
        }
        $categories = get_the_category( $post_id );
        if ($categories) {
            $category_ids = array();
            foreach ($categories as $category) {
                $category_ids[] = $category->term_id;
            }
            ?>
            <div class="related-post-wrapper">
                <h2 class="widget-title">
                    <?php _e('Related posts', 'wow-blog'); ?>
                </h2>
                <div class="row">
                    <?php
                    $read_more_cat_post_args = array(
                        'category__in'       => $category_ids,
                        'post__not_in'       => array($post_id),
                        'post_type'          => 'post',
                        'posts_per_page'     => 3,
                        'post_status'        => 'publish',
                        'ignore_sticky_posts'=> true
                    );
                    $read_more_featured_query = new WP_Query( $read_more_cat_post_args );

                    while ( $read_more_featured_query->have_posts() ) : $read_more_featured_query->the_post();
                        ?>
                        <div class="blog-item col-sm-12 col-md-3">
                            <?php get_template_part( 'template-parts/content', 'recents' );?>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
            <?php
        }
    }
endif;
add_action( 'read_more_related_posts', 'read_more_related_posts_left', 10, 1 );