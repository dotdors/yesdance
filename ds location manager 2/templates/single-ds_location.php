<?php
/**
 * Template Name: Single Location
 * Description: Custom template for displaying individual location pages
 * v5.1 layout: header card (photo + map, title overlay) / about + sticky contact card / news (if any) / freeform
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

// Directions link: prefer coordinates, fall back to address
$directions_url = '';
if ($location['latitude'] && $location['longitude']) {
    $directions_url = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($location['latitude'] . ',' . $location['longitude']);
} elseif ($location['address']) {
    $directions_url = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($location['location_name'] . ' ' . $location['address']);
}

$has_map = ($location['latitude'] && $location['longitude']);

get_header();
?>

<div class="ds-location-template">

    <!-- HEADER CARD: photo + map in one card, title overlaid on photo -->
    <section class="ds-location-header">
        <div class="container">
            <div class="ds-location-headercard<?php echo $has_map ? '' : ' ds-location-headercard--no-map'; ?>">

                <figure class="ds-location-headercard__media">
                    <?php if ($location['featured_image']): ?>
                        <img src="<?php echo esc_url($location['featured_image']); ?>"
                             alt="<?php echo esc_attr($location['location_name']); ?>"
                             class="ds-location-headercard__image" />
                    <?php endif; ?>

                    <figcaption class="ds-location-headercard__overlay">
                        <?php if ($location['logo_url']): ?>
                            <span class="ds-location-headercard__logo">
                                <img src="<?php echo esc_url($location['logo_url']); ?>"
                                     alt="<?php echo esc_attr($location['location_name']); ?> Logo" />
                            </span>
                        <?php endif; ?>

                        <p class="ds-location-headercard__city"><?php echo esc_html($location['title']); ?></p>
                        <h1 class="ds-location-headercard__title"><?php echo esc_html($location['location_name']); ?></h1>
                    </figcaption>
                </figure>

                <?php if ($has_map): ?>
                    <div class="ds-location-headercard__map">
                        <div id="ds-location-map" class="ds-location-headercard__map-container"></div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <!-- MAIN: About + sticky contact card -->
    <section class="ds-location-main">
        <div class="container">
            <div class="ds-location-main__grid<?php echo ($location['yycd_description'] || $location['description']) ? '' : ' ds-location-main__grid--solo'; ?>">

                <?php if ($location['yycd_description'] || $location['description']): ?>
                    <div class="ds-location-about">
                        <h2 class="ds-location-about__heading">About Our <span class="text-accent">YYCD</span> Program</h2>

                        <?php if ($location['description']): ?>
                            <p class="ds-location-about__lede"><?php echo esc_html($location['description']); ?></p>
                        <?php endif; ?>

                        <?php if ($location['yycd_description']): ?>
                            <div class="ds-location-about__content">
                                <?php echo wpautop(esc_html($location['yycd_description'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <aside class="ds-contact-card">
                    <h2 class="ds-contact-card__heading"><span class="text-accent">Contact</span> Us</h2>

                    <p class="ds-contact-card__intro">
                        Come dance with us. Find out more about our <strong>Yes, You Can Dance</strong> programs!
                    </p>

                    <div class="ds-contact-card__group">
                        <h3 class="ds-contact-card__label">Find Us</h3>
                        <p class="ds-contact-card__name"><?php echo esc_html($location['location_name']); ?></p>
                        <?php if ($location['address']): ?>
                            <address class="ds-contact-card__address">
                                <?php echo nl2br(esc_html($location['address'])); ?>
                            </address>
                        <?php endif; ?>
                    </div>

                    <div class="ds-contact-card__group">
                        <h3 class="ds-contact-card__label">Get In Touch</h3>
                        <?php if ($location['contact_name']): ?>
                            <p class="ds-contact-card__item">Contact: <?php echo esc_html($location['contact_name']); ?></p>
                        <?php endif; ?>
                        <?php if ($location['phone']): ?>
                            <p class="ds-contact-card__item">
                                Phone: <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $location['phone'])); ?>"><?php echo esc_html($location['phone']); ?></a>
                            </p>
                        <?php endif; ?>
                        <?php if ($location['email']): ?>
                            <p class="ds-contact-card__item">
                                Email: <a href="mailto:<?php echo esc_attr($location['email']); ?>"><?php echo esc_html($location['email']); ?></a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if ($directions_url): ?>
                        <p class="ds-contact-card__directions">
                            <a class="ds-btn ds-btn--solid" href="<?php echo esc_url($directions_url); ?>" target="_blank" rel="noopener noreferrer">
                                Get Directions
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if ($location['website']): ?>
                        <p class="ds-contact-card__website">
                            <a href="<?php echo esc_url($location['website']); ?>" target="_blank" rel="noopener noreferrer">
                                Visit our main website ↗
                            </a>
                        </p>
                    <?php endif; ?>
                </aside>

            </div>
        </div>
    </section>

    <!-- NEWS: renders nothing when the location has no posts -->
    <?php if ($term_id):
        $news_html = do_shortcode('[ds_location_news_carousel location="current" heading="Latest News" subtitle="" limit="10" no_results_message=""]');
        if (trim($news_html)) {
            echo $news_html;
        } elseif (current_user_can('edit_posts')) {
            echo '<div class="ds-location-admin-note container">News section hidden: this location has no posts yet. (Only admins see this note.)</div>';
        }
    endif; ?>

    <!-- FREEFORM BLOCK CONTENT (skipped when effectively empty) -->
    <?php if (trim(wp_strip_all_tags(get_the_content()))): ?>
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
