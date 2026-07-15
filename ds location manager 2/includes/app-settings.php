<?php
/**
 * App Settings - Mobile App Configuration
 * Manages content and settings for the YYCD mobile app
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_App_Settings {
    
    private $plugin;
    
    public function __construct($plugin) {
        $this->plugin = $plugin;
        
        // Add admin menu page
        add_action('admin_menu', array($this, 'add_app_settings_page'));
        
        // Handle form submission
        add_action('admin_post_ds_save_app_settings', array($this, 'handle_settings_save'));
        
        // Register REST API endpoint
        add_action('rest_api_init', array($this, 'register_splash_endpoint'));
    }
    
    /**
     * Add App Settings submenu under Locations
     */
    public function add_app_settings_page() {
        add_submenu_page(
            'edit.php?post_type=ds_location',
            'App Settings',
            'App Settings',
            'manage_options',  // Only admins can access
            'ds-app-settings',
            array($this, 'render_app_settings_page')
        );
    }
    
    /**
     * Render the App Settings page
     */
    public function render_app_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        // Get current settings
        $splash_logo_id = get_option('ds_app_splash_logo', '');
        $splash_image_id = get_option('ds_app_splash_image', '');
        $splash_text = get_option('ds_app_splash_text', 'Experience the joy of ballroom dance. Find your local location and join our community.');
        
        $logo_url = $splash_logo_id ? wp_get_attachment_url($splash_logo_id) : '';
        $image_url = $splash_image_id ? wp_get_attachment_url($splash_image_id) : '';
        
        // Check for save message
        $saved = isset($_GET['saved']) && $_GET['saved'] === '1';
        
        // Enqueue media uploader
        wp_enqueue_media();
        
        ?>
        <div class="wrap ds-app-settings-wrap">
            <h1>Mobile App Settings</h1>
            
            <?php if ($saved): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>App settings saved successfully!</strong></p>
                </div>
            <?php endif; ?>
            
            <p class="description" style="max-width: 800px; margin-bottom: 20px;">
                Configure content for the YYCD mobile app. Changes here will be immediately available to the app via the REST API.
            </p>
            
            <!-- API Endpoint Info -->
            <div class="notice notice-info" style="max-width: 800px; margin-bottom: 30px;">
                <p>
                    <strong>API Endpoint:</strong> 
                    <code><?php echo esc_html(rest_url('ds/v1/splash')); ?></code>
                    <a href="<?php echo esc_url(rest_url('ds/v1/splash')); ?>" target="_blank" class="button button-small" style="margin-left: 10px;">
                        Test Endpoint
                    </a>
                </p>
            </div>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="ds_save_app_settings">
                <?php wp_nonce_field('ds_app_settings', 'ds_app_settings_nonce'); ?>
                
                <table class="form-table" role="presentation">
                    
                    <!-- Splash Screen Section -->
                    <tr>
                        <th colspan="2">
                            <h2 style="margin: 30px 0 10px 0; padding: 0;">Splash Screen / Landing Page</h2>
                            <p class="description">Content displayed when users first open the app</p>
                        </th>
                    </tr>
                    
                    <!-- Logo Image -->
                    <tr>
                        <th scope="row">
                            <label for="splash_logo">App Logo</label>
                        </th>
                        <td>
                            <div class="ds-media-upload-field">
                                <div class="ds-media-preview" style="margin-bottom: 10px;">
                                    <?php if ($logo_url): ?>
                                        <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 10px; background: white;">
                                    <?php else: ?>
                                        <div style="width: 200px; height: 100px; border: 2px dashed #ddd; display: flex; align-items: center; justify-content: center; color: #999;">
                                            No logo selected
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="splash_logo" id="splash_logo" value="<?php echo esc_attr($splash_logo_id); ?>">
                                <button type="button" class="button ds-upload-media-button" data-field="splash_logo">
                                    <?php echo $logo_url ? 'Change Logo' : 'Select Logo'; ?>
                                </button>
                                <?php if ($logo_url): ?>
                                    <button type="button" class="button ds-remove-media-button" data-field="splash_logo">Remove</button>
                                <?php endif; ?>
                                <p class="description">
                                    The YYCD logo displayed at the top of the app. Recommended: PNG with transparent background, minimum 400px wide.
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Splash Image -->
                    <tr>
                        <th scope="row">
                            <label for="splash_image">Splash Image</label>
                        </th>
                        <td>
                            <div class="ds-media-upload-field">
                                <div class="ds-media-preview" style="margin-bottom: 10px;">
                                    <?php if ($image_url): ?>
                                        <img src="<?php echo esc_url($image_url); ?>" style="max-width: 400px; height: auto; border: 1px solid #ddd;">
                                    <?php else: ?>
                                        <div style="width: 400px; height: 250px; border: 2px dashed #ddd; display: flex; align-items: center; justify-content: center; color: #999;">
                                            No image selected
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="splash_image" id="splash_image" value="<?php echo esc_attr($splash_image_id); ?>">
                                <button type="button" class="button ds-upload-media-button" data-field="splash_image">
                                    <?php echo $image_url ? 'Change Image' : 'Select Image'; ?>
                                </button>
                                <?php if ($image_url): ?>
                                    <button type="button" class="button ds-remove-media-button" data-field="splash_image">Remove</button>
                                <?php endif; ?>
                                <p class="description">
                                    Photo of people dancing displayed on the splash screen. Recommended: 800x500px or larger, JPG or PNG.
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Splash Text -->
                    <tr>
                        <th scope="row">
                            <label for="splash_text">Welcome Message</label>
                        </th>
                        <td>
                            <textarea name="splash_text" id="splash_text" rows="3" class="large-text" maxlength="200"><?php echo esc_textarea($splash_text); ?></textarea>
                            <p class="description">
                                Brief welcome message displayed below the image (max 200 characters). Keep it short and inviting!
                            </p>
                        </td>
                    </tr>
                    
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">Save App Settings</button>
                </p>
            </form>
        </div>
        
        <style>
            .ds-app-settings-wrap h2 {
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
            }
            .ds-media-upload-field img {
                display: block;
            }
            .ds-media-preview {
                min-height: 50px;
            }
            .ds-remove-media-button {
                color: #b32d2e;
                border-color: #b32d2e;
                margin-left: 10px;
            }
            .ds-remove-media-button:hover {
                background: #b32d2e;
                color: white;
                border-color: #b32d2e;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Media uploader
            $('.ds-upload-media-button').on('click', function(e) {
                e.preventDefault();
                
                var button = $(this);
                var fieldName = button.data('field');
                var field = $('#' + fieldName);
                var preview = button.closest('.ds-media-upload-field').find('.ds-media-preview');
                
                var frame = wp.media({
                    title: 'Select Image',
                    button: { text: 'Use This Image' },
                    multiple: false
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    field.val(attachment.id);
                    
                    // Update preview
                    preview.html('<img src="' + attachment.url + '" style="max-width: ' + 
                        (fieldName === 'splash_logo' ? '200px' : '400px') + '; height: auto; border: 1px solid #ddd;">');
                    
                    // Update button text
                    button.text(fieldName === 'splash_logo' ? 'Change Logo' : 'Change Image');
                    
                    // Show remove button if not already visible
                    if (!button.siblings('.ds-remove-media-button').length) {
                        button.after('<button type="button" class="button ds-remove-media-button" data-field="' + 
                            fieldName + '">Remove</button>');
                    }
                });
                
                frame.open();
            });
            
            // Remove media
            $(document).on('click', '.ds-remove-media-button', function(e) {
                e.preventDefault();
                
                var button = $(this);
                var fieldName = button.data('field');
                var field = $('#' + fieldName);
                var preview = button.closest('.ds-media-upload-field').find('.ds-media-preview');
                var uploadButton = button.siblings('.ds-upload-media-button');
                
                // Clear field
                field.val('');
                
                // Update preview
                var placeholderText = fieldName === 'splash_logo' ? 'No logo selected' : 'No image selected';
                var dimensions = fieldName === 'splash_logo' ? 'width: 200px; height: 100px;' : 'width: 400px; height: 250px;';
                preview.html('<div style="' + dimensions + ' border: 2px dashed #ddd; display: flex; align-items: center; justify-content: center; color: #999;">' + 
                    placeholderText + '</div>');
                
                // Update button text
                uploadButton.text(fieldName === 'splash_logo' ? 'Select Logo' : 'Select Image');
                
                // Remove this button
                button.remove();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle settings form submission
     */
    public function handle_settings_save() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to save these settings.');
        }
        
        // Verify nonce
        if (!isset($_POST['ds_app_settings_nonce']) || 
            !wp_verify_nonce($_POST['ds_app_settings_nonce'], 'ds_app_settings')) {
            wp_die('Security check failed.');
        }
        
        // Save settings
        $splash_logo = isset($_POST['splash_logo']) ? intval($_POST['splash_logo']) : '';
        $splash_image = isset($_POST['splash_image']) ? intval($_POST['splash_image']) : '';
        $splash_text = isset($_POST['splash_text']) ? sanitize_textarea_field($_POST['splash_text']) : '';
        
        // Limit splash text to 200 characters
        $splash_text = substr($splash_text, 0, 200);
        
        update_option('ds_app_splash_logo', $splash_logo);
        update_option('ds_app_splash_image', $splash_image);
        update_option('ds_app_splash_text', $splash_text);
        
        // Redirect back with success message
        wp_redirect(add_query_arg('saved', '1', admin_url('edit.php?post_type=ds_location&page=ds-app-settings')));
        exit;
    }
    
    /**
     * Register REST API endpoint for splash screen data
     */
    public function register_splash_endpoint() {
        register_rest_route('ds/v1', '/splash', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_splash_data'),
            'permission_callback' => '__return_true', // Public endpoint
        ));
    }
    
    /**
     * Get splash screen data for API
     */
    public function get_splash_data($request) {
        $splash_logo_id = get_option('ds_app_splash_logo', '');
        $splash_image_id = get_option('ds_app_splash_image', '');
        $splash_text = get_option('ds_app_splash_text', 'Experience the joy of ballroom dance. Find your local location and join our community.');
        
        // Get full URLs for images
        $logo_url = $splash_logo_id ? wp_get_attachment_url($splash_logo_id) : '';
        $image_url = $splash_image_id ? wp_get_attachment_url($splash_image_id) : '';
        
        return array(
            'title_url' => $logo_url,
            'splash_url' => $image_url,
            'splash_text' => $splash_text,
        );
    }
}
