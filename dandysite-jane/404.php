
<?php
// ===== 404.php =====
get_header(); ?>

<div class="container">
    <div class="error-404 text-center">
        <header class="page-header">
            <h1 class="page-title"><?php _e('404', 'dandysite-portfolio'); ?></h1>
            <p class="page-description">
                <?php _e('Sorry, the page you are looking for could not be found.', 'dandysite-portfolio'); ?>
            </p>
        </header>

        <div class="error-content">
            <p><a href="<?php echo esc_url(home_url('/')); ?>" class="read-more">
                <?php _e('Go back to homepage', 'dandysite-portfolio'); ?>
            </a></p>
            
            <div class="search-form-wrapper">
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
