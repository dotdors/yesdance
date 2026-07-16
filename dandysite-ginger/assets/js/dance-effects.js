/**
 * Dandysite Ginger - Dance-Specific Effects
 * 
 * Specialized animations and effects for dance-themed content
 */

class DanceEffects {
    constructor() {
        this.danceStepsSections = document.querySelectorAll('.dance-steps-section');
        this.backgroundMediaSections = document.querySelectorAll('.has-background-media');
        this.activeFootsteps = new Map();
        
        this.init();
    }

    init() {
        this.setupDanceSteps();
        this.setupBackgroundMedia();
        this.setupMouseFollowEffects();
        this.setupRhythmicElements();
        
        // Listen for animation events from ScrollAnimator
        document.addEventListener('dandysite:animated', (event) => {
            this.handleAnimationEvent(event.detail);
        });
    }

    setupDanceSteps() {
        this.danceStepsSections.forEach((section, index) => {
            this.createFootstepPattern(section, index);
            
            // Observe when section comes into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.startDanceAnimation(entry.target);
                    } else {
                        this.stopDanceAnimation(entry.target);
                    }
                });
            }, { threshold: 0.3 });
            
            observer.observe(section);
        });
    }

    createFootstepPattern(container, containerIndex) {
        const footstepCount = 12;
        const patterns = [
            // Pattern 1: Circular
            { type: 'circle', radius: 150 },
            // Pattern 2: Figure-8
            { type: 'figure8', width: 200, height: 100 },
            // Pattern 3: Spiral
            { type: 'spiral', radius: 120, turns: 2 }
        ];
        
        const pattern = patterns[containerIndex % patterns.length];
        const footsteps = [];
        
        for (let i = 0; i < footstepCount; i++) {
            const footstep = document.createElement('div');
            footstep.className = 'footstep';
            footstep.style.setProperty('--step-delay', i);
            
            // Calculate position based on pattern
            const position = this.calculateFootstepPosition(i, footstepCount, pattern);
            const rotation = this.calculateFootstepRotation(i, footstepCount, pattern);
            
            footstep.style.left = `${position.x}%`;
            footstep.style.top = `${position.y}%`;
            footstep.style.setProperty('--step-rotation', `${rotation}deg`);
            
            // Add alternating left/right foot styling
            if (i % 2 === 0) {
                footstep.classList.add('left-foot');
            } else {
                footstep.classList.add('right-foot');
            }
            
            container.appendChild(footstep);
            footsteps.push(footstep);
        }
        
        this.activeFootsteps.set(container, footsteps);
    }

    calculateFootstepPosition(index, total, pattern) {
        const progress = index / total;
        const centerX = 50;
        const centerY = 50;
        
        switch (pattern.type) {
            case 'circle':
                const angle = progress * Math.PI * 2;
                return {
                    x: centerX + Math.cos(angle) * (pattern.radius / window.innerWidth * 100),
                    y: centerY + Math.sin(angle) * (pattern.radius / window.innerHeight * 100)
                };
                
            case 'figure8':
                const t = progress * Math.PI * 2;
                return {
                    x: centerX + Math.sin(t) * (pattern.width / window.innerWidth * 50),
                    y: centerY + Math.sin(2 * t) * (pattern.height / window.innerHeight * 25)
                };
                
            case 'spiral':
                const spiralAngle = progress * Math.PI * 2 * pattern.turns;
                const spiralRadius = (pattern.radius * progress) / window.innerWidth * 100;
                return {
                    x: centerX + Math.cos(spiralAngle) * spiralRadius,
                    y: centerY + Math.sin(spiralAngle) * spiralRadius
                };
                
            default:
                return { x: centerX, y: centerY };
        }
    }

    calculateFootstepRotation(index, total, pattern) {
        const progress = index / total;
        
        switch (pattern.type) {
            case 'circle':
                return progress * 360;
            case 'figure8':
                return Math.sin(progress * Math.PI * 4) * 30;
            case 'spiral':
                return progress * 180;
            default:
                return Math.random() * 30 - 15;
        }
    }

    startDanceAnimation(container) {
        const footsteps = this.activeFootsteps.get(container);
        if (!footsteps) return;
        
        footsteps.forEach((footstep, index) => {
            setTimeout(() => {
                footstep.style.animationPlayState = 'running';
                footstep.classList.add('active');
            }, index * 200);
        });
    }

    stopDanceAnimation(container) {
        const footsteps = this.activeFootsteps.get(container);
        if (!footsteps) return;
        
        footsteps.forEach(footstep => {
            footstep.style.animationPlayState = 'paused';
            footstep.classList.remove('active');
        });
    }

    setupBackgroundMedia() {
        this.backgroundMediaSections.forEach(section => {
            const bgType = section.dataset.backgroundType;
            const bgImage = section.dataset.backgroundImage;
            const bgVideo = section.dataset.backgroundVideo;
            const overlayOpacity = section.dataset.backgroundOverlay || 50;
            
            if (bgType === 'video' && bgVideo) {
                this.createBackgroundVideo(section, bgVideo, overlayOpacity);
            } else if (bgType === 'image' && bgImage) {
                this.createBackgroundImage(section, bgImage, overlayOpacity);
            }
        });
    }

    createBackgroundVideo(container, videoUrl, overlayOpacity) {
        const video = document.createElement('video');
        video.className = 'background-video';
        video.src = videoUrl;
        video.autoplay = true;
        video.loop = true;
        video.muted = true;
        video.playsInline = true;
        
        const overlay = document.createElement('div');
        overlay.className = 'background-overlay';
        overlay.style.backgroundColor = `rgba(0, 0, 0, ${overlayOpacity / 100})`;
        
        container.appendChild(video);
        container.appendChild(overlay);
        container.classList.add('has-background-media');
        
        // Handle video load errors
        video.addEventListener('error', () => {
            console.warn('Background video failed to load:', videoUrl);
            container.classList.add('video-error');
        });
    }

    createBackgroundImage(container, imageUrl, overlayOpacity) {
        const imageDiv = document.createElement('div');
        imageDiv.className = 'background-image';
        imageDiv.style.backgroundImage = `url(${imageUrl})`;
        
        const overlay = document.createElement('div');
        overlay.className = 'background-overlay';
        overlay.style.backgroundColor = `rgba(0, 0, 0, ${overlayOpacity / 100})`;
        
        container.appendChild(imageDiv);
        container.appendChild(overlay);
        container.classList.add('has-background-media');
    }

// mouse-follow musical notes effect

setupMouseFollowEffects() {
    let mouseTrailTimer;
    let lastTrailTime = 0;
    
    document.addEventListener('mousemove', (e) => {
        // Only create trail effects in dance sections
        const danceSection = e.target.closest('.dance-steps-section, .hero-section');
        if (!danceSection) return;
        
        // Throttle to avoid too many notes (every 200ms max)
        const now = Date.now();
        if (now - lastTrailTime < 200) return;
        lastTrailTime = now;
        
        clearTimeout(mouseTrailTimer);
        mouseTrailTimer = setTimeout(() => {
            this.createMusicalTrail(e.clientX, e.clientY);
        }, 50);
    });
}

createMusicalTrail(x, y) {
    const notes = ['♪', '♫', '♬', '♩'];
    const randomNote = notes[Math.floor(Math.random() * notes.length)];
    
    const note = document.createElement('div');
    note.innerHTML = randomNote;
    note.className = 'musical-trail';
    
    // Position with slight random offset for organic feel
    const offsetX = (Math.random() - 0.5) * 20;
    const offsetY = (Math.random() - 0.5) * 20;
    
    // Vary size between 20px and 36px for dynamic feel
    const fontSize = Math.random() * 16 + 20; // Random between 20-36px
    
    // Adjust opacity based on size - larger notes slightly more visible
    const baseOpacity = 0.52;
    const sizeMultiplier = (fontSize - 20) / 16; // 0 to 1 range
    const opacity = baseOpacity + (sizeMultiplier * 0.06); // 0.58 to 0.64 range

    note.style.left = (x + offsetX) + 'px';
    note.style.top = (y + offsetY) + 'px';
    note.style.position = 'fixed';
    note.style.fontSize = fontSize + 'px';
    note.style.color = `rgba(255, 255, 255, ${opacity})`;
    note.style.fontFamily = 'serif';
    note.style.pointerEvents = 'none';
    note.style.zIndex = '999';
    note.style.userSelect = 'none';
    note.style.transform = 'rotate(' + (Math.random() * 30 - 15) + 'deg)'; // Slight random rotation
    note.style.animation = 'musical-note-fade 3s ease-out forwards';
    
    document.body.appendChild(note);
    
    // Clean up after animation
    setTimeout(() => {
        if (note.parentNode) {
            note.parentNode.removeChild(note);
        }
    }, 3000);
}
    setupRhythmicElements() {
        // Add rhythmic pulse to certain elements
        const rhythmicElements = document.querySelectorAll(
            '.dance-homepage .wp-block-button, .dance-homepage .program-card'
        );
        
        rhythmicElements.forEach((element, index) => {
            element.style.setProperty('--rhythm-delay', `${index * 0.5}s`);
            element.classList.add('rhythmic-element');
        });
        
        // Create subtle rhythmic animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes subtle-pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.02); }
            }
            
            .rhythmic-element:hover {
                animation: subtle-pulse 2s ease-in-out infinite;
                animation-delay: var(--rhythm-delay, 0s);
            }
            
            @keyframes fade-out-trail {
                0% { 
                    opacity: 1;
                    transform: scale(1) rotate(0deg);
                }
                100% { 
                    opacity: 0;
                    transform: scale(0) rotate(180deg);
                }
            }
        `;
        document.head.appendChild(style);
    }

    handleAnimationEvent(detail) {
        const { type, element } = detail;
        
        switch (type) {
            case 'dance-steps':
                // Additional effects when dance steps animation triggers
                this.addSparkleEffect(element);
                break;
            case 'hero-fade-up':
                // Add background animation to hero
                this.addHeroBackgroundEffect(element);
                break;
            case 'card-cascade':
                // Add hover enhancement to cards
                this.enhanceCardInteractions(element);
                break;
        }
    }

    addSparkleEffect(container) {
        const sparkleCount = 8;
        
        for (let i = 0; i < sparkleCount; i++) {
            setTimeout(() => {
                const sparkle = document.createElement('div');
                sparkle.className = 'sparkle';
                sparkle.style.position = 'absolute';
                sparkle.style.width = '4px';
                sparkle.style.height = '4px';
                sparkle.style.background = 'white';
                sparkle.style.borderRadius = '50%';
                sparkle.style.left = Math.random() * 100 + '%';
                sparkle.style.top = Math.random() * 100 + '%';
                sparkle.style.animation = 'sparkle-fade 2s ease-out forwards';
                sparkle.style.zIndex = '10';
                
                container.appendChild(sparkle);
                
                setTimeout(() => {
                    if (sparkle.parentNode) {
                        sparkle.parentNode.removeChild(sparkle);
                    }
                }, 2000);
            }, i * 100);
        }
    }

    addHeroBackgroundEffect(heroElement) {
        // Create floating dance-themed shapes
        const shapes = ['♪', '♫', '♬', '♩'];
        const shapeCount = 6;
        
        for (let i = 0; i < shapeCount; i++) {
            const shape = document.createElement('div');
            shape.className = 'floating-music-note';
            shape.textContent = shapes[Math.floor(Math.random() * shapes.length)];
            shape.style.position = 'absolute';
            shape.style.fontSize = '2rem';
            shape.style.color = 'rgba(255, 255, 255, 0.3)';
            shape.style.left = Math.random() * 100 + '%';
            shape.style.top = Math.random() * 100 + '%';
            shape.style.animation = `float-note 10s ease-in-out infinite ${i * 0.5}s`;
            shape.style.pointerEvents = 'none';
            shape.style.zIndex = '1';
            
            heroElement.appendChild(shape);
        }
    }

    enhanceCardInteractions(container) {
        const cards = container.querySelectorAll('.program-card, .wp-block-column');
        
        cards.forEach((card, index) => {
            // Add tilt effect on hover
            card.addEventListener('mouseenter', () => {
                const randomTilt = (Math.random() - 0.5) * 4; // Random tilt between -2 and 2 degrees
                card.style.transform = `translateY(-5px) rotate(${randomTilt}deg) scale(1.02)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) rotate(0deg) scale(1)';
            });
            
            // Add click ripple effect
            card.addEventListener('click', (e) => {
                this.createRippleEffect(e, card);
            });
        });
    }

    createRippleEffect(event, element) {
        const rect = element.getBoundingClientRect();
        const ripple = document.createElement('span');
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.position = 'absolute';
        ripple.style.width = size + 'px';
        ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.style.borderRadius = '50%';
        ripple.style.background = 'rgba(255, 255, 255, 0.3)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple-effect 0.6s linear';
        ripple.style.pointerEvents = 'none';
        ripple.style.zIndex = '1';
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => {
            if (ripple.parentNode) {
                ripple.parentNode.removeChild(ripple);
            }
        }, 600);
    }

    // Method to add custom dance animations
    addCustomDanceSequence(container, sequence) {
        const { steps, timing, pattern } = sequence;
        
        steps.forEach((step, index) => {
            setTimeout(() => {
                this.executeStep(container, step);
            }, index * timing);
        });
    }

    executeStep(container, step) {
        const { type, target, properties } = step;
        const element = container.querySelector(target);
        
        if (!element) return;
        
        switch (type) {
            case 'bounce':
                element.style.animation = 'dance-bounce 0.5s ease-in-out';
                break;
            case 'spin':
                element.style.animation = 'dance-spin 1s ease-in-out';
                break;
            case 'pulse':
                element.style.animation = 'dance-pulse 0.8s ease-in-out';
                break;
        }
    }

    // Cleanup method
    destroy() {
        // Clear all footstep patterns
        this.activeFootsteps.forEach((footsteps, container) => {
            footsteps.forEach(footstep => {
                if (footstep.parentNode) {
                    footstep.parentNode.removeChild(footstep);
                }
            });
        });
        this.activeFootsteps.clear();
        
        // Remove event listeners
        document.removeEventListener('dandysite:animated', this.handleAnimationEvent);
    }
}

// Additional CSS animations to be injected
function injectDanceAnimations() {
    const style = document.createElement('style');
    style.id = 'dance-effects-styles';
    style.textContent = `
        @keyframes sparkle-fade {
            0% { 
                opacity: 1;
                transform: scale(0) rotate(0deg);
            }
            50% {
                opacity: 1;
                transform: scale(1) rotate(180deg);
            }
            100% { 
                opacity: 0;
                transform: scale(0) rotate(360deg);
            }
        }
        
        @keyframes float-note {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }
            25% { 
                transform: translateY(-20px) rotate(90deg);
                opacity: 0.6;
            }
            50% { 
                transform: translateY(-10px) rotate(180deg);
                opacity: 0.4;
            }
            75% { 
                transform: translateY(-30px) rotate(270deg);
                opacity: 0.5;
            }
        }
        
        @keyframes ripple-effect {
            0% {
                transform: scale(0);
                opacity: 1;
            }
            100% {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        @keyframes dance-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes dance-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes dance-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* Enhanced footstep styles */
        .footstep.left-foot {
            transform-origin: center bottom;
        }
        
        .footstep.right-foot {
            transform-origin: center bottom;
            filter: hue-rotate(30deg);
        }
        
        .footstep.active {
            filter: brightness(1.2) saturate(1.1);
        }
        
        /* Music note floating animation */
        .floating-music-note {
            user-select: none;
            pointer-events: none;
        }
    `;
    
    // Only inject if not already present
    if (!document.getElementById('dance-effects-styles')) {
        document.head.appendChild(style);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Inject additional styles
    injectDanceAnimations();
    
    // Initialize dance effects
    const danceEffects = new DanceEffects();
    
    // Make it globally available
    window.dandysiteGingerDanceEffects = danceEffects;
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DanceEffects;
}