<?php
// admin/exportar-bat.php

/**
 * Script de Geração de Lote (.bat) para Automação de Cópia
 * * Este script gera um arquivo .bat que, quando executado em um
 * diretório no Windows, cria uma subpasta "ALBUM_SELECIONADO"
 * e copia os arquivos listados para dentro dela.
 */

// 1. O SEGURANÇA E CONEXÃO
// Garante que apenas o admin logado possa acessar
require_once 'auth-check.php';
require_once '../includes/db.php';

// 2. FUNÇÃO SLUGIFY (Regra 10: Boa prática)
// (Necessária para criar um nome de arquivo seguro para o .bat)
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

$pdo = getDb();

// 3. VALIDAÇÃO DO PROJETO
if (!isset($_GET['id_projeto']) || !is_numeric($_GET['id_projeto'])) {
    die("Erro: ID de projeto inválido.");
}
$id_projeto = $_GET['id_projeto'];

// 4. BUSCAR DADOS DO PROJETO E NOMES DOS ARQUIVOS
try {
    // 4a. Buscar o nome do projeto (para o título do script)
    $stmt_projeto = $pdo->prepare("SELECT titulo FROM projetos_clientes WHERE id = ?");
    $stmt_projeto->execute([$id_projeto]);
    $projeto = $stmt_projeto->fetch();
    $nome_projeto = $projeto ? $projeto['titulo'] : 'Projeto Desconhecido';

    // 4b. Buscar a lista de nomes de arquivos selecionados
    $stmt_fotos = $pdo->prepare("
        SELECT f.nome_arquivo
        FROM selecoes_clientes s
        JOIN fotos_clientes f ON s.id_foto = f.id
        WHERE f.id_projeto = ?
        ORDER BY f.nome_arquivo ASC
    ");
    $stmt_fotos->execute([$id_projeto]);
    $nomes_arquivos = $stmt_fotos->fetchAll(PDO::FETCH_COLUMN, 0);

} catch (PDOException $e) {
    die("Erro de banco de dados: " . $e->getMessage());
}

if (empty($nomes_arquivos)) {
    die("Nenhuma foto selecionada encontrada para este projeto.");
}

// 5. CONSTRUIR O CONTEÚDO DO ARQUIVO .BAT
$nome_pasta_destino = "ALBUM_SELECIONADO";
$total_fotos = count($nomes_arquivos);

// @echo off: desliga a repetição de comandos
$conteudo_bat = "@echo off\r\n";
$conteudo_bat .= "TITLE Script de Copia - " . $nome_projeto . "\r\n";
$conteudo_bat .= "echo.\r\n";
$conteudo_bat .= "echo =======================================\r\n";
$conteudo_bat .= "echo    Script de Automacao ECM Fotografia\r\n";
$conteudo_bat .= "echo    Projeto: " . $nome_projeto . "\r\n";
$conteudo_bat .= "echo =======================================\r\n";
$conteudo_bat .= "echo.\r\n";
$conteudo_bat .= "echo Criando pasta \"" . $nome_pasta_destino . "\"...\r\n";
// mkdir: cria o diretório
$conteudo_bat .= "mkdir \"" . $nome_pasta_destino . "\"\r\n";
$conteudo_bat .= "echo.\r\n";
$conteudo_bat .= "echo Copiando " . $total_fotos . " arquivos...\r\n";

// Loop para criar os comandos 'copy'
foreach ($nomes_arquivos as $nome) {
    // Ponto de Atenção (Regra 6): Usamos aspas ("") para garantir
    // que nomes de arquivo com espaços (ex: "IMG 1234.jpg") funcionem.
    $conteudo_bat .= "copy \"" . $nome . "\" \"" . $nome_pasta_destino . "\\\"\r\n";
}

$conteudo_bat .= "echo.\r\n";
$conteudo_bat .= "echo =======================================\r\n";
$conteudo_bat .= "echo    CONCLUIDO! As " . $total_fotos . " fotos estao na pasta " . $nome_pasta_destino . ".\r\n";
$conteudo_bat .= "echo =======================================\r\n";
$conteudo_bat .= "echo.\r\n";
$conteudo_bat .= "pause\r\n"; // Mantém o prompt aberto para o usuário ler

// 6. FORÇAR O DOWNLOAD
$nome_arquivo_final = 'COPIAR_ALBUM_' . slugify($nome_projeto) . '.bat';

// Headers para forçar o download
header('Content-Type: text/plain'); // .bat é essencialmente texto
header('Content-Disposition: attachment; filename="' . $nome_arquivo_final . '"');
header('Content-Length: ' . strlen($conteudo_bat)); // Importante
header('Pragma: no-cache');
header('Expires: 0');

// Envia o conteúdo
echo $conteudo_bat;
exit;
?>
