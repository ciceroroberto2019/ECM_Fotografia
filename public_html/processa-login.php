<?php
// processa-login.php

// 1. Iniciar a Sessão
// É OBRIGATÓRIO iniciar a sessão antes de qualquer output.
session_start();

// 2. Carregar os arquivos de conexão
require_once 'config.php';
require_once 'includes/db.php';

// 3. Verificar se os dados foram enviados (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $usuario_login = $_POST['usuario']; // Pode ser o 'usuario' do admin ou o 'email' do cliente
    $senha_digitada = $_POST['senha'];

    try {
        $pdo = getDb();

        // --- 4. TENTATIVA DE LOGIN COMO ADMIN ---
        $stmt = $pdo->prepare("SELECT id, usuario, senha FROM admin WHERE usuario = ?");
        $stmt->execute([$usuario_login]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($senha_digitada, $admin['senha'])) {
            // SUCESSO COMO ADMIN!
            // Limpa a sessão antiga (segurança)
            session_regenerate_id(true); 
            
            // Armazena os dados do admin na sessão
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            
            // Redireciona para o Painel de Controle (que criaremos a seguir)
            header("Location: " . BASE_URL . "admin/dashboard.php");
            exit;
        }
        
        // --- 5. TENTATIVA DE LOGIN COMO CLIENTE ---
        // (Se não for admin, verifica se é cliente)
        $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM clientes WHERE email = ?");
        $stmt->execute([$usuario_login]); // Clientes logam com email
        $cliente = $stmt->fetch();

        if ($cliente && password_verify($senha_digitada, $cliente['senha'])) {
            // SUCESSO COMO CLIENTE!
            session_regenerate_id(true);
            
            // Armazena os dados do cliente na sessão
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nome'] = $cliente['nome'];
            
            // Redireciona para a Área do Cliente (que criaremos a seguir)
            header("Location: " . BASE_URL . "cliente/area-cliente.php");
            exit;
        }

        // --- 6. FALHA NO LOGIN ---
        // (Se não for admin E nem cliente)
        header("Location: " . BASE_URL . "login?erro=1");
        exit;

    } catch (PDOException $e) {
        // Erro de banco de dados
        die("Erro ao processar login: " . $e->getMessage());
    }

} else {
    // Se alguém tentar acessar o arquivo diretamente
    header("Location: " . BASE_URL . "login");
    exit;
}
?>