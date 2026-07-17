<?php
/**
 * Location Meta Boxes
 * Handles all meta box functionality for locations
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Location_Meta_Boxes {
    
    private $plugin;
    
    public function __construct($plugin) {
        $this->plugin = $plugin;
        add_action('add_meta_boxes', array($this, 'add_location_meta_boxes'));
        add_action('save_post', array($this, 'save_location_meta'));
        
        // Add admin notice on location editor pointing to settings page
        add_action('admin_notices', array($this, 'location_editor_notice'));
    }
    
    /**
     * Add meta boxes
     */
    public function add_location_meta_boxes() {
        // Only show simplified meta box - main editing happens on Settings page
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

    /**
     * Show notice on location editor linking to Settings page
     */
    public function location_editor_notice() {
        $screen = get_current_screen();
        
        if (!$screen || $screen->post_type !== 'ds_location' || $screen->base !== 'post') {
            return;
        }
        
        // Get the post ID
        global $post;
        if (!$post || !$post->ID) {
            return;
        }
        
        $settings_url = admin_url('admin.php?page=ds-location-settings&location_id=' . $post->ID);
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <strong>Tip:</strong> Use the 
                <a href="<?php echo esc_url($settings_url); ?>">Location Settings</a> 
                page for an easier way to edit contact info, address, and other location details. 
                This editor is for adding custom page content.
            </p>
        </div>
        <?php
    }

    /**
     * Location details meta box callback — simplified.
     * All location fields (name, contact info, logo, flyer, etc.) are
     * edited on the Settings page now; this editor is only for the
     * location's page content and its title (which is the City).
     */
    public function location_details_meta_box($post) {
        wp_nonce_field('ds_location_meta', 'ds_location_meta_nonce');

        $settings_url = admin_url('admin.php?page=ds-location-settings&location_id=' . $post->ID);
        ?>
        <style>
            .ds-settings-link {
                display: block;
                margin-bottom: 15px;
                padding: 10px;
                background: #f0f6fc;
                border: 1px solid #c3c4c7;
                border-left: 4px solid #2271b1;
                border-radius: 2px;
            }
            .ds-settings-link a {
                font-weight: 600;
            }
        </style>

        <div class="ds-settings-link">
            <a href="<?php echo esc_url($settings_url); ?>">✏️ Edit Location Settings</a>
            <br><small>Name, contact info, logo, flyer, and all other location details are managed there.</small>
        </div>

        <p><small>This editor is only for this location's page content (the About/program body) — and the title above, which is this location's <strong>City</strong>. Everything else lives on the Settings page.</small></p>
        <?php
    }

    /**
     * Location statistics meta box callback
     */
    public function location_stats_meta_box($post) {
        $term_id = 0;
        if (taxonomy_exists('ds_post_location')) {
            $term_id = $this->plugin->get_term_id_for_location($post->ID);
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
     * Save location meta data — standard editor only touches the title
     * and page content now, so all this does is keep the _ds_location_city
     * mirror in sync in case the title (the city) changed here.
     */
    public function save_location_meta($post_id) {
        $has_metabox_nonce = isset($_POST['ds_location_meta_nonce']) &&
            wp_verify_nonce($_POST['ds_location_meta_nonce'], 'ds_location_meta');

        if (!$has_metabox_nonce) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (get_post_type($post_id) !== 'ds_location') {
            return;
        }

        DS_Location_Data::sync_city_from_title($post_id);

        // Sync taxonomy term
        $this->plugin->ensure_term_for_location($post_id);
    }
}
