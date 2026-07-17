<?php
/**
 * Location Grid Template
 * 
 * @var WP_Query $locations
 * @var array $atts
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ds-location-grid-section">
    
    <?php if (!empty($atts['heading'])) : ?>
        <div class="ds-location-grid-header">
            <?php 
            // Two-color heading
            $heading = trim($atts['heading']);
            $heading_parts = explode(' ', $heading);
            $split_position = $atts['heading_word_split'];
            
            // Support "last" or negative numbers for last word
            if ($split_position === 'last' || $split_position === '-1' || intval($split_position) === -1) {
                $split_position = count($heading_parts);
            } else {
                $split_position = intval($split_position);
            }
            
            if ($split_position > 0 && $split_position <= count($heading_parts)) {
                echo '<h2 class="ds-heading-split">';
                foreach ($heading_parts as $index => $word) {
                    $word_num = $index + 1;
                    $class = ($word_num == $split_position) ? 'ds-heading-split__accent' : 'ds-heading-split__primary';
                    echo '<span class="' . esc_attr($class) . '">' . esc_html($word) . '</span>';
                    if ($index < count($heading_parts) - 1) {
                        echo ' ';
                    }
                }
                echo '</h2>';
            } else {
                echo '<h2>' . esc_html($heading) . '</h2>';
            }
            ?>
            
            <?php if (!empty($atts['subtitle'])) : ?>
                <p class="ds-location-grid-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="ds-location-grid">
        <?php while ($locations->have_posts()) : $locations->the_post(); ?>
            <?php 
            $location_data = DS_Location_Grid_Shortcode::get_location_data(get_the_ID());
            ?>
            
            <div class="ds-location-card">
                <a href="<?php the_permalink(); ?>" class="ds-location-card__link">
                    
                    <div class="ds-location-card__image">
                        <?php if ($location_data['featured_image']) : ?>
                            <img src="<?php echo esc_url($location_data['featured_image']); ?>" 
                                 alt="<?php echo esc_attr($location_data['name']); ?>">
                        <?php else : ?>
                            <div class="ds-location-card__placeholder">
                                <span>📍</span>
                            </div>
                        <?php endif; ?>
                        
                        <span class="ds-location-card__pin" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                    
                    <div class="ds-location-card__content">
                        <h3 class="ds-location-card__title"><?php echo esc_html($location_data['name']); ?></h3>
                        <?php if ($location_data['city']) : ?>
                            <p class="ds-location-card__subtitle"><?php echo esc_html($location_data['city']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                </a>
            </div>
            
        <?php endwhile; ?>
    </div>
    
    <?php if ($atts['show_link'] === 'true' && !empty($atts['link_url'])) : ?>
        <div class="ds-location-grid__more">
            <a href="<?php echo esc_url($atts['link_url']); ?>" class="ds-location-grid__more-link">
                <?php echo esc_html($atts['link_text']); ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    <?php endif; ?>
    
</div>
