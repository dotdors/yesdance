<?php
/**
 * REST API Endpoints for Locations and Related Posts (with Debugging)
 */

// -----------------------------------------------------------------------------
// Locations with meta only
// -----------------------------------------------------------------------------
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/locations', array(
        'methods'             => 'GET',
        'callback'            => 'ds_get_locations',
        'permission_callback' => '__return_true',
    ));
});

function ds_get_locations(WP_REST_Request $request) {
    $debug = $request->get_param('debug');

    $locations = get_posts(array(
        'post_type'      => 'ds_location',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ));

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

    if ($debug) {
        return [
            'count'     => count($locations),
            'ids'       => wp_list_pluck($locations, 'ID'),
            'data'      => $data,
        ];
    }

    return $data;
}

// -----------------------------------------------------------------------------
// Locations with related posts
// -----------------------------------------------------------------------------
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/locations-with-posts', array(
        'methods'             => 'GET',
        'callback'            => 'ds_get_locations_with_posts',
        'permission_callback' => '__return_true',
    ));
});

function ds_get_locations_with_posts(WP_REST_Request $request) {
    $debug = $request->get_param('debug');

    $locations = get_posts(array(
        'post_type'      => 'ds_location',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ));

    $data = [];

    foreach ($locations as $location) {
        $posts = get_posts(array(
            'post_type'      => 'post',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'ds_post_location',
                    'field'    => 'term_id',
                    'terms'    => $location->ID,
                ),
            ),
        ));

        $posts_data = [];
        foreach ($posts as $p) {
            $posts_data[] = [
                'id'    => $p->ID,
                'title' => get_the_title($p),
                'url'   => get_permalink($p),
                'date'  => get_the_date('', $p),
                'excerpt' => wp_trim_words($p->post_content, 30),
            ];
        }

        $data[] = [
            'id'          => $location->ID,
            'title'       => get_the_title($location),
            'name'        => get_post_meta($location->ID, '_ds_location_name', true),
            'address'     => get_post_meta($location->ID, '_ds_location_address', true),
            'phone'       => get_post_meta($location->ID, '_ds_location_phone', true),
            'email'       => get_post_meta($location->ID, '_ds_location_email', true),
            'contact'     => get_post_meta($location->ID, '_ds_location_contact_name', true),
            'description' => get_post_meta($location->ID, '_ds_location_description', true),
            'posts'       => $posts_data,
        ];
    }

    if ($debug) {
        return [
            'count'        => count($locations),
            'ids'          => wp_list_pluck($locations, 'ID'),
            'data'         => $data,
        ];
    }

    return $data;
}
