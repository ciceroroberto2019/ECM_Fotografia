<?php
// admin/exportar-selecao.php

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();

// 2. VERIFICAR O ID DO PROJETO (da URL)
if (!isset($_GET['id_projeto']) || !is_numeric($_GET['id_projeto'])) {
    die("Erro: ID de projeto inválido.");
}
$id_projeto = $_GET['id_projeto'];

// 3. Buscar o nome do projeto (para o nome do arquivo)
$stmt_projeto = $pdo->prepare("SELECT titulo FROM projetos_clientes WHERE id = ?");
$stmt_projeto->execute([$id_projeto]);
$projeto = $stmt_projeto->fetch();
$nome_arquivo_zip = $projeto ? preg_replace('/[^a-z0-9]+/', '-', strtolower($projeto['titulo'])) : 'selecao';

// 4. Buscar APENAS OS NOMES dos arquivos selecionados
$stmt_fotos = $pdo->prepare("
    SELECT f.nome_arquivo
    FROM selecoes_clientes s
    JOIN fotos_clientes f ON s.id_foto = f.id
    WHERE f.id_projeto = ?
    ORDER BY f.nome_arquivo ASC
");
$stmt_fotos->execute([$id_projeto]);
$nomes_arquivos = $stmt_fotos->fetchAll(PDO::FETCH_COLUMN, 0);

// 5. Definir os Cabeçalhos (Headers) para forçar o download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="selecao_' . $nome_arquivo_zip . '.txt"');

// 6. Imprimir (echo) cada nome de arquivo, um por linha
foreach ($nomes_arquivos as $nome) {
    echo $nome . "\r\n"; // \r\n é a quebra de linha universal (Windows/Mac/Linux)
}

// 7. Finaliza o script
exit;
?>