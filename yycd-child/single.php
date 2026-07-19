<?php
/**
 * Single post template — YYCD child theme
 *
 * Editorial split hero, mirroring the location page's header-split
 * pattern: dark stage (meta + title, blush accent word) on the left,
 * featured image on the right. Body is prose + skinny contact sidebar
 * when the post belongs to a location; centered prose otherwise.
 * Styles: _page-templates.less (ds-theme-customisations).
 */

get_header();

// The sidebar replaces the auto-appended location footer on this template.
if (function_exists('ds_location_manager')) {
    remove_filter('the_content', array(ds_location_manager(), 'append_location_footer_to_post'));
}
?>

<main id="primary" class="site-main ds-single-post">
    <?php while (have_posts()) : the_post(); ?>

        <?php
        $location_id   = function_exists('ds_get_post_location_id') ? ds_get_post_location_id(get_the_ID()) : 0;
        $post_location = (class_exists('DS_Location_News_Carousel'))
            ? DS_Location_News_Carousel::get_post_location(get_the_ID())
            : null;
        $has_image = has_post_thumbnail();
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if ($has_image) : ?>
                <div class="container">
                    <header class="ds-post-hero">
                        <div class="ds-post-hero__stage">
                            <div class="ds-post-hero__stage-inner">
                                <div class="ds-post-hero__meta">
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php echo esc_html(get_the_date()); ?>
                                    </time>
                                    <?php if ($post_location) : ?>
                                        <span class="ds-post-hero__meta-sep" aria-hidden="true">&middot;</span>
                                        <span class="ds-post-hero__location"><?php echo esc_html($post_location['name']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php
                                if (function_exists('ds_split_heading')) {
                                    echo ds_split_heading(get_the_title(), 'last', 'h1', 'ds-post-hero__title');
                                } else {
                                    echo '<h1 class="ds-post-hero__title">' . esc_html(get_the_title()) . '</h1>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="ds-post-hero__media">
                            <?php the_post_thumbnail('large', array('loading' => 'eager')); ?>
                        </div>
                    </header>
                </div>
            <?php endif; ?>

            <div class="ds-post-layout <?php echo $location_id ? 'ds-post-layout--has-sidebar' : 'ds-post-layout--centered'; ?>">
                <div class="container">
                    <div class="ds-post-layout__grid">

                        <div class="ds-post-layout__content entry-content">
                            <?php if (!$has_image) : ?>
                                <header class="ds-post-plain-header">
                                    <div class="ds-post-hero__meta">
                                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                            <?php echo esc_html(get_the_date()); ?>
                                        </time>
                                        <?php if ($post_location) : ?>
                                            <span class="ds-post-hero__meta-sep" aria-hidden="true">&middot;</span>
                                            <span class="ds-post-hero__location"><?php echo esc_html($post_location['name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    if (function_exists('ds_split_heading')) {
                                        echo ds_split_heading(get_the_title(), 'last', 'h1', 'ds-post-plain-header__title');
                                    } else {
                                        echo '<h1 class="ds-post-plain-header__title">' . esc_html(get_the_title()) . '</h1>';
                                    }
                                    ?>
                                </header>
                            <?php endif; ?>

                            <?php the_content(); ?>

                            <?php if ($location_id) : ?>
                                <div class="ds-post-layout__from">
                                    <span class="ds-post-layout__from-label">This post is from</span>
                                    <a class="ds-post-layout__from-name" href="<?php echo esc_url(get_permalink($location_id)); ?>">
                                        <?php
                                        $loc_name = get_post_meta($location_id, '_ds_location_name', true) ?: get_the_title($location_id);
                                        echo esc_html($loc_name);
                                        ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($location_id && function_exists('ds_render_contact_card')) : ?>
                            <aside class="ds-post-layout__sidebar">
                                <?php echo ds_render_contact_card($location_id, array('heading' => '')); ?>
                            </aside>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </article>

        <nav class="ds-post-nav container" aria-label="Post navigation">
            <div class="ds-post-nav__prev"><?php previous_post_link('%link', '&larr; %title'); ?></div>
            <div class="ds-post-nav__next"><?php next_post_link('%link', '%title &rarr;'); ?></div>
        </nav>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
