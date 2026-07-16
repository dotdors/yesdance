<?php
// ===== taxonomy-dsp_project_type.php =====
get_header(); ?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">
            <?php single_term_title(); ?> <?php _e('Projects', 'dandysite-portfolio'); ?>
        </h1>
        <?php if (term_description()) : ?>
            <div class="archive-description"><?php echo term_description(); ?></div>
        <?php endif; ?>
    </div>

    <?php if (have_posts()) : ?>
        <!-- Back to all projects link -->
        <div class="archive-navigation">
            <a href="<?php echo get_post_type_archive_link('dsp_project'); ?>" class="back-link">
                &larr; <?php _e('All Projects', 'dandysite-portfolio'); ?>
            </a>
        </div>

        <div class="projects-grid">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('project-card'); ?>>
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="project-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('dsp-project-large'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="project-content">
                        <h3 class="project-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        
                        <div class="project-meta">
                            <?php
                            $project_date = get_post_meta(get_the_ID(), 'dsp_project_date', true);
                            if ($project_date) : ?>
                                <span><?php echo esc_html(date('F Y', strtotime($project_date))); ?></span>
                            <?php endif;
                            
                            $platform = get_post_meta(get_the_ID(), 'dsp_project_platform', true);
                            if ($platform) : ?>
                                <span><?php echo esc_html($platform); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="project-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
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
            <h2><?php _e('No projects found in this category', 'dandysite-portfolio'); ?></h2>
            <p><a href="<?php echo get_post_type_archive_link('dsp_project'); ?>">
                <?php _e('View all projects', 'dandysite-portfolio'); ?>
            </a></p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
