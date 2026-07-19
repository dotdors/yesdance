<?php
/**
 * Location News Carousel Template
 * 
 * @var WP_Query $news_posts
 * @var array $atts
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ds-news-carousel-section">
    
    <?php if (!empty($atts['heading'])) : ?>
        <div class="ds-news-carousel-header">
            <?php echo ds_split_heading($atts['heading'], $atts['heading_word_split']); ?>
            
            <?php if (!empty($atts['subtitle'])) : ?>
                <p class="ds-news-carousel-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="ds-news-carousel-wrapper">
        
        <button class="ds-news-carousel-nav ds-news-carousel-nav--prev" aria-label="Previous">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        
        <div class="ds-news-carousel">
            <div class="ds-news-carousel-track">
                
                <?php
                while ($news_posts->have_posts()) : $news_posts->the_post();
                    // Shared renderer — same card as the news page
                    echo ds_render_news_card(get_the_ID());
                endwhile;
                ?>
                
            </div>
        </div>
        
        <button class="ds-news-carousel-nav ds-news-carousel-nav--next" aria-label="Next">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
        
    </div>
    
</div>
