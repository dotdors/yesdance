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
            <?php echo ds_split_heading($atts['heading'], $atts['heading_word_split']); ?>
            
            <?php if (!empty($atts['subtitle'])) : ?>
                <p class="ds-location-grid-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="ds-location-grid">
        <?php
        while ($locations->have_posts()) : $locations->the_post();
            // Shared renderer — same card as the locations archive
            echo ds_render_location_card(get_the_ID());
        endwhile;
        ?>
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
