<?php
/**
 * Plugin Name: DS Location Manager 2
 * Description: Manage locations with dedicated pages and posts, including user access control and block patterns
 * Version: 2.2.0
 * Author: NC Dorsner
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Location_Manager_V2 {

    private $version = '2.3.0';

    /**
     * Bump this whenever the Location Manager role's capability set changes.
     * maybe_upgrade_roles() compares it to the stored option and re-runs the
     * role definition once — instead of writing role caps to the DB on
     * every page load.
     */
    const CAPS_VERSION = '4';

    public function __construct() {
        // Include required files
        require_once plugin_dir_path(__FILE__) . 'includes/class-location-fields.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-location-data.php';
        require_once plugin_dir_path(__FILE__) . 'includes/meta-boxes.php';
        require_once plugin_dir_path(__FILE__) . 'includes/admin-customizations.php';
        require_once plugin_dir_path(__FILE__) . 'includes/app-settings.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-location-grid.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-location-news-carousel.php';
        require_once plugin_dir_path(__FILE__) . 'includes/location-picker.php';
        require_once plugin_dir_path(__FILE__) . 'includes/template-parts.php';

        
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('enter_title_here', array($this, 'location_title_placeholder'), 10, 2);
        
        // Template loader for single location pages
        add_filter('template_include', array($this, 'load_location_template'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_location_template_assets'));
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Hide Add New submenu and button
        add_action('admin_menu', array($this, 'remove_add_new_submenu'));
        add_action('admin_head', array($this, 'hide_add_new_button'));
        
        // Hide default dashboard for location managers
        add_action('admin_menu', array($this, 'remove_default_dashboard'), 999);
        add_action('admin_init', array($this, 'redirect_from_default_dashboard'));
        
        // Post lock management
        add_action('wp_logout', array($this, 'clear_user_locks'));
        add_action('admin_init', array($this, 'clear_stale_location_locks'));
    }

    public function init() {
        $this->register_location_post_type();
        $this->register_location_taxonomy();
        $this->maybe_upgrade_roles();
        $this->setup_access_control();
        $this->setup_rest_api();
        add_filter('the_content', array($this, 'append_location_footer_to_post'));
        
        // Initialize app settings (REST endpoint needs to work outside admin)
        new DS_App_Settings($this);
        
        // Initialize admin customizations
        if (is_admin()) {
            new DS_Location_Admin_Customizations($this);
        }
        
        // Initialize meta boxes
        new DS_Location_Meta_Boxes($this);
    }

    /**
     * Load custom template for single location pages
     */
    public function load_location_template($template) {
        if (is_singular('ds_location')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/single-ds_location.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        if (is_post_type_archive('ds_location')) {
            $archive_template = plugin_dir_path(__FILE__) . 'templates/archive-ds_location.php';
            if (file_exists($archive_template)) {
                return $archive_template;
            }
        }

        return $template;
    }

    /**
     * Locations archive query: all published locations, alphabetical.
     * (A curated set this small doesn't need pagination; revisit alongside
     * search/filtering if the location count ever warrants it.)
     */
    public function customize_location_archive_query($query) {
        if (is_admin() || !$query->is_main_query() || !$query->is_post_type_archive('ds_location')) {
            return;
        }
        $query->set('posts_per_page', -1);
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
    }

    /**
     * Enqueue assets for location template
     */
    public function enqueue_location_template_assets() {
        // Contact-card styles are shared with the single-post location
        // footer, so the template stylesheet loads on both.
        if (is_singular(array('ds_location', 'post'))) {
            $template_css = plugin_dir_path(__FILE__) . 'assets/location-template.css';
            wp_enqueue_style(
                'ds-location-template',
                plugin_dir_url(__FILE__) . 'assets/location-template.css',
                array(),
                file_exists($template_css) ? filemtime($template_css) : $this->version
            );
        }

        if (is_singular('ds_location')) {
            wp_enqueue_style(
                'leaflet-css',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                array(),
                '1.9.4'
            );
            
            wp_enqueue_script(
                'leaflet-js',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                array(),
                '1.9.4',
                true
            );
            
            wp_enqueue_script(
                'ds-location-template-js',
                plugin_dir_url(__FILE__) . 'assets/location-template.js',
                array('jquery', 'leaflet-js'),
                $this->version,
                true
            );
            
            global $post;
            if ($post) {
                $lat = get_post_meta($post->ID, '_ds_location_latitude', true);
                $lng = get_post_meta($post->ID, '_ds_location_longitude', true);
                $name = get_post_meta($post->ID, '_ds_location_name', true) ?: $post->post_title;
                
                wp_localize_script('ds-location-template-js', 'dsLocationData', array(
                    'latitude' => $lat ?: '33.7490',
                    'longitude' => $lng ?: '-84.3880',
                    'name' => $name,
                    'hasCoordinates' => !empty($lat) && !empty($lng)
                ));
            }
        }
    }

    /**
     * Helper function to get location data for template
     */
    public function get_location_display_data($post_id = null) {
        if (!$post_id) {
            global $post;
            $post_id = $post ? $post->ID : 0;
        }

        if (!$post_id || get_post_type($post_id) !== 'ds_location') {
            return false;
        }

        $post_obj = get_post($post_id);
        $data = DS_Location_Data::get_all($post_id);

        return array(
            'id'               => $post_id,
            'title'            => $post_obj->post_title,
            'content'          => $post_obj->post_content,
            'location_name'    => $data['name'] ?: $post_obj->post_title,
            'city'             => $data['city'],
            'address'          => $data['address'],
            'phone'            => $data['phone'],
            'text_phone'       => $data['text_phone'],
            'email'            => $data['email'],
            'website'          => $data['website'],
            'contact_name'     => $data['contact_name'],
            'description'      => $data['description'],
            'yycd_description' => $data['yycd_description'],
            'featured_image'   => $data['featured_image'],
            'logo_url'         => $data['logo_url'],
            'flyer_url'        => $data['flyer_url'],
            'latitude'         => $data['latitude'],
            'longitude'        => $data['longitude'],
        );
    }

    /**
     * Relabel the title prompt on the standard editor — for ds_location
     * posts, the title IS the city (single source of truth, see
     * DS_Location_Data), so prompt for that instead of a generic title.
     */
    public function location_title_placeholder($title, $post) {
        if (isset($post->post_type) && $post->post_type === 'ds_location') {
            return __('City (e.g. Jupiter, FL)', 'ds-location-manager');
        }
        return $title;
    }

    /**
     * Extract city from address
     *
     * @deprecated City now comes from the post title (see DS_Location_Data).
     * Left in place in case address-based extraction is useful elsewhere —
     * no longer called by the save paths or get_location_display_data().
     */
    public function extract_city_from_address($address) {
        $lines = explode("\n", $address);
        
        if (isset($lines[1])) {
            $line = trim($lines[1]);
            if (strpos($line, ',') !== false) {
                $parts = explode(',', $line);
                return trim($parts[0]);
            }
        }
        
        return '';
    }

    /**
     * Register Location Custom Post Type
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
            'capabilities' => array(
                // Real gate for creating locations — the hidden "Add New"
                // button is cosmetic; this is what actually blocks
                // post-new.php?post_type=ds_location for non-admins.
                'create_posts'          => 'create_locations',
                'edit_post'             => 'edit_location',
                'read_post'             => 'read_location',
                'delete_post'           => 'delete_location',
                'edit_posts'            => 'edit_locations',
                'edit_others_posts'     => 'edit_others_locations',
                'publish_posts'         => 'publish_locations',
                'read_private_posts'    => 'read_private_locations',
                'delete_posts'          => 'delete_locations',
                'delete_private_posts'  => 'delete_private_locations',
                'delete_published_posts'=> 'delete_published_locations',
                'delete_others_posts'   => 'delete_others_locations',
                'edit_private_posts'    => 'edit_private_locations',
                'edit_published_posts'  => 'edit_published_locations',
            ),
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-location-alt',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt', 'author'),
            'template_lock' => false,
            'template' => array()
        );

        register_post_type('ds_location', $args);
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
     * Ensure 1:1 term mapping for location - WITH SYNC
     * 
     * REPLACES the old ensure_term_for_location() method
     */
    public function ensure_term_for_location($location_post_id) {
        $location_post_id = intval($location_post_id);
        if (!$location_post_id || !taxonomy_exists('ds_post_location')) {
            return 0;
        }

        $location = get_post($location_post_id);
        if (!$location) {
            return 0;
        }

        // Skip if location is still auto-draft or doesn't have a real title yet
        if ($location->post_status === 'auto-draft' || $location->post_title === 'Auto Draft') {
            return 0;
        }

        $desired_slug = $location->post_name ?: 'location-' . $location_post_id;
        $desired_name = $location->post_title;

        // Check for cached term ID
        $cached_term_id = get_post_meta($location_post_id, '_ds_taxonomy_term_id', true);
        
        if ($cached_term_id) {
            $term = get_term(intval($cached_term_id), 'ds_post_location');
            
            if ($term && !is_wp_error($term)) {
                // Term exists - check if it needs updating
                $needs_update = false;
                $update_args = array();
                
                if ($term->name !== $desired_name) {
                    $update_args['name'] = $desired_name;
                    $needs_update = true;
                }
                
                if ($term->slug !== $desired_slug) {
                    $update_args['slug'] = $desired_slug;
                    $needs_update = true;
                }
                
                if ($needs_update) {
                    wp_update_term($term->term_id, 'ds_post_location', $update_args);
                }
                
                return intval($term->term_id);
            }
            
            // Cached term doesn't exist anymore - clear the cache
            delete_post_meta($location_post_id, '_ds_taxonomy_term_id');
        }

        // No valid cached term - look for existing term by slug
        $term = get_term_by('slug', $desired_slug, 'ds_post_location');
        if ($term && !is_wp_error($term)) {
            update_post_meta($location_post_id, '_ds_taxonomy_term_id', intval($term->term_id));
            
            if ($term->name !== $desired_name) {
                wp_update_term($term->term_id, 'ds_post_location', array('name' => $desired_name));
            }
            
            return intval($term->term_id);
        }

        // No term exists - create one
        $result = wp_insert_term($desired_name, 'ds_post_location', array('slug' => $desired_slug));
        
        if (is_wp_error($result)) {
            if ($result->get_error_code() === 'term_exists') {
                $existing_term_id = $result->get_error_data('term_exists');
                update_post_meta($location_post_id, '_ds_taxonomy_term_id', intval($existing_term_id));
                return intval($existing_term_id);
            }
            return 0;
        }

        if (!empty($result['term_id'])) {
            $term_id = intval($result['term_id']);
            update_post_meta($location_post_id, '_ds_taxonomy_term_id', $term_id);
            return $term_id;
        }

        return 0;
    }

    public function get_term_id_for_location($location_post_id) {
        return $this->ensure_term_for_location($location_post_id);
    }

    /**
     * Run the role/capability definition once per CAPS_VERSION.
     *
     * Role capabilities are persisted in the wp_user_roles option, so they
     * only need writing when the definition changes — not on every request
     * (the old setup_location_manager_caps() was issuing up to 9
     * update_option() writes per page load).
     */
    public function maybe_upgrade_roles() {
        if (get_option('ds_lm_caps_version') === self::CAPS_VERSION) {
            return;
        }

        $this->create_location_manager_role();
        update_option('ds_lm_caps_version', self::CAPS_VERSION);
    }

    /**
     * Create/update the Location Manager role — the single, canonical
     * definition of its capability set. Called only from activation and
     * maybe_upgrade_roles(), never on normal page loads.
     *
     * Post-type caps (edit_posts, edit_others_posts, etc.) are intentionally
     * broad here; restrict_post_meta_caps() is what scopes them to the
     * manager's assigned location. Notable exclusions:
     * - edit_files: never appropriate for a low-trust role (theme/plugin
     *   file editing) — revoked from existing installs by the migration.
     * - create_locations: managers are assigned to locations, they don't
     *   create them (see 'create_posts' in register_location_post_type()).
     * - edit_others_locations / delete_others_locations: previously granted
     *   and then removed again on every init by two methods fighting each
     *   other; the effective state (removed) is now the defined state.
     * - delete_location / delete_locations / delete_published_locations /
     *   delete_private_locations: deleting locations is admin-only —
     *   managers must not be able to trash their own location page
     *   (map_location_meta_caps also hard-denies delete_location).
     */
    public function create_location_manager_role() {
        $required_caps = array(
            'read' => true,
            'upload_files' => true,
            'edit_posts' => true,
            'edit_published_posts' => true,
            'publish_posts' => true,
            'delete_posts' => true,
            'delete_published_posts' => true,
            'edit_others_posts' => true,
            'delete_others_posts' => true,
            'read_private_posts' => true,
            'edit_location' => true,
            'edit_locations' => true,
            'edit_published_locations' => true,
            'publish_locations' => true,
            'read_location' => true,
            'read_private_locations' => true,
            'assign_terms' => true
        );

        // Caps to actively strip from existing installs (in-place update
        // rather than remove_role/add_role, so the role never disappears
        // even for an instant).
        $revoked_caps = array(
            'edit_files',
            'create_locations',
            'edit_others_locations',
            'delete_others_locations',
            'delete_location',
            'delete_locations',
            'delete_published_locations',
            'delete_private_locations',
        );

        $role = get_role('ds_location_manager');

        if (!$role) {
            add_role('ds_location_manager', 'Location Manager', $required_caps);
        } else {
            foreach ($required_caps as $cap => $grant) {
                if (!$role->has_cap($cap)) {
                    $role->add_cap($cap, $grant);
                }
            }
            foreach ($revoked_caps as $cap) {
                if ($role->has_cap($cap)) {
                    $role->remove_cap($cap);
                }
            }
        }

        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_caps = array(
                'create_locations',
                'edit_location', 'edit_locations', 'edit_others_locations',
                'edit_published_locations', 'edit_private_locations', 'publish_locations',
                'read_location', 'read_private_locations', 'delete_location',
                'delete_locations', 'delete_others_locations', 'delete_published_locations',
                'delete_private_locations'
            );
            foreach ($admin_caps as $cap) {
                if (!$admin_role->has_cap($cap)) {
                    $admin_role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Setup access control
     */
    public function setup_access_control() {
        add_action('pre_get_posts', array($this, 'filter_posts_by_location'));
        add_action('pre_get_posts', array($this, 'customize_location_archive_query'));
        add_filter('map_meta_cap', array($this, 'map_location_meta_caps'), 10, 4);
        add_filter('map_meta_cap', array($this, 'restrict_post_meta_caps'), 10, 4);
        add_action('save_post', array($this, 'auto_assign_post_location'));
        add_filter('wp_insert_post_data', array($this, 'restrict_location_manager_posts'), 10, 2);
        add_action('admin_init', array($this, 'restrict_location_manager_access'));
    }

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

        $screen_id = '';
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && isset($screen->id)) {
                $screen_id = $screen->id;
            }
        }

        $author_q = $query->get('author');
        if (!empty($author_q) && intval($author_q) === intval($user->ID)) {
            return;
        }

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
                    $query->set('post__in', array(0));
                }
            } else {
                $query->set('cat', intval($user_location));
            }
        }

        if ($query->get('post_type') === 'ds_location' || $screen_id === 'edit-ds_location') {
            $query->set('post__in', array(intval($user_location)));
        }
    }

    public function map_location_meta_caps($caps, $cap, $user_id, $args) {
        if (strpos($cap, 'location') === false) {
            return $caps;
        }

        $user = get_userdata($user_id);
        if (!$user) return $caps;

        if (in_array('administrator', (array) $user->roles)) {
            return array('exist');
        }

        if (in_array('ds_location_manager', (array) $user->roles)) {
            $user_location = get_user_meta($user_id, 'ds_assigned_location', true);
            
            if (!$user_location) {
                return array('do_not_allow');
            }

            if (isset($args[0]) && is_numeric($args[0])) {
                $post_id = intval($args[0]);

                if ($post_id == $user_location) {
                    // Managers run their location; they don't get to trash
                    // it. Deleting locations is admin-only.
                    if ('delete_location' === $cap) {
                        return array('do_not_allow');
                    }
                    return array('exist');
                }

                return array('do_not_allow');
            }
            
            $allowed_general_caps = array(
                'edit_locations',
                'edit_location', 
                'publish_locations',
                'read_location'
            );
            
            if (in_array($cap, $allowed_general_caps)) {
                return array('exist');
            }
            
            $deny_caps = array(
                'edit_others_locations',
                'delete_others_locations',
                'edit_private_locations',
                'delete_private_locations',
                'delete_location',
                'delete_locations'
            );
            
            if (in_array($cap, $deny_caps)) {
                return array('do_not_allow');
            }
        }

        return $caps;
    }

    /**
     * Scope regular posts to the Location Manager's assigned location.
     *
     * The role holds edit_others_posts / delete_others_posts so managers can
     * run ALL posts at their location, not just their own — but without this
     * filter those caps are sitewide: pre_get_posts only hides other
     * locations' posts from the LIST TABLE, while direct post.php URLs and
     * the core REST API (/wp/v2/posts/{id}) would happily let a manager edit
     * or delete any post on the site. Routing the check through
     * current_user_can() here closes admin UI and REST in one place.
     *
     * Deliberately read-only: this runs on every current_user_can()
     * edit_post/delete_post check (once per row in list tables), so it reads
     * the cached _ds_taxonomy_term_id meta directly instead of calling
     * ensure_term_for_location(), which can write term updates.
     *
     * @param string[] $caps    Primitive caps the user must have.
     * @param string   $cap     Meta cap being checked.
     * @param int      $user_id
     * @param array    $args    args[0] is the post ID for post meta caps.
     * @return string[]
     */
    public function restrict_post_meta_caps($caps, $cap, $user_id, $args) {
        if (!in_array($cap, array('edit_post', 'delete_post'), true) || empty($args[0])) {
            return $caps;
        }

        $user = get_userdata($user_id);
        if (!$user || in_array('administrator', (array) $user->roles, true)) {
            return $caps;
        }

        if (!in_array('ds_location_manager', (array) $user->roles, true)) {
            return $caps;
        }

        $post = get_post($args[0]);
        if (!$post || $post->post_type !== 'post') {
            // ds_location is handled by map_location_meta_caps(); everything
            // else keeps core behavior.
            return $caps;
        }

        // A manager's own posts are always theirs to manage.
        if ((int) $post->post_author === $user_id) {
            return $caps;
        }

        $assigned = get_user_meta($user_id, 'ds_assigned_location', true);
        if (!$assigned) {
            return array('do_not_allow');
        }

        $term_id = (int) get_post_meta((int) $assigned, '_ds_taxonomy_term_id', true);
        if (!$term_id || !has_term($term_id, 'ds_post_location', $post)) {
            return array('do_not_allow');
        }

        return $caps;
    }

    public function remove_add_new_submenu() {
        $user = wp_get_current_user();
        if (in_array('ds_location_manager', (array) $user->roles)) {
            remove_submenu_page('edit.php?post_type=ds_location', 'post-new.php?post_type=ds_location');
        }
    }

    public function hide_add_new_button() {
        $screen = get_current_screen();
        $user = wp_get_current_user();
        if (in_array('ds_location_manager', (array) $user->roles) && $screen && $screen->post_type === 'ds_location') {
            echo '<style>#wpbody-content .page-title-action { display:none; }</style>';
        }
    }

    /**
     * Remove default dashboard menu item for location managers
     */
    public function remove_default_dashboard() {
        $user = wp_get_current_user();
        if (in_array('ds_location_manager', (array) $user->roles)) {
            remove_menu_page('index.php');
        }
    }

    /**
     * Redirect location managers from default dashboard to location dashboard
     */
    public function redirect_from_default_dashboard() {
        $user = wp_get_current_user();
        if (!in_array('ds_location_manager', (array) $user->roles)) {
            return;
        }
        
        global $pagenow;
        if ($pagenow === 'index.php') {
            wp_safe_redirect(admin_url('admin.php?page=ds-location-dashboard'));  // Fixed
            exit;
        }
    }

    /**
     * Clear all post locks for user on logout
     */
    /**
     * Clear all post locks for user on logout
     * 
     * @param int $user_id User ID passed by wp_logout action (WP 5.5+)
     */
    public function clear_user_locks($user_id = 0) {
        // WordPress 5.5+ passes user_id, fallback for older versions
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return;
        }
        
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} 
                WHERE meta_key = '_edit_lock' 
                AND meta_value LIKE %s",
                '%:' . $user_id
            )
        );
    }

    /**
     * Clear stale location locks (older than 2 minutes)
     * Runs once per admin page load with caching to avoid performance impact
     */
    public function clear_stale_location_locks() {
        // Only run occasionally to avoid performance impact
        if (wp_cache_get('ds_location_locks_cleared', 'ds_location')) {
            return;
        }
        wp_cache_set('ds_location_locks_cleared', true, 'ds_location', 300);
        
        global $wpdb;
        
        $locks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID, pm.meta_value 
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = %s 
                AND pm.meta_key = '_edit_lock'",
                'ds_location'
            )
        );
        
        $current_time = time();
        
        foreach ($locks as $lock) {
            $lock_data = explode(':', $lock->meta_value);
            $lock_time = isset($lock_data[0]) ? absint($lock_data[0]) : 0;
            
            // If lock is older than 2 minutes, clear it
            if ($lock_time && ($current_time - $lock_time) > 120) {
                delete_post_meta($lock->ID, '_edit_lock');
                delete_post_meta($lock->ID, '_edit_last');
            }
        }
    }

    public function auto_assign_post_location($post_id) {
        if (get_post_type($post_id) !== 'post') {
            return;
        }

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

        $term_id = $this->get_term_id_for_location($user_location);
        if ($term_id) {
            wp_set_post_terms($post_id, array($term_id), 'ds_post_location', false);
            return;
        }

        if (term_exists(intval($user_location), 'category')) {
            wp_set_post_terms($post_id, array(intval($user_location)), 'category', false);
        }
    }

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
     * Setup REST API
     */
    public function setup_rest_api() {
        require_once plugin_dir_path(__FILE__) . 'rest.php';
        require_once plugin_dir_path(__FILE__) . 'patterns.php';
    }

    /**
     * Append the location footer to single posts.
     *
     * Rebuilt to reuse the shared contact card (ds_render_contact_card),
     * so the footer always matches the single-location sidebar — including
     * text_phone and directions, which the old hand-rolled markup lacked.
     * Wrapper keeps the .ds-post-location-footer header ("This post is
     * from ...") and CTA link around the shared card.
     */
    public function append_location_footer_to_post($content) {
        if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        global $post;

        $terms = wp_get_post_terms($post->ID, 'ds_post_location');
        if (empty($terms) || is_wp_error($terms)) {
            return $content;
        }

        $location = get_posts(array(
            'post_type'   => 'ds_location',
            'meta_key'    => '_ds_taxonomy_term_id',
            'meta_value'  => $terms[0]->term_id,
            'post_status' => 'publish',
            'numberposts' => 1
        ));

        if (empty($location)) {
            return $content;
        }

        $location_id  = $location[0]->ID;
        $location_url = get_permalink($location_id);
        $data         = DS_Location_Data::get_all($location_id);
        $name         = !empty($data['name']) ? $data['name'] : $location[0]->post_title;

        // CTA: external website when available, otherwise the location page
        $cta_url      = !empty($data['website']) ? $data['website'] : $location_url;
        $cta_external = !empty($data['website']);

        $html  = '<aside class="ds-post-location-footer">';
        $html .= '<div class="ds-post-location-footer__inner">';

        $html .= '<div class="ds-post-location-footer__header">';
        $html .= '<span class="ds-post-location-footer__label">This post is from</span>';
        $html .= '<h3 class="ds-post-location-footer__title">';
        $html .= '<a href="' . esc_url($location_url) . '">' . esc_html($name) . '</a>';
        $html .= '</h3>';
        $html .= '</div>';

        // Shared contact card — same component as the location page sidebar.
        // Website link suppressed inside the card; the CTA below covers it.
        $html .= ds_render_contact_card($location_id, array(
            'heading'      => '',
            'show_website' => false,
            'extra_class'  => 'ds-contact-card--post-footer',
        ));

        $html .= '<a href="' . esc_url($cta_url) . '" class="ds-post-location-footer__cta"';
        if ($cta_external) {
            $html .= ' target="_blank" rel="noopener noreferrer"';
        }
        $html .= '>Visit ' . esc_html($name) . ($cta_external ? ' ↗' : ' →') . '</a>';

        $html .= '</div>';
        $html .= '</aside>';

        return $content . $html;
    }

    /**
     * Format phone number for display
     */
    private function format_phone_number($phone) {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($digits) === 10) {
            return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }
        
        return $phone; // Return original if not 10 digits
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        $css_file = plugin_dir_path(__FILE__) . 'assets/frontend.css';
        $css_version = file_exists($css_file) ? filemtime($css_file) : $this->version;
        
        wp_enqueue_style(
            'ds-location-frontend', 
            plugin_dir_url(__FILE__) . 'assets/frontend.css', 
            array(), 
            $css_version
        );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        $this->register_location_post_type();
        $this->register_location_taxonomy();
        $this->create_location_manager_role();
        update_option('ds_lm_caps_version', self::CAPS_VERSION);
        flush_rewrite_rules();

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
                $this->ensure_term_for_location($location_id);
            }
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    }

// Initialize — singleton accessor so templates can reach the instance
// without re-instantiating the class (which would re-register every hook).
function ds_location_manager() {
    static $instance = null;
    if (null === $instance) {
        $instance = new DS_Location_Manager_V2();
    }
    return $instance;
}
ds_location_manager();
