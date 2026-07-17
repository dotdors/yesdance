<?php
/**
 * DS Location Data
 *
 * Single read/write path for location data. Used by:
 * - the meta box save handler (standard editor)
 * - the settings-page save handler
 * - the "+ Add New Location" creation flow
 * - get_location_display_data() (front-end template + prepend_location_data_to_post)
 * - the /ds/v1/locations REST endpoint
 *
 * Data-model note: a location's CITY is its post title, not a separate meta
 * field — _ds_location_city is kept as a synced mirror only, for any code
 * still reading that meta key directly (kept in sync automatically here).
 *
 * @package DS_Location_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Location_Data {

    /**
     * Get every registered field for a location, plus computed values
     * (city, logo/flyer URLs, featured image) that templates and REST need.
     *
     * @param int $location_id
     * @return array
     */
    public static function get_all($location_id) {
        $data = array(
            'id'   => $location_id,
            'city' => get_the_title($location_id), // post title IS the city
        );

        foreach (DS_Location_Fields::all() as $field => $config) {
            $data[$field] = get_post_meta($location_id, $config['meta_key'], true);
        }

        $data['logo_url']            = $data['logo'] ? wp_get_attachment_url($data['logo']) : '';
        $data['logo_url_medium']     = $data['logo'] ? wp_get_attachment_image_url($data['logo'], 'medium') : '';
        $data['flyer_url']           = $data['flyer'] ? wp_get_attachment_url($data['flyer']) : '';
        $data['featured_image_id']   = get_post_thumbnail_id($location_id);
        $data['featured_image']      = get_the_post_thumbnail_url($location_id, 'large');
        $data['featured_image_medium'] = get_the_post_thumbnail_url($location_id, 'medium');

        return $data;
    }

    /**
     * Get a single field's raw value. Rarely needed over get_all(), but
     * handy for one-off reads.
     *
     * @param int $location_id
     * @param string $field
     * @return mixed
     */
    public static function get($location_id, $field) {
        if ($field === 'city') {
            return get_the_title($location_id);
        }

        $config = DS_Location_Fields::get($field);
        if (!$config) {
            return '';
        }

        return get_post_meta($location_id, $config['meta_key'], true);
    }

    /**
     * Save posted form values for a location. This is the one save path —
     * the meta box, the settings page, and location creation all call this
     * instead of each looping their own copy of the field list.
     *
     * @param int $location_id
     * @param array $posted Raw $_POST-style array (ds_location_* keys, plus ds_featured_image)
     */
    public static function save($location_id, array $posted) {
        // City lives on the post title. Update it when a value was
        // actually submitted; otherwise just re-sync the mirror meta so
        // it can never silently drift from whatever the title already is.
        if (isset($posted['ds_location_city']) && trim($posted['ds_location_city']) !== '') {
            $city = sanitize_text_field($posted['ds_location_city']);
            wp_update_post(array('ID' => $location_id, 'post_title' => $city));
            update_post_meta($location_id, '_ds_location_city', $city);
        } else {
            self::sync_city_from_title($location_id);
        }

        foreach (DS_Location_Fields::all() as $field => $config) {
            $post_key = 'ds_location_' . $field;

            if ($config['type'] === 'attachment') {
                if (isset($posted[$post_key])) {
                    $attachment_id = intval($posted[$post_key]);
                    if ($attachment_id > 0) {
                        update_post_meta($location_id, $config['meta_key'], $attachment_id);
                    } else {
                        delete_post_meta($location_id, $config['meta_key']);
                    }
                }
                continue;
            }

            if (!isset($posted[$post_key])) {
                continue;
            }

            $value = call_user_func($config['sanitize'], $posted[$post_key]);

            // Empty URL/email fields get removed rather than stored as ''
            if (($config['type'] === 'url' || $config['type'] === 'email') && $value === '') {
                delete_post_meta($location_id, $config['meta_key']);
                continue;
            }

            update_post_meta($location_id, $config['meta_key'], $value);
        }

        // Featured image is a post thumbnail, not a custom meta field
        if (isset($posted['ds_featured_image'])) {
            $featured_id = intval($posted['ds_featured_image']);
            if ($featured_id > 0) {
                set_post_thumbnail($location_id, $featured_id);
            } else {
                delete_post_thumbnail($location_id);
            }
        }
    }

    /**
     * Keep _ds_location_city mirrored to the post title, whatever path
     * changed the title (standard editor included).
     *
     * @param int $location_id
     * @return string The synced city value
     */
    public static function sync_city_from_title($location_id) {
        $title = get_the_title($location_id);
        update_post_meta($location_id, '_ds_location_city', $title);
        return $title;
    }

    /**
     * Create a new, empty location and hand back its ID, ready for the
     * settings page. The only sanctioned creation path outside of the
     * sample location created on plugin activation.
     *
     * @return int Location post ID, or 0 on failure
     */
    public static function create() {
        $location_id = wp_insert_post(array(
            'post_title'  => '',
            'post_type'   => 'ds_location',
            'post_status' => 'draft',
        ));

        if (is_wp_error($location_id) || !$location_id) {
            return 0;
        }

        update_post_meta($location_id, '_ds_location_city', '');

        return $location_id;
    }
}
