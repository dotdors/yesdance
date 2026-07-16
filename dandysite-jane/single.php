<?php get_header(); ?>

<div class="container">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <div class="entry-meta">
                    <time datetime="<?php echo get_the_date('c'); ?>">
                        <?php echo get_the_date(); ?>
                    </time>
                    <span class="author">
                        <?php _e('by', 'dandysite-portfolio'); ?>
                        <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>">
                            <?php the_author(); ?>
                        </a>
                    </span>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="featured-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content content-wrapper">
                <?php the_content(); ?>
                <?php
                wp_link_pages([
                    'before' => '<div class="page-links">' . __('Pages:', 'dandysite-portfolio'),
                    'after'  => '</div>',
                ]);
                ?>
            </div>

            <footer class="entry-footer">
                <?php
                the_post_navigation([
                    'prev_text' => '<span class="nav-subtitle">' . __('Previous Post', 'dandysite-portfolio') . '</span><span class="nav-title">%title</span>',
                    'next_text' => '<span class="nav-subtitle">' . __('Next Post', 'dandysite-portfolio') . '</span><span class="nav-title">%title</span>',
                ]);
                ?>
            </footer>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>