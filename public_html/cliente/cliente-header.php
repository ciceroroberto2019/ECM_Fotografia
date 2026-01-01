<?php
// cliente/cliente-header.php
require_once '../config.php'; 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Área | ECM Fotografia</title>
    
    <link rel="stylesheet" href="cliente-style.css?v=<?php echo SITE_VERSION; ?>">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="../js/magnific-popup/magnific-popup.css?v=<?php echo SITE_VERSION; ?>">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <script src="../js/magnific-popup/jquery.magnific-popup.min.js?v=<?php echo SITE_VERSION; ?>"></script>

</head>
<body>
    <header class="cliente-header">
        <a href="<?php echo BASE_URL; ?>" class="logo">ECM Fotografia</a>
        <div class="user-info">
            Olá, <strong><?php echo htmlspecialchars($_SESSION['cliente_nome']); ?></strong>!
        
            <a href="<?php echo BASE_URL; ?>ajuda" target="_blank" class="link-ajuda">
                Ajuda
            </a> | 
            <a href="../logout.php" class="link-sair">Sair</a>
        </div>
    </header>
    <main class="cliente-main">