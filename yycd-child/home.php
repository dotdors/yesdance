<?php
/**
 * News page (posts index) — YYCD child theme
 *
 * Renders posts as .ds-news-card cards (same shared renderer as the
 * news carousel) in a responsive grid, with pagination.
 * Styles: cards from the plugin's location-news-carousel.css;
 * grid layout from _page-templates.less (ds-theme-customisations).
 */

get_header();
?>

<main id="primary" class="site-main ds-news-page">
    <div class="container">

        <header class="ds-page-hero ds-page-hero--flush">
            <?php
            $news_title = get_the_title(get_option('page_for_posts')) ?: 'Latest News';
            if (function_exists('ds_split_heading')) {
                echo ds_split_heading($news_title, 'last', 'h1', 'ds-page-hero__title');
            } else {
                echo '<h1 class="ds-page-hero__title">' . esc_html($news_title) . '</h1>';
            }
            ?>
        </header>

        <?php if (have_posts()) : ?>

            <div class="ds-news-grid">
                <?php
                while (have_posts()) {
                    the_post();
                    if (function_exists('ds_render_news_card')) {
                        echo ds_render_news_card(get_the_ID());
                    } else {
                        // Plugin inactive fallback
                        echo '<article><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></article>';
                    }
                }
                ?>
            </div>

            <nav class="ds-news-pagination" aria-label="News pagination">
                <?php
                the_posts_pagination(array(
                    'prev_text' => '&larr; Newer',
                    'next_text' => 'Older &rarr;',
                ));
                ?>
            </nav>

        <?php else : ?>
            <p class="ds-news-page__empty">No news yet — check back soon!</p>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
