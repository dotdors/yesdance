/**
 * Dandysite Ginger - Scroll Animation Controller
 * 
 * Lightweight scroll-triggered animation system using Intersection Observer
 */

class ScrollAnimator {
    constructor() {
        this.elements = document.querySelectorAll('[data-animate]');
        this.animatedElements = new Set();
        this.observers = new Map();
        
        this.init();
    }

    init() {
        if (!this.elements.length) return;

        // Check for reduced motion preference
        this.respectsReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (this.respectsReducedMotion) {
            this.showAllElements();
            return;
        }

        this.setupObservers();
        this.setupStaggeredElements();
    }

    setupObservers() {
        // Main intersection observer for basic animations
        const mainObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.animatedElements.has(entry.target)) {
                    this.triggerAnimation(entry.target);
                    this.animatedElements.add(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        // Observer for elements that need to animate out when leaving viewport
        const bidirectionalObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const element = entry.target;
                if (entry.isIntersecting) {
                    element.classList.add('in-view');
                } else {
                    element.classList.remove('in-view');
                }
            });
        }, {
            threshold: 0.2,
            rootMargin: '0px 0px -20px 0px'
        });

        this.elements.forEach((element, index) => {
            // Add index for staggering
            element.style.setProperty('--animation-index', index);
            
            const animationType = element.dataset.animate;
            
            // Use bidirectional observer for certain animation types
            if (['dance-steps', 'background-parallax'].includes(animationType)) {
                bidirectionalObserver.observe(element);
            } else {
                mainObserver.observe(element);
            }
        });

        this.observers.set('main', mainObserver);
        this.observers.set('bidirectional', bidirectionalObserver);
    }

    setupStaggeredElements() {
        // Setup staggered animations for card cascades
        document.querySelectorAll('[data-animate="card-cascade"]').forEach(container => {
            const cards = container.querySelectorAll('.program-card, .wp-block-column');
            cards.forEach((card, index) => {
                card.style.setProperty('--card-index', index);
                card.style.setProperty('--stagger-delay', `${index * 0.2}s`);
            });
        });

        // Setup character-level animations for text
        this.setupTextAnimations();
    }

    setupTextAnimations() {
        const textElements = document.querySelectorAll('[data-animate*="text"], [data-animate*="hero"]');
        
        textElements.forEach(element => {
            const headings = element.querySelectorAll('h1, h2, h3, h4, h5, h6');
            const paragraphs = element.querySelectorAll('p');
            
            // Wrap text content for character-level animation
            [...headings, ...paragraphs].forEach((textEl, index) => {
                if (textEl.children.length > 0) return; // Skip if already processed
                
                const text = textEl.textContent;
                const words = text.split(' ');
                
                textEl.innerHTML = words.map((word, wordIndex) => {
                    const characters = word.split('').map((char, charIndex) => 
                        `<span class="char" style="--char-delay: ${(wordIndex * 3 + charIndex) * 0.05}s">${char}</span>`
                    ).join('');
                    return `<span class="word">${characters}</span>`;
                }).join('<span class="char"> </span>');
                
                textEl.style.setProperty('--text-index', index);
            });
        });
    }

    triggerAnimation(element) {
        const animationType = element.dataset.animate;
        
        // Add base animation class
        element.classList.add('in-view');
        
        // Handle specific animation types
        switch (animationType) {
            case 'hero-fade-up':
                this.animateHeroSection(element);
                break;
            case 'card-cascade':
                this.animateCardCascade(element);
                break;
            case 'dance-steps':
                this.animateDanceSteps(element);
                break;
            case 'circle-reveal':
                this.animateCircleReveal(element);
                break;
            case 'slide-text':
                this.animateSlideText(element);
                break;
            default:
                // Default fade-in animation
                break;
        }
        
        // Emit custom event for other scripts to listen to
        element.dispatchEvent(new CustomEvent('dandysite:animated', {
            detail: { type: animationType, element }
        }));
    }

    animateHeroSection(element) {
        const textElements = element.querySelectorAll('.char');
        
        textElements.forEach((char, index) => {
            setTimeout(() => {
                char.style.transform = 'translateY(0)';
                char.style.opacity = '1';
            }, index * 50);
        });
    }

    animateCardCascade(element) {
        const cards = element.querySelectorAll('.program-card, .wp-block-column');
        
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.transform = 'translateX(0) rotate(0deg) scale(1)';
                card.style.opacity = '1';
            }, index * 200);
        });
    }

    animateDanceSteps(element) {
        // This will be handled by the DanceEffects class
        element.classList.add('dancing');
    }

    animateCircleReveal(element) {
        element.style.clipPath = 'circle(100% at 50% 50%)';
        element.style.transform = 'scale(1)';
    }

    animateSlideText(element) {
        const textElements = element.querySelectorAll('h1, h2, h3, p, blockquote');
        
        textElements.forEach((textEl, index) => {
            setTimeout(() => {
                textEl.style.transform = 'translateY(0)';
                textEl.style.opacity = '1';
            }, index * 300);
        });
    }

    showAllElements() {
        // For users with reduced motion preference
        this.elements.forEach(element => {
            element.style.opacity = '1';
            element.style.transform = 'none';
            element.classList.add('in-view');
        });
    }

    // Method to refresh observer (useful for dynamic content)
    refresh() {
        // Disconnect existing observers
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();
        this.animatedElements.clear();
        
        // Reinitialize
        this.elements = document.querySelectorAll('[data-animate]');
        this.init();
    }

    // Method to manually trigger animation
    triggerElement(selector) {
        const element = document.querySelector(selector);
        if (element && !this.animatedElements.has(element)) {
            this.triggerAnimation(element);
            this.animatedElements.add(element);
        }
    }

    // Cleanup method
    destroy() {
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();
        this.animatedElements.clear();
    }
}

// Utility function to debounce resize events
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize scroll animator
    const scrollAnimator = new ScrollAnimator();
    
    // Make it globally available
    window.dandysiteGingerAnimator = scrollAnimator;
    
    // Handle window resize
    const handleResize = debounce(() => {
        scrollAnimator.refresh();
    }, 250);
    
    window.addEventListener('resize', handleResize);
    
    // Handle route changes for SPA-like navigation
    window.addEventListener('popstate', () => {
        setTimeout(() => {
            scrollAnimator.refresh();
        }, 100);
    });
    
    // Listen for WordPress block editor updates
    if (typeof wp !== 'undefined' && wp.data) {
        wp.data.subscribe(() => {
            setTimeout(() => {
                scrollAnimator.refresh();
            }, 500);
        });
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScrollAnimator;
}

// circle-reveal specific animation handling
document.addEventListener('DOMContentLoaded', function() {
    const circleRevealElements = document.querySelectorAll('[data-animate="circle-reveal"]');
    
    if (circleRevealElements.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: [0, 0.1, 0.25, 0.5],
        rootMargin: '0px 0px -20px 0px'
    });
    
    circleRevealElements.forEach(element => {
        observer.observe(element);
    });
});

// Scroll-Hijacking Horizontal Gallery - Full Implementation

console.log('🚀 Simple gallery script loaded!');

class SimpleHorizontalGallery {
  constructor() {
    this.galleries = document.querySelectorAll('.horizontal-scroll-gallery');
    this.isLocked = false;
    this.currentGallery = null;
    this.progress = 0;
    this.maxTranslate = 0;
    
    this.init();
  }

  init() {
    if (this.galleries.length === 0) return;
    
    console.log('Found', this.galleries.length, 'galleries');
    
    // Setup each gallery
    this.galleries.forEach(gallery => {
      const images = gallery.querySelectorAll('.wp-block-image');
      const imageWidth = 350;
      const gap = 48;
      const totalWidth = (images.length * imageWidth) + ((images.length - 1) * gap);
      const maxTranslate = Math.max(0, totalWidth - window.innerWidth + 200); // Add padding
      
      gallery.dataset.maxTranslate = maxTranslate;
      console.log('Gallery setup:', { images: images.length, totalWidth, maxTranslate });
    });
    
    // Setup listeners
    window.addEventListener('scroll', this.handleScroll.bind(this));
    window.addEventListener('wheel', this.handleWheel.bind(this), { passive: false });
    
    // Create progress bar
    const progressBar = document.createElement('div');
    progressBar.className = 'gallery-progress';
    progressBar.style.cssText = `
      position: fixed;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%);
      width: 0%;
      max-width: 200px;
      height: 4px;
      background: #e76f51;
      border-radius: 2px;
      z-index: 1001;
      opacity: 0;
      transition: opacity 0.3s ease, width 0.1s ease;
    `;
    document.body.appendChild(progressBar);
  }

  handleScroll() {
    if (this.isLocked) return;
    
    // Check if any gallery should be active
    this.galleries.forEach(gallery => {
      const rect = gallery.getBoundingClientRect();
      const activationTop = rect.top - window.innerHeight;
      const activationBottom = rect.top;
      
      // Activate when gallery is well-centered
      if (activationTop <= -300 && activationBottom >= 300) {
        if (!this.isLocked) {
          this.activateGallery(gallery);
        }
      }
    });
  }

  activateGallery(gallery) {
    console.log('🎯 Activating gallery');
    
    this.isLocked = true;
    this.currentGallery = gallery;
    this.maxTranslate = parseFloat(gallery.dataset.maxTranslate);
    
    // Don't reset progress - keep current position
    
    // Add visual feedback
    document.body.style.overflow = 'hidden';
    gallery.classList.add('gallery-active');
    
    const progressBar = document.querySelector('.gallery-progress');
    if (progressBar) {
      progressBar.style.opacity = '1';
    }
    
    console.log('Gallery active - maxTranslate:', this.maxTranslate);
  }

  handleWheel(e) {
    if (!this.isLocked || !this.currentGallery) return;
    
    e.preventDefault();
    
    // MUCH SLOWER sensitivity
    const delta = e.deltaY;
    const sensitivity = 0.00005; // Very slow!
    
    const oldProgress = this.progress;
    this.progress += delta * sensitivity;
    this.progress = Math.max(0, Math.min(1, this.progress));
    
    console.log('Wheel:', { delta, oldProgress, newProgress: this.progress });
    
    // Update gallery position
    const translateX = this.progress * this.maxTranslate;
    this.currentGallery.style.transform = `translateX(-${translateX}px)`;
    
    // Update progress bar
    const progressBar = document.querySelector('.gallery-progress');
    if (progressBar) {
      progressBar.style.width = `${this.progress * 100}%`;
    }
    
    // Exit conditions
    if (this.progress >= 1 && delta > 0) {
      console.log('Reached end - staying at final position');
      this.deactivateGallery();
    } else if (this.progress <= 0 && delta < 0) {
      console.log('Reached start - resetting to beginning');
      this.progress = 0;
      this.currentGallery.style.transform = 'translateX(0px)';
      this.deactivateGallery();
    }
  }

  deactivateGallery() {
    console.log('Deactivating gallery');
    
    this.isLocked = false;
    document.body.style.overflow = '';
    
    if (this.currentGallery) {
      this.currentGallery.classList.remove('gallery-active');
    }
    
    const progressBar = document.querySelector('.gallery-progress');
    if (progressBar) {
      progressBar.style.opacity = '0';
    }
    
    this.currentGallery = null;
  }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  console.log('Initializing simple gallery...');
  new SimpleHorizontalGallery();
});