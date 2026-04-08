/**
 * Responsive Menu Toggle
 * Handles mobile hamburger menu functionality
 */

function toggleSidebar() {
    const sidebarMenu = document.getElementById('sidebarMenu');
    if (sidebarMenu) {
        sidebarMenu.classList.toggle('active');
    }
}

/**
 * Close sidebar menu when a link is clicked
 */
function closeSidebarOnNavigation() {
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    const sidebarMenu = document.getElementById('sidebarMenu');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (sidebarMenu && window.innerWidth <= 768) {
                sidebarMenu.classList.remove('active');
            }
        });
    });
}

/**
 * Close sidebar when clicking outside on mobile
 */
function closeSidebarOnClickOutside() {
    document.addEventListener('click', (e) => {
        const sidebar = document.querySelector('.sidebar');
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebarMenu = document.getElementById('sidebarMenu');
        
        if (sidebar && hamburgerBtn && sidebarMenu && window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && sidebarMenu.classList.contains('active')) {
                sidebarMenu.classList.remove('active');
            }
        }
    });
}

/**
 * Handle window resize - close menu if resizing to larger screens
 */
function handleWindowResize() {
    const sidebarMenu = document.getElementById('sidebarMenu');
    
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && sidebarMenu) {
            sidebarMenu.classList.remove('active');
        }
    });
}

/**
 * Initialize responsive features
 */
document.addEventListener('DOMContentLoaded', () => {
    closeSidebarOnNavigation();
    closeSidebarOnClickOutside();
    handleWindowResize();
});

/**
 * Adjust font sizes and spacing for very small screens
 */
function handleSmallScreenOptimization() {
    if (window.innerWidth <= 480) {
        document.body.style.fontSize = '14px';
    } else {
        document.body.style.fontSize = '16px';
    }
}

window.addEventListener('resize', handleSmallScreenOptimization);
window.addEventListener('load', handleSmallScreenOptimization);
