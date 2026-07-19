<?php
/**
 * Standard page template — YYCD child theme
 *
 * Same editorial split hero as single.php (dark stage + featured image);
 * stage-only when the page has no featured image. Content constrained
 * to prose width, centered.
 * Styles: _page-templates.less (ds-theme-customisations).
 */

get_header();
?>

<main id="primary" class="site-main ds-page">
    <?php while (have_posts()) : the_post(); ?>

        <?php $has_image = has_post_thumbnail(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if ($has_image) : ?>
                <div class="container">
                    <header class="ds-post-hero">
                        <div class="ds-post-hero__stage">
                            <div class="ds-post-hero__stage-inner">
                                <?php
                                if (function_exists('ds_split_heading')) {
                                    echo ds_split_heading(get_the_title(), 'last', 'h1', 'ds-post-hero__title');
                                } else {
                                    echo '<h1 class="ds-post-hero__title">' . esc_html(get_the_title()) . '</h1>';
                                }
                                ?>
                                <?php if (has_excerpt()) : ?>
                                    <p class="ds-post-hero__subtitle"><?php echo esc_html(get_the_excerpt()); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ds-post-hero__media">
                            <?php the_post_thumbnail('large', array('loading' => 'eager')); ?>
                        </div>
                    </header>
                </div>
            <?php endif; ?>

            <div class="ds-post-layout ds-post-layout--centered">
                <div class="container">
                    <div class="ds-post-layout__grid">
                        <div class="ds-post-layout__content entry-content">
                            <?php if (!$has_image) : ?>
                                <header class="ds-post-plain-header">
                                    <?php
                                    if (function_exists('ds_split_heading')) {
                                        echo ds_split_heading(get_the_title(), 'last', 'h1', 'ds-post-plain-header__title');
                                    } else {
                                        echo '<h1 class="ds-post-plain-header__title">' . esc_html(get_the_title()) . '</h1>';
                                    }
                                    ?>
                                </header>
                            <?php endif; ?>
                            <?php
                            the_content();

                            wp_link_pages(array(
                                'before' => '<div class="page-links">' . __('Pages:', 'yycd-child'),
                                'after'  => '</div>',
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>

        </article>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
