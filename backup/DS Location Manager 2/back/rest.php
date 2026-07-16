<?php
/**
 * Register custom REST API endpoint for Locations with Posts
 */
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/locations-with-posts', array(
        'methods'  => 'GET',
        'callback' => 'ds_get_locations_with_posts',
        'permission_callback' => '__return_true', // 🔒 Public for read-only. Use auth if you need private data
    ));
});

/**
 * Callback: Get all Locations with their related Posts
 */
function ds_get_locations_with_posts(WP_REST_Request $request) {
    $locations = get_posts(array(
        'post_type'      => 'ds_location',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ));

    $results = array();

    foreach ($locations as $location) {
        $location_id = $location->ID;

        // Collect meta fields
        $meta = array(
            'address'       => get_post_meta($location_id, 'ds_address', true),
            'phone'         => get_post_meta($location_id, 'ds_phone', true),
            'email'         => get_post_meta($location_id, 'ds_email', true),
            'contact_name'  => get_post_meta($location_id, 'ds_contact_name', true),
            'description'   => get_post_meta($location_id, 'ds_description', true),
        );

        // Fetch related posts by taxonomy
        $posts = get_posts(array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 5, // 🔧 Adjust as needed or make dynamic
            'tax_query'      => array(
                array(
                    'taxonomy' => 'ds_post_location',
                    'field'    => 'term_id',
                    'terms'    => $location_id,
                ),
            ),
        ));

        // Map posts
        $post_data = array_map(function($p) {
            return array(
                'id'      => $p->ID,
                'date'    => $p->post_date,
                'title'   => get_the_title($p),
                'excerpt' => wp_trim_words($p->post_content, 40),
                'link'    => get_permalink($p),
            );
        }, $posts);

        // Build location result
        $results[] = array(
            'id'          => $location_id,
            'title'       => get_the_title($location_id),
            'meta'        => $meta,
            'posts'       => $post_data,
        );
    }

    return rest_ensure_response($results);
}