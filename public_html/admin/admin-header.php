<?php
// admin/admin-header.php
require_once '../config.php'; 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin | ECM Fotografia</title>
    
    <link rel="stylesheet" href="admin-style.css?v=<?php echo SITE_VERSION; ?>">
    
    <link rel="stylesheet" href="../js/lib/magnific-popup.css?v=<?php echo SITE_VERSION; ?>">
    
    <script src="../js/tinymce/tinymce.min.js?v=<?php echo SITE_VERSION; ?>" referrerpolicy="origin"></script>
    
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <a href="dashboard.php" class="admin-logo-link">
                <img src="<?php echo BASE_URL; ?>imagens/ecm-logo004.png" alt="ECM Admin Logo" class="admin-logo-img">
            </a>
            
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    
                    <hr class="menu-divisor">
                    <li style="padding: 5px 25px; font-size: 0.8rem; text-transform: uppercase; color: #7f8c8d;">Site</li>
                    <li><a href="gerenciar-hero.php">Carrossel da Home</a></li>
                    <li><a href="gerenciar-paginas.php">Páginas (Ex: Sobre)</a></li>
                    
                    <hr class="menu-divisor">
                    <li style="padding: 5px 25px; font-size: 0.8rem; text-transform: uppercase; color: #7f8c8d;">Portfólio</li>
                    <li><a href="gerenciar-categorias.php">Categorias</a></li>
                    <li><a href="gerenciar-portfolio.php">Fotos</a></li>
                    
                    <hr class="menu-divisor">
                    <li style="padding: 5px 25px; font-size: 0.8rem; text-transform: uppercase; color: #7f8c8d;">Blog</li>
                    <li><a href="gerenciar-blog-categorias.php">Categorias Blog</a></li>
                    <li><a href="gerenciar-blog-posts.php">Artigos / Posts</a></li>

                    <hr class="menu-divisor">
                    <li style="padding: 5px 25px; font-size: 0.8rem; text-transform: uppercase; color: #7f8c8d;">Clientes</li>
                    <li><a href="gerenciar-clientes.php">Clientes (Galeria)</a></li>
                    
                    <hr class="menu-divisor">
                    <li><a href="../logout.php" class="logout-link">Sair</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main">