document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.navbar-links');
    const navbar = document.querySelector('.navbar');

    // Função para fechar o menu com animação
   window.closeMenu = function () {
    if (!navLinks.classList.contains('active')) return;

    navLinks.classList.remove('active');
    document.body.style.overflow = 'auto';
};

    // aTIVA o botão hamburguer
    toggleButton.addEventListener('click', function (e) {
    e.stopPropagation();
    navLinks.classList.toggle('active');
    document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : 'auto';
});

    // Fecha o menu ao clicar fora
    document.addEventListener('click', function (e) {
        if (
            navLinks.classList.contains('active') &&
            !navLinks.contains(e.target) &&
            !toggleButton.contains(e.target)
        ) {
            closeMenu();
        }
    });

    document.addEventListener('touchstart', function (e) {
        if (
            navLinks.classList.contains('active') &&
            !navLinks.contains(e.target) &&
            !toggleButton.contains(e.target)
        ) {
            closeMenu();
        }
    });
});