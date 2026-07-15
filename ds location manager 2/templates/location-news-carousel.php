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
            <?php 
            // Two-color heading (reuse same pattern as location grid)
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
                
                <?php while ($news_posts->have_posts()) : $news_posts->the_post(); ?>
                    <?php 
                    $location = DS_Location_News_Carousel::get_post_location(get_the_ID());
                    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                    $logo_url = ($location && !empty($location['logo_url'])) ? $location['logo_url'] : '';
                    ?>
                    
                    <article class="ds-news-card">
                        <a href="<?php the_permalink(); ?>" class="ds-news-card__link">
                            
                            <div class="ds-news-card__image">
                                <?php if ($featured_image) : ?>
                                    <img src="<?php echo esc_url($featured_image); ?>" 
                                         alt="<?php the_title_attribute(); ?>"
                                         loading="lazy">
                                <?php elseif ($logo_url) : ?>
                                    <div class="ds-news-card__placeholder ds-news-card__placeholder--has-logo">
                                        <img src="<?php echo esc_url($logo_url); ?>" 
                                             alt="<?php echo esc_attr($location['name']); ?>" 
                                             class="ds-news-card__placeholder-logo"
                                             loading="lazy">
                                    </div>
                                <?php else : ?>
                                    <div class="ds-news-card__placeholder"></div>
                                <?php endif; ?>
                                
                                <?php if ($location) : ?>
                                    <span class="ds-news-card__badge ds-news-card__badge--<?php echo esc_attr($location['type']); ?>">
                                        <?php echo esc_html($location['name']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="ds-news-card__content">
                                <h3 class="ds-news-card__title">
                                    <?php the_title(); ?>
                                </h3>
                                
                                <time class="ds-news-card__date" datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo get_the_date(); ?>
                                </time>
                            </div>
                            
                        </a>
                    </article>
                    
                <?php endwhile; ?>
                
            </div>
        </div>
        
        <button class="ds-news-carousel-nav ds-news-carousel-nav--next" aria-label="Next">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
        
    </div>
    
</div>
