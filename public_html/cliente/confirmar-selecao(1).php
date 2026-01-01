<?php
// cliente/confirmar-selecao.php

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

// Define o cabeçalho como JSON
header('Content-Type: application/json');

$id_cliente = $_SESSION['cliente_id'];

// Pega o ID do projeto enviado pelo JavaScript (AJAX)
// file_get_contents é necessário para ler o JSON "raw"
$dados = json_decode(file_get_contents('php://input'), true);
$id_projeto = $dados['id_projeto'];

if (empty($id_projeto)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do projeto ausente.']);
    exit;
}

$pdo = getDb();

try {
    // 2. A Trava: Atualiza o status do projeto para 'Finalizado'
    $stmt = $pdo->prepare("UPDATE projetos_clientes SET status = 'Finalizado' WHERE id = ? AND id_cliente = ?");
    $stmt->execute([$id_projeto, $id_cliente]);

    // Verifica se a linha foi realmente atualizada
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Seleção finalizada com sucesso!']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Não foi possível encontrar o projeto ou ele já estava finalizado.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro de banco de dados: ' . $e->getMessage()]);
}
?>