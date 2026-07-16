<?php
/**
 * Front Page Template - Scroll Animation Testing
 * 
 * @author NC Dorsner
 * @link https://dandysite.com
 */

get_header(); ?>

<div class="scroll-container">
    
    <!-- Section 1: Fade Up Animation -->
    <section class="section" data-anim="fade-up">
        <div class="section-content">
            <h1>Welcome to Scroll Animations</h1>
            <p>This section uses fade-up animation. Scroll down to see it animate as it comes into view.</p>
        </div>
    </section>

    <!-- Section 2: Parallax Background -->
    <section class="section color-accent" data-anim="parallax">
        <div class="section-content">
            <h2>Parallax Effect</h2>
            <p>The background of this section moves at a different speed than the content, creating a parallax effect.</p>
        </div>
    </section>

    <!-- Section 3: Scale In Animation -->
    <section class="section color-secondary" data-anim="scale-in">
        <div class="section-content">
            <h2>Scale Animation</h2>
            <p>This section scales up as you scroll. Watch it grow from small to full size.</p>
        </div>
    </section>

    <!-- Section 4: Perspective 3D -->
    <section class="section color-surface" data-anim="perspective">
        <div class="section-content">
            <h2>3D Perspective</h2>
            <p>This section tilts and moves in 3D space as you scroll past it.</p>
        </div>
    </section>

    <!-- Section 5: Slide from Left -->
    <section class="section" data-anim="slide-left">
        <div class="section-content">
            <h2>Slide Left</h2>
            <p>Content slides in from the left side of the screen.</p>
        </div>
    </section>

    <!-- Section 6: Slide from Right -->
    <section class="section color-accent" data-anim="slide-right">
        <div class="section-content">
            <h2>Slide Right</h2>
            <p>Content slides in from the right side of the screen.</p>
        </div>
    </section>

    <!-- Section 7: No Animation (Control) -->
    <section class="section color-secondary">
        <div class="section-content">
            <h2>Static Section</h2>
            <p>This section has no animation - it's our control to see the base styling.</p>
        </div>
    </section>
       <!-- Section 8: Gradient Shift Animation -->
    <section class="section" data-anim="gradient-shift">
        <div class="section-content">
            <h2>Gradient Shift</h2>
            <p>This section transitions between two different gradients based on scroll position.</p>
        </div>
    </section>

    <!-- Section 9: Multiple Effects -->
    <section class="section color-surface" data-anim="fade-up">
        <div class="section-content">
            <h2>Final Section</h2>
            <p>Thanks for scrolling! This demonstrates all our animation capabilities working together.</p>
            <p><strong>Debug:</strong> Check the console for scroll progress values.</p>
        </div>
    </section>

</main>

<style>
/* Quick inline styles for testing - move to LESS later */
.section-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
    z-index: 10;
    position: relative;
}

.section h1, .section h2 {
    margin-bottom: 1rem;
}

.section p {
    margin-bottom: 1rem;
}

.section p:last-child {
    margin-bottom: 0;
}

/* Visual debugging - add borders to see sections clearly */
.section {
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-sizing: border-box;
}

.section:nth-child(odd) {
    border-color: rgba(255, 255, 0, 0.3);
}


/* Fix fade-up animation - reverse the logic */
.section[data-anim="fade-up"] {
  /* Start visible, fade based on how much it's in view */
  opacity: calc(0.3 + (var(--scroll-progress, 0) * 0.7)) !important;
  transform: translateY(calc((1 - var(--scroll-progress, 0)) * 30px)) !important;
}

/* Make the pseudo-element always visible for debugging */
.section[data-anim="parallax"]::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    var(--color-secondary) 0%,    /* Pink */
    var(--color-surface) 100%     /* Purple */
  ) !important;
  z-index: -1; /* Behind the main content */
  
  /* Dramatic movement for testing */
  transform: translate(
    calc(var(--scroll-progress, 0) * 50px),
    calc(var(--scroll-progress, 0) * -200px)
  ) !important;
}

/* Make sure main section allows pseudo-element to show through */
.section[data-anim="parallax"] {
  background: rgba(227, 85, 79, 0.3) !important; /* 30% opacity red */
  position: relative; /* Establish positioning context */
}

/* Gradient shift animation */
.section[data-anim="gradient-shift"] {
  background: linear-gradient(135deg,
    hsl(calc(240 + (var(--scroll-progress) * -225)), calc(100% + (var(--scroll-progress) * -10%)), calc(20% + (var(--scroll-progress) * 35%))),
    hsl(calc(280 + (var(--scroll-progress) * 40)), calc(80% + (var(--scroll-progress) * -10%)), calc(30% + (var(--scroll-progress) * 15%)))
  ) !important;
}

</style>
<?php get_footer(); ?>