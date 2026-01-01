<?php
// paginas/login.php
// A variável BASE_URL já está disponível aqui.
?>

<section class="page-hero-section">
    <div class="page-hero-content">
        <h1>Área Restrita</h1>
        <p>Acesse sua galeria de cliente ou o painel administrativo.</p>
    </div>
</section>

<section class="login-section">
    <div class="login-container">
        <h2>Login</h2>
        
        <?php
        if (isset($_GET['erro'])) {
            echo '<p class="alerta erro">Usuário ou senha inválidos.</p>';
        }
        ?>

        <form id="formulario-login" action="<?php echo BASE_URL; ?>processa-login.php" method="POST">
            <div class="form-group">
                <label for="usuario">Email ou Usuário</label>
                <input type="text" id="usuario" name="usuario" required class="input"> 
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required class="input">
            </div>
            
            <button type="submit" class="cta-button">Entrar</button>
        </form>
    </div>
</section>