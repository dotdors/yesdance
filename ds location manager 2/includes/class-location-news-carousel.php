<?php
/**
 * Location News Carousel Shortcode
 * 
 * Displays a horizontal scrolling carousel of recent posts from locations
 * Shortcode: [ds_location_news_carousel]
 * 
 * @package DS_Location_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Location_News_Carousel {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('ds_location_news_carousel', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets() {
        // Only enqueue if shortcode is present (optional optimization)
        wp_enqueue_style(
            'ds-location-news-carousel',
            plugin_dir_url(__FILE__) . '../assets/location-news-carousel.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . '../assets/location-news-carousel.css')
        );
        
        wp_enqueue_script(
            'ds-location-news-carousel',
            plugin_dir_url(__FILE__) . '../assets/location-news-carousel.js',
            array(),
            filemtime(plugin_dir_path(__FILE__) . '../assets/location-news-carousel.js'),
            true
        );
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit'              => 10,
            'location'           => '',        // NEW: 'current', term slug, or empty for all
            'order'              => 'DESC',
            'orderby'            => 'date',
            'heading'            => 'Latest News',
            'heading_word_split' => 'last',    // 'last', number, or 0 for no split
            'subtitle'           => '',
            'show_badge'         => 'true',    // Show location badge on cards
            'no_results_message' => 'No news posts found.',
        ), $atts, 'ds_location_news_carousel');
        
        // Build query
        $query_args = array(
            'post_type'      => 'post',
            'posts_per_page' => intval($atts['limit']),
            'orderby'        => $atts['orderby'],
            'order'          => strtoupper($atts['order']),
            'post_status'    => 'publish',
        );
        
        // Handle location filtering
        $location_term = $this->resolve_location($atts['location']);
        
        if ($location_term) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'ds_post_location',
                    'field'    => 'term_id',
                    'terms'    => array($location_term->term_id),
                ),
            );
        }
        // If no location specified, show all posts (no tax_query)
        
        $news_posts = new WP_Query($query_args);
        
        if (!$news_posts->have_posts()) {
            // Return empty or message
            if (!empty($atts['no_results_message'])) {
                return '<p class="ds-news-carousel-empty">' . esc_html($atts['no_results_message']) . '</p>';
            }
            return '';
        }
        
        // Start output buffering
        ob_start();
        
        // Pass variables to template
        include plugin_dir_path(__FILE__) . '../templates/location-news-carousel.php';
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Resolve location parameter to taxonomy term
     * 
     * @param string $location 'current', term slug, term ID, or empty
     * @return WP_Term|null
     */
    private function resolve_location($location) {
        if (empty($location)) {
            return null; // Show all posts
        }
        
        // Auto-detect from current ds_location post
        if ($location === 'current' || $location === 'auto') {
            return $this->get_current_location_term();
        }
        
        // Try as term slug first
        $term = get_term_by('slug', sanitize_title($location), 'ds_post_location');
        if ($term && !is_wp_error($term)) {
            return $term;
        }
        
        // Try as term ID
        if (is_numeric($location)) {
            $term = get_term(intval($location), 'ds_post_location');
            if ($term && !is_wp_error($term)) {
                return $term;
            }
        }
        
        // Try as location post slug (find matching taxonomy term)
        $location_post = get_page_by_path($location, OBJECT, 'ds_location');
        if ($location_post) {
            return $this->get_term_for_location_post($location_post->ID);
        }
        
        return null;
    }
    
    /**
     * Get taxonomy term for current ds_location post
     * 
     * @return WP_Term|null
     */
    private function get_current_location_term() {
        // Check if we're on a single ds_location post
        if (!is_singular('ds_location')) {
            return null;
        }
        
        return $this->get_term_for_location_post(get_the_ID());
    }
    
    /**
     * Get taxonomy term that matches a ds_location post
     * 
     * Matching logic (in order of priority):
     * 1. Post meta '_ds_taxonomy_term_id' stores the term ID directly
     * 2. Term slug matches post slug
     * 3. Term name matches post title
     * 
     * @param int $post_id ds_location post ID
     * @return WP_Term|null
     */
    private function get_term_for_location_post($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'ds_location') {
            return null;
        }
        
        // Method 1: Check post meta for linked term ID (this is how DS Location Manager stores it)
        $term_id = get_post_meta($post_id, '_ds_taxonomy_term_id', true);
        if ($term_id) {
            $term = get_term(intval($term_id), 'ds_post_location');
            if ($term && !is_wp_error($term)) {
                return $term;
            }
        }
        
        // Method 2: Match by slug (fallback)
        $term = get_term_by('slug', $post->post_name, 'ds_post_location');
        if ($term && !is_wp_error($term)) {
            return $term;
        }
        
        // Method 3: Match by name (fallback)
        $term = get_term_by('name', $post->post_title, 'ds_post_location');
        if ($term && !is_wp_error($term)) {
            return $term;
        }
        
        return null;
    }
    
    /**
     * Get post location badge info
     * Returns array with 'type' to determine badge color and 'logo_url' for placeholder
     * 
     * @param int $post_id
     * @return array|null
     */
    public static function get_post_location($post_id) {
        // Check ds_post_location taxonomy first
        $terms = get_the_terms($post_id, 'ds_post_location');
        if ($terms && !is_wp_error($terms)) {
            $term = $terms[0];
            
            // Find the corresponding ds_location post to get the logo
            $logo_url = self::get_location_logo_for_term($term->term_id);
            
            return array(
                'name' => $term->name,
                'slug' => $term->slug,
                'url'  => get_term_link($term),
                'type' => 'location', // For badge styling
                'logo_url' => $logo_url,
            );
        }
        
        // Fallback: Try other location taxonomies
        $fallback_taxonomies = array('post_locations', 'post_location', 'location');
        foreach ($fallback_taxonomies as $taxonomy) {
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }
            $terms = get_the_terms($post_id, $taxonomy);
            if ($terms && !is_wp_error($terms)) {
                $term = $terms[0];
                return array(
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'url'  => get_term_link($term),
                    'type' => 'location',
                    'logo_url' => '',
                );
            }
        }
        
        // Fallback: Category (but skip "Uncategorized")
        $categories = get_the_category($post_id);
        if ($categories && !is_wp_error($categories)) {
            foreach ($categories as $cat) {
                if (strtolower($cat->name) !== 'uncategorized') {
                    return array(
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'url'  => get_category_link($cat->term_id),
                        'type' => 'category', // Different badge style
                        'logo_url' => '', // Categories don't have logos
                    );
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get location logo URL for a taxonomy term
     * 
     * @param int $term_id The ds_post_location term ID
     * @return string Logo URL or empty string
     */
    private static function get_location_logo_for_term($term_id) {
        // Find the ds_location post that has this term_id stored
        $location_posts = get_posts(array(
            'post_type' => 'ds_location',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_ds_taxonomy_term_id',
                    'value' => $term_id,
                    'compare' => '='
                )
            )
        ));
        
        if (empty($location_posts)) {
            return '';
        }
        
        $location_id = $location_posts[0]->ID;
        $logo_id = get_post_meta($location_id, '_ds_location_logo', true);
        
        if ($logo_id) {
            return wp_get_attachment_url($logo_id);
        }
        
        return '';
    }
}

// Initialize
new DS_Location_News_Carousel();
