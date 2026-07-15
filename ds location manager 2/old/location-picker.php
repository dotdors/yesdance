<?php
/**
 * Location Picker Navigation Dropdown
 * 
 * Displays a personalized location selector in the main navigation
 * Shows user's last-selected location or "Locations" by default
 * 
 * @package DS_Location_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate location picker dropdown HTML
 */
function yycd_location_nav_dropdown() {
    // Query all published locations
    $locations = get_posts(array(
        'post_type' => 'ds_location',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_status' => 'publish'
    ));
    
    if (empty($locations)) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="location-picker" data-location-picker>
        <button class="location-picker__button" type="button" aria-haspopup="true" aria-expanded="false">
            <span class="location-picker__current">Locations</span>
            <svg class="location-picker__arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        
        <div class="location-picker__dropdown" role="menu">
            <?php foreach ($locations as $location) : 
                $city = get_post_meta($location->ID, 'ds_location_city', true);
                $name = get_post_meta($location->ID, 'ds_location_name', true);
                
                // Use city name for display, fall back to post title
                $display_name = $city ?: $name ?: $location->post_title;
            ?>
                <a href="<?php echo esc_url(get_permalink($location->ID)); ?>" 
                   class="location-picker__item"
                   role="menuitem"
                   data-location-name="<?php echo esc_attr($display_name); ?>"
                   data-location-id="<?php echo esc_attr($location->ID); ?>">
                    <?php echo esc_html($display_name); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Add location picker to main navigation
 * 
 * Hooks into wp_nav_menu_items filter
 * Only adds to 'primary' menu location
 */
function yycd_add_location_picker_to_nav($items, $args) {
    // Only add to primary navigation
    if ($args->theme_location !== 'primary') {
        return $items;
    }
    
    // Generate location picker
    $location_picker = yycd_location_nav_dropdown();
    
    if (empty($location_picker)) {
        return $items;
    }
    
    // Wrap in menu item and append to menu
    $items .= '<li class="menu-item menu-item-type-custom menu-item-location-picker">' . $location_picker . '</li>';
    
    return $items;
}
add_filter('wp_nav_menu_items', 'yycd_add_location_picker_to_nav', 10, 2);

/**
 * Enqueue location picker assets
 */
function yycd_enqueue_location_picker_assets() {
    // CSS is in main plugin stylesheet (location-picker.less compiled into plugin-style.css)
    
    // Enqueue JavaScript
    wp_enqueue_script(
        'yycd-location-picker',
        plugin_dir_url(__FILE__) . '../assets/location-picker.js',
        array(),
        filemtime(plugin_dir_path(__FILE__) . '../assets/location-picker.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'yycd_enqueue_location_picker_assets');
