// js/script.js (VERSÃO MESTRE FINAL)

$(document).ready(function() {
    
    // --- LÓGICA DO TEMA CLARO/ESCURO ---
    const themeToggle = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement; // Controla o <html>

    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // Alterna a classe no <html>
            htmlElement.classList.toggle('dark-mode');
            
            // Salva a escolha no localStorage
            if (htmlElement.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    }

    // --- LÓGICA DO MENU HAMBURGER ---
    const menuToggle = document.getElementById('menu-toggle');
    const navMenu = document.getElementById('nav-menu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('menu-aberto');
            menuToggle.classList.toggle('ativo');
        });
    }

    // --- LÓGICA DO FILTRO DO PORTFÓLIO ---
    const filtroBotoes = document.querySelectorAll('.filtro-btn');
    const portfolioItens = document.querySelectorAll('.portfolio-item');

    if (filtroBotoes.length > 0 && portfolioItens.length > 0) {
        
        filtroBotoes.forEach(function(botao) {
            botao.addEventListener('click', function() {
                
                filtroBotoes.forEach(function(btn) {
                    btn.classList.remove('ativo');
                });
                
                this.classList.add('ativo');

                const filtro = this.getAttribute('data-filter');

                portfolioItens.forEach(function(item) {
                    item.style.display = 'none';

                    if (filtro === 'todos' || item.classList.contains(filtro)) {
                        item.style.display = 'block'; 
                    }
                });
            });
        });
    }
    
    // --- INICIALIZAÇÃO DO LIGHTBOX DO PORTFÓLIO ---
    $('.portfolio-grid').magnificPopup({
        delegate: '.portfolio-item',
        type: 'image',
        gallery: {
            enabled: true,
            preload: [0, 1]
        },
        image: {
            titleSrc: 'alt'
        }
    });

});