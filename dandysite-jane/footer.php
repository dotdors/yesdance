<?php
/**
 * Footer Template
 * dandysite-jane theme
 */
?>

    </main><!-- #main -->

    <footer id="colophon" class="site-footer">
        
        <?php if (is_active_sidebar('footer-widgets')) : ?>
            <div class="footer-widgets">
                <div class="container">
                    <?php dynamic_sidebar('footer-widgets'); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (has_nav_menu('footer')) : ?>
            <nav class="footer-nav" aria-label="<?php esc_attr_e('Footer Menu', 'dandysite-jane'); ?>">
                <div class="container">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_class'     => 'footer-menu',
                        'container'      => false,
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ));
                    ?>
                </div>
            </nav>
        <?php endif; ?>

        <div class="site-colophon">
            <div class="container">
                <p>
                    &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?> | 
                    Website by <a href="https://dabbledstudios.com/" target="_blank" rel="nofollow" title="website credit: dabbledstudios | Atlanta GA">dabbledstudios</a> | 
                    <a href="<?php echo esc_url(home_url('/website-info')); ?>" title="website information">Website Info</a> | 
                    <a href="<?php echo esc_url(home_url('/get-started')); ?>" title="contact us">Contact</a><?php 
                    if (is_user_logged_in()) : ?> | <?php wp_register('', ''); ?><?php endif; ?> | <?php wp_loginout(); ?>
                </p>
            </div>
        </div>

    </footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
