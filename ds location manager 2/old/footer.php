</main>

    <footer id="colophon" class="site-footer">
        <div class="container">
            <?php if (has_nav_menu('footer')) : ?>
                <nav class="footer-navigation">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'footer',
                        'menu_id'        => 'footer-menu',
                        'depth'          => 1,
                    ]);
                    ?>
                </nav>
            <?php endif; ?>
            
            <div class="site-info">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?> |  
                <?php _e('built by Dandysite | ', 'dandysite-portfolio'); ?><?php wp_loginout( home_url() ); ?></p>
            </div>
        </div>
    </footer>
</div>

<?php wp_footer(); ?>
</body>
</html>