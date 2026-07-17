<?php
/**
 * DS Location Fields
 *
 * The field registry for locations: one array describing every meta-backed
 * field, its meta key, and how to sanitize it. This is what DS_Location_Data
 * loops over to save and read location fields, so adding a new field means
 * adding one row here instead of touching the meta box, the settings page,
 * and REST separately.
 *
 * Deliberately NOT included here:
 * - 'city'      — lives on the post title, not a meta field (see DS_Location_Data)
 * - 'featured'  — the post thumbnail, handled via set_post_thumbnail()
 *
 * @package DS_Location_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Location_Fields {

    /**
     * @return array<string, array{meta_key: string, type: string, sanitize: callable}>
     */
    public static function all() {
        return array(
            'name' => array(
                'meta_key' => '_ds_location_name',
                'type'     => 'text',
                'sanitize' => 'sanitize_text_field',
            ),
            'address' => array(
                'meta_key' => '_ds_location_address',
                'type'     => 'textarea',
                'sanitize' => 'sanitize_textarea_field',
            ),
            'phone' => array(
                'meta_key' => '_ds_location_phone',
                'type'     => 'text',
                'sanitize' => 'sanitize_text_field',
            ),
            'text_phone' => array(
                'meta_key' => '_ds_location_text_phone',
                'type'     => 'text',
                'sanitize' => 'sanitize_text_field',
            ),
            'email' => array(
                'meta_key' => '_ds_location_email',
                'type'     => 'email',
                'sanitize' => 'sanitize_email',
            ),
            'website' => array(
                'meta_key' => '_ds_location_website',
                'type'     => 'url',
                'sanitize' => 'esc_url_raw',
            ),
            'contact_name' => array(
                'meta_key' => '_ds_location_contact_name',
                'type'     => 'text',
                'sanitize' => 'sanitize_text_field',
            ),
            'description' => array(
                'meta_key' => '_ds_location_description',
                'type'     => 'textarea',
                'sanitize' => 'sanitize_textarea_field',
            ),
            'yycd_description' => array(
                'meta_key' => '_ds_location_yycd_description',
                'type'     => 'textarea',
                'sanitize' => 'sanitize_textarea_field',
            ),
            'latitude' => array(
                'meta_key' => '_ds_location_latitude',
                'type'     => 'text',
                'sanitize' => 'sanitize_text_field',
            ),
            'longitude' => array(
                'meta_key' => '_ds_location_longitude',
                'type'     => 'text',
                'sanitize' => 'sanitize_text_field',
            ),
            'logo' => array(
                'meta_key' => '_ds_location_logo',
                'type'     => 'attachment',
                'sanitize' => 'intval',
            ),
            'flyer' => array(
                'meta_key' => '_ds_location_flyer',
                'type'     => 'attachment',
                'sanitize' => 'intval',
            ),
        );
    }

    /**
     * @return array|null Field config, or null if the field isn't registered.
     */
    public static function get($field) {
        $all = self::all();
        return isset($all[$field]) ? $all[$field] : null;
    }
}
