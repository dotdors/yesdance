<?php
/**
 * Locations Archive Template (/location/)
 * Served by DS_Location_Manager_V2::load_location_template().
 *
 * Structure: split heading + optional intro area + card grid using the
 * SAME renderer as the [ds_location_grid] shortcode. The intro area is a
 * hook so a map / intro text can drop in later without touching this file.
 * Query is tuned in DS_Location_Manager_V2::customize_location_archive_query()
 * (all locations, alphabetical).
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$archive_heading  = apply_filters('ds_location_archive_heading', 'Find Your Location');
$archive_split    = apply_filters('ds_location_archive_heading_split', 'last');
$archive_subtitle = apply_filters(
    'ds_location_archive_subtitle',
    "We're proud to serve communities across the country. Choose a location to learn more about its program."
);
?>

<main id="primary" class="site-main ds-location-archive">
    <div class="container">

        <header class="ds-location-grid-header ds-location-archive__header">
            <?php echo ds_split_heading($archive_heading, $archive_split, 'h1'); ?>
            <?php if ($archive_subtitle) : ?>
                <p class="ds-location-grid-subtitle"><?php echo esc_html($archive_subtitle); ?></p>
            <?php endif; ?>
        </header>

        <?php
        /**
         * Intro slot — future home for a map, search box, or intro text.
         * add_action('ds_location_archive_intro', ...) to populate.
         */
        do_action('ds_location_archive_intro');
        ?>

        <?php if (have_posts()) : ?>
            <div class="ds-location-grid">
                <?php
                while (have_posts()) {
                    the_post();
                    echo ds_render_location_card(get_the_ID());
                }
                ?>
            </div>
        <?php else : ?>
            <p class="ds-location-archive__empty">No locations found. Check back soon — new programs are always on the way!</p>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
