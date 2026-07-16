<?php
// ===== archive-dsp_project.php (Projects archive) =====
get_header(); ?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title"><?php _e('All Projects', 'dandysite-portfolio'); ?></h1>
        <?php if (term_description()) : ?>
            <div class="archive-description"><?php echo term_description(); ?></div>
        <?php endif; ?>
    </div>

    <?php if (have_posts()) : ?>
        <!-- Project Type Filter -->
        <div class="project-filter text-center">
            <?php
            $terms = get_terms([
                'taxonomy' => 'dsp_project_type',
                'hide_empty' => true
            ]);
            
            if (!empty($terms) && !is_wp_error($terms)) : ?>
                <div class="filter-buttons">
                    <a href="<?php echo get_post_type_archive_link('dsp_project'); ?>" 
                       class="filter-btn <?php echo !is_tax() ? 'active' : ''; ?>">
                        <?php _e('All', 'dandysite-portfolio'); ?>
                    </a>
                    <?php foreach ($terms as $term) : ?>
                        <a href="<?php echo get_term_link($term); ?>" 
                           class="filter-btn <?php echo is_tax('dsp_project_type', $term->term_id) ? 'active' : ''; ?>">
                            <?php echo esc_html($term->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
                            $project_types = get_the_terms(get_the_ID(), 'dsp_project_type');
                            if ($project_types && !is_wp_error($project_types)) :
                                foreach ($project_types as $type) : ?>
                                    <span class="project-type"><?php echo esc_html($type->name); ?></span>
                                <?php endforeach;
                            endif;
                            
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
            <h2><?php _e('No projects found', 'dandysite-portfolio'); ?></h2>
            <p><?php _e('Check back soon for new projects!', 'dandysite-portfolio'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>