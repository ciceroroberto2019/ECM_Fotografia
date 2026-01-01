<?php
// admin/auth-check.php
// ESTE SCRIPT SERÁ O "SEGURANÇA" DO PAINEL

// Inicia a sessão para checar se o usuário está logado
session_start();

// Carrega o config SÓ para pegar a BASE_URL
// (../ sobe um nível, da pasta /admin/ para a raiz)
require_once '../config.php';

// A VERIFICAÇÃO:
// Se a variável de sessão 'admin_id' NÃO EXISTIR...
if (!isset($_SESSION['admin_id'])) {
    
    // Usuário não está logado. Redireciona para o login.
    header("Location: " . BASE_URL . "login?erro=restrito");
    exit; // Para o script
}

// Se o script continuar, significa que o usuário ESTÁ logado.
?>