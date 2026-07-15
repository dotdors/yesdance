<?php
/**
 * Template Name: Single Location
 * Description: Custom template for displaying individual location pages
 */

// Get location data
$location_manager = new DS_Location_Manager_V2();
$location = $location_manager->get_location_display_data(get_the_ID());

if (!$location) {
    get_template_part('singular');
    return;
}

// Get term ID for this location to query posts
$term_id = get_post_meta(get_the_ID(), '_ds_taxonomy_term_id', true);

get_header(); 
?>

<div class="ds-location-template">

    <!-- HERO SECTION -->
    <section class="ds-location-hero">
        
        <!-- Curved overlay shape - top edge is curved, overlays image with blend mode -->
        <div class="ds-location-hero__overlay" aria-hidden="true">
            <svg viewBox="0 0 1200 600" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path class="ds-location-hero__overlay-shape" 
                      d="M 0,330 Q 600,250 1200,330 L 1200,600 L 0,600 Z" />
            </svg>
        </div>
        
        <div class="container">
            <div class="ds-location-hero__inner">
                
                <!-- LEFT: Content column -->
                <div class="ds-location-hero__left grid-content">
                    <?php if ($location['logo_url']): ?>
                        <div class="ds-location-hero__logo">
                            <img src="<?php echo esc_url($location['logo_url']); ?>" 
                                 alt="<?php echo esc_attr($location['location_name']); ?> Logo" />
                        </div>
                    <?php endif; ?>
                    
                    <p class="ds-location-hero__city">
                        <?php echo esc_html($location['title']); ?>
                    </p>
                    
                    <h1 class="ds-location-hero__title">
                        <?php echo esc_html($location['location_name']); ?>
                    </h1>
                    
                    <?php if ($location['description']): ?>
                        <p class="ds-location-hero__description">
                            <?php echo esc_html($location['description']); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- RIGHT: Image column -->
                <?php if ($location['featured_image']): ?>
                    <div class="ds-location-hero__right grid-media">
                        <img src="<?php echo esc_url($location['featured_image']); ?>" 
                             alt="<?php echo esc_attr($location['location_name']); ?>"
                             class="ds-location-hero__image" />
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </section>

    <!-- YYCD PROGRAM SECTION -->
    <?php if ($location['yycd_description']): ?>
        <section class="ds-location-program">
            <div class="container">
                <h2 class="ds-location-program__header">About Our YYCD Program</h2>
                <div class="ds-location-program__description">
                    <?php echo wpautop(esc_html($location['yycd_description'])); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- LATEST NEWS SECTION -->
    <?php if ($term_id): ?>
        <?php echo do_shortcode('[ds_location_news_carousel location="current" heading="Latest News" subtitle="" limit="10"]'); ?>
    <?php endif; ?>

    <!-- LOCATION DETAILS SECTION -->
    <section class="ds-location-details">
        <div class="container">
            <div class="ds-location-details__grid">
                
                <!-- Contact Information -->
                <div class="ds-location-details__contact grid-content">
                    <h2 class="ds-location-details__heading"><span class="ds-location-details__heading-accent">Contact</span> Us</h2>
                    
                    <p class="ds-location-details__intro">
                        Come dance with us. Find out more about our <strong>Yes, You Can Dance</strong> programs!
                    </p>
                    
                    <!-- Find Us -->
                    <div class="ds-location-details__group">
                        <h3 class="ds-location-details__subheading">Find Us</h3>
                        <p class="ds-location-details__name">
                            <?php echo esc_html($location['location_name']); ?>
                        </p>
                        <?php if ($location['address']): ?>
                            <address class="ds-location-details__address">
                                <?php echo nl2br(esc_html($location['address'])); ?>
                            </address>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Get In Touch -->
                    <div class="ds-location-details__group">
                        <h3 class="ds-location-details__subheading">Get In Touch</h3>
                        <?php if ($location['contact_name']): ?>
                            <p class="ds-location-details__item">
                                Contact: <?php echo esc_html($location['contact_name']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($location['phone']): ?>
                            <p class="ds-location-details__item">
                                Phone: <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $location['phone'])); ?>"><?php echo esc_html($location['phone']); ?></a>
                            </p>
                        <?php endif; ?>
                        <?php if ($location['email']): ?>
                            <p class="ds-location-details__item">
                                Email: <a href="mailto:<?php echo esc_attr($location['email']); ?>"><?php echo esc_html($location['email']); ?></a>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($location['website']): ?>
                        <p class="ds-location-details__website">
                            <a href="<?php echo esc_url($location['website']); ?>" target="_blank" rel="noopener noreferrer">
                                Visit our main website ↗
                            </a>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Map -->
                <?php if ($location['latitude'] && $location['longitude']): ?>
                    <div class="ds-location-details__map grid-media">
                        <div id="ds-location-map" class="ds-location-details__map-container"></div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </section>

    <!-- BLOCK EDITOR CONTENT -->
    <?php if (get_the_content()): ?>
        <section class="ds-location-content">
            <div class="container">
                <div class="ds-location-content__inner">
                    <?php the_content(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
