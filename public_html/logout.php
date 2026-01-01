<?php
// logout.php

// 1. Inicia a sessão para poder acessá-la
session_start();

// 2. Limpa todas as variáveis de sessão
$_SESSION = array();

// 3. Destrói a sessão do servidor
session_destroy();

// 4. Carrega o config SÓ para pegar a BASE_URL
require_once 'config.php';

// 5. Redireciona para a página inicial
header("Location: " . BASE_URL);
exit;
?>