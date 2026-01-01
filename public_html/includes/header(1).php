<?php
// includes/header.php
// A variável $titulo_da_pagina e $meta_descricao já estão definidas no index.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $titulo_da_pagina; ?></title>
    <meta name="description" content="<?php echo $meta_descricao; ?>">
    
    <link rel="icon" href="<?php echo BASE_URL; ?>imagens/favicon.png" type="image/png">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=<?php echo SITE_VERSION; ?>">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>js/lib/magnific-popup.css?v=<?php echo SITE_VERSION; ?>">

    <script>
        (function() {
            try {
                const temaSalvo = localStorage.getItem('theme');
                if (temaSalvo === 'dark') {
                    // Aplica a classe no <html> (raiz do documento)
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) {}
        })();
    </script>
</head>

<body>

    <header>
        <a href="<?php echo BASE_URL; ?>" class="logo-link">
            <img src="<?php echo BASE_URL; ?>imagens/ecm-logo003.png" alt="Logo ECM Fotografia" class="logo-img logo-claro">
            <img src="<?php echo BASE_URL; ?>imagens/ecm-logo004.png" alt="Logo ECM Fotografia" class="logo-img logo-escuro">
        </a>
        
        <button id="menu-toggle" aria-label="Abrir Menu">
            <span class="hamburger-linha"></span>
            <span class="hamburger-linha"></span>
            <span class="hamburger-linha"></span>
        </button>

        <nav id="nav-menu">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>sobre">Sobre</a></li>
                <li><a href="<?php echo BASE_URL; ?>portfolio">Portfólio</a></li>
                <li><a href="<?php echo BASE_URL; ?>contato">Contato</a></li>
                <li><a href="<?php echo BASE_URL; ?>login">Login</a></li>
                <li class="tema-toggle-item">
                    <button id="theme-toggle" aria-label="Mudar Tema">
                        <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </button>
                </li>
            </ul>
        </nav>
    </header>

    <main>