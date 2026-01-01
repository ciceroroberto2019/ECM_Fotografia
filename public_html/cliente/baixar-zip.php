<?php
// cliente/baixar-zip.php (VERSÃO CORRIGIDA)

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

// 2. A FUNÇÃO FALTANDO (CORREÇÃO DO ERRO FATAL)
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

// 3. AUMENTAR LIMITES
set_time_limit(300); // 5 minutos de limite de execução
ini_set('memory_limit', '512M');

$pdo = getDb();
$id_cliente = $_SESSION['cliente_id'];

// 4. VERIFICAR O ID DO PROJETO
if (!isset($_GET['id_projeto']) || !is_numeric($_GET['id_projeto'])) {
    die("Erro: ID de projeto inválido.");
}
$id_projeto = $_GET['id_projeto'];

// 5. VERIFICAR SE O CLIENTE É O DONO DO PROJETO
$stmt_projeto = $pdo->prepare("SELECT titulo FROM projetos_clientes WHERE id = ? AND id_cliente = ?");
$stmt_projeto->execute([$id_projeto, $id_cliente]);
$projeto = $stmt_projeto->fetch();

if (!$projeto) {
    die("Erro: Acesso negado. Este projeto não pertence a você.");
}

// 6. BUSCAR AS FOTOS NO BANCO
// Como o pedido é 'Baixar Todas', buscamos todas as fotos
$stmt_fotos = $pdo->prepare("
    SELECT nome_arquivo, caminho_arquivo 
    FROM fotos_clientes 
    WHERE id_projeto = ?
");
$stmt_fotos->execute([$id_projeto]);
$fotos = $stmt_fotos->fetchAll();

if (empty($fotos)) {
    die("Nenhuma foto encontrada para este projeto.");
}

// 7. CRIAR O NOME DO ARQUIVO ZIP
// Agora que slugify está definido, podemos usá-lo com segurança
$nome_base = slugify($projeto['titulo']); 
$nome_arquivo_zip = 'ecmfotografia_' . $nome_base . '.zip';
$caminho_arquivo_zip = sys_get_temp_dir() . '/' . $nome_arquivo_zip;

// 8. INICIAR O ZIP
$zip = new ZipArchive();
if ($zip->open($caminho_arquivo_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Erro: Não foi possível criar o arquivo ZIP no servidor.");
}

// 9. ADICIONAR FOTOS AO ZIP
foreach ($fotos as $foto) {
    // Caminho físico da foto no servidor (A correção do caminho)
    // Subir um nível (do /cliente/ para a raiz)
    $caminho_fisico = '../' . $foto['caminho_arquivo']; 
    
    if (file_exists($caminho_fisico)) {
        // Adiciona o arquivo ao ZIP usando o NOME ORIGINAL (ex: img01.jpg)
        $zip->addFile($caminho_fisico, $foto['nome_arquivo']);
    }
    // Caso contrário, a foto é ignorada (pode ser um upload falho)
}

$zip->close();

// 10. FORÇAR O DOWNLOAD NO NAVEGADOR
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $nome_arquivo_zip . '"');
header('Content-Length: ' . filesize($caminho_arquivo_zip));
header('Pragma: no-cache');
header('Expires: 0');

// Limpa o buffer de saída (Obrigatório para downloads grandes)
ob_clean();
flush();

// Envia o arquivo
readfile($caminho_arquivo_zip);

// 11. LIMPAR O ARQUIVO TEMPORÁRIO
unlink($caminho_arquivo_zip);
exit;
?>