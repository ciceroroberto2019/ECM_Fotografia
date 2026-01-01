// js/script.js (VERSÃO MESTRE COM HEADER STICKY)

$(document).ready(function() {
    
    // --- LÓGICA DO HEADER STICKY (NOVO) ---
    const $header = $('#main-header');
    const $heroCarousel = $('.hero-carousel-wrapper');
    
    // Adiciona uma classe ao body se for a página home
    if ($heroCarousel.length > 0) {
        $('body').addClass('pagina-home');
    }

    $(window).on('scroll', function() {
        const scrollTop = $(window).scrollTop();
        
        // Se a página for a HOME
        if ($('body').hasClass('pagina-home')) {
            // Fica fixo após rolar a altura do carrossel (ou 100vh)
            if (scrollTop > $(window).height() - 80) { 
                $header.addClass('header-scrolled texto-escuro').removeClass('texto-claro');
            } else {
                $header.removeClass('header-scrolled');
                // Re-aplica a lógica de contraste dinâmico
                const $slideAtivo = $('.hero-slide.ativo');
                if ($slideAtivo.length > 0) {
                     $header.removeClass('texto-claro texto-escuro').addClass($slideAtivo.data('estilo-texto'));
                }
            }
        } 
        // (Em outras páginas, o header já é fixo/estático via CSS)
    });

    
    // --- LÓGICA DO TEMA CLARO/ESCURO ---
    const themeToggle = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement; 
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            htmlElement.classList.toggle('dark-mode');
            if (htmlElement.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
            
            // Força a re-checagem do header (se estiver no topo)
            $(window).trigger('scroll');
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
    
    // --- LIGHTBOX DO PORTFÓLIO ---
    $('.portfolio-grid').magnificPopup({
        delegate: '.portfolio-item',
        type: 'image',
        gallery: { enabled: true, preload: [0, 1] },
        image: { titleSrc: 'alt' }
    });

    // --- CARROSSEL HERO CROSS-FADE ---
    const $slides = $('.hero-slide');
    if ($slides.length > 1) {
        let currentSlide = 0;
        
        function changeSlide() {
            const $slideAtual = $($slides.get(currentSlide));
            currentSlide = (currentSlide + 1) % $slides.length;
            const $proximoSlide = $($slides.get(currentSlide));
            const estilo = $proximoSlide.data('estilo-texto');
            
            // Só muda a cor do header se o usuário NÃO ROLOU A PÁGINA
            if ($(window).scrollTop() < $(window).height() - 80) {
                $header.removeClass('texto-claro texto-escuro').addClass(estilo);
            }
            
            $slideAtual.removeClass('ativo');
            $proximoSlide.addClass('ativo');
        }
        
        // Define o tema para o PRIMEIRO slide
        const estiloInicial = $($slides.get(0)).data('estilo-texto');
        $header.addClass(estiloInicial);
        
        setInterval(changeSlide, 5000); // Timer

    } else if ($slides.length == 1) {
        // Se houver só 1 slide
        const estiloInicial = $($slides.get(0)).data('estilo-texto');
        $header.addClass(estiloInicial);
    }
    // --- FIM DO CARROSSEL ---

});