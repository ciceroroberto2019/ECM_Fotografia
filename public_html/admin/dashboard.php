<?php
// 1. O SEGURANÇA: Verifica se o usuário está logado
require_once 'auth-check.php';

// 2. O CABEÇALHO: Inclui o layout e o menu
include 'admin-header.php';
?>

<h1>Dashboard</h1>
<p>Olá, <strong><?php echo $_SESSION['admin_usuario']; ?></strong>! Bem-vindo ao seu painel de controle.</p>
<p>Aqui você poderá gerenciar o conteúdo do seu site, cadastrar clientes e criar galerias para seleção.</p>

<?php
// 4. O RODAPÉ: Fecha o HTML
include 'admin-footer.php';
?>