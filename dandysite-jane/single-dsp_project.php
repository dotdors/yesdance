
<?php
// ===== single-dsp_project.php (Individual project page) =====
get_header(); ?>

<div class="container">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('project-single'); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                
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
                        <span><strong><?php _e('Date:', 'dandysite-portfolio'); ?></strong> 
                              <?php echo esc_html(date('F Y', strtotime($project_date))); ?></span>
                    <?php endif;
                    
                    $platform = get_post_meta(get_the_ID(), 'dsp_project_platform', true);
                    if ($platform) : ?>
                        <span><strong><?php _e('Platform:', 'dandysite-portfolio'); ?></strong> 
                              <?php echo esc_html($platform); ?></span>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="project-featured-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content content-wrapper">
                <?php the_content(); ?>
            </div>

            <footer class="entry-footer">
                <?php
                // Project navigation
                $prev_post = get_previous_post(true, '', 'dsp_project_type');
                $next_post = get_next_post(true, '', 'dsp_project_type');
                
                if ($prev_post || $next_post) : ?>
                    <nav class="project-navigation">
                        <div class="nav-links">
                            <?php if ($prev_post) : ?>
                                <div class="nav-previous">
                                    <a href="<?php echo get_permalink($prev_post->ID); ?>" rel="prev">
                                        <span class="nav-subtitle"><?php _e('Previous Project', 'dandysite-portfolio'); ?></span>
                                        <span class="nav-title"><?php echo get_the_title($prev_post->ID); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($next_post) : ?>
                                <div class="nav-next">
                                    <a href="<?php echo get_permalink($next_post->ID); ?>" rel="next">
                                        <span class="nav-subtitle"><?php _e('Next Project', 'dandysite-portfolio'); ?></span>
                                        <span class="nav-title"><?php echo get_the_title($next_post->ID); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </nav>
                <?php endif; ?>
                
                <div class="back-to-projects text-center">
                    <a href="<?php echo get_post_type_archive_link('dsp_project'); ?>" class="read-more">
                        &larr; <?php _e('Back to All Projects', 'dandysite-portfolio'); ?>
                    </a>
                </div>
            </footer>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>