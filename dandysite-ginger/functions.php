<?php
/**
 * Dandysite Ginger Child Theme Functions
 * 
 * Dance studio theme with advanced animations and motion graphics
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Child theme version
define('DANDYSITE_GINGER_VERSION', '1.0.0');

/**
 * Enqueue parent and child theme styles
 */
function dandysite_ginger_child_enqueue_styles() {
    // Enqueue parent theme stylesheet
    wp_enqueue_style(
        'dandysite-jane-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->get('Version')
    );

    // Enqueue child theme stylesheet
    wp_enqueue_style(
        'dandysite-ginger-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('dandysite-jane-parent-style'),
        DANDYSITE_GINGER_VERSION
    );

    // Enqueue compiled custom CSS (from Easy LESS)
    $custom_css = get_stylesheet_directory() . '/assets/css/custom.css';
    if (file_exists($custom_css)) {
        wp_enqueue_style(
            'dandysite-ginger-custom',
            get_stylesheet_directory_uri() . '/assets/css/custom.css',
            array('dandysite-ginger-style'),
            filemtime($custom_css) // Auto cache busting
        );
    }

    // Enqueue animation styles
    wp_enqueue_style(
        'dandysite-ginger-animations',
        get_stylesheet_directory_uri() . '/assets/css/animations.css',
        array('dandysite-ginger-custom'),
        DANDYSITE_GINGER_VERSION
    );

    // Enqueue dance section styles
    wp_enqueue_style(
        'dandysite-ginger-dance-sections',
        get_stylesheet_directory_uri() . '/assets/css/dance-sections.css',
        array('dandysite-ginger-animations'),
        DANDYSITE_GINGER_VERSION
    );

    // Enqueue block pattern styles
    wp_enqueue_style(
        'dandysite-ginger-block-patterns',
        get_stylesheet_directory_uri() . '/assets/css/block-patterns.css',
        array('dandysite-ginger-dance-sections'),
        DANDYSITE_GINGER_VERSION
    );
}
add_action('wp_enqueue_scripts', 'dandysite_ginger_child_enqueue_styles');

/**
 * Enqueue custom JavaScript
 */
function dandysite_ginger_enqueue_scripts() {
    // Scroll animator (core animation system)
    wp_enqueue_script(
        'dandysite-ginger-scroll-animator',
        get_stylesheet_directory_uri() . '/assets/js/scroll-animator.js',
        array(),
        DANDYSITE_GINGER_VERSION,
        true
    );

    // Dance effects
    wp_enqueue_script(
        'dandysite-ginger-dance-effects',
        get_stylesheet_directory_uri() . '/assets/js/dance-effects.js',
        array('dandysite-ginger-scroll-animator'),
        DANDYSITE_GINGER_VERSION,
        true
    );

    // Localize script with theme data
    wp_localize_script('dandysite-ginger-scroll-animator', 'dandysiteGinger', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dandysite_ginger_nonce'),
        'themeUrl' => get_stylesheet_directory_uri(),
    ));
}
add_action('wp_enqueue_scripts', 'dandysite_ginger_enqueue_scripts');

/**
 * Add custom body classes for dance theme
 */
function dandysite_ginger_body_classes($classes) {
    $classes[] = 'dandysite-ginger';
    
    if (is_front_page()) {
        $classes[] = 'dance-homepage';
    }
    
    return $classes;
}
add_filter('body_class', 'dandysite_ginger_body_classes');

/**
 * Register block patterns for animated sections
 */
function dandysite_ginger_register_block_patterns() {
    // Hero section pattern - using CSS classes instead of inline styles
    register_block_pattern(
        'dandysite-ginger/animated-hero',
        array(
            'title' => __('Animated Hero Section', 'dandysite-ginger'),
            'description' => __('Hero section with fade-up animation for dance studios', 'dandysite-ginger'),
            'categories' => array('dandysite-ginger'),
            'content' => '<!-- wp:group {"className":"hero-section"} -->
<div class="wp-block-group hero-section">
<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">Dance Your Way to Joy</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Discover the joy of movement with our inclusive dance classes</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Start Dancing Today</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->'
        )
    );

    // Dance steps section pattern
    register_block_pattern(
        'dandysite-ginger/dance-steps',
        array(
            'title' => __('Dance Steps Animation', 'dandysite-ginger'),
            'description' => __('Animated dance steps background section', 'dandysite-ginger'),
            'categories' => array('dandysite-ginger'),
            'content' => '<!-- wp:group {"className":"dance-steps-section"} -->
<div class="wp-block-group dance-steps-section">
<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">Follow the Steps to Success</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Every dancer starts with a single step. Let us guide your journey.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->'
        )
    );

    // Programs cascade section - using CSS classes
    register_block_pattern(
        'dandysite-ginger/programs-cascade',
        array(
            'title' => __('Programs Cascade', 'dandysite-ginger'),
            'description' => __('Programs section with cascading card animation', 'dandysite-ginger'),
            'categories' => array('dandysite-ginger'),
            'content' => '<!-- wp:group {"className":"programs-section"} -->
<div class="wp-block-group programs-section">
<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">Our Dance Programs</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column {"className":"program-card"} -->
<div class="wp-block-column program-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Beginner Classes</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Perfect for those taking their first dance steps</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"className":"program-card"} -->
<div class="wp-block-column program-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Intermediate</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Build on your foundation and develop your style</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"className":"program-card"} -->
<div class="wp-block-column program-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Advanced</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Master complex choreography and technique</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->'
        )
    );

    // About section with circle reveal animation
    register_block_pattern(
        'dandysite-ginger/about-circle-reveal',
        array(
            'title' => __('About Section - Circle Reveal', 'dandysite-ginger'),
            'description' => __('About section with circle reveal animation', 'dandysite-ginger'),
            'categories' => array('dandysite-ginger'),
            'content' => '<!-- wp:group {"className":"about-section"} -->
<div class="wp-block-group about-section">
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">About Our Studio</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>With over 20 years of experience, our studio has been the heart of the local dance community. We believe that dance is for everyone, regardless of age, ability, or experience level.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Our passionate instructors are dedicated to helping you discover the joy of movement while building confidence, strength, and artistic expression.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:image {"className":"about-image"} -->
<figure class="wp-block-image about-image"><img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?ixlib=rb-4.0.3&amp;ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&amp;auto=format&amp;fit=crop&amp;w=1000&amp;q=80" alt="Dance studio interior with mirrors and barres"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->'
        )
    );
}
add_action('init', 'dandysite_ginger_register_block_patterns');

/**
 * Register custom block pattern category
 */
function dandysite_ginger_register_pattern_category() {
    register_block_pattern_category(
        'dandysite-ginger',
        array('label' => __('Dandysite Ginger', 'dandysite-ginger'))
    );
}
add_action('init', 'dandysite_ginger_register_pattern_category');

/**
 * Add custom meta box for page background options
 */
function dandysite_ginger_add_background_meta_box() {
    add_meta_box(
        'dandysite_ginger_background',
        __('Background Media Options', 'dandysite-ginger'),
        'dandysite_ginger_background_meta_box_callback',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'dandysite_ginger_add_background_meta_box');

/**
 * Meta box callback function
 */
function dandysite_ginger_background_meta_box_callback($post) {
    wp_nonce_field('dandysite_ginger_background_nonce', 'dandysite_ginger_background_nonce');
    
    $bg_type = get_post_meta($post->ID, '_dandysite_bg_type', true) ?: 'none';
    $bg_image = get_post_meta($post->ID, '_dandysite_bg_image', true);
    $bg_video = get_post_meta($post->ID, '_dandysite_bg_video', true);
    $bg_overlay = get_post_meta($post->ID, '_dandysite_bg_overlay', true) ?: '50';
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="dandysite_bg_type"><?php _e('Background Type', 'dandysite-ginger'); ?></label></th>
            <td>
                <select id="dandysite_bg_type" name="dandysite_bg_type" class="widefat">
                    <option value="none" <?php selected($bg_type, 'none'); ?>><?php _e('None', 'dandysite-ginger'); ?></option>
                    <option value="image" <?php selected($bg_type, 'image'); ?>><?php _e('Image', 'dandysite-ginger'); ?></option>
                    <option value="video" <?php selected($bg_type, 'video'); ?>><?php _e('Video', 'dandysite-ginger'); ?></option>
                </select>
            </td>
        </tr>
        <tr id="bg_image_row" style="display: <?php echo $bg_type === 'image' ? 'table-row' : 'none'; ?>;">
            <th><label for="dandysite_bg_image"><?php _e('Background Image', 'dandysite-ginger'); ?></label></th>
            <td>
                <input type="hidden" id="dandysite_bg_image" name="dandysite_bg_image" value="<?php echo esc_attr($bg_image); ?>" />
                <button type="button" class="button" id="upload_image_button"><?php _e('Choose Image', 'dandysite-ginger'); ?></button>
                <button type="button" class="button" id="remove_image_button" style="display: <?php echo $bg_image ? 'inline-block' : 'none'; ?>;"><?php _e('Remove', 'dandysite-ginger'); ?></button>
                <div id="image_preview" style="margin-top: 10px;">
                    <?php if ($bg_image): ?>
                        <img src="<?php echo esc_url($bg_image); ?>" style="max-width: 200px; height: auto;" />
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <tr id="bg_video_row" style="display: <?php echo $bg_type === 'video' ? 'table-row' : 'none'; ?>;">
            <th><label for="dandysite_bg_video"><?php _e('Background Video', 'dandysite-ginger'); ?></label></th>
            <td>
                <input type="hidden" id="dandysite_bg_video" name="dandysite_bg_video" value="<?php echo esc_attr($bg_video); ?>" />
                <button type="button" class="button" id="upload_video_button"><?php _e('Choose Video', 'dandysite-ginger'); ?></button>
                <button type="button" class="button" id="remove_video_button" style="display: <?php echo $bg_video ? 'inline-block' : 'none'; ?>;"><?php _e('Remove', 'dandysite-ginger'); ?></button>
                <div id="video_preview" style="margin-top: 10px;">
                    <?php if ($bg_video): ?>
                        <video controls style="max-width: 200px; height: auto;">
                            <source src="<?php echo esc_url($bg_video); ?>" type="video/mp4">
                        </video>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <tr id="bg_overlay_row" style="display: <?php echo $bg_type !== 'none' ? 'table-row' : 'none'; ?>;">
            <th><label for="dandysite_bg_overlay"><?php _e('Overlay Opacity (%)', 'dandysite-ginger'); ?></label></th>
            <td>
                <input type="range" id="dandysite_bg_overlay" name="dandysite_bg_overlay" min="0" max="100" step="10" value="<?php echo esc_attr($bg_overlay); ?>" class="widefat" />
                <span id="overlay_value"><?php echo esc_html($bg_overlay); ?>%</span>
            </td>
        </tr>
    </table>
    
    <script>
    jQuery(document).ready(function($) {
        // Show/hide fields based on background type
        $('#dandysite_bg_type').change(function() {
            var type = $(this).val();
            $('#bg_image_row, #bg_video_row, #bg_overlay_row').hide();
            
            if (type === 'image') {
                $('#bg_image_row, #bg_overlay_row').show();
            } else if (type === 'video') {
                $('#bg_video_row, #bg_overlay_row').show();
            }
        });
        
        // Update overlay value display
        $('#dandysite_bg_overlay').on('input', function() {
            $('#overlay_value').text($(this).val() + '%');
        });
        
        // Media upload for image
        $('#upload_image_button').click(function(e) {
            e.preventDefault();
            var mediaUploader = wp.media({
                title: 'Choose Background Image',
                button: { text: 'Choose Image' },
                multiple: false,
                library: { type: 'image' }
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#dandysite_bg_image').val(attachment.url);
                $('#image_preview').html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;" />');
                $('#remove_image_button').show();
            });
            
            mediaUploader.open();
        });
        
        // Media upload for video
        $('#upload_video_button').click(function(e) {
            e.preventDefault();
            var mediaUploader = wp.media({
                title: 'Choose Background Video',
                button: { text: 'Choose Video' },
                multiple: false,
                library: { type: 'video' }
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#dandysite_bg_video').val(attachment.url);
                $('#video_preview').html('<video controls style="max-width: 200px; height: auto;"><source src="' + attachment.url + '" type="video/mp4"></video>');
                $('#remove_video_button').show();
            });
            
            mediaUploader.open();
        });
        
        // Remove buttons
        $('#remove_image_button').click(function() {
            $('#dandysite_bg_image').val('');
            $('#image_preview').empty();
            $(this).hide();
        });
        
        $('#remove_video_button').click(function() {
            $('#dandysite_bg_video').val('');
            $('#video_preview').empty();
            $(this).hide();
        });
    });
    </script>
    <?php
}

/**
 * Save meta box data
 */
function dandysite_ginger_save_background_meta_box($post_id) {
    if (!isset($_POST['dandysite_ginger_background_nonce']) || 
        !wp_verify_nonce($_POST['dandysite_ginger_background_nonce'], 'dandysite_ginger_background_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $fields = array(
        'dandysite_bg_type' => '_dandysite_bg_type',
        'dandysite_bg_image' => '_dandysite_bg_image',
        'dandysite_bg_video' => '_dandysite_bg_video',
        'dandysite_bg_overlay' => '_dandysite_bg_overlay'
    );
    
    foreach ($fields as $field => $meta_key) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'dandysite_ginger_save_background_meta_box');

/**
 * Add animation attributes to group blocks based on their classes
 */
function dandysite_ginger_add_animation_attributes($block_content, $block) {
    if ($block['blockName'] !== 'core/group') {
        return $block_content;
    }

    $classes = $block['attrs']['className'] ?? '';
    
    // Map section classes to animation types
    $animation_map = array(
        'hero-section' => 'hero-fade-up',
        'programs-section' => 'card-cascade',
        'dance-steps-section' => 'dance-steps',
        'about-section' => 'circle-reveal',
        'testimonials-section' => 'slide-text'
    );

    foreach ($animation_map as $class => $animation) {
        if (strpos($classes, $class) !== false) {
            // Add data-animate attribute if not already present
            if (strpos($block_content, 'data-animate=') === false) {
                $block_content = str_replace(
                    'class="wp-block-group',
                    'data-animate="' . $animation . '" class="wp-block-group',
                    $block_content
                );
            }
            break;
        }
    }

    return $block_content;
}
add_filter('render_block', 'dandysite_ginger_add_animation_attributes', 10, 2);

/**
 * Theme setup function
 */
function dandysite_ginger_setup() {
    // Add theme support for various features
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    
    // Load theme textdomain
    load_theme_textdomain('dandysite-ginger', get_stylesheet_directory() . '/languages');
}
add_action('after_setup_theme', 'dandysite_ginger_setup');