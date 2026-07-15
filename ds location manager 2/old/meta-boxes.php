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
    }
    
    /**
     * Add meta boxes
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

    /**
     * Location details meta box callback
     */
    public function location_details_meta_box($post) {
        wp_nonce_field('ds_location_meta', 'ds_location_meta_nonce');

        $location_name = get_post_meta($post->ID, '_ds_location_name', true) ?: $post->post_title;
        $city = get_post_meta($post->ID, '_ds_location_city', true);
        $address = get_post_meta($post->ID, '_ds_location_address', true);
        $phone = get_post_meta($post->ID, '_ds_location_phone', true);
        $email = get_post_meta($post->ID, '_ds_location_email', true);
        $contact_name = get_post_meta($post->ID, '_ds_location_contact_name', true);
        $description = get_post_meta($post->ID, '_ds_location_description', true);
        $yycd_description = get_post_meta($post->ID, '_ds_location_yycd_description', true);
        $logo_id = get_post_meta($post->ID, '_ds_location_logo', true);
        $latitude = get_post_meta($post->ID, '_ds_location_latitude', true);
        $longitude = get_post_meta($post->ID, '_ds_location_longitude', true);
        
        $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
        ?>
        <style>
            .ds-location-field { margin-bottom: 15px; }
            .ds-location-field label { display: block; font-weight: bold; margin-bottom: 3px; }
            .ds-location-field input[type="text"],
            .ds-location-field input[type="email"],
            .ds-location-field textarea { width: 100%; }
            .ds-location-field textarea { resize: vertical; }
            .ds-location-field textarea.short { height: 60px; }
            .ds-location-field textarea.tall { height: 120px; }
            .ds-location-field small { display: block; color: #666; margin-top: 3px; }
            .ds-logo-preview { margin-top: 10px; max-width: 200px; }
            .ds-logo-preview img { max-width: 100%; height: auto; border: 1px solid #ddd; padding: 5px; background: #f9f9f9; }
            .ds-location-coords { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
            .button.remove-logo { margin-top: 5px; }
        </style>

        <div class="ds-location-field">
            <label for="ds_location_name">Location Name:</label>
            <input type="text" id="ds_location_name" name="ds_location_name" value="<?php echo esc_attr($location_name); ?>" />
            <small>Display name for this location</small>
        </div>

        <div class="ds-location-field">
            <label for="ds_location_logo">Location Logo:</label>
            <input type="hidden" id="ds_location_logo" name="ds_location_logo" value="<?php echo esc_attr($logo_id); ?>" />
            <button type="button" class="button upload-logo-button">
                <?php echo $logo_url ? 'Change Logo' : 'Upload Logo'; ?>
            </button>
            <?php if ($logo_url): ?>
                <button type="button" class="button remove-logo">Remove Logo</button>
                <div class="ds-logo-preview">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="Location Logo" />
                </div>
            <?php endif; ?>
            <small>Logo displayed in hero section (optional)</small>
        </div>

        <div class="ds-location-field">
            <label for="ds_location_city">City:</label>
            <input type="text" id="ds_location_city" name="ds_location_city" value="<?php echo esc_attr($city); ?>" />
            <small>If left blank, will attempt to extract from address</small>
        </div>

        <div class="ds-location-field">
            <label for="ds_location_address">Address:</label>
            <textarea id="ds_location_address" name="ds_location_address" class="short"><?php echo esc_textarea($address); ?></textarea>
            <small>Full mailing address</small>
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
            <textarea id="ds_location_description" name="ds_location_description" class="short"><?php echo esc_textarea($description); ?></textarea>
            <small>Brief tagline or intro (displays in hero section)</small>
        </div>

        <div class="ds-location-field">
            <label for="ds_location_yycd_description">YYCD Program Description:</label>
            <textarea id="ds_location_yycd_description" name="ds_location_yycd_description" class="tall"><?php echo esc_textarea($yycd_description); ?></textarea>
            <small>Detailed description of the YYCD program at this location</small>
        </div>

        <div class="ds-location-field">
            <label>Map Coordinates:</label>
            <div class="ds-location-coords">
                <div>
                    <label for="ds_location_latitude">Latitude:</label>
                    <input type="text" id="ds_location_latitude" name="ds_location_latitude" value="<?php echo esc_attr($latitude); ?>" placeholder="33.7490" />
                </div>
                <div>
                    <label for="ds_location_longitude">Longitude:</label>
                    <input type="text" id="ds_location_longitude" name="ds_location_longitude" value="<?php echo esc_attr($longitude); ?>" placeholder="-84.3880" />
                </div>
            </div>
            <small>For map display. Find coordinates at <a href="https://www.latlong.net/" target="_blank">latlong.net</a></small>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var mediaUploader;
            
            $('.upload-logo-button').on('click', function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: 'Choose Location Logo',
                    button: { text: 'Use this logo' },
                    multiple: false,
                    library: { type: 'image' }
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#ds_location_logo').val(attachment.id);
                    $('.upload-logo-button').text('Change Logo');
                    
                    if ($('.ds-logo-preview').length) {
                        $('.ds-logo-preview img').attr('src', attachment.url);
                    } else {
                        $('.upload-logo-button').after(
                            '<button type="button" class="button remove-logo">Remove Logo</button>' +
                            '<div class="ds-logo-preview"><img src="' + attachment.url + '" alt="Location Logo" /></div>'
                        );
                    }
                    
                    bindRemoveButton();
                });
                
                mediaUploader.open();
            });
            
            function bindRemoveButton() {
                $('.remove-logo').off('click').on('click', function(e) {
                    e.preventDefault();
                    $('#ds_location_logo').val('');
                    $('.ds-logo-preview').remove();
                    $('.remove-logo').remove();
                    $('.upload-logo-button').text('Upload Logo');
                });
            }
            
            bindRemoveButton();
        });
        </script>
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
     * Save location meta data
     */
    public function save_location_meta($post_id) {
        if (!isset($_POST['ds_location_meta_nonce']) || 
            !wp_verify_nonce($_POST['ds_location_meta_nonce'], 'ds_location_meta')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (get_post_type($post_id) !== 'ds_location') {
            return;
        }

        // Save text fields
        $text_fields = array('name', 'city', 'phone', 'email', 'contact_name', 'latitude', 'longitude');
        foreach ($text_fields as $field) {
            if (isset($_POST['ds_location_' . $field])) {
                update_post_meta($post_id, '_ds_location_' . $field, sanitize_text_field($_POST['ds_location_' . $field]));
            }
        }

        // Save textarea fields
        $textarea_fields = array('address', 'description', 'yycd_description');
        foreach ($textarea_fields as $field) {
            if (isset($_POST['ds_location_' . $field])) {
                update_post_meta($post_id, '_ds_location_' . $field, sanitize_textarea_field($_POST['ds_location_' . $field]));
            }
        }

        // Save logo ID
        if (isset($_POST['ds_location_logo'])) {
            $logo_id = intval($_POST['ds_location_logo']);
            if ($logo_id > 0) {
                update_post_meta($post_id, '_ds_location_logo', $logo_id);
            } else {
                delete_post_meta($post_id, '_ds_location_logo');
            }
        }

        // Auto-extract city if empty
        if (empty($_POST['ds_location_city']) && !empty($_POST['ds_location_address'])) {
            $city = $this->plugin->extract_city_from_address($_POST['ds_location_address']);
            if ($city) {
                update_post_meta($post_id, '_ds_location_city', sanitize_text_field($city));
            }
        }

        // Store location ID for easier querying
        update_post_meta($post_id, '_ds_location_id', $post_id);

        // Sync taxonomy term
        $this->plugin->ensure_term_for_location($post_id);
    }
}