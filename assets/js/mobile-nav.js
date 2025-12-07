// Script para controlar o drawer mobile da navbar
document.addEventListener('DOMContentLoaded', function() {
    // Fechar drawer mobile quando clicar em um link
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const navbarCollapse = document.getElementById('mainNavbar');
    const navbarToggler = document.querySelector('.navbar-toggler');
    
    if (navbarCollapse && navbarToggler) {
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Em mobile, fechar o drawer
                if (window.innerWidth < 992) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });

        // Fechar drawer ao clicar fora (mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                const isClickInside = navbarCollapse.contains(e.target) || navbarToggler.contains(e.target);
                if (!isClickInside && navbarCollapse.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            }
        });
    }
});
