<?php
// admin/upload-handler.php

// 1. SEGURANÇA E CONEXÃO
require_once 'auth-check.php'; // Garante que SÓ o admin pode fazer upload
require_once '../includes/db.php';

$pdo = getDb();

// 2. VERIFICAR DADOS DO POST
if (!isset($_POST['id_projeto']) || !is_numeric($_POST['id_projeto']) || empty($_FILES['file'])) {
    // Se dados vitais faltarem, retorna um erro
    header("HTTP/1.1 400 Bad Request");
    echo "Erro: ID do projeto ou arquivo ausente.";
    exit;
}

$id_projeto = $_POST['id_projeto'];
$imagem = $_FILES['file']; // Dropzone envia como 'file'

// 3. BUSCAR A PASTA DO PROJETO
$stmt = $pdo->prepare("SELECT caminho_pasta FROM projetos_clientes WHERE id = ?");
$stmt->execute([$id_projeto]);
$projeto = $stmt->fetch();

if (!$projeto || empty($projeto['caminho_pasta'])) {
    header("HTTP/1.1 404 Not Found");
    echo "Erro: Projeto ou pasta de destino não encontrados.";
    exit;
}

// Caminho relativo à RAIZ DO SITE (ex: 'uploads/clientes/1-casamento-ana...')
$caminho_pasta_db = $projeto['caminho_pasta'];
// Caminho FÍSICO (a partir deste script em /admin/)
$caminho_pasta_fisica = '../' . $caminho_pasta_db;

// 4. PROCESSAR O ARQUIVO
$nome_arquivo_original = $imagem['name'];
$extensao = pathinfo($nome_arquivo_original, PATHINFO_EXTENSION);
// Cria um nome único para evitar sobreposições
$nome_arquivo_unico = uniqid('foto_', true) . '.' . $extensao;
$caminho_destino_fisico = $caminho_pasta_fisica . '/' . $nome_arquivo_unico;

// 5. MOVER O ARQUIVO
if (move_uploaded_file($imagem['tmp_name'], $caminho_destino_fisico)) {
    // 6. SALVAR NO BANCO
    try {
        $stmt_insert = $pdo->prepare("INSERT INTO fotos_clientes (id_projeto, nome_arquivo, caminho_arquivo) VALUES (?, ?, ?)");
        
        // Salva o caminho RELATIVO À RAIZ no banco
        $caminho_arquivo_db = $caminho_pasta_db . '/' . $nome_arquivo_unico;
        
        $stmt_insert->execute([$id_projeto, $nome_arquivo_original, $caminho_arquivo_db]);
        
        // Sucesso!
        header("HTTP/1.1 200 OK");
        echo "Upload com sucesso: " . $nome_arquivo_original;
        exit;
        
    } catch (PDOException $e) {
        // Se falhar o banco, deleta o arquivo que acabamos de salvar
        unlink($caminho_destino_fisico);
        header("HTTP/1.1 500 Internal Server Error");
        echo "Erro de banco de dados: " . $e->getMessage();
        exit;
    }
}

// 7. FALHA NO UPLOAD
header("HTTP/1.1 500 Internal Server Error");
echo "Erro ao mover o arquivo para o servidor.";
exit;
?>