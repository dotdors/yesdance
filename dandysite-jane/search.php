
<?php
// ===== search.php =====
get_header(); ?>

<div class="container">
    <header class="page-header">
        <h1 class="page-title">
            <?php printf(__('Search Results for: %s', 'dandysite-portfolio'), '<span>' . get_search_query() . '</span>'); ?>
        </h1>
    </header>

    <?php if (have_posts()) : ?>
        <div class="search-results">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('search-result'); ?>>
                    <header class="entry-header">
                        <h2 class="entry-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="entry-meta">
                            <span class="post-type">
                                <?php 
                                $post_type_obj = get_post_type_object(get_post_type());
                                echo $post_type_obj->labels->singular_name;
                                ?>
                            </span>
                            <time datetime="<?php echo get_the_date('c'); ?>">
                                <?php echo get_the_date(); ?>
                            </time>
                        </div>
                    </header>
                    
                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
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
        <div class="no-results">
            <h2><?php _e('Nothing found', 'dandysite-portfolio'); ?></h2>
            <p><?php _e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'dandysite-portfolio'); ?></p>
            
            <div class="search-form-wrapper">
                <?php get_search_form(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>