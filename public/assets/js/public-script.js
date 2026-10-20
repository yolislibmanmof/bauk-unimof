/**
 * BAUK UNIMOF - Public JavaScript
 * Handles all frontend interactions
 * ES6+ Compatible
 */

'use strict';

// =====================================================
// DOM READY
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    initNavbar();
    initBackToTop();
    initCounterAnimation();
    initScrollAnimations();
    initFlashMessages();
});

// =====================================================
// NAVBAR FUNCTIONALITY
// =====================================================
function initNavbar() {
    const header = document.getElementById('mainHeader');
    const toggler = document.getElementById('navbarToggler');
    const menu = document.getElementById('navbarMenu');
    
    // Scroll effect
    if (header) {
        let lastScroll = 0;
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
    }
    
    // Mobile menu toggle
    if (toggler && menu) {
        toggler.addEventListener('click', function() {
            menu.classList.toggle('active');
            document.body.style.overflow = menu.classList.contains('active') ? 'hidden' : '';
        });
        
        // Close menu on link click
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                menu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
        // Close menu on outside click
        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && !toggler.contains(e.target)) {
                menu.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
}

// =====================================================
// BACK TO TOP BUTTON
// =====================================================
function initBackToTop() {
    const btn = document.getElementById('backToTop');
    
    if (!btn) return;
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    });
    
    btn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// =====================================================
// COUNTER ANIMATION
// =====================================================
function initCounterAnimation() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    
    if (counters.length === 0) return;
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-count'));
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(function() {
        current += step;
        
        if (current >= target) {
            element.textContent = target.toLocaleString('id-ID');
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString('id-ID');
        }
    }, 16);
}

// =====================================================
// SCROLL ANIMATIONS
// =====================================================
function initScrollAnimations() {
    const elements = document.querySelectorAll('.service-card, .news-card, .stat-card');
    
    if (elements.length === 0) return;
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// =====================================================
// FLASH MESSAGES
// =====================================================
function initFlashMessages() {
    const messages = document.querySelectorAll('.flash-message');
    
    messages.forEach(msg => {
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (msg.parentElement) {
                msg.style.opacity = '0';
                msg.style.transform = 'translateX(100%)';
                setTimeout(() => msg.remove(), 300);
            }
        }, 5000);
    });
}

// =====================================================
// FORM VALIDATION
// =====================================================
function validateContactForm(form) {
    const errors = [];
    
    const name = form.querySelector('[name="name"]');
    const email = form.querySelector('[name="email"]');
    const subject = form.querySelector('[name="subject"]');
    const message = form.querySelector('[name="message"]');
    
    if (name && !name.value.trim()) errors.push('Nama wajib diisi');
    if (email && !isValidEmail(email.value)) errors.push('Email tidak valid');
    if (subject && !subject.value.trim()) errors.push('Subjek wajib diisi');
    if (message && message.value.trim().length < 10) errors.push('Pesan minimal 10 karakter');
    
    return errors;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// =====================================================
// SMOOTH SCROLL
// =====================================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === '#') return;
        
        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            const offset = 80;
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
            
            window.scrollTo({
                top: top,
                behavior: 'smooth'
            });
        }
    });
});

// =====================================================
// LAZY LOADING IMAGES
// =====================================================
if ('IntersectionObserver' in window) {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
}

// =====================================================
// UTILITY: Debounce
// =====================================================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// =====================================================
// CONSOLE BRANDING
// =====================================================
console.log('%c BAUK UNIMOF ', 'background: #1a5276; color: white; font-size: 16px; font-weight: bold; padding: 10px;');
console.log('%c Sistem Informasi Biro Administrasi Umum dan Keuangan ', 'color: #2980b9; font-size: 12px;');
console.log('%c Universitas Muhammadiyah Maumere ', 'color: #64748b; font-size: 11px;');