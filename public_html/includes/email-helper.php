<?php
// includes/email-helper.php
/**
 * Helper de Email centralizado usando PHPMailer.
 * Isso segue a Regra 10 (Soluções Modernas), pois abstrai a lógica de envio.
 */

// 1. Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 2. Carrega os arquivos do PHPMailer (presume que estão em /includes/lib/)
// (Ajuste o caminho se você colocou a pasta PHPMailer em /includes/ e não /includes/lib/)
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

// 3. Carrega o config para as senhas
require_once __DIR__ . '/../config.php';

/**
 * Função global para enviar emails com múltiplos anexos
 *
 * @param string $para_email Email do destinatário
 * @param string $para_nome Nome do destinatário
 * @param string $assunto Assunto do email
 * @param string $corpo_html Corpo em HTML
 * @param array $anexos Array de anexos. Formato: [['conteudo' => '...', 'nome' => 'arquivo1.txt'], ...]
 * @return bool True se enviado, false se falhar
 */
function enviarEmail($para_email, $para_nome, $assunto, $corpo_html, $anexos = []) {
    
    $mail = new PHPMailer(true); // Habilita exceções

    try {
        // --- 4. Configurações do Servidor (Puxadas do config.php) ---
        // Ponto de Atenção (Regra 6): Se o config.php não tiver estas constantes,
        // este script falhará.
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = MAIL_SECURE; // Ex: PHPMailer::ENCRYPTION_SMTPS
        $mail->Port       = MAIL_PORT;   // Ex: 465
        $mail->CharSet    = 'UTF-8';

        // Remetente (Quem envia)
        $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
        
        // Destinatário (Quem recebe)
        $mail->addAddress($para_email, $para_nome);

        // --- 5. Conteúdo ---
        $mail->isHTML(true); // Envia como HTML
        $mail->Subject = $assunto;
        $mail->Body    = $corpo_html;
        $mail->AltBody = strip_tags($corpo_html); // Versão texto puro

        // --- 6. Lógica de Anexos (A Melhoria) ---
        if (!empty($anexos)) {
            foreach ($anexos as $anexo) {
                if (isset($anexo['conteudo']) && isset($anexo['nome'])) {
                    // addStringAttachment é uma boa prática (Regra 4)
                    // pois não cria arquivos temporários no disco.
                    $mail->addStringAttachment($anexo['conteudo'], $anexo['nome']);
                }
            }
        }

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Regra 6: Em caso de falha, registra o erro para debug
        error_log("PHPMailer Erro: " . $mail->ErrorInfo);
        return false;
    }
}
?>