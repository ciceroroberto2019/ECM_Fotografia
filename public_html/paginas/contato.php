<?php
// paginas/contato.php

$mensagem_status = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'sucesso':
            $mensagem_status = '<div class="alerta sucesso">Mensagem enviada com sucesso! Obrigado.</div>';
            break;
        case 'erro':
            $mensagem_status = '<div class="alerta erro">Erro: Por favor, preencha todos os campos obrigatórios.</div>';
            break;
        case 'email_invalido':
            $mensagem_status = '<div class="alerta erro">Erro: O formato do email é inválido.</div>';
            break;
        case 'falha_servidor':
            $mensagem_status = '<div class="alerta erro">Erro: Não foi possível enviar sua mensagem. Tente novamente mais tarde.</div>';
            break;
    }
}
?>

<section class="page-hero-section">
    <div class="page-hero-content">
        <h1>Vamos Conversar</h1>
        <p>Estou ansioso para saber mais sobre seu projeto.</p>
    </div>
</section>

<section class="contato-section">
    <div class="contato-grid">
        <div class="contato-info">
            <h2>Informações de Contato</h2>
            <p>Sinta-se à vontade para me contatar por qualquer um destes canais ou preenchendo o formulário ao lado.</p>
            
            <ul>
                <li>
                    <strong>Email:</strong>
                    <a href="mailto:contato@ecmfotografia.com.br">contato@ecmfotografia.com.br</a>
                </li><!--
                <li>
                    <strong>Telefone / WhatsApp:</strong>
                    <a href="https://wa.me/55119XXXXXXXX">(11) 9XXXX-XXXX</a>
                </li>-->
                <li>
                    <strong>Instagram:</strong>
                    <a href="https://www.instagram.com/cicero_roberto_s/" target="_blank">@cicero_roberto_s</a>
                </li>
                <li>
                    <strong>Localização:</strong>
                    Guarulhos, SP (Atendimento com hora marcada)
                </li>
            </ul>
        </div>

        <div class="contato-form">
            <h2>Envie sua Mensagem</h2>
            <form id="formulario-contato" action="<?php echo BASE_URL; ?>processa-contato.php" method="POST">    
                <div class="form-group">
                    <label for="nome">Seu Nome</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Seu Melhor Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="assunto">Assunto</label>
                    <input type="text" id="assunto" name="assunto">
                </div>
                
                <div class="form-group">
                    <label for="mensagem">Mensagem</label>
                    <textarea id="mensagem" name="mensagem" rows="6" required></textarea>
                </div>
                
                <button type="submit" class="cta-button">Enviar Mensagem</button>
            </form>
        </div>
    </div>
</section>