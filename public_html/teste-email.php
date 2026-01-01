<?php
// teste-email.php
// Este script é um teste unitário para o PHPMailer

echo "<h1>Teste de Envio de Email (PHPMailer)</h1>";

// 1. Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 2. Carrega os arquivos (Caminho baseado na sua imagem 'image_9a771d.png')
// Ponto de Atenção (Regra 6): Se este caminho falhar, o script para aqui.
require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';

echo "<p>Biblioteca PHPMailer carregada com sucesso.</p>";

// 3. Configurar e Enviar o Email
$mail = new PHPMailer(true); // Habilita exceções

try {
    // --- PREENCHA EXATAMENTE COMO NO OUTRO SCRIPT ---
    $senha_do_email = 'Plano@notificacoes1602'; // <-- PREENCHA SUA SENHA AQUI
    // -----------------------------------------------

    if ($senha_do_email === 'SUA_SENHA_AQUI' || empty($senha_do_email)) {
        echo "<h2 style='color: red;'>ERRO DE CONFIGURAÇÃO</h2>";
        echo "<p>Você não preencheu a variável 'SUA_SENHA_AQUI' neste script de teste.</p>";
        exit;
    }

    echo "<p>Iniciando conexão com o servidor SMTP (Hostinger)...</p>";

    // Configurações do Servidor (Hostinger)
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'notificacoes@ecmfotografia.com.br';
    $mail->Password   = $senha_do_email; // Usa a senha que você definiu acima
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    
    // (Opcional) Debug de SMTP:
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Descomente esta linha se o teste falhar

    // Remetente e Destinatário
    $mail->setFrom('notificacoes@ecmfotografia.com.br', 'Sistema ECM (Teste)');
    $mail->addAddress('cicero.roberto.rufino@gmail.com', 'Admin ECM (Teste)'); 
    
    // Conteúdo
    $mail->isHTML(true); 
    $mail->Subject = "TESTE DE ENVIO DE EMAIL - " . date("Y-m-d H:i:s");
    $mail->Body    = "<h1>Teste de Email</h1><p>Se você recebeu este email, a conexão SMTP com a Hostinger está funcionando perfeitamente.</p>";
    $mail->AltBody = "Teste de Email. Se você recebeu isto, a conexão SMTP está OK.";

    echo "<p>Enviando email para cicero.roberto.rufino@gmail.com...</p>";

    $mail->send();
    
    echo "<h2 style='color: green;'>SUCESSO!</h2>";
    echo "<p>O email de teste foi enviado com sucesso!</p>";
    echo "<p>Verifique sua caixa de entrada (e spam).</p>";

} catch (Exception $e) {
    // Se o envio falhar, o PHPMailer joga uma Exceção
    echo "<h2 style='color: red;'>FALHA NO ENVIO!</h2>";
    echo "<p>Ocorreu um erro ao tentar enviar o email:</p>";
    echo "<p><strong>Erro do PHPMailer:</strong> " . $mail->ErrorInfo . "</p>";
    echo "<p><strong>Possível Causa:</strong> Verifique se a 'SUA_SENHA_AQUI' está 100% correta.</p>";
}

echo "<p style='color:red; font-weight:bold; margin-top: 20px;'>POR FAVOR, DELETE ESTE ARQUIVO (teste-email.php) APÓS O TESTE!</p>";
?>