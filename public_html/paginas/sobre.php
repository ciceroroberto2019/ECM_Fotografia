<?php
// paginas/sobre.php
// Este arquivo é incluído pelo index.php, que já buscou os dados
// e os colocou na variável $pagina_dados
?>

<section class="page-hero-section">
    <div class="page-hero-content">
        <h1><?php echo htmlspecialchars($pagina_dados['titulo']); ?></h1>
        <p>Conheça o fotógrafo por trás das lentes.</p>
    </div>
</section>

<section class="sobre-mim-section">
    <div class="sobre-mim-grid">
        
        <div class="sobre-mim-imagem">
            <?php if (!empty($pagina_dados['imagem_destaque_url'])): ?>
                <img src="<?php echo BASE_URL . htmlspecialchars($pagina_dados['imagem_destaque_url']); ?>" alt="Foto de <?php echo htmlspecialchars($pagina_dados['titulo']); ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/600x600" alt="Foto do Fotógrafo">
            <?php endif; ?>
        </div>

        <div class="sobre-mim-texto">
            
            <?php echo $pagina_dados['conteudo']; ?>
            
            <a href="<?php echo BASE_URL; ?>contato" class="cta-button" style="margin-top: 20px;">Vamos criar algo incrível juntos</a>
        </div>
    </div>
</section>