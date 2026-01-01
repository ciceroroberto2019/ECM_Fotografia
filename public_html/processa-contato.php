<?php
// processa-contato.php

// Verifique se o formulário foi enviado (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Configuração ---
    $email_para_onde_vai = "cicero.roberto.rufino@gmail.com"; // SEU EMAIL AQUI seu-email@ecmfotografia.com.br
    $assunto_do_email = "Novo Contato do Site ECM Fotografia";
    
    // --- 1. Coletar e Limpar os Dados ---
    // trim() remove espaços em branco no início e fim
    // htmlspecialchars() previne ataques XSS
    $nome = htmlspecialchars(trim($_POST['nome']));
    $email = htmlspecialchars(trim($_POST['email']));
    $assunto_usuario = htmlspecialchars(trim($_POST['assunto']));
    $mensagem = htmlspecialchars(trim($_POST['mensagem']));

    // --- 2. Validação Simples ---
    // Verifica se os campos obrigatórios estão preenchidos
    if (empty($nome) || empty($email) || empty($mensagem)) {
        // Se algo estiver faltando, redireciona de volta com erro
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=erro");
        exit;
    }
    
    // Verifica se o email é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=email_invalido");
        exit;
    }

    // --- 3. Montar o Corpo do Email ---
    $corpo_email = "Você recebeu uma nova mensagem do site:\n\n";
    $corpo_email .= "Nome: $nome\n";
    $corpo_email .= "Email: $email\n";
    $corpo_email .= "Assunto: $assunto_usuario\n";
    $corpo_email .= "Mensagem:\n$mensagem\n";

    // --- 4. Montar os Cabeçalhos (Headers) ---
    // Isso é VITAL para o email não cair no SPAM
    $headers = "From: $nome <$email>\r\n"; // O email do usuário como "From"
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // --- 5. Enviar o Email ---
    // A função mail() depende da configuração do seu servidor de hospedagem
    if (mail($email_para_onde_vai, $assunto_do_email, $corpo_email, $headers)) {
        // Sucesso: Redireciona de volta para a pág. de contato com ?status=sucesso
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=sucesso");
    } else {
        // Falha no servidor: Redireciona com erro
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?status=falha_servidor");
    }

} else {
    // Se alguém tentar acessar o arquivo .php diretamente, manda de volta
    header("Location: " . $_SERVER['HTTP_REFERER']);
}
?>