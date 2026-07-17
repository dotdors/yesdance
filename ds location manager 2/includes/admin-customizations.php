<?php
/**
 * Location Admin Customizations
 * Dashboard, menus, user profile fields, Location Settings page
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Location_Admin_Customizations {
    
    private $plugin;
    
    public function __construct($plugin) {
        $this->plugin = $plugin;
        
        add_action('admin_menu', array($this, 'customize_admin_menu'));
        add_action('admin_menu', array($this, 'add_location_settings_page'));
        add_action('show_user_profile', array($this, 'add_location_assignment_field'));
        add_action('edit_user_profile', array($this, 'add_location_assignment_field'));
        add_action('personal_options_update', array($this, 'save_location_assignment_field'));
        add_action('edit_user_profile_update', array($this, 'save_location_assignment_field'));
        add_action('admin_bar_menu', array($this, 'customize_admin_bar'), 999);
        add_filter('admin_footer_text', array($this, 'custom_admin_footer'));
        add_filter('manage_ds_location_posts_columns', array($this, 'location_posts_columns'));
        add_filter('manage_edit-ds_location_sortable_columns', array($this, 'location_sortable_columns'));
        
        // Handle settings page form submission
        add_action('admin_post_ds_save_location_settings', array($this, 'handle_settings_save'));
        add_action('admin_post_ds_create_location', array($this, 'handle_create_location'));
    }

    /**
     * Customize admin menu for location managers
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
     * Add Location Settings page to admin menu
     */
    public function add_location_settings_page() {
        // For admins - add under Locations menu
        add_submenu_page(
            'edit.php?post_type=ds_location',
            'Location Settings',
            'Edit Settings',
            'edit_locations',
            'ds-location-settings',
            array($this, 'render_location_settings_page')
        );
        
        // For location managers - add as top-level menu item
        $user = wp_get_current_user();
        if (in_array('ds_location_manager', (array) $user->roles)) {
            add_menu_page(
                'Location Settings',
                'Location Settings',
                'edit_locations',
                'ds-location-settings',
                array($this, 'render_location_settings_page'),
                'dashicons-admin-settings',
                3
            );
        }
    }

    /**
     * Render the Location Settings page
     */
    public function render_location_settings_page() {
        // Determine which location to edit
        $location_id = $this->get_editable_location_id();
        
        if (!$location_id) {
            echo '<div class="wrap"><h1>Location Settings</h1>';
            
            $user = wp_get_current_user();
            
            // For admins, show location selector
            if (current_user_can('administrator') && !in_array('ds_location_manager', (array) $user->roles)) {
                $all_locations = get_posts(array(
                    'post_type' => 'ds_location',
                    'posts_per_page' => -1,
                    'post_status' => array('publish', 'draft'),
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));

                $add_new_form = '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin: 15px 0;">'
                    . '<input type="hidden" name="action" value="ds_create_location">'
                    . wp_nonce_field('ds_create_location', 'ds_create_location_nonce', true, false)
                    . '<button type="submit" class="button button-primary">+ Add New Location</button>'
                    . '</form>';

                if (empty($all_locations)) {
                    echo '<div class="notice notice-warning"><p>No locations found yet.</p></div>';
                    echo $add_new_form;
                } else {
                    echo '<div class="notice notice-info"><p>Select a location to edit its settings:</p></div>';
                    echo $add_new_form;
                    echo '<div style="margin-top: 20px; padding: 20px; background: white; border: 1px solid #c3c4c7; max-width: 600px;">';
                    echo '<h2 style="margin-top: 0;">Choose Location</h2>';
                    echo '<ul style="list-style: none; margin: 0; padding: 0;">';
                    foreach ($all_locations as $loc) {
                        $edit_url = add_query_arg('location_id', $loc->ID, admin_url('edit.php?post_type=ds_location&page=ds-location-settings'));
                        $title = $loc->post_title !== '' ? $loc->post_title : '(untitled — set the City)';
                        echo '<li style="margin: 10px 0; padding: 15px; background: #f6f7f7; border-left: 4px solid #2271b1;">';
                        echo '<a href="' . esc_url($edit_url) . '" style="text-decoration: none; font-size: 16px; font-weight: 600;">';
                        echo esc_html($title);
                        if ($loc->post_status === 'draft') {
                            echo ' <span style="font-weight: 400; font-size: 13px; color: #997404;">(draft)</span>';
                        }
                        echo ' <span style="color: #2271b1;">→</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }
            } 
            // For location managers without assignment
            else if (in_array('ds_location_manager', (array) $user->roles)) {
                echo '<div class="notice notice-error"><p>No location found to edit. ';
                echo 'Please contact an administrator to assign you to a location.';
                echo '</p></div>';
            }
            // Other users
            else {
                echo '<div class="notice notice-error"><p>No location found to edit. ';
                echo '<a href="' . admin_url('edit.php?post_type=ds_location') . '">View all locations</a>.</p></div>';
            }
            
            echo '</div>';
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $location_id)) {
            wp_die('You do not have permission to edit this location.');
        }
        
        $location = get_post($location_id);
        if (!$location || $location->post_type !== 'ds_location') {
            wp_die('Invalid location.');
        }
        
        // Get all location data through the shared data class — single source of truth
        $data = DS_Location_Data::get_all($location_id);

        // A brand-new post reached from an unsaved editor screen carries
        // WordPress's "Auto Draft" placeholder title — never show that as
        // the City.
        if ($location->post_status === 'auto-draft' && $data['city'] === 'Auto Draft') {
            $data['city'] = '';
        }

        // Status shown in the form: auto-drafts are presented as Draft
        // (saving will promote them to a real draft).
        $display_status = ($location->post_status === 'publish') ? 'publish' : 'draft';

        $logo_url = $data['logo_url'];
        $flyer_url = $data['flyer_url'];
        $featured_image_id = $data['featured_image_id'];
        $featured_image_url = $data['featured_image_medium'];
        
        // Check for save message
        $saved = isset($_GET['saved']) && $_GET['saved'] === '1';
        $created = isset($_GET['created']) && $_GET['created'] === '1';
        
        // Enqueue media uploader
        wp_enqueue_media();
        
        ?>
        <div class="wrap ds-location-settings-wrap">
            <h1>
                <?php echo esc_html($data['name'] ?: ($data['city'] ?: 'New Location')); ?> — Location Settings
            </h1>
            
            <?php 
            // Show location selector for admins
            $user = wp_get_current_user();
            if (current_user_can('administrator') && !in_array('ds_location_manager', (array) $user->roles)): 
                $all_locations = get_posts(array(
                    'post_type' => 'ds_location',
                    'posts_per_page' => -1,
                    'post_status' => array('publish', 'draft'),
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));
                
                if (count($all_locations) > 1):
            ?>
                <div class="ds-location-selector" style="background: #f0f0f1; padding: 15px; border-left: 4px solid #2271b1; margin: 20px 0;">
                    <label for="ds-location-switcher" style="font-weight: 600; margin-right: 10px;">
                        Switch Location:
                    </label>
                    <select id="ds-location-switcher" style="min-width: 300px;">
                        <?php foreach ($all_locations as $loc): ?>
                            <option value="<?php echo esc_attr($loc->ID); ?>" <?php selected($location_id, $loc->ID); ?>>
                                <?php echo esc_html($loc->post_title !== '' ? $loc->post_title : '(untitled)'); ?><?php echo $loc->post_status === 'draft' ? ' (draft)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <script>
                    document.getElementById('ds-location-switcher').addEventListener('change', function() {
                        window.location.href = '<?php echo admin_url('edit.php?post_type=ds_location&page=ds-location-settings&location_id='); ?>' + this.value;
                    });
                    </script>
                </div>
            <?php 
                endif;
            endif; 
            ?>
            
            <?php if ($created): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>New location created!</strong> Start with the City field below — everything else can follow.</p>
                </div>
            <?php endif; ?>
            
            <?php if ($saved): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Settings saved successfully!</strong></p>
                </div>
            <?php endif; ?>
            
            <div class="ds-location-settings-header">
                <div class="ds-location-settings-actions">
                    <a href="<?php echo esc_url(get_permalink($location_id)); ?>" class="button" target="_blank">
                        👁️ View Location Page
                    </a>
                    <a href="<?php echo esc_url(get_edit_post_link($location_id)); ?>" class="button">
                        📝 Edit Page Content
                    </a>
                </div>
            </div>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="ds-location-settings-form">
                <input type="hidden" name="action" value="ds_save_location_settings">
                <input type="hidden" name="location_id" value="<?php echo esc_attr($location_id); ?>">
                <?php wp_nonce_field('ds_location_settings', 'ds_location_settings_nonce'); ?>
                
                <div class="ds-settings-grid">
                    
                    <!-- Basic Information -->
                    <div class="ds-settings-section">
                        <h2>📋 Basic Information</h2>
                        
                        <div class="ds-field">
                            <label for="ds_location_city">City</label>
                            <input type="text" id="ds_location_city" name="ds_location_city" 
                                   value="<?php echo esc_attr($data['city']); ?>" class="regular-text"
                                   placeholder="e.g. Jupiter, FL">
                            <p class="description">This becomes the location's title across the site and app — keep it just the city (e.g. "Jupiter, FL"), not the studio name.</p>
                        </div>

                        <div class="ds-field">
                            <label for="ds_location_status">Status</label>
                            <select id="ds_location_status" name="ds_location_status">
                                <option value="draft" <?php selected($display_status, 'draft'); ?>>Draft — hidden from the site and app</option>
                                <option value="publish" <?php selected($display_status, 'publish'); ?>>Published — live on the site and app</option>
                            </select>
                            <?php if ($display_status === 'draft'): ?>
                                <p class="description" style="color: #997404;"><strong>This location is a draft</strong> — it won't appear on the website, in the app, or in the location picker until it's published.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ds-field">
                            <label for="ds_location_name">Studio / Program Name</label>
                            <input type="text" id="ds_location_name" name="ds_location_name" 
                                   value="<?php echo esc_attr($data['name']); ?>" class="regular-text">
                            <p class="description">The studio or program name (e.g. "In Motion Ballroom") — separate from City above.</p>
                        </div>
                        
                        <div class="ds-field">
                            <label for="ds_location_description">Short Description / Tagline</label>
                            <textarea id="ds_location_description" name="ds_location_description" 
                                      rows="2" class="large-text"><?php echo esc_textarea($data['description']); ?></textarea>
                            <p class="description">Brief intro displayed in the hero section (1-2 sentences)</p>
                        </div>
                        
                        <div class="ds-field">
                            <label for="ds_location_yycd_description">YYCD Program Description</label>
                            <textarea id="ds_location_yycd_description" name="ds_location_yycd_description" 
                                      rows="5" class="large-text"><?php echo esc_textarea($data['yycd_description']); ?></textarea>
                            <p class="description">Detailed description of the YYCD program at this location</p>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="ds-settings-section">
                        <h2>📞 Contact Information</h2>
                        
                        <div class="ds-field">
                            <label for="ds_location_contact_name">Contact Person</label>
                            <input type="text" id="ds_location_contact_name" name="ds_location_contact_name" 
                                   value="<?php echo esc_attr($data['contact_name']); ?>" class="regular-text">
                            <p class="description">Primary contact name for this location</p>
                        </div>
                        
                        <?php $same_number = ($data['text_phone'] === '' || $data['text_phone'] === $data['phone']); ?>

                        <div class="ds-field">
                            <label for="ds_location_phone">Phone Number (accepts calls)</label>
                            <input type="tel" id="ds_location_phone" name="ds_location_phone" 
                                   value="<?php echo esc_attr($data['phone']); ?>" class="regular-text" 
                                   placeholder="(555) 123-4567">
                        </div>
                        
                        <div class="ds-field">
                            <label>
                                <input type="checkbox" id="ds_location_same_number" <?php checked($same_number); ?>>
                                Same number accepts texts
                            </label>
                        </div>
                        
                        <div class="ds-field">
                            <label for="ds_location_text_phone">Phone Number (accepts texts)</label>
                            <input type="tel" id="ds_location_text_phone" name="ds_location_text_phone" 
                                   value="<?php echo esc_attr($same_number ? $data['phone'] : $data['text_phone']); ?>" 
                                   class="regular-text" placeholder="(555) 123-4567"
                                   <?php echo $same_number ? 'readonly' : ''; ?>>
                            <p class="description">Uncheck the box above for a different number, or leave this blank if this location can't receive texts at all (e.g. a landline).</p>
                        </div>
                        
                        <div class="ds-field">
                            <label for="ds_location_email">Email Address</label>
                            <input type="email" id="ds_location_email" name="ds_location_email" 
                                   value="<?php echo esc_attr($data['email']); ?>" class="regular-text"
                                   placeholder="contact@example.com">
                        </div>
                        
                        <div class="ds-field">
                            <label for="ds_location_website">Website URL</label>
                            <input type="url" id="ds_location_website" name="ds_location_website" 
                                   value="<?php echo esc_attr($data['website']); ?>" class="regular-text"
                                   placeholder="https://example.com">
                            <p class="description">External website for this location (if they have their own site)</p>
                        </div>
                    </div>
                    
                    <!-- Address & Map -->
                    <div class="ds-settings-section">
                        <h2>📍 Address & Map</h2>
                        
                        <div class="ds-field">
                            <label for="ds_location_address">Street Address</label>
                            <textarea id="ds_location_address" name="ds_location_address" 
                                      rows="3" class="large-text"><?php echo esc_textarea($data['address']); ?></textarea>
                            <p class="description">Full mailing address (include street, city, state, zip on separate lines)</p>
                        </div>
                        
                        <div class="ds-field-row">
                            <div class="ds-field ds-field-half">
                                <label for="ds_location_latitude">Latitude</label>
                                <input type="text" id="ds_location_latitude" name="ds_location_latitude" 
                                       value="<?php echo esc_attr($data['latitude']); ?>" 
                                       placeholder="33.7490">
                            </div>
                            <div class="ds-field ds-field-half">
                                <label for="ds_location_longitude">Longitude</label>
                                <input type="text" id="ds_location_longitude" name="ds_location_longitude" 
                                       value="<?php echo esc_attr($data['longitude']); ?>" 
                                       placeholder="-84.3880">
                            </div>
                        </div>
                        <p class="description">
                            Map coordinates for the location pin. 
                            <a href="https://www.latlong.net/" target="_blank">Find coordinates →</a>
                        </p>
                    </div>
                    
                    <!-- Branding -->
                    <div class="ds-settings-section">
                        <h2>🎨 Branding</h2>
                        
                        <div class="ds-field">
                            <label>Location Logo</label>
                            <div class="ds-image-uploader" data-target="ds_location_logo">
                                <input type="hidden" id="ds_location_logo" name="ds_location_logo" 
                                       value="<?php echo esc_attr($data['logo']); ?>">
                                
                                <div class="ds-image-preview <?php echo $logo_url ? 'has-image' : ''; ?>">
                                    <?php if ($logo_url): ?>
                                        <img src="<?php echo esc_url($logo_url); ?>" alt="Location Logo">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="ds-image-actions">
                                    <button type="button" class="button ds-upload-image">
                                        <?php echo $logo_url ? 'Change Logo' : 'Upload Logo'; ?>
                                    </button>
                                    <button type="button" class="button ds-remove-image <?php echo $logo_url ? '' : 'hidden'; ?>">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <p class="description">Logo displayed in the hero section (optional, recommended: PNG with transparent background)</p>
                        </div>
                        
                        <div class="ds-field">
                            <label>Featured Image</label>
                            <div class="ds-image-uploader" data-target="ds_featured_image">
                                <input type="hidden" id="ds_featured_image" name="ds_featured_image" 
                                       value="<?php echo esc_attr($featured_image_id); ?>">
                                
                                <div class="ds-image-preview ds-image-preview-large <?php echo $featured_image_url ? 'has-image' : ''; ?>">
                                    <?php if ($featured_image_url): ?>
                                        <img src="<?php echo esc_url($featured_image_url); ?>" alt="Featured Image">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="ds-image-actions">
                                    <button type="button" class="button ds-upload-image">
                                        <?php echo $featured_image_url ? 'Change Image' : 'Upload Image'; ?>
                                    </button>
                                    <button type="button" class="button ds-remove-image <?php echo $featured_image_url ? '' : 'hidden'; ?>">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <p class="description">Main image for this location (displayed in hero and cards)</p>
                        </div>
                        
                        <div class="ds-field">
                            <label>Program Flyer</label>
                            <div class="ds-flyer-uploader">
                                <input type="hidden" id="ds_location_flyer" name="ds_location_flyer" 
                                       value="<?php echo esc_attr($data['flyer']); ?>">
                                
                                <div class="ds-flyer-preview">
                                    <?php if ($flyer_url): ?>
                                        <a href="<?php echo esc_url($flyer_url); ?>" target="_blank"><?php echo esc_html(basename($flyer_url)); ?></a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="ds-image-actions">
                                    <button type="button" class="button ds-upload-flyer">
                                        <?php echo $flyer_url ? 'Change Flyer' : 'Upload Flyer'; ?>
                                    </button>
                                    <button type="button" class="button ds-remove-flyer <?php echo $flyer_url ? '' : 'hidden'; ?>">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <p class="description">Optional PDF or image — becomes a "Download our flyer" button on the location page and in the app.</p>
                        </div>
                    </div>
                    
                </div>
                
                <div class="ds-settings-footer">
                    <button type="submit" class="button button-primary button-hero">
                        💾 Save Location Settings
                    </button>
                </div>
                
            </form>
        </div>
        
        <style>
            .ds-location-settings-wrap {
                max-width: 1200px;
            }
            
            .ds-location-settings-header {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px solid #c3c4c7;
            }
            
            .ds-location-settings-actions {
                display: flex;
                gap: 10px;
            }
            
            .ds-settings-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
            }
            
            @media (max-width: 1024px) {
                .ds-settings-grid {
                    grid-template-columns: 1fr;
                }
            }
            
            .ds-settings-section {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                padding: 20px;
            }
            
            .ds-settings-section h2 {
                margin-top: 0;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
                font-size: 1.1em;
            }
            
            .ds-field {
                margin-bottom: 20px;
            }
            
            .ds-field:last-child {
                margin-bottom: 0;
            }
            
            .ds-field label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            
            .ds-field input[type="text"],
            .ds-field input[type="email"],
            .ds-field input[type="tel"],
            .ds-field input[type="url"],
            .ds-field textarea {
                width: 100%;
            }
            
            .ds-field .description {
                color: #646970;
                font-size: 13px;
                margin-top: 5px;
            }
            
            .ds-field-row {
                display: flex;
                gap: 15px;
            }
            
            .ds-field-half {
                flex: 1;
            }
            
            .ds-image-uploader {
                display: flex;
                align-items: flex-start;
                gap: 15px;
            }
            
            .ds-image-preview {
                width: 120px;
                height: 120px;
                border: 2px dashed #c3c4c7;
                border-radius: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f6f7f7;
                overflow: hidden;
            }
            
            .ds-image-preview.has-image {
                border-style: solid;
            }
            
            .ds-image-preview img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }
            
            .ds-image-preview-large {
                width: 200px;
                height: 150px;
            }
            
            .ds-image-preview-large img {
                object-fit: cover;
                width: 100%;
                height: 100%;
            }
            
            .ds-image-actions {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .ds-image-actions .hidden {
                display: none;
            }
            
            .ds-settings-footer {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #c3c4c7;
            }
            
            .button-hero {
                padding: 10px 30px !important;
                height: auto !important;
                font-size: 14px !important;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Keep "Phone Number (accepts texts)" mirrored to "Phone Number (accepts calls)"
            // whenever "Same number accepts texts" is checked.
            var $callPhone = $('#ds_location_phone');
            var $sameNumber = $('#ds_location_same_number');
            var $textPhone = $('#ds_location_text_phone');

            function syncTextPhone() {
                if ($sameNumber.is(':checked')) {
                    $textPhone.val($callPhone.val()).prop('readonly', true);
                } else {
                    $textPhone.prop('readonly', false);
                }
            }

            $sameNumber.on('change', syncTextPhone);
            $callPhone.on('input', function() {
                if ($sameNumber.is(':checked')) {
                    $textPhone.val($callPhone.val());
                }
            });

            // Image uploader functionality
            $('.ds-image-uploader').each(function() {
                var $uploader = $(this);
                var $input = $uploader.find('input[type="hidden"]');
                var $preview = $uploader.find('.ds-image-preview');
                var $uploadBtn = $uploader.find('.ds-upload-image');
                var $removeBtn = $uploader.find('.ds-remove-image');
                var targetId = $uploader.data('target');
                var mediaUploader;
                
                $uploadBtn.on('click', function(e) {
                    e.preventDefault();
                    
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    
                    mediaUploader = wp.media({
                        title: 'Choose Image',
                        button: { text: 'Use this image' },
                        multiple: false,
                        library: { type: 'image' }
                    });
                    
                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $input.val(attachment.id);
                        $preview.addClass('has-image').html('<img src="' + attachment.url + '" alt="">');
                        $uploadBtn.text('Change Image');
                        $removeBtn.removeClass('hidden');
                    });
                    
                    mediaUploader.open();
                });
                
                $removeBtn.on('click', function(e) {
                    e.preventDefault();
                    $input.val('');
                    $preview.removeClass('has-image').html('');
                    $uploadBtn.text('Upload Image');
                    $(this).addClass('hidden');
                });
            });

            // Flyer uploader (PDF or image — kept separate from the image-only uploader above)
            var $flyerInput = $('#ds_location_flyer');
            var $flyerPreview = $('.ds-flyer-preview');
            var $flyerUploadBtn = $('.ds-upload-flyer');
            var $flyerRemoveBtn = $('.ds-remove-flyer');
            var flyerUploader;

            $flyerUploadBtn.on('click', function(e) {
                e.preventDefault();

                if (flyerUploader) {
                    flyerUploader.open();
                    return;
                }

                flyerUploader = wp.media({
                    title: 'Choose Flyer',
                    button: { text: 'Use this file' },
                    multiple: false,
                    library: { type: ['application/pdf', 'image'] }
                });

                flyerUploader.on('select', function() {
                    var attachment = flyerUploader.state().get('selection').first().toJSON();
                    $flyerInput.val(attachment.id);
                    $flyerPreview.html('<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>');
                    $flyerUploadBtn.text('Change Flyer');
                    $flyerRemoveBtn.removeClass('hidden');
                });

                flyerUploader.open();
            });

            $flyerRemoveBtn.on('click', function(e) {
                e.preventDefault();
                $flyerInput.val('');
                $flyerPreview.html('');
                $flyerUploadBtn.text('Upload Flyer');
                $(this).addClass('hidden');
            });
        });
        </script>
        <?php
    }

    /**
     * Get the location ID that current user can edit
     */
    private function get_editable_location_id() {
        // If location_id is passed in URL, use that (for admins selecting different locations)
        if (isset($_GET['location_id'])) {
            $location_id = intval($_GET['location_id']);
            if ($location_id && current_user_can('edit_post', $location_id)) {
                return $location_id;
            }
        }
        
        // For location managers, use their assigned location
        $user = wp_get_current_user();
        if (in_array('ds_location_manager', (array) $user->roles)) {
            $assigned = get_user_meta($user->ID, 'ds_assigned_location', true);
            if ($assigned) {
                return intval($assigned);
            }
            return 0;
        }
        
        // For admins, require them to select a location via URL parameter
        // This prevents auto-selecting the first location
        if (current_user_can('administrator')) {
            // If no location_id in URL, show selector instead
            return 0;
        }
        
        return 0;
    }

    /**
     * Handle settings page form submission
     */
    public function handle_settings_save() {
        // Verify nonce
        if (!isset($_POST['ds_location_settings_nonce']) || 
            !wp_verify_nonce($_POST['ds_location_settings_nonce'], 'ds_location_settings')) {
            wp_die('Security check failed.');
        }
        
        $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
        
        if (!$location_id || !current_user_can('edit_post', $location_id)) {
            wp_die('You do not have permission to edit this location.');
        }

        DS_Location_Data::save($location_id, $_POST);

        // Sync taxonomy term
        $this->plugin->ensure_term_for_location($location_id);
        
        // Redirect back to settings page with success message
        $redirect_url = admin_url('admin.php?page=ds-location-settings&location_id=' . $location_id . '&saved=1');
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Handle "+ Add New Location" — the sanctioned creation path.
     * Admin-only: location managers are assigned to an existing location
     * rather than expected to create new ones.
     */
    public function handle_create_location() {
        if (!isset($_POST['ds_create_location_nonce']) ||
            !wp_verify_nonce($_POST['ds_create_location_nonce'], 'ds_create_location')) {
            wp_die('Security check failed.');
        }

        if (!current_user_can('publish_locations')) {
            wp_die('You do not have permission to create a location.');
        }

        $location_id = DS_Location_Data::create();

        if (!$location_id) {
            wp_die('Could not create the new location. Please try again.');
        }

        $this->plugin->ensure_term_for_location($location_id);

        $redirect_url = admin_url('admin.php?page=ds-location-settings&location_id=' . $location_id . '&created=1');
        wp_safe_redirect($redirect_url);
        exit;
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

        // Get term id
        $term_id = 0;
        if (taxonomy_exists('ds_post_location')) {
            $term_id = $this->plugin->get_term_id_for_location($user_location_id);
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
                        <a href="<?php echo admin_url('admin.php?page=ds-location-settings'); ?>" class="button">Location Settings</a>
                        <a href="<?php echo get_edit_post_link($user_location_id); ?>" class="button">Edit Page Content</a>
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
                    $website = get_post_meta($user_location_id, '_ds_location_website', true);
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

                    <?php if ($website) : ?>
                        <p><strong>Website:</strong> <a href="<?php echo esc_url($website); ?>" target="_blank"><?php echo esc_html($website); ?></a></p>
                    <?php endif; ?>
                    
                    <p><a href="<?php echo admin_url('admin.php?page=ds-location-settings'); ?>">Edit location details &rarr;</a></p>
                </div>

                <div class="ds-dashboard-widget">
                    <h3>Help & Resources</h3>
                    <ul>
                        <li><a href="#" onclick="alert('Help documentation coming soon!')">How to create posts</a></li>
                        <li><a href="#" onclick="alert('Help documentation coming soon!')">Using block patterns</a></li>
                        <li><a href="#" onclick="alert('Help documentation coming soon!')">Customizing your location page</a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=ds-location-settings'); ?>">Edit location settings</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
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
                    'href' => admin_url('admin.php?page=ds-location-settings')
                ));

                $wp_admin_bar->add_node(array(
                    'id' => 'ds-location-settings',
                    'parent' => 'ds-location-quick',
                    'title' => 'Location Settings',
                    'href' => admin_url('admin.php?page=ds-location-settings')
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
                    'title' => 'Edit Page Content',
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
     * Ensure author column appears for ds_location
     */
    public function location_posts_columns($columns) {
        if (isset($columns['author'])) {
            return $columns;
        }

        $new = array();
        foreach ($columns as $k => $v) {
            $new[$k] = $v;
            if ($k === 'title') {
                $new['author'] = __('Author');
            }
        }
        return $new;
    }

    /**
     * Make the author column sortable
     */
    public function location_sortable_columns($columns) {
        $columns['author'] = 'author';
        return $columns;
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
}
