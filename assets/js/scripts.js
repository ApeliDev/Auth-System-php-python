// Toggle sidebar on mobile
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggler = document.querySelector('[data-bs-target="#sidebarCollapse"]');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggler && sidebar) {
        sidebarToggler.addEventListener('click', function() {
            sidebar.classList.toggle('d-none');
        });
    }
    
    // Highlight active nav link
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop();
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });
});