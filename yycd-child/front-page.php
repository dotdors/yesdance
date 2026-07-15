<?php 
/**
 * Enhanced front page template for YYCD
 * Shows fullscreen video with scrolling content overlay
 */
get_header(); ?>

<!-- Fixed Video/Image Background -->
<div class="homepage-background">
    <?php
    // Display video/image background with enhanced features
    if (function_exists('display_responsive_video')) {
        echo display_responsive_video(null, [
            'container_class' => 'fullscreen-background-wrapper',
            'show_overlay' => true, // Enable overlay for better text readability
            'show_toggle' => false,  // Disabled for now - will implement later
            'autoplay' => true,
            'muted' => true,
            'loop' => true
        ]);
    } else {
        // Fallback if plugin not active
        echo '<div class="video-fallback">
            <h2>Video plugin not active</h2>
            <p>Please activate the DS Responsive Video Background plugin.</p>
        </div>';
    }
    ?>
</div>

<!-- Scrollable Content Area -->
<main id="primary" class="site-main homepage-content">
    <!-- Initial spacer to allow full video view -->
    <div class="video-spacer"></div>
    
    <!-- Page Content Container -->
   
        <div class="container">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('homepage-article'); ?>>
                        
                        <?php if (get_the_title()) : ?>
                            <header class="entry-header">
                                <h1 class="entry-title"><?php the_title(); ?></h1>
                            </header>
                        <?php endif; ?>

                        <?php if (has_post_thumbnail()) : ?>
                            <div class="featured-image">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>

                        <div class="entry-content xxxcontent-wrapper">
                            <?php 
                            the_content();
                            
                            wp_link_pages([
                                'before' => '<div class="page-links">' . __('Pages:', 'yycd-child'),
                                'after'  => '</div>',
                            ]);
                            ?>
                        </div>
                        
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <!-- No content fallback -->
                <div class="no-content">
                    <h2>Welcome</h2>
                    <p>Add content to this page to display it here.</p>
                </div>
            <?php endif; ?>
        </div>
   
</main>

<style>
/* ===== BACKGROUND STYLES (Video/Image handled by plugin) ===== */
.homepage-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -2;
    overflow: hidden;
}

.fullscreen-background-wrapper {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
}

/* Enhance the plugin's video styles for our layout */
.fullscreen-background-wrapper video {
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

/* Ensure fallback image fills properly */
.fullscreen-background-wrapper .fallback-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

/* Plugin overlay enhancement */
.fullscreen-background-wrapper .video-background-overlay {
    background: rgba(0, 0, 0, 0.3);
    transition: opacity 0.3s ease;
}

/* Darken overlay when content is visible */
body.content-visible .fullscreen-background-wrapper .video-background-overlay {
    background: rgba(0, 0, 0, 0.5);
}

/* Static overlay - no JS needed */

/* Background fallback for when plugin is not active */
.video-fallback {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
    padding: var(--spacing-lg, 2rem);
}

.video-fallback h2 {
    color: white;
    margin-bottom: var(--spacing-md, 1rem);
}

.video-fallback p {
    opacity: 0.8;
    font-size: var(--font-size-large, 1.125rem);
}

/* ===== SCROLLABLE CONTENT STYLES ===== */
.homepage-content {
    position: relative;
    z-index: 1;
    padding: 0;
    margin: 0;
}

.video-spacer {
    height: 100vh;
    pointer-events: none;
}

.content-slide-up {
    background: var(--color-background, #ffffff);
    min-height: 100vh;
    position: relative;
    box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.3);
    border-radius: 20px 20px 0 0;
    overflow: hidden;
    transform: translateY(0);
    transition: transform 0.3s ease-out;
}

/* Content styling */
.homepage-article {
    padding: var(--spacing-2xl, 4rem) 0;
}

.homepage-article .entry-header {
    text-align: center;
    margin-bottom: var(--spacing-xl, 3rem);
}

.homepage-article .entry-title {
    font-size: clamp(2rem, 5vw, 4rem);
    margin-bottom: var(--spacing-lg, 2rem);
    color: var(--color-primary, #000000);
}

.homepage-article .entry-content {
    font-size: var(--font-size-large, 1.125rem);
    line-height: var(--line-height-base, 1.6);
}

/* No content fallback */
.no-content {
    padding: var(--spacing-2xl, 4rem) 0;
    text-align: center;
    color: var(--color-text-light, #666666);
}

/* ===== HEADER ADJUSTMENTS ===== */


/* Static header - no JS needed for basic functionality */

/* ===== RESPONSIVE ADJUSTMENTS ===== */
@media (max-width: 768px) {
    .content-slide-up {
        border-radius: 15px 15px 0 0;
    }
    
    .homepage-article {
        padding: var(--spacing-xl, 3rem) 0;
    }
    
    .homepage-article .entry-header {
        margin-bottom: var(--spacing-lg, 2rem);
    }
}

@media (max-width: 480px) {
    .video-spacer {
        height: 80vh; /* Shorter on mobile */
    }
    
    .content-slide-up {
        border-radius: 10px 10px 0 0;
    }
    
    .homepage-article {
        padding: var(--spacing-lg, 2rem) 0;
    }
}

/* ===== SCROLL BEHAVIOR ===== */
html {
    scroll-behavior: smooth;
}

/* Hide scrollbar for cleaner look (optional) */
/* 
body::-webkit-scrollbar {
    width: 8px;
}

body::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

body::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
*/
</style>


<?php get_footer(); ?>