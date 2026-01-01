<?php
// cliente/confirmar-selecao.php (VERSÃO v3.0 COM MÚLTIPLOS ANEXOS)

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';
require_once '../includes/email-helper.php'; // Carrega o novo helper

// Função slugify (para o nome do arquivo)
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

header('Content-Type: application/json');

$id_cliente = $_SESSION['cliente_id'];
$nome_cliente = $_SESSION['cliente_nome']; 
$dados = json_decode(file_get_contents('php://input'), true);
$id_projeto = $dados['id_projeto'];

if (empty($id_projeto)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do projeto ausente.']);
    exit;
}

$pdo = getDb();

try {
    // 2. Trava o projeto
    $stmt = $pdo->prepare("UPDATE projetos_clientes SET status = 'Finalizado' WHERE id = ? AND id_cliente = ?");
    $stmt->execute([$id_projeto, $id_cliente]);

    if ($stmt->rowCount() > 0) {
        
        // --- 3. LÓGICA DE EMAIL (A MELHORIA) ---
        
        // 3a. Buscar dados
        $stmt_projeto = $pdo->prepare("SELECT titulo FROM projetos_clientes WHERE id = ?");
        $stmt_projeto->execute([$id_projeto]);
        $projeto = $stmt_projeto->fetch();
        $nome_projeto = $projeto ? $projeto['titulo'] : 'Projeto ID ' . $id_projeto;
        $nome_arquivo_base = "selecao_" . slugify($nome_projeto); 
        
        $stmt_fotos = $pdo->prepare("
            SELECT f.nome_arquivo
            FROM selecoes_clientes s
            JOIN fotos_clientes f ON s.id_foto = f.id
            WHERE f.id_projeto = ? AND s.id_cliente = ?
            ORDER BY f.nome_arquivo ASC
        ");
        $stmt_fotos->execute([$id_projeto, $id_cliente]);
        $nomes_arquivos = $stmt_fotos->fetchAll(PDO::FETCH_COLUMN, 0);
        $total_fotos = count($nomes_arquivos);

        // 3b. Gerar Anexo 1 (o .txt)
        $conteudo_txt = "Seleção de Fotos: " . $nome_projeto . "\r\n";
        $conteudo_txt .= "Cliente: " . $nome_cliente . "\r\n";
        $conteudo_txt .= "Total de Fotos: " . $total_fotos . "\r\n";
        $conteudo_txt .= "--------------------------------------------------\r\n\r\n";
        $conteudo_txt .= implode("\r\n", $nomes_arquivos);

        // 3c. Gerar Anexo 2 (o .bat)
        $conteudo_bat = "@echo off\r\n";
        $conteudo_bat .= "TITLE Script de Copia - " . $nome_projeto . "\r\n";
        $conteudo_bat .= "echo.\r\n";
        $conteudo_bat .= "echo =======================================\r\n";
        $conteudo_bat .= "echo    Script de Automacao ECM Fotografia\r\n";
        $conteudo_bat .= "echo    Projeto: " . $nome_projeto . "\r\n";
        $conteudo_bat .= "echo =======================================\r\n";
        $conteudo_bat .= "echo.\r\n";
        $conteudo_bat .= "echo Criando pasta \"ALBUM_SELECIONADO\"...\r\n";
        $conteudo_bat .= "mkdir \"ALBUM_SELECIONADO\"\r\n";
        $conteudo_bat .= "echo.\r\n";
        $conteudo_bat .= "echo Copiando " . $total_fotos . " arquivos...\r\n";
        foreach ($nomes_arquivos as $nome) {
            $conteudo_bat .= "copy \"" . $nome . "\" \"ALBUM_SELECIONADO\\\"\r\n";
        }
        $conteudo_bat .= "echo.\r\n";
        $conteudo_bat .= "echo =======================================\r\n";
        $conteudo_bat .= "echo    CONCLUIDO!\r\n";
        $conteudo_bat .= "echo =======================================\r\n";
        $conteudo_bat .= "echo.\r\n";
        $conteudo_bat .= "pause\r\n";

        // 3d. Montar o array de anexos
        $anexos_para_enviar = [
            ['conteudo' => $conteudo_txt, 'nome' => $nome_arquivo_base . ".txt"],
            ['conteudo' => $conteudo_bat, 'nome' => $nome_arquivo_base . "_COPIAR.bat"]
        ];

        // 3e. Montar o corpo do email
        $assunto_email = "Seleção Finalizada: " . $nome_cliente . " (" . $nome_projeto . ")";
        $corpo_html_email = "<h1>Seleção Finalizada!</h1>"
                          . "<p>O cliente <strong>" . $nome_cliente . "</strong> finalizou a seleção de fotos para o projeto <strong>" . $nome_projeto . "</strong>.</p>"
                          . "<p>Total de fotos selecionadas: <strong>" . $total_fotos . "</strong>.</p>"
                          . "<p>Os arquivos de automação (.txt e .bat) estão em anexo.</p>";

        // 3f. Enviar o email (Regra 8: Exemplo de uso real)
        enviarEmail(ADMIN_EMAIL_RECEBE, 'Admin ECM', $assunto_email, $corpo_html_email, $anexos_para_enviar);
        
        // --- FIM DA LÓGICA DE EMAIL ---

        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Seleção finalizada com sucesso!']);
        
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Não foi possível encontrar o projeto ou ele já estava finalizado.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro de banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Captura erros do PHPMailer (Regra 6: Análise de Risco)
    // O projeto foi travado, mas o email falhou. O cliente vê sucesso.
    // O admin não foi notificado, mas o 'error_log' no helper foi.
    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Seleção finalizada, mas falha ao enviar o email de notificação.']);
}
?>