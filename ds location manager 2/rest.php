<?php
/**
 * REST API Endpoints for Locations and Related Posts – App Optimized
 * 
 * ENDPOINTS:
 * - GET /ds/v1/locations - All locations with meta (paginated)
 * - GET /ds/v1/posts_by_location - Posts grouped by location
 * - GET /ds/v1/locations/{id}/posts - Posts for specific location (with full content)
 * - GET /ds/v1/posts/{id} - Single post with full content (NEW for mobile app)
 * 
 * MOBILE APP UPDATES:
 * - Posts now include 'content' field (full HTML via apply_filters('the_content'))
 * - Posts include 'sticky' flag for featured content
 * - Dates in ISO 8601 format (YYYY-MM-DDTHH:MM:SS)
 * - Featured images use 'large' size for better mobile quality
 * - Proper excerpt generation via get_the_excerpt()
 */

// -----------------------------------------------------------------------------
// Register REST fields for new location meta
// -----------------------------------------------------------------------------
add_action('rest_api_init', function() {
    // Add new fields to location REST response
    $new_fields = array(
        'logo_id' => array(
            'meta_key' => '_ds_location_logo',
            'description' => 'Location logo attachment ID',
            'type' => 'integer'
        ),
        'logo_url' => array(
            'meta_key' => '_ds_location_logo',
            'description' => 'Location logo URL',
            'type' => 'string',
            'get_callback' => function($post) {
                $logo_id = get_post_meta($post['id'], '_ds_location_logo', true);
                return $logo_id ? wp_get_attachment_url($logo_id) : '';
            }
        ),
        'city' => array(
            'meta_key' => '_ds_location_city',
            'description' => 'City name',
            'type' => 'string'
        ),
        'yycd_description' => array(
            'meta_key' => '_ds_location_yycd_description',
            'description' => 'YYCD Program Description',
            'type' => 'string'
        ),
        'latitude' => array(
            'meta_key' => '_ds_location_latitude',
            'description' => 'Latitude coordinate',
            'type' => 'string'
        ),
        'longitude' => array(
            'meta_key' => '_ds_location_longitude',
            'description' => 'Longitude coordinate',
            'type' => 'string'
        )
    );

    foreach ($new_fields as $field_name => $config) {
        $args = array(
            'description' => $config['description'],
            'type' => $config['type'],
            'context' => array('view', 'edit'),
            'schema' => array(
                'description' => $config['description'],
                'type' => $config['type']
            )
        );

        // Custom get callback for logo_url
        if (isset($config['get_callback'])) {
            $args['get_callback'] = $config['get_callback'];
        } else {
            $args['get_callback'] = function($post) use ($config) {
                return get_post_meta($post['id'], $config['meta_key'], true);
            };
        }

        // Update callback
        if ($field_name !== 'logo_url') { // logo_url is read-only, derived from logo_id
            $args['update_callback'] = function($value, $post) use ($config) {
                if ($config['type'] === 'integer') {
                    $value = intval($value);
                } else {
                    $value = sanitize_text_field($value);
                }
                return update_post_meta($post->ID, $config['meta_key'], $value);
            };
        }

        register_rest_field('ds_location', $field_name, $args);
    }
});

// -----------------------------------------------------------------------------
// Locations with meta only (paginated)
// -----------------------------------------------------------------------------
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/locations', array(
        'methods'             => 'GET',
        'callback'            => 'ds_get_locations_app',
        'permission_callback' => '__return_true',
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
        $loc = DS_Location_Data::get_all($location->ID);

        $entry = [
            'id'               => $location->ID,
            'title'            => get_the_title($location),
            'name'             => $loc['name'],
            'city'             => $loc['city'],
            'address'          => $loc['address'],
            'phone'            => $loc['phone'],
            'email'            => $loc['email'],
            'contact'          => $loc['contact_name'],
            'description'      => $loc['description'],
            'yycd_description' => $loc['yycd_description'],
            'logo_id'          => $loc['logo'] ? intval($loc['logo']) : null,
            'logo_url'         => $loc['logo_url_medium'],
            'latitude'         => $loc['latitude'],
            'longitude'        => $loc['longitude'],
            'website'          => $loc['website'],
            'page_url'         => get_permalink($location->ID),
            'flyer_url'        => $loc['flyer_url'],
        ];

        if ($loc['text_phone']) {
            $entry['text_phone'] = $loc['text_phone']; // omitted entirely when unset — app falls back to `phone`
        }

        $data[] = $entry;
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
        'permission_callback' => '__return_true',
        'args'                => array(
            'per_location' => array('default' => 10, 'sanitize_callback' => 'absint'),
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

// -----------------------------------------------------------------------------
// Get posts for a single location
// -----------------------------------------------------------------------------
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/locations/(?P<id>\d+)/posts', array(
        'methods'             => 'GET',
        'callback'            => 'ds_get_posts_for_location',
        'permission_callback' => '__return_true',
        'args'                => array(
            'per_page' => array('default' => 10, 'sanitize_callback' => 'absint'),
            'page'     => array('default' => 1, 'sanitize_callback' => 'absint'),
        ),
    ));
});

function ds_get_posts_for_location(WP_REST_Request $request) {
    $location_id = absint($request['id']);
    $page        = $request->get_param('page');
    $per_page    = $request->get_param('per_page');

    // get location post
    $location = get_post($location_id);
    if (!$location || $location->post_type !== 'ds_location') {
        return new WP_Error('not_found', 'Location not found', array('status' => 404));
    }

    // get taxonomy term tied to this location (assumes same slug/title convention)
    $terms = get_terms([
        'taxonomy'   => 'ds_post_location',
        'slug'       => sanitize_title($location->post_name),
        'hide_empty' => false,
    ]);

    if (empty($terms) || is_wp_error($terms)) {
        return [
            'location' => [
                'id'    => $location->ID,
                'title' => get_the_title($location),
                'posts' => [],
            ]
        ];
    }

    $term = $terms[0];

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'post_status'    => 'publish',
        'tax_query'      => [
            [
                'taxonomy' => 'ds_post_location',
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            ]
        ],
    ];

    $query = new WP_Query($args);

    $posts_data = [];
    foreach ($query->posts as $p) {
        setup_postdata($p); // Ensure post context is set
        
        $posts_data[] = [
            'id'             => $p->ID,
            'title'          => get_the_title($p),
            'url'            => get_permalink($p),
            'excerpt'        => get_the_excerpt($p),
            'content'        => apply_filters('the_content', $p->post_content), // Full HTML content
            'date'           => get_the_date('c', $p), // ISO 8601 format for apps
            'featured_image' => get_the_post_thumbnail_url($p, 'large'),
            'sticky'         => is_sticky($p->ID),
        ];
    }
    wp_reset_postdata();

    return [
        'location' => [
            'id'          => $location->ID,
            'title'       => get_the_title($location),
            'address'     => get_post_meta($location->ID, '_ds_location_address', true),
            'phone'       => get_post_meta($location->ID, '_ds_location_phone', true),
            'email'       => get_post_meta($location->ID, '_ds_location_email', true),
            'description' => get_post_meta($location->ID, '_ds_location_description', true),
        ],
        'posts' => [
            'page'        => $page,
            'per_page'    => $per_page,
            'total'       => (int) $query->found_posts,
            'total_pages' => (int) $query->max_num_pages,
            'items'       => $posts_data,
        ],
    ];
}

// -----------------------------------------------------------------------------
// Get single post (for deep-linking or on-demand fetching)
// -----------------------------------------------------------------------------
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/posts/(?P<id>\d+)', array(
        'methods'             => 'GET',
        'callback'            => 'ds_get_single_post',
        'permission_callback' => '__return_true',
    ));
});

function ds_get_single_post(WP_REST_Request $request) {
    $post_id = absint($request['id']);
    
    // Get the post
    $post = get_post($post_id);
    
    // Verify post exists and is published
    if (!$post || $post->post_status !== 'publish' || $post->post_type !== 'post') {
        return new WP_Error('not_found', 'Post not found', array('status' => 404));
    }
    
    setup_postdata($post);
    
    $post_data = [
        'id'             => $post->ID,
        'title'          => get_the_title($post),
        'url'            => get_permalink($post),
        'excerpt'        => get_the_excerpt($post),
        'content'        => apply_filters('the_content', $post->post_content),
        'date'           => get_the_date('c', $post),
        'featured_image' => get_the_post_thumbnail_url($post, 'large'),
        'sticky'         => is_sticky($post->ID),
    ];
    
    wp_reset_postdata();
    
    return rest_ensure_response([
        'success' => true,
        'data'    => $post_data,
    ]);
}
