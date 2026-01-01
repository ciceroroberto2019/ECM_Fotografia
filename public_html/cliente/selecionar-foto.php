<?php
// cliente/selecionar-foto.php
// (Handler para o AJAX de seleção de fotos)

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

// Define o cabeçalho como JSON
header('Content-Type: application/json');

// Pega os dados do POST (enviados pelo JavaScript)
$id_foto = $_POST['id_foto'];
$id_projeto = $_POST['id_projeto'];
$id_cliente = $_SESSION['cliente_id']; // Pega o cliente logado

// 2. Validação
if (empty($id_foto) || empty($id_projeto) || empty($id_cliente)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos.']);
    exit;
}

$pdo = getDb();

try {
    // 3. Verifica se a foto JÁ está selecionada
    $stmt_check = $pdo->prepare("SELECT id FROM selecoes_clientes WHERE id_cliente = ? AND id_foto = ?");
    $stmt_check->execute([$id_cliente, $id_foto]);
    $selecao_existente = $stmt_check->fetch();

    if ($selecao_existente) {
        // --- JÁ SELECIONADA: Vamos REMOVER ---
        $stmt_delete = $pdo->prepare("DELETE FROM selecoes_clientes WHERE id = ?");
        $stmt_delete->execute([$selecao_existente['id']]);
        
        echo json_encode(['status' => 'sucesso', 'acao' => 'removida', 'id_foto' => $id_foto]);
    
    } else {
        // --- NÃO SELECIONADA: Vamos ADICIONAR ---
        $stmt_insert = $pdo->prepare("INSERT INTO selecoes_clientes (id_cliente, id_foto) VALUES (?, ?)");
        $stmt_insert->execute([$id_cliente, $id_foto]);
        
        echo json_encode(['status' => 'sucesso', 'acao' => 'selecionada', 'id_foto' => $id_foto]);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro de banco de dados: ' . $e->getMessage()]);
}
?>