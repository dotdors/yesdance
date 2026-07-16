<?php get_header(); ?>

<main id="primary" class="site-main">
    <?php
    // Only show video if the plugin function exists
    if (function_exists('display_responsive_video')) {
        echo display_responsive_video(null, [
            'container_class' => 'fullscreen-video-container',
            'show_overlay' => false,
            'autoplay' => true,
            'muted' => true,
            'loop' => true
        ]);
    } else {
        // Fallback if plugin not active
        echo '<div class="no-video">Video plugin not active</div>';
    }
    ?>
</main>

<style>
.fullscreen-video-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
    overflow: hidden;
}

.fullscreen-video-container video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: auto;
    height: auto;
    transform: translateX(-50%) translateY(-50%);
    object-fit: cover;
}

/* Make sure header/footer are above video */
.site-header,
.site-footer {
    position: relative;
    z-index: 1;
}

/* Hide main content margins/padding */
.site-main {
    padding: 0;
    margin: 0;
}

/* Fallback styles */
.no-video {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    background: #000;
    color: #fff;
    font-size: 2rem;
}
</style>

<?php get_footer(); ?>