<?php
// reseta-admin.php

// --- 1. CONFIGURAÇÃO ---
// Defina o usuário que você quer resetar e a NOVA senha.
$usuario_admin_para_resetar = 'admin'; 
$nova_senha = 'Plano@admin123'; // <-- MUDE ISSO AQUI

// --- FIM DA CONFIGURAÇÃO ---

echo "<h1>Script de Reset de Senha Admin</h1>";

// 2. Carregar os arquivos de conexão
require_once 'config.php';
require_once 'includes/db.php';

try {
    // 3. Criptografar a NOVA senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    echo "<p>Conectando ao banco de dados...</p>";
    
    // 4. Pegar a conexão do banco
    $pdo = getDb();

    // 5. Preparar e executar o UPDATE
    // Esta é a linha que muda: UPDATE em vez de INSERT
    $sql = "UPDATE admin SET senha = ? WHERE usuario = ?";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([$senha_hash, $usuario_admin_para_resetar]);
    
    // 6. Verificar se a linha foi afetada
    if ($stmt->rowCount() > 0) {
        echo "<h2>SUCESSO!</h2>";
        echo "<p>A senha do usuário <strong>'$usuario_admin_para_resetar'</strong> foi atualizada.</p>";
        echo "<p>Sua nova senha é: <strong>$nova_senha</strong></p>";
        echo "<p style='color:red; font-weight:bold;'>POR FAVOR, DELETE ESTE ARQUIVO (reseta-admin.php) DO SEU SERVIDOR AGORA!</p>";
    } else {
        echo "<h2>ERRO!</h2>";
        echo "<p>Não foi encontrado nenhum usuário com o nome Example 1 <strong>'$usuario_admin_para_resetar'</strong>.</p>";
        echo "<p>Verifique o nome de usuário na linha 6 deste script.</p>";
    }

} catch (PDOException $e) {
    echo "<h2>ERRO DE CONEXÃO!</h2>";
    echo "<p>Não foi possível conectar ao banco de dados.</p>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
}
?>