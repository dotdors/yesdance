<?php
/**
 * REST API Endpoints for Locations and Related Posts – App Optimized
 */

// -----------------------------------------------------------------------------
// Locations with meta only (paginated)
// -----------------------------------------------------------------------------
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/locations', array(
        'methods'             => 'GET',
        'callback'            => 'ds_get_locations_app',
        'permission_callback' => '__return_true', // Replace with auth callback if needed
        'args'                => array(
            'page'     => array('default' => 1, 'sanitize_callback' => 'absint'),
            'per_page' => array('default' => 20, 'sanitize_callback' => 'absint'),
            'debug'    => array('default' => false),
        ),
    ));
});

function ds_get_locations_app(WP_REST_Request $request) {
    $page     = $request->get_param('page');
    $per_page = $request->get_param('per_page');
    $debug    = $request->get_param('debug');

    $args = [
        'post_type'      => 'ds_location',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'post_status'    => 'publish',
    ];

    $query     = new WP_Query($args);
    $locations = $query->posts;

    $data = [];
    foreach ($locations as $location) {
        $data[] = [
            'id'          => $location->ID,
            'title'       => get_the_title($location),
            'name'        => get_post_meta($location->ID, '_ds_location_name', true),
            'address'     => get_post_meta($location->ID, '_ds_location_address', true),
            'phone'       => get_post_meta($location->ID, '_ds_location_phone', true),
            'email'       => get_post_meta($location->ID, '_ds_location_email', true),
            'contact'     => get_post_meta($location->ID, '_ds_location_contact_name', true),
            'description' => get_post_meta($location->ID, '_ds_location_description', true),
        ];
    }

    $response = [
        'page'       => $page,
        'per_page'   => $per_page,
        'total'      => (int) $query->found_posts,
        'total_pages'=> (int) $query->max_num_pages,
        'locations'  => $data,
    ];

    if ($debug) {
        $response['ids'] = wp_list_pluck($locations, 'ID');
    }

    return rest_ensure_response($response);
}

// -----------------------------------------------------------------------------
// Locations with related posts (paginated)
// -----------------------------------------------------------------------------
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/posts_by_location', array(
        'methods'             => 'GET',
        'callback'            => 'ds_get_posts_by_location_app',
        'permission_callback' => '__return_true', // Replace with auth callback if needed
        'args'                => array(
            'per_location' => array('default' => 10, 'sanitize_callback' => 'absint'), // Max posts per location
        ),
    ));
});

function ds_get_posts_by_location_app(WP_REST_Request $request) {
    $per_location = $request->get_param('per_location');

    $terms = get_terms([
        'taxonomy'   => 'ds_post_location',
        'hide_empty' => false,
    ]);

    $data = [];
    foreach ($terms as $term) {
        $posts = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => $per_location,
            'post_status'    => 'publish',
            'tax_query'      => [
                [
                    'taxonomy' => 'ds_post_location',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ]
            ],
        ]);

        $posts_data = [];
        foreach ($posts as $p) {
            $posts_data[] = [
                'id'    => $p->ID,
                'title' => get_the_title($p),
                'url'   => get_permalink($p),
            ];
        }

        $data[] = [
            'term_id'   => $term->term_id,
            'name'      => $term->name,
            'slug'      => $term->slug,
            'post_count'=> $term->count,
            'posts'     => $posts_data,
        ];
    }

    return rest_ensure_response($data);
}
