<?php
/**
 * Plugin Name: DS Location Manager 2
 * Description: Manage locations with dedicated pages and posts, including user access control and block patterns
 * Version: 2.0.2
 * Author: NC Dorsner
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Location_Manager_V2 {

    private $version = '2.0.1-fixed';

    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    public function init() {
        $this->register_location_post_type();
        $this->register_location_taxonomy();
        $this->create_location_manager_role();
        $this->setup_access_control();
        $this->setup_admin_customizations();
        $this->setup_rest_api();
        $this->register_block_patterns();
        $this->setup_block_categories();
    }

    /**
     * Register Location Custom Post Type with Enhanced Block Template
     */
    public function register_location_post_type() {
        $args = array(
            'label' => 'Locations',
            'labels' => array(
                'name' => 'Locations',
                'singular_name' => 'Location',
                'menu_name' => 'Locations',
                'add_new' => 'Add New Location',
                'add_new_item' => 'Add New Location',
                'edit_item' => 'Edit Location',
                'new_item' => 'New Location',
                'view_item' => 'View Location',
                'search_items' => 'Search Locations',
                'not_found' => 'No locations found',
                'not_found_in_trash' => 'No locations found in trash'
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'rest_base' => 'locations',
            'query_var' => true,
            'rewrite' => array('slug' => 'location'),
            'capability_type' => array('location', 'locations'),
            'map_meta_cap' => true,
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-location-alt',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'),
            'template_lock' => false
        );

        register_post_type('ds_location', $args);

        // Add custom fields hooks (meta boxes)
        add_action('add_meta_boxes', array($this, 'add_location_meta_boxes'));
        add_action('save_post', array($this, 'save_location_meta'));
    }

    /**
     * Register Location Taxonomy for Posts
     */
    public function register_location_taxonomy() {
        $args = array(
            'labels' => array(
                'name' => 'Post Locations',
                'singular_name' => 'Post Location',
                'search_items' => 'Search Locations',
                'all_items' => 'All Locations',
                'edit_item' => 'Edit Location',
                'update_item' => 'Update Location',
                'add_new_item' => 'Add New Location',
                'new_item_name' => 'New Location Name',
                'menu_name' => 'Post Locations',
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
            'rest_base' => 'post-locations',
            'capabilities' => array(
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts'
            )
        );

        register_taxonomy('ds_post_location', array('post'), $args);
    }

    /**
     * Ensure there is a 1:1 term for a location post and return the term_id.
     * Stores _ds_taxonomy_term_id on the location post for fast lookup.
     */
    private function ensure_term_for_location($location_post_id) {
        $location_post_id = intval($location_post_id);
        if (!$location_post_id || !taxonomy_exists('ds_post_location')) {
            return 0;
        }

        // Check post meta for cached term id
        $cached_term = get_post_meta($location_post_id, '_ds_taxonomy_term_id', true);
        if ($cached_term) {
            $term = get_term_by('id', intval($cached_term), 'ds_post_location');
            if ($term && !is_wp_error($term)) {
                return intval($term->term_id);
            }
            // If cached term doesn't exist anymore, clear it and continue to recreate
            delete_post_meta($location_post_id, '_ds_taxonomy_term_id');
        }

        // Try to find a term by slug (use post_name)
        $location = get_post($location_post_id);
        if (!$location) {
            return 0;
        }

        $slug = $location->post_name ?: 'location-' . $location_post_id;
        $term = get_term_by('slug', $slug, 'ds_post_location');
        if ($term && !is_wp_error($term)) {
            update_post_meta($location_post_id, '_ds_taxonomy_term_id', intval($term->term_id));
            return intval($term->term_id);
        }

        // Create a new term for this location
        $result = wp_insert_term($location->post_title, 'ds_post_location', array('slug' => $slug));
        if (is_wp_error($result)) {
            return 0;
        }

        if (!empty($result['term_id'])) {
            $term_id = intval($result['term_id']);
            update_post_meta($location_post_id, '_ds_taxonomy_term_id', $term_id);
            return $term_id;
        }

        return 0;
    }

    private function get_term_id_for_location($location_post_id) {
        return $this->ensure_term_for_location($location_post_id);
    }

    /**
     * Create Location Manager Role with Simplified Permissions
     */
    public function create_location_manager_role() {
        remove_role('ds_location_manager');

        add_role('ds_location_manager', 'Location Manager', array(
            // Basic WordPress capabilities
            'read' => true,
            'upload_files' => true,

            // Post capabilities (limited by location)
            'edit_posts' => true,
            'edit_published_posts' => true,
            'publish_posts' => true,
            'delete_posts' => true,
            'delete_published_posts' => true,
            'edit_others_posts'    => true,
            'delete_others_posts'  => true,
            'read_private_posts'   => true,

            // Location capabilities
            'edit_location' => true,
            'edit_locations' => true,
            'edit_published_locations' => true,
            'publish_locations' => true,
            'read_location' => true,
            'read_private_locations' => true,
            'edit_others_locations' => true,
            'delete_locations' => true,
            'delete_others_locations' => true,
            'delete_published_locations' => true,
            'delete_private_locations' => true,

            // Media
            'edit_files' => true,

            // Taxonomy
            'assign_terms' => true
        ));

        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_caps = array(
                'edit_location', 'edit_locations', 'edit_others_locations',
                'edit_published_locations', 'edit_private_locations', 'publish_locations',
                'read_location', 'read_private_locations', 'delete_location',
                'delete_locations', 'delete_others_locations', 'delete_published_locations',
                'delete_private_locations'
            );
            foreach ($admin_caps as $cap) {
                $admin_role->add_cap($cap);
            }
        }
    }

    /**
     * Setup access control: admin filters, REST-friendly insert filter and saves
     */
    public function setup_access_control() {
        add_action('pre_get_posts', array($this, 'filter_posts_by_location'));
        add_filter('map_meta_cap', array($this, 'map_location_meta_caps'), 10, 4);
        add_action('save_post', array($this, 'auto_assign_post_location'));
        add_filter('wp_insert_post_data', array($this, 'restrict_location_manager_posts'), 10, 2);
        add_action('admin_init', array($this, 'restrict_location_manager_access'));
    }

    /**
     * Restrict location managers to only their assigned content in admin lists
     */
    public function filter_posts_by_location($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        $user = wp_get_current_user();
        if (!in_array('ds_location_manager', (array) $user->roles)) {
            return;
        }

        $user_location = get_user_meta($user->ID, 'ds_assigned_location', true);
        if (!$user_location) {
            $query->set('post__in', array(0));
            return;
        }

        // get screen id safely
        $screen_id = '';
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && isset($screen->id)) {
                $screen_id = $screen->id;
            }
        }

        // if the user intentionally filtered to "Mine" (author = current user), let it through
        $author_q = $query->get('author');
        if (!empty($author_q) && intval($author_q) === intval($user->ID)) {
            return;
        }

        // For posts: convert location post ID to term ID and filter
        if ($query->get('post_type') === 'post' || $screen_id === 'edit-post') {
            if (taxonomy_exists('ds_post_location')) {
                $term_id = $this->get_term_id_for_location($user_location);
                if ($term_id) {
                    $tax_query = array(
                        array(
                            'taxonomy' => 'ds_post_location',
                            'field'    => 'term_id',
                            'terms'    => array(intval($term_id)),
                        )
                    );
                    $query->set('tax_query', $tax_query);
                } else {
                    // No mapped term — show none to avoid leakage
                    $query->set('post__in', array(0));
                }
            } else {
                // fallback to category: assume user_location stores category ID
                $query->set('cat', intval($user_location));
            }
        }

        // For ds_location CPT — only show the assigned location post
        if ($query->get('post_type') === 'ds_location' || $screen_id === 'edit-ds_location') {
            $query->set('post__in', array(intval($user_location)));
        }
    }

    /**
     * Enhanced capability mapping
     */
    public function map_location_meta_caps($caps, $cap, $user_id, $args) {
        if (strpos($cap, 'location') !== false) {
            $user = get_userdata($user_id);
            if (!$user) return $caps;

            if (in_array('administrator', (array) $user->roles)) {
                return array('exist');
            }

            if (in_array('ds_location_manager', (array) $user->roles)) {
                $user_location = get_user_meta($user_id, 'ds_assigned_location', true);
                if (isset($args[0])) {
                    $post_id = $args[0];
                    if ($user_location && $post_id == $user_location) {
                        return array('exist');
                    }
                } elseif ($cap === 'edit_locations' || $cap === 'edit_location') {
                    return $user_location ? array('exist') : $caps;
                }
            }

            return array('do_not_allow');
        }

        return $caps;
    }

    /**
     * Auto-assign a location taxonomy term to a post when saved by a Location Manager
     */
    public function auto_assign_post_location($post_id) {
        if (get_post_type($post_id) !== 'post') {
            return;
        }

        // Skip if this is an auto-save or revision
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        $user = wp_get_current_user();
        if (!in_array('ds_location_manager', (array) $user->roles)) {
            return;
        }

        $user_location = get_user_meta($user->ID, 'ds_assigned_location', true);
        if (empty($user_location)) {
            return;
        }

        // Map location post -> term (create if missing)
        $term_id = $this->get_term_id_for_location($user_location);
        if ($term_id) {
            wp_set_post_terms($post_id, array($term_id), 'ds_post_location', false);
            return;
        }

        // Fallback: if taxonomy isn't available or mapping failed, try category by ID
        if (term_exists(intval($user_location), 'category')) {
            wp_set_post_terms($post_id, array(intval($user_location)), 'category', false);
        }
    }

    /**
     * Restrict what location managers can edit (REST-friendly)
     */
    public function restrict_location_manager_posts($data, $postarr = array()) {
        $user = wp_get_current_user();
        if (!in_array('ds_location_manager', (array) $user->roles)) {
            return $data;
        }

        $allowed_post_types = array('post');
        $user_location = get_user_meta($user->ID, 'ds_assigned_location', true);
        if ($user_location && isset($postarr['ID']) && $postarr['ID'] == $user_location) {
            $allowed_post_types[] = 'ds_location';
        }

        // determine post_type robustly
        $post_type = '';
        if (!empty($data['post_type'])) {
            $post_type = $data['post_type'];
        } elseif (!empty($postarr['post_type'])) {
            $post_type = $postarr['post_type'];
        }

        if (empty($post_type)) {
            return $data;
        }

        if (in_array($post_type, $allowed_post_types)) {
            return $data;
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return $data;
        }

        if (is_admin()) {
            wp_die(__('You do not have permission to edit this content type.', 'ds-location-manager'));
        }

        return $data;
    }

    /**
     * Additional admin restrictions
     */
    public function restrict_location_manager_access() {
        $user = wp_get_current_user();
        if (!in_array('ds_location_manager', (array) $user->roles)) {
            return;
        }

        global $pagenow;
        $forbidden_pages = array(
            'themes.php', 'plugins.php', 'tools.php', 'options-general.php',
            'edit-comments.php', 'users.php', 'user-new.php'
        );

        if (in_array($pagenow, $forbidden_pages)) {
            wp_redirect(admin_url('edit.php'));
            exit;
        }
    }

    /**
     * Enhanced location meta boxes
     */
    public function add_location_meta_boxes() {
        add_meta_box(
            'ds_location_details',
            'Location Details',
            array($this, 'location_details_meta_box'),
            'ds_location',
            'side',
            'high'
        );

        add_meta_box(
            'ds_location_stats',
            'Location Statistics',
            array($this, 'location_stats_meta_box'),
            'ds_location',
            'side',
            'default'
        );
    }

    public function location_details_meta_box($post) {
        wp_nonce_field('ds_location_meta', 'ds_location_meta_nonce');

        $location_name = get_post_meta($post->ID, '_ds_location_name', true) ?: $post->post_title;
        $address = get_post_meta($post->ID, '_ds_location_address', true);
        $phone = get_post_meta($post->ID, '_ds_location_phone', true);
        $email = get_post_meta($post->ID, '_ds_location_email', true);
        $contact_name = get_post_meta($post->ID, '_ds_location_contact_name', true);
        $description = get_post_meta($post->ID, '_ds_location_description', true);
        ?>
        <style>
            .ds-location-field { margin-bottom: 15px; }
            .ds-location-field label { display: block; font-weight: bold; margin-bottom: 3px; }
            .ds-location-field input, .ds-location-field textarea { width: 100%; }
            .ds-location-field textarea { height: 60px; resize: vertical; }
        </style>

        <div class="ds-location-field">
            <label for="ds_location_name">Location Name:</label>
            <input type="text" id="ds_location_name" name="ds_location_name" value="<?php echo esc_attr($location_name); ?>" />
        </div>

        <div class="ds-location-field">
            <label for="ds_location_address">Address:</label>
            <textarea id="ds_location_address" name="ds_location_address"><?php echo esc_textarea($address); ?></textarea>
        </div>

        <div class="ds-location-field">
            <label for="ds_location_phone">Phone:</label>
            <input type="text" id="ds_location_phone" name="ds_location_phone" value="<?php echo esc_attr($phone); ?>" />
        </div>

        <div class="ds-location-field">
            <label for="ds_location_email">Email:</label>
            <input type="email" id="ds_location_email" name="ds_location_email" value="<?php echo esc_attr($email); ?>" />
        </div>

        <div class="ds-location-field">
            <label for="ds_location_contact_name">Contact Name:</label>
            <input type="text" id="ds_location_contact_name" name="ds_location_contact_name" value="<?php echo esc_attr($contact_name); ?>" />
        </div>

        <div class="ds-location-field">
            <label for="ds_location_description">Short Description:</label>
            <textarea id="ds_location_description" name="ds_location_description"><?php echo esc_textarea($description); ?></textarea>
        </div>
        <?php
    }

    public function location_stats_meta_box($post) {
        $term_id = 0;
        if (taxonomy_exists('ds_post_location')) {
            $term_id = $this->get_term_id_for_location($post->ID);
        }

        $location_posts = array();
        if ($term_id) {
            $location_posts = get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'ds_post_location',
                        'field' => 'term_id',
                        'terms' => $term_id
                    )
                ),
                'fields' => 'ids'
            ));
        }

        $total_posts = count($location_posts);
        $recent_posts = array();
        if ($term_id) {
            $recent_posts = get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => 5,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'ds_post_location',
                        'field' => 'term_id',
                        'terms' => $term_id
                    )
                )
            ));
        }

        echo '<p>Total posts for this location: ' . esc_html($total_posts) . '</p>';
        if (!empty($recent_posts)) {
            echo '<ul>';
            foreach ($recent_posts as $rp) {
                echo '<li><a href="' . esc_url(get_edit_post_link($rp)) . '">' . esc_html(get_the_title($rp)) . '</a></li>';
            }
            echo '</ul>';
        }
    }

    /**
     * Enhanced save location meta
     */
    public function save_location_meta($post_id) {
        if (!isset($_POST['ds_location_meta_nonce']) || !wp_verify_nonce($_POST['ds_location_meta_nonce'], 'ds_location_meta')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (get_post_type($post_id) !== 'ds_location') {
            return;
        }

        $fields = array('name', 'address', 'phone', 'email', 'contact_name', 'description');

        foreach ($fields as $field) {
            if (isset($_POST['ds_location_' . $field])) {
                $value = sanitize_text_field($_POST['ds_location_' . $field]);
                if ($field === 'address' || $field === 'description') {
                    $value = sanitize_textarea_field($_POST['ds_location_' . $field]);
                }
                update_post_meta($post_id, '_ds_location_' . $field, $value);
            }
        }

        // Store location ID for easier querying
        update_post_meta($post_id, '_ds_location_id', $post_id);

        // Create or update taxonomy term for this location
        $this->sync_location_taxonomy($post_id);
    }

    /**
     * Sync location with taxonomy (create/update term and store mapping)
     */
    private function sync_location_taxonomy($location_id) {
        $location = get_post($location_id);
        if (!$location || !taxonomy_exists('ds_post_location')) return;

        // Try to get existing mapped term id
        $term_id = get_post_meta($location_id, '_ds_taxonomy_term_id', true);
        if ($term_id && get_term($term_id) && !is_wp_error(get_term($term_id))) {
            // ensure term name/slug are up to date
            wp_update_term($term_id, 'ds_post_location', array(
                'name' => $location->post_title,
                'slug' => $location->post_name
            ));
            return;
        }

        // Look up by slug
        $term = get_term_by('slug', $location->post_name, 'ds_post_location');
        if ($term && !is_wp_error($term)) {
            update_post_meta($location_id, '_ds_taxonomy_term_id', intval($term->term_id));
            return;
        }

        // Insert new term
        $result = wp_insert_term(
            $location->post_title,
            'ds_post_location',
            array('slug' => $location->post_name)
        );

        if (!is_wp_error($result) && !empty($result['term_id'])) {
            update_post_meta($location_id, '_ds_taxonomy_term_id', intval($result['term_id']));
        }
    }

    /**
     * Simplified admin menu for location managers
     */
    public function customize_admin_menu() {
        $user = wp_get_current_user();
        if (!in_array('ds_location_manager', (array) $user->roles)) {
            return;
        }

        // Remove menu items
        $remove_menus = array(
            'edit-comments.php',
            'themes.php',
            'plugins.php',
            'tools.php',
            'options-general.php',
            'users.php',
            'edit.php?post_type=page'
        );

        foreach ($remove_menus as $menu) {
            remove_menu_page($menu);
        }

        global $menu;
        foreach ($menu as $key => $item) {
            if ($item[2] === 'edit.php') {
                $menu[$key][0] = 'My Posts';
            }
            if ($item[2] === 'edit.php?post_type=ds_location') {
                $menu[$key][0] = 'My Location';
            }
        }

        add_menu_page(
            'Location Dashboard',
            'Dashboard',
            'read',
            'ds-location-dashboard',
            array($this, 'location_dashboard_page'),
            'dashicons-dashboard',
            2
        );
    }

    /**
     * Location Manager Dashboard
     */
    public function location_dashboard_page() {
        $user = wp_get_current_user();
        $user_location_id = get_user_meta($user->ID, 'ds_assigned_location', true);
        if (!$user_location_id) {
            echo '<div class="wrap"><h1>No Location Assigned</h1><p>Please contact an administrator to assign you to a location.</p></div>';
            return;
        }

        $location = get_post($user_location_id);
        if (!$location) {
            echo '<div class="wrap"><h1>Assigned location not found</h1></div>';
            return;
        }

        // Get mapped term id
        $term_id = 0;
        if (taxonomy_exists('ds_post_location')) {
            $term_id = $this->get_term_id_for_location($user_location_id);
        }

        $query_args = array(
            'post_type' => 'post',
            'posts_per_page' => 5,
        );
        if ($term_id) {
            $query_args['tax_query'] = array(array(
                'taxonomy' => 'ds_post_location',
                'field'    => 'term_id',
                'terms'    => array(intval($term_id))
            ));
        } else {
            // if no term mapping, ensure none returned
            $query_args['post__in'] = array(0);
        }

        $location_posts = get_posts($query_args);

        $my_posts_count = 0;
        if ($term_id) {
            $my_posts = get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'ds_post_location',
                        'field' => 'term_id',
                        'terms' => $term_id
                    )
                ),
                'fields' => 'ids'
            ));
            $my_posts_count = count($my_posts);
        }

        $total_posts = wp_count_posts('post')->publish;

        ?>
        <div class="wrap">
            <h1>Location Dashboard</h1>

            <div class="ds-location-dashboard">
                <style>
                    .ds-location-dashboard { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
                    .ds-dashboard-widget { background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 20px; }
                    .ds-dashboard-widget h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                    .ds-stats { display: flex; justify-content: space-between; margin-bottom: 20px; }
                    .ds-stat { text-align: center; padding: 15px; background: #f8f9fa; border-radius: 4px; }
                    .ds-stat strong { display: block; font-size: 24px; color: #1d2327; }
                    .ds-quick-actions a { display: inline-block; margin-right: 10px; margin-bottom: 10px; }
                    @media (max-width: 782px) { .ds-location-dashboard { grid-template-columns: 1fr; } }
                </style>

                <div class="ds-dashboard-widget">
                    <h3>Quick Stats</h3>
                    <div class="ds-stats">
                        <div class="ds-stat">
                            <strong><?php echo $my_posts_count; ?></strong>
                            <span>My Posts</span>
                        </div>
                        <div class="ds-stat">
                            <strong><?php echo count($location_posts); ?></strong>
                            <span>Recent Posts</span>
                        </div>
                    </div>

                    <div class="ds-quick-actions">
                        <a href="<?php echo admin_url('post-new.php'); ?>" class="button button-primary">New Post</a>
                        <a href="<?php echo get_edit_post_link($user_location_id); ?>" class="button">Edit Location Page</a>
                        <a href="<?php echo get_permalink($user_location_id); ?>" class="button" target="_blank">View Location Page</a>
                    </div>
                </div>

                <div class="ds-dashboard-widget">
                    <h3>Recent Posts</h3>
                    <?php if (!empty($location_posts)) : ?>
                        <ul>
                            <?php foreach ($location_posts as $post) : setup_postdata($post); ?>
                                <li>
                                    <a href="<?php echo get_edit_post_link($post->ID); ?>">
                                        <?php echo esc_html(get_the_title($post)); ?>
                                    </a>
                                    <small style="color: #666;"> - <?php echo get_post_status($post->ID); ?></small>
                                </li>
                            <?php endforeach; wp_reset_postdata(); ?>
                        </ul>
                        <p><a href="<?php echo admin_url('edit.php'); ?>">View all posts &rarr;</a></p>
                    <?php else : ?>
                        <p>No posts yet. <a href="<?php echo admin_url('post-new.php'); ?>">Create your first post</a>!</p>
                    <?php endif; ?>
                </div>

                <div class="ds-dashboard-widget">
                    <h3>Location Information</h3>
                    <?php
                    $address = get_post_meta($user_location_id, '_ds_location_address', true);
                    $phone = get_post_meta($user_location_id, '_ds_location_phone', true);
                    $email = get_post_meta($user_location_id, '_ds_location_email', true);
                    ?>

                    <?php if ($address) : ?>
                        <p><strong>Address:</strong><br><?php echo nl2br(esc_html($address)); ?></p>
                    <?php endif; ?>

                    <?php if ($phone) : ?>
                        <p><strong>Phone:</strong> <?php echo esc_html($phone); ?></p>
                    <?php endif; ?>

                    <?php if ($email) : ?>
                        <p><strong>Email:</strong> <?php echo esc_html($email); ?></p>
                    <?php endif; ?>
                </div>

                <div class="ds-dashboard-widget">
                    <h3>Help & Resources</h3>
                    <ul>
                        <li><a href="#" onclick="alert('Help documentation coming soon!')">How to create posts</a></li>
                        <li><a href="#" onclick="alert('Help documentation coming soon!')">Using block patterns</a></li>
                        <li><a href="#" onclick="alert('Help documentation coming soon!')">Customizing your location page</a></li>
                        <li><a href="<?php echo admin_url('edit.php?post_type=ds_location'); ?>">Edit location details</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Setup admin customizations
     */
    public function setup_admin_customizations() {
        add_action('admin_menu', array($this, 'customize_admin_menu'));
        add_action('show_user_profile', array($this, 'add_location_assignment_field'));
        add_action('edit_user_profile', array($this, 'add_location_assignment_field'));
        add_action('personal_options_update', array($this, 'save_location_assignment_field'));
        add_action('edit_user_profile_update', array($this, 'save_location_assignment_field'));
        add_action('admin_bar_menu', array($this, 'customize_admin_bar'), 999);
        add_filter('admin_footer_text', array($this, 'custom_admin_footer'));
    }

    /**
     * Customize admin bar for location managers
     */
    public function customize_admin_bar($wp_admin_bar) {
        $user = wp_get_current_user();
        if (!in_array('ds_location_manager', (array) $user->roles)) {
            return;
        }

        $user_location_id = get_user_meta($user->ID, 'ds_assigned_location', true);
        if ($user_location_id) {
            $location = get_post($user_location_id);
            if ($location) {
                $wp_admin_bar->add_node(array(
                    'id' => 'ds-location-quick',
                    'title' => 'My Location: ' . $location->post_title,
                    'href' => get_edit_post_link($user_location_id)
                ));

                $wp_admin_bar->add_node(array(
                    'id' => 'ds-view-location',
                    'parent' => 'ds-location-quick',
                    'title' => 'View Location Page',
                    'href' => get_permalink($user_location_id)
                ));

                $wp_admin_bar->add_node(array(
                    'id' => 'ds-edit-location',
                    'parent' => 'ds-location-quick',
                    'title' => 'Edit Location Page',
                    'href' => get_edit_post_link($user_location_id)
                ));
            }
        }
    }

    public function custom_admin_footer($text) {
        $user = wp_get_current_user();
        if (in_array('ds_location_manager', (array) $user->roles)) {
            return 'Managing your location content with DS Location Manager.';
        }
        return $text;
    }

    /**
     * Enhanced location assignment field
     */
    public function add_location_assignment_field($user) {
        if (!current_user_can('edit_users')) {
            return;
        }

        $assigned_location = get_user_meta($user->ID, 'ds_assigned_location', true);
        $locations = get_posts(array(
            'post_type' => 'ds_location',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        ?>
        <h3>DS Location Assignment</h3>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="ds_assigned_location">Assigned Location</label></th>
                <td>
                    <select name="ds_assigned_location" id="ds_assigned_location">
                        <option value="">Select Location...</option>
                        <?php foreach ($locations as $location) : ?>
                            <option value="<?php echo $location->ID; ?>" <?php selected($assigned_location, $location->ID); ?>>
                                <?php echo esc_html($location->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        Assign this user to manage a specific location. Location Managers can only edit their assigned location and create posts for that location.
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_location_assignment_field($user_id) {
        if (!current_user_can('edit_users')) {
            return;
        }

        if (isset($_POST['ds_assigned_location'])) {
            $location_id = intval($_POST['ds_assigned_location']);
            update_user_meta($user_id, 'ds_assigned_location', $location_id);
        }
    }

    /**
     * Enhanced REST API setup
     */
    public function setup_rest_api() {
        add_action('rest_api_init', array($this, 'register_rest_fields'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    /**
     * Register enhanced REST API fields
     */
    public function register_rest_fields() {
        // Add all location details to REST API
        $location_fields = array('name', 'address', 'phone', 'email', 'contact_name', 'description');

        foreach ($location_fields as $field) {
            register_rest_field('ds_location', $field, array(
                'get_callback' => function($post) use ($field) {
                    return get_post_meta($post['id'], '_ds_location_' . $field, true);
                },
                'update_callback' => function($value, $post) use ($field) {
                    return update_post_meta($post->ID, '_ds_location_' . $field, sanitize_text_field($value));
                },
                'schema' => array(
                    'description' => ucfirst(str_replace('_', ' ', $field)),
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            ));
        }

        // Add post count to location API
        register_rest_field('ds_location', 'post_count', array(
            'get_callback' => function($post) {
                $term_id = get_post_meta($post['id'], '_ds_taxonomy_term_id', true);
                if (!$term_id) return 0;

                $posts = get_posts(array(
                    'post_type' => 'post',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'ds_post_location',
                            'field' => 'term_id',
                            'terms' => intval($term_id)
                        )
                    ),
                    'fields' => 'ids'
                ));
                return count($posts);
            },
            'schema' => array(
                'description' => 'Number of posts for this location',
                'type' => 'integer',
                'context' => array('view', 'edit')
            )
        ));

        // Add location info to posts
        register_rest_field('post', 'location', array(
            'get_callback' => function($post) {
                $locations = wp_get_post_terms($post['id'], 'ds_post_location');
                if (!empty($locations)) {
                    $location_term = $locations[0];
                    // find matching location post via stored term meta or post meta lookup
                    $location_posts = get_posts(array(
                        'post_type' => 'ds_location',
                        'meta_query' => array(
                            array(
                                'key' => '_ds_taxonomy_term_id',
                                'value' => $location_term->term_id
                            )
                        ),
                        'posts_per_page' => 1
                    ));
                    if (!empty($location_posts)) {
                        return array(
                            'id' => $location_posts[0]->ID,
                            'name' => $location_posts[0]->post_title,
                            'slug' => $location_posts[0]->post_name
                        );
                    }
                }
                return null;
            },
            'schema' => array(
                'description' => 'Location information for this post',
                'type' => 'object',
                'context' => array('view', 'edit')
            )
        ));
    }

    /**
     * Register custom REST routes
     */
    public function register_rest_routes() {
        register_rest_route('ds/v1', '/locations-with-posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_locations_with_posts'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('ds/v1', '/locations/(?P<id>\d+)/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_location_posts_enhanced'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array('required' => true, 'type' => 'integer'),
                'per_page' => array('default' => 10, 'type' => 'integer'),
                'page' => array('default' => 1, 'type' => 'integer'),
                'orderby' => array('default' => 'date', 'type' => 'string'),
                'order' => array('default' => 'desc', 'type' => 'string')
            )
        ));

        register_rest_route('ds/v1', '/locations/(?P<id>\d+)/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_location_stats'),
            'permission_callback' => '__return_true',
            'args' => array('id' => array('required' => true, 'type' => 'integer'))
        ));
    }

    /**
     * Get all locations with their post counts
     */
    public function get_locations_with_posts($request) {
        $locations = get_posts(array(
            'post_type' => 'ds_location',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));

        $data = array();
        foreach ($locations as $location) {
            $term_id = $this->get_term_id_for_location($location->ID);
            $posts = array();
            if ($term_id) {
                $posts = get_posts(array(
                    'post_type' => 'post',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'ds_post_location',
                            'field' => 'term_id',
                            'terms' => $term_id
                        )
                    ),
                    'fields' => 'ids'
                ));
            }

            $location_data = array(
                'id' => $location->ID,
                'title' => $location->post_title,
                'slug' => $location->post_name,
                'link' => get_permalink($location->ID),
                'post_count' => count($posts)
            );

            $fields = array('name', 'address', 'phone', 'email', 'contact_name', 'description');
            foreach ($fields as $field) {
                $location_data[$field] = get_post_meta($location->ID, '_ds_location_' . $field, true);
            }

            $data[] = $location_data;
        }

        return new WP_REST_Response($data, 200);
    }

    /**
     * Enhanced get location posts
     */
    public function get_location_posts_enhanced($request) {
        $location_post_id = intval($request['id']);
        $per_page = intval($request['per_page']);
        $page = intval($request['page']);
        $orderby = sanitize_text_field($request['orderby']);
        $order = sanitize_text_field($request['order']);

        $term_id = $this->get_term_id_for_location($location_post_id);
        if (!$term_id) {
            return new WP_REST_Response(array(), 200);
        }

        $posts = get_posts(array(
            'post_type' => 'post',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => $orderby,
            'order' => $order,
            'tax_query' => array(
                array(
                    'taxonomy' => 'ds_post_location',
                    'field' => 'term_id',
                    'terms' => $term_id
                )
            )
        ));

        $data = array();
        foreach ($posts as $post) {
            $data[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'excerpt' => $post->post_excerpt ?: wp_trim_excerpt('', $post),
                'date' => $post->post_date,
                'modified' => $post->post_modified,
                'status' => $post->post_status,
                'link' => get_permalink($post->ID),
                'featured_image' => get_the_post_thumbnail_url($post->ID, 'medium'),
                'author' => get_the_author_meta('display_name', $post->post_author)
            );
        }

        $response = new WP_REST_Response($data, 200);
        $response->header('X-WP-Total', count($posts));
        $response->header('X-WP-TotalPages', ceil(count($posts) / max(1, $per_page)));

        return $response;
    }

    /**
     * Get location statistics
     */
    public function get_location_stats($request) {
        $location_post_id = intval($request['id']);
        $term_id = $this->get_term_id_for_location($location_post_id);
        if (!$term_id) {
            return new WP_REST_Response(array(
                'total_posts' => 0,
                'published_posts' => 0,
                'draft_posts' => 0,
                'pending_posts' => 0,
                'recent_posts' => array()
            ), 200);
        }

        $all_posts = get_posts(array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'ds_post_location',
                    'field' => 'term_id',
                    'terms' => $term_id
                )
            ),
            'post_status' => array('publish', 'draft', 'pending')
        ));

        $stats = array(
            'total_posts' => count($all_posts),
            'published_posts' => 0,
            'draft_posts' => 0,
            'pending_posts' => 0,
            'recent_posts' => array()
        );

        foreach ($all_posts as $post) {
            switch ($post->post_status) {
                case 'publish':
                    $stats['published_posts']++;
                    break;
                case 'draft':
                    $stats['draft_posts']++;
                    break;
                case 'pending':
                    $stats['pending_posts']++;
                    break;
            }
        }

        $recent = get_posts(array(
            'post_type' => 'post',
            'posts_per_page' => 5,
            'tax_query' => array(
                array(
                    'taxonomy' => 'ds_post_location',
                    'field' => 'term_id',
                    'terms' => $term_id
                )
            )
        ));

        foreach ($recent as $post) {
            $stats['recent_posts'][] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'date' => $post->post_date,
                'status' => $post->post_status
            );
        }

        return new WP_REST_Response($stats, 200);
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('ds-location-frontend', plugin_dir_url(__FILE__) . 'assets/location-frontend.css', array(), $this->version);
    }

    /**
     * Plugin activation
     */
    public function activate() {
        $this->register_location_post_type();
        $this->register_location_taxonomy();
        $this->create_location_manager_role();
        flush_rewrite_rules();

        // Create default location if none exist
        $existing_locations = get_posts(array('post_type' => 'ds_location', 'posts_per_page' => 1));
        if (empty($existing_locations)) {
            $location_id = wp_insert_post(array(
                'post_title' => 'Sample Location',
                'post_content' => '',
                'post_type' => 'ds_location',
                'post_status' => 'publish'
            ));

            if ($location_id && !is_wp_error($location_id)) {
                update_post_meta($location_id, '_ds_location_name', 'Sample Location');
                update_post_meta($location_id, '_ds_location_address', '123 Main Street\nAnytown, ST 12345');
                update_post_meta($location_id, '_ds_location_phone', '(555) 123-4567');
                update_post_meta($location_id, '_ds_location_email', 'info@samplelocation.com');
                update_post_meta($location_id, '_ds_location_contact_name', 'John Doe');
                update_post_meta($location_id, '_ds_location_description', 'A sample location to get you started.');
                update_post_meta($location_id, '_ds_location_id', $location_id);

                // ensure taxonomy term created
                $this->sync_location_taxonomy($location_id);
            }
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Block patterns (kept as-is, omitted here for brevity in this snippet)
     */
    public function register_block_patterns() {
        // copy your previous register_block_patterns() implementation here (unchanged)
        // for brevity in this reply it's safe to simply re-use your original block pattern registrations
        // (if needed I can paste the full patterns block back in)
        // --- BEGIN ORIGINAL PATTERNS ---
        // you had hero, contact-info, recent-posts patterns; re-add them exactly.
        // --- END ORIGINAL PATTERNS ---
    }

    /**
     * Block category
     */
    public function setup_block_categories() {
        add_filter('block_categories_all', function($categories) {
            array_unshift($categories, array(
                'slug' => 'ds-location',
                'title' => 'Location Blocks'
            ));
            return $categories;
        });
    }
}

// Initialize
new DS_Location_Manager_V2();

require_once plugin_dir_path(__FILE__) . 'patterns.php';
require_once plugin_dir_path(__FILE__) . 'rest.php';