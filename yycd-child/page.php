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

            <div class="ds-post-layout ds-post-layout--centered">
                <div class="container">
                    <div class="ds-post-layout__grid">
                        <div class="ds-post-layout__content entry-content">
                            <header class="ds-post-plain-header">
                                <?php
                                if (function_exists('ds_split_heading')) {
                                    echo ds_split_heading(get_the_title(), 'last', 'h1', 'ds-post-plain-header__title');
                                } else {
                                    echo '<h1 class="ds-post-plain-header__title">' . esc_html(get_the_title()) . '</h1>';
                                }
                                ?>
                            </header>

                            <?php
                            if ($has_image) {
                                /**
                                 * Magazine breakout figure — floated inline-end,
                                 * escapes the prose column via negative margin so
                                 * the text wraps around it. Injected after the
                                 * Nth paragraph (a float can't start higher than
                                 * its position in the flow, so placement = markup
                                 * position). Styles: .ds-page-figure in
                                 * _page-templates.less. (single.php still uses
                                 * the split .ds-post-hero.)
                                 */
                                $ds_after_paragraph = 1; // figure starts beside paragraph N+1

                                $ds_caption = get_the_post_thumbnail_caption();
                                $ds_figure  = '<figure class="ds-page-figure">'
                                    . get_the_post_thumbnail(null, 'large', array('loading' => 'eager'))
                                    . ($ds_caption ? '<figcaption class="ds-page-figure__caption">' . esc_html($ds_caption) . '</figcaption>' : '')
                                    . '</figure>';

                                $ds_content = apply_filters('the_content', get_the_content());
                                $ds_content = str_replace(']]>', ']]&gt;', $ds_content);

                                // Find the Nth top-level paragraph close; fall back
                                // to prepending if the content has fewer paragraphs.
                                $ds_offset = 0;
                                $ds_found  = false;
                                for ($i = 0; $i < $ds_after_paragraph; $i++) {
                                    $ds_pos = strpos($ds_content, '</p>', $ds_offset);
                                    if ($ds_pos === false) {
                                        $ds_found = false;
                                        break;
                                    }
                                    $ds_offset = $ds_pos + 4;
                                    $ds_found  = true;
                                }

                                if ($ds_found) {
                                    $ds_content = substr_replace($ds_content, $ds_figure, $ds_offset, 0);
                                } else {
                                    $ds_content = $ds_figure . $ds_content;
                                }

                                echo $ds_content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content-filtered
                            } else {
                                the_content();
                            }

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
