// Main JavaScript for Reborn Dolls Auction Site

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Form validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = 'var(--danger-color)';
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields');
        }
    });
});

// Auto-hide alerts after 5 seconds
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.remove();
        }, 500);
    }, 5000);
});

// Image lazy loading fallback
document.addEventListener('DOMContentLoaded', function() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});

// Mobile menu toggle (if needed)
const createMobileMenu = () => {
    const nav = document.querySelector('.nav');
    if (!nav) return;
    
    const menuButton = document.createElement('button');
    menuButton.className = 'mobile-menu-toggle';
    menuButton.innerHTML = '☰';
    menuButton.style.cssText = `
        display: none;
        background: none;
        border: none;
        font-size: 24px;
        color: white;
        cursor: pointer;
        padding: 10px;
    `;
    
    if (window.innerWidth <= 768) {
        menuButton.style.display = 'block';
        nav.querySelector('.container').prepend(menuButton);
        
        menuButton.addEventListener('click', () => {
            const navList = nav.querySelector('.nav-list');
            navList.style.display = navList.style.display === 'flex' ? 'none' : 'flex';
        });
    }
};

// Initialize mobile menu on load and resize
createMobileMenu();
window.addEventListener('resize', createMobileMenu);

// Number formatting for price inputs
document.querySelectorAll('input[type="number"][name*="price"], input[type="number"][name*="amount"]').forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
});

// Confirm before deleting
document.querySelectorAll('[data-confirm]').forEach(element => {
    element.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm || 'Are you sure?')) {
            e.preventDefault();
        }
    });
});

// Auto-update current time displays
const updateTimeDisplays = () => {
    document.querySelectorAll('[data-timestamp]').forEach(element => {
        const timestamp = parseInt(element.dataset.timestamp);
        const now = Date.now();
        const diff = now - timestamp;
        
        let timeAgo = '';
        if (diff < 60000) {
            timeAgo = 'just now';
        } else if (diff < 3600000) {
            timeAgo = Math.floor(diff / 60000) + ' minutes ago';
        } else if (diff < 86400000) {
            timeAgo = Math.floor(diff / 3600000) + ' hours ago';
        } else {
            timeAgo = Math.floor(diff / 86400000) + ' days ago';
        }
        
        element.textContent = timeAgo;
    });
};

setInterval(updateTimeDisplays, 60000); // Update every minute

// Prevent double form submission
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            // Re-enable after 3 seconds in case of error
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = submitBtn.dataset.originalText || 'Submit';
            }, 3000);
        }
    });
});

console.log('Reborn Dolls Auction - v1.0');
