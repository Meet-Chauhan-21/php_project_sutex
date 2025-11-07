// Mobile Menu Toggle
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const nav = document.getElementById('nav');

if (mobileMenuBtn && nav) {
    mobileMenuBtn.addEventListener('click', () => {
        nav.classList.toggle('active');
        mobileMenuBtn.innerHTML = nav.classList.contains('active') ? 
            '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
    });
}

// Smooth Scrolling for Navigation Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
            
            // Close mobile menu if open
            if (nav.classList.contains('active')) {
                nav.classList.remove('active');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            }
        }
    });
});

// Header Scroll Effect
const header = document.getElementById('header');
if (header) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
}

// Profile Link - Handle conditional redirect based on login status
const profileLink = document.getElementById('profileLink');
if (profileLink) {
    profileLink.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Check if user is logged in
        const isLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
        
        if (isLoggedIn) {
            // User is logged in, redirect to profile page
            window.location.href = 'profile.html';
        } else {
            // User is not logged in, redirect to login page
            window.location.href = 'login.html';
        }
    });
}

// Navigation Links Management Based on Authentication
function updateNavigationLinks() {
    // This function is no longer needed for index page since we only have one Profile link
    // But we keep it for other pages that might need it
}

// Call on page load
document.addEventListener('DOMContentLoaded', function() {
    // No need to call updateNavigationLinks for index page
    // The profile link now handles redirect logic directly
});

// Counter Animation
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200;
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const increment = target / speed;
        
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(animateCounters, 1);
        } else {
            counter.innerText = target;
            if (counter.getAttribute('data-target') === "85") {
                counter.innerText += "%";
            }
        }
    });
}

// Intersection Observer for Scroll Animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animated');
            
            // Start counter animation when stats section is visible
            if (entry.target.id === 'stats') {
                animateCounters();
            }
        }
    });
}, {
    threshold: 0.1
});

document.querySelectorAll('.animate').forEach(el => {
    observer.observe(el);
});

// Observe sections for navigation highlighting
const sections = document.querySelectorAll('section');
window.addEventListener('scroll', () => {
    let current = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        
        if (pageYOffset >= (sectionTop - 100)) {
            current = section.getAttribute('id');
        }
    });
    
    document.querySelectorAll('nav ul li a').forEach(a => {
        a.classList.remove('active');
        if (a.getAttribute('href') === `#${current}`) {
            a.classList.add('active');
        }
    });
});

// Form Submission
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Thank you for your message! Our admissions team will contact you shortly.');
        e.target.reset();
    });
}
