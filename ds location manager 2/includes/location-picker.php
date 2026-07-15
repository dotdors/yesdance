<?php
/**
 * Location Picker Navigation Dropdown
 * 
 * Split-button design:
 * - Click location name → navigates directly to that location's page
 * - Click dropdown arrow → opens menu to see all locations
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
    
    // Default text when no location is remembered
    // JS will update this from localStorage if user has selected one before
    $default_name = 'Locations';
    $default_url = get_post_type_archive_link('ds_location') ?: '#';
    
    ob_start();
    ?>
    <div class="location-picker" data-location-picker>
        <!-- Split button: name link + dropdown toggle -->
        <div class="location-picker__split">
            <!-- Location name - clicks through to location page -->
            <a href="<?php echo esc_url($default_url); ?>" 
               class="location-picker__link"
               data-location-link
               data-default-url="<?php echo esc_url($default_url); ?>">
                <span class="location-picker__current" data-default-name="<?php echo esc_attr($default_name); ?>">
                    <?php echo esc_html($default_name); ?>
                </span>
            </a>
            
            <!-- Divider line -->
            <span class="location-picker__divider" aria-hidden="true"></span>
            
            <!-- Dropdown toggle button -->
            <button class="location-picker__toggle" 
                    type="button" 
                    aria-haspopup="true" 
                    aria-expanded="false"
                    aria-label="Select a different location">
                <svg class="location-picker__arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        
        <!-- Dropdown menu -->
        <div class="location-picker__dropdown" role="menu">
            <?php foreach ($locations as $location) : 
                // Use post title as the display name
                $display_name = $location->post_title;
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
    // CSS is compiled via LESS in ds-theme-customizations plugin (_location-picker.less)
    // No standalone CSS needed
    
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
