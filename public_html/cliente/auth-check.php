<?php
// cliente/auth-check.php
// ESTE SCRIPT SERÁ O "SEGURANÇA" DA ÁREA DO CLIENTE

// Inicia a sessão para checar se o cliente está logado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Carrega o config SÓ para pegar a BASE_URL
require_once '../config.php';

// A VERIFICAÇÃO:
// Se a variável de sessão 'cliente_id' NÃO EXISTIR...
if (!isset($_SESSION['cliente_id'])) {
    
    // Usuário não está logado. Redireciona para o login.
    header("Location: " . BASE_URL . "login?erro=restrito_cliente");
    exit; // Para o script
}

// Se o script continuar, significa que o cliente ESTÁ logado.
?>