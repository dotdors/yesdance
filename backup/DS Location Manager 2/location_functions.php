<?php
/**
 * Location Template Functions
 * Helper functions for location templates and shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Location_Template_Functions {

    public function __construct() {
        add_shortcode('ds_location_contact', array($this, 'location_contact_shortcode'));
        add_shortcode('ds_location_posts', array($this, 'location_posts_shortcode'));
        add_shortcode('ds_location_hero', array($this, 'location_hero_shortcode'));
        add_shortcode('ds_location_details', array($this, 'location_details_shortcode'));
    }

    /**
     * Get location meta data with fallbacks
     */
    public function get_location_data($post_id = null) {
        if (!$post_id) {
            global $post;
            $post_id = $post ? $post->ID : 0;
        }

        if (!$post_id || get_post_type($post_id) !== 'ds_location') {
            return false;
        }

        $post_obj = get_post($post_id);
        
        return array(
            'id' => $post_id,
            'title' => $post_obj->post_title,
            'content' => $post_obj->post_content,
            'location_name' => get_post_meta($post_id, '_ds_location_name', true) ?: $post_obj->post_title,
            'address' => get_post_meta($post_id, '_ds_location_address', true),
            'phone' => get_post_meta($post_id, '_ds_location_phone', true),
            'email' => get_post_meta($post_id, '_ds_location_email', true),
            'contact_name' => get_post_meta($post_id, '_ds_location_contact_name', true),
            'description' => get_post_meta($post_id, '_ds_location_description', true),
            'featured_image' => get_the_post_thumbnail_url($post_id, 'large'),
        );
    }

    /**
     * Hero section shortcode
     */
    public function location_hero_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_image' => 'yes',
            'background' => 'light-gray'
        ), $atts);

        $location = $this->get_location_data();
        if (!$location) {
            return '<div class="ds-location-hero-placeholder"><p><em>Location hero will appear here.</em></p></div>';
        }

        ob_start();
        ?>
        <div class="ds-location-hero" style="background-color: #f8f9fa; padding: 2rem 1rem;">
            <div class="ds-hero-container" style="max-width: 1200px; margin: 0 auto;">
                <div class="ds-hero-content" style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                    <div class="ds-hero-text" style="flex: 2; min-width: 300px;">
                        <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 1rem; line-height: 1.2;">
                            <?php echo esc_html($location['location_name']); ?>
                        </h1>
                        <?php if ($location['description']): ?>
                            <p style="font-size: 1.2rem; color: #666; margin-bottom: 1.5rem;">
                                <?php echo esc_html($location['description']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($atts['show_image'] === 'yes' && $location['featured_image']): ?>
                        <div class="ds-hero-image" style="flex: 1; min-width: 250px;">
                            <img src="<?php echo esc_url($location['featured_image']); ?>" 
                                 alt="<?php echo esc_attr($location['location_name']); ?>"
                                 style="width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Contact information shortcode
     */
    public function location_contact_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'card',
            'show_title' => 'yes'
        ), $atts);

        $location = $this->get_location_data();
        if (!$location) {
            return '<div class="ds-location-contact-placeholder">
                <p><em>Contact information will appear here once you add it in the Location Details meta box.</em></p>
            </div>';
        }

        // Check if any contact info exists
        if (!$location['contact_name'] && !$location['address'] && !$location['phone'] && !$location['email']) {
            return '<div class="ds-location-contact-placeholder">
                <p><em>Contact information will appear here once you add it in the Location Details meta box.</em></p>
            </div>';
        }

        $card_style = $atts['style'] === 'card' ? 'background: #f8f9fa; padding: 1.5rem; border: 1px solid #e0e0e0; border-radius: 8px;' : '';

        ob_start();
        ?>
        <div class="ds-location-contact" style="<?php echo $card_style; ?>">
            <?php if ($atts['show_title'] === 'yes'): ?>
                <h4 style="margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid #ddd; padding-bottom: 0.5rem;">Contact Information</h4>
            <?php endif; ?>
            
            <?php if ($location['contact_name']): ?>
                <p style="font-weight: 600; margin-bottom: 0.5rem;">
                    <?php echo esc_html($location['contact_name']); ?>
                </p>
            <?php endif; ?>
            
            <?php if ($location['address']): ?>
                <p style="margin-bottom: 1rem;">
                    <strong>Address:</strong><br>
                    <?php echo nl2br(esc_html($location['address'])); ?>
                </p>
            <?php endif; ?>
            
            <?php if ($location['phone']): ?>
                <p style="margin-bottom: 1rem;">
                    <strong>Phone:</strong><br>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $location['phone'])); ?>">
                        <?php echo esc_html($location['phone']); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <?php if ($location['email']): ?>
                <p style="margin-bottom: 0;">
                    <strong>Email:</strong><br>
                    <a href="mailto:<?php echo esc_attr($location['email']); ?>">
                        <?php echo esc_html($location['email']); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Location posts shortcode
     */
    public function location_posts_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 6,
            'columns' => 2,
            'show_images' => 'yes',
            'show_excerpts' => 'yes',
            'show_dates' => 'yes'
        ), $atts);

        $location = $this->get_location_data();
        if (!$location) {
            return '<div class="ds-location-posts-placeholder">
                <p><em>Posts for this location will appear here.</em></p>
            </div>';
        }

        // Get the taxonomy term for this location
        $term_id = $this->get_term_id_for_location($location['id']);
        
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => intval($atts['posts_per_page']),
            'post_status' => 'publish'
        );

        if ($term_id) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'ds_post_location',
                    'field' => 'term_id',
                    'terms' => array($term_id)
                )
            );
        } else {
            // No term mapping, show placeholder
            return '<div class="ds-location-posts-placeholder">
                <p><em>Posts for this location will appear here once they are published.</em></p>
            </div>';
        }

        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<div class="ds-location-posts-placeholder">
                <p><em>No posts found for this location. <a href="' . admin_url('post-new.php') . '">Create your first post</a>!</em></p>
            </div>';
        }

        ob_start();
        ?>
        <div class="ds-location-posts" style="display: grid; grid-template-columns: repeat(<?php echo intval($atts['columns']); ?>, 1fr); gap: 1.5rem;">
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <article class="ds-location-post" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; transition: box-shadow 0.3s ease;">
                    
                    <?php if ($atts['show_images'] === 'yes' && has_post_thumbnail()): ?>
                        <div class="post-thumbnail">
                            <a href="<?php echo get_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array(
                                    'style' => 'width: 100%; height: 200px; object-fit: cover; display: block;'
                                )); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-content" style="padding: 1rem;">
                        <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem;">
                            <a href="<?php echo get_permalink(); ?>" style="text-decoration: none; color: #333;">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                        
                        <?php if ($atts['show_excerpts'] === 'yes'): ?>
                            <div class="post-excerpt" style="color: #666; margin-bottom: 0.5rem;">
                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_dates'] === 'yes'): ?>
                            <p style="font-size: 0.9rem; color: #999; margin: 0;">
                                <?php echo get_the_date(); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        
        <style>
            .ds-location-post:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
            }
        </style>
        <?php
        
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Location details shortcode (for general content areas)
     */
    public function location_details_shortcode($atts) {
        $atts = shortcode_atts(array(
            'fields' => 'address,phone,email', // comma-separated list of fields to show
            'layout' => 'list' // 'list' or 'inline'
        ), $atts);

        $location = $this->get_location_data();
        if (!$location) {
            return '';
        }

        $fields = array_map('trim', explode(',', $atts['fields']));
        $has_content = false;

        ob_start();
        ?>
        <div class="ds-location-details ds-layout-<?php echo esc_attr($atts['layout']); ?>">
            <?php foreach ($fields as $field): ?>
                <?php if (!empty($location[$field])): ?>
                    <?php $has_content = true; ?>
                    <div class="ds-detail-item ds-detail-<?php echo esc_attr($field); ?>">
                        <?php
                        switch($field) {
                            case 'address':
                                echo '<strong>Address:</strong> ' . nl2br(esc_html($location['address']));
                                break;
                            case 'phone':
                                echo '<strong>Phone:</strong> <a href="tel:' . esc_attr(preg_replace('/[^0-9]/', '', $location['phone'])) . '">' . esc_html($location['phone']) . '</a>';
                                break;
                            case 'email':
                                echo '<strong>Email:</strong> <a href="mailto:' . esc_attr($location['email']) . '">' . esc_html($location['email']) . '</a>';
                                break;
                            case 'contact_name':
                                echo '<strong>Contact:</strong> ' . esc_html($location['contact_name']);
                                break;
                            case 'description':
                                echo esc_html($location['description']);
                                break;
                        }
                        ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <style>
            .ds-layout-inline .ds-detail-item {
                display: inline-block;
                margin-right: 1rem;
                margin-bottom: 0.5rem;
            }
            .ds-layout-list .ds-detail-item {
                margin-bottom: 0.75rem;
            }
        </style>
        <?php

        return $has_content ? ob_get_clean() : '';
    }

    /**
     * Helper function to get term ID for location
     */
    private function get_term_id_for_location($location_post_id) {
        // Check post meta for cached term id
        $cached_term = get_post_meta($location_post_id, '_ds_taxonomy_term_id', true);
        if ($cached_term) {
            $term = get_term_by('id', intval($cached_term), 'ds_post_location');
            if ($term && !is_wp_error($term)) {
                return intval($term->term_id);
            }
        }
        return 0;
    }
}

// Initialize the template functions
new DS_Location_Template_Functions();