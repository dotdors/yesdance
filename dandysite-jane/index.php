<?php get_header(); ?>

<div class="container">
    <?php if (have_posts()) : ?>
        <div class="page-header">
            <?php if (is_home() && !is_front_page()) : ?>
                <h1 class="page-title"><?php single_post_title(); ?></h1>
            <?php else : ?>
                <h1 class="page-title"><?php _e('Latest Posts', 'dandysite-portfolio'); ?></h1>
            <?php endif; ?>
        </div>

        <div class="post-grid">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
                    <header class="entry-header">
                        <h2 class="entry-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="post-meta">
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

                    <div class="entry-content">
                        <?php the_excerpt(); ?>
                        <a href="<?php the_permalink(); ?>" class="read-more">
                            <?php _e('Read More', 'dandysite-portfolio'); ?>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php
        the_posts_pagination([
            'mid_size' => 2,
            'prev_text' => __('&laquo; Previous', 'dandysite-portfolio'),
            'next_text' => __('Next &raquo;', 'dandysite-portfolio'),
        ]);
        ?>

    <?php else : ?>
        <div class="no-posts">
            <h1><?php _e('Nothing here yet!', 'dandysite-portfolio'); ?></h1>
            <p><?php _e('Check back soon for updates.', 'dandysite-portfolio'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>