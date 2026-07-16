<?php
/**
 * Register custom block patterns for Locations
 */

add_action('init', function() {
    if ( ! function_exists('register_block_pattern_category') ) {
        return; // Gutenberg not active
    }

    // Register category
    register_block_pattern_category(
        'ds-location',
        array('label' => __('Location Templates', 'ds-location'))
    );

    // Register a pattern for location landing pages
    register_block_pattern(
        'ds-location/landing-page',
        array(
            'title'       => __('Location Landing Page', 'ds-location'),
            'description' => __('Standard template for Location landing pages with header, description, and posts list.', 'ds-location'),
            'categories'  => array('ds-location'),
            'content'     => '
                <!-- wp:group {"tagName":"section"} -->
                <section class="ds-location-landing">
                    <!-- wp:heading --> <h2>Location Name</h2> <!-- /wp:heading -->
                    <!-- wp:paragraph --> <p>Address, phone, contact info here.</p> <!-- /wp:paragraph -->
                    <!-- wp:separator /-->
                    <!-- wp:heading {"level":3} --> <h3>Latest Updates</h3> <!-- /wp:heading -->
                    <!-- wp:query {"query":{"postType":"post","taxQuery":[{"taxonomy":"ds_post_location","field":"term_id","terms":["123"]}]}} -->
                    <div class="wp-block-query"></div>
                    <!-- /wp:query -->
                </section>
                <!-- /wp:group -->
            ',
        )
    );
});
