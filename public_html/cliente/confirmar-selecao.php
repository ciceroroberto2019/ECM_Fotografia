<?php
// cliente/confirmar-selecao.php (VERSÃO FINAL COM ANEXO .TXT)

// 1. Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 2. Carrega os arquivos do PHPMailer (Baseado na sua estrutura 'image_9a771d.png')
require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';

// 3. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

// Função slugify (necessária para o nome do anexo .txt)
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

// Define o cabeçalho como JSON
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
    // 4. Trava o projeto
    $stmt = $pdo->prepare("UPDATE projetos_clientes SET status = 'Finalizado' WHERE id = ? AND id_cliente = ?");
    $stmt->execute([$id_projeto, $id_cliente]);

    // Ponto de Atenção (Regra 6): O email só é enviado se a trava for BEM SUCEDIDA (rowCount > 0)
    if ($stmt->rowCount() > 0) {
        
        // --- 5. LÓGICA DE EMAIL (COM ANEXO .TXT) ---
        
        // 5a. Buscar dados
        $stmt_projeto = $pdo->prepare("SELECT titulo FROM projetos_clientes WHERE id = ?");
        $stmt_projeto->execute([$id_projeto]);
        $projeto = $stmt_projeto->fetch();
        $nome_projeto = $projeto ? $projeto['titulo'] : 'Projeto ID ' . $id_projeto;
        $nome_arquivo_base = "selecao_" . slugify($nome_projeto); // Ex: selecao_batizado-ana

        $stmt_fotos = $pdo->prepare("
            SELECT f.nome_arquivo
            FROM selecoes_clientes s
            JOIN fotos_clientes f ON s.id_foto = f.id
            WHERE f.id_projeto = ? AND s.id_cliente = ?
            ORDER BY f.nome_arquivo ASC
        ");
        $stmt_fotos->execute([$id_projeto, $id_cliente]);
        $fotos_selecionadas = $stmt_fotos->fetchAll(PDO::FETCH_COLUMN, 0);
        $total_fotos = count($fotos_selecionadas);

        // 5b. Montar o conteúdo (para o corpo E para o anexo)
        $lista_arquivos_txt = implode("\r\n", $fotos_selecionadas);

        // 5c. Montar o corpo do email (O que você já tem)
        $corpo_email = "O cliente '$nome_cliente' finalizou a seleção do álbum.\n\n";
        $corpo_email .= "Projeto: $nome_projeto\n";
        $corpo_email .= "Total de fotos selecionadas: $total_fotos\n";
        $corpo_email .= "--------------------------------------------------\n";
        $corpo_email .= "LISTA DE ARQUIVOS (para o álbum):\n";
        $corpo_email .= "--------------------------------------------------\n";
        $corpo_email .= $lista_arquivos_txt;
        $corpo_email .= "\n--------------------------------------------------\n";
        $corpo_email .= "Acesse o painel de admin para confirmar.\n";
        $corpo_email .= "(Uma cópia desta lista também foi enviada em anexo como .txt)";

        // 5d. Gerar o conteúdo do anexo .TXT (é o mesmo da lista)
        $conteudo_txt_anexo = "Seleção de Fotos: " . $nome_projeto . "\r\n";
        $conteudo_txt_anexo .= "Cliente: " . $nome_cliente . "\r\n";
        $conteudo_txt_anexo .= "Total de Fotos: " . $total_fotos . "\r\n";
        $conteudo_txt_anexo .= "--------------------------------------------------\r\n\r\n";
        $conteudo_txt_anexo .= $lista_arquivos_txt;

        // 5e. Configurar e Enviar o Email
        $mail = new PHPMailer(true); 
        try {
            // Configurações do Servidor (Hostinger)
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'notificacoes@ecmfotografia.com.br';
            $mail->Password   = 'Plano@notificacoes1602'; // <-- PREENCHA SUA SENHA AQUI
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
            $mail->Port       = 465;
            
            // Remetente e Destinatário
            $mail->setFrom('notificacoes@ecmfotografia.com.br', 'Sistema ECM Fotografia');
            $mail->addAddress('cicero.roberto.rufino@gmail.com', 'Admin ECM'); 
            
            // Conteúdo
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(false); // Mantém como texto puro
            $mail->Subject = "Seleção Finalizada: $nome_projeto (Cliente: $nome_cliente)";
            $mail->Body    = $corpo_email; // O corpo do email (como estava)

            // *** A MELHORIA: Adicionar o Anexo .TXT ***
            $mail->addStringAttachment($conteudo_txt_anexo, $nome_arquivo_base . ".txt");

            $mail->send();
            
        } catch (Exception $e) {
            error_log("PHPMailer Erro: {$mail->ErrorInfo}");
        }
        
        // --- FIM DA LÓGICA DE EMAIL ---

        // 6. Retornar sucesso
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Seleção finalizada com sucesso!']);
        
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Não foi possível encontrar o projeto ou ele já estava finalizado.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro de banco de dados: ' . $e->getMessage()]);
}
?>