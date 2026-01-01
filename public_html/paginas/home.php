<?php
// paginas/home.php
// Variáveis $titulo_hero, $subtitulo_hero, $hero_slides e $trabalhos_recentes vêm do index.php
?>

<section class="hero-carousel-wrapper">
    
    <div class="hero-carousel">
        <?php foreach ($hero_slides as $index => $slide): ?>
            <div class="hero-slide <?php echo ($index == 0) ? 'ativo' : ''; ?>"
                 style="background-image: url('<?php echo BASE_URL . htmlspecialchars($slide['caminho_imagem']); ?>');"
                 data-estilo-texto="<?php echo htmlspecialchars($slide['estilo_texto']); ?>">
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($hero_slides)): ?>
            <div class="hero-slide ativo" style="background-color: #333;"></div>
        <?php endif; ?>
    </div>
    
    <div class="hero-content">
        <h1><?php echo $titulo_hero; ?></h1>
        <p><?php echo $subtitulo_hero; ?></p>
        <a href="<?php echo BASE_URL; ?>portfolio" class="cta-button">Veja meu Portfólio</a>
    </div>
</section>

<section class="destaques-section">
    <h2>Trabalhos Recentes</h2>
    <div class="galeria-grid">
        
        <?php if (!empty($trabalhos_recentes)): ?>
            <?php foreach ($trabalhos_recentes as $trabalho): ?>
                <div class="grid-item">
                    <a href="<?php echo BASE_URL; ?>portfolio">
                        <img src="<?php echo BASE_URL . htmlspecialchars($trabalho['caminho_imagem']); ?>" 
                            alt="<?php echo htmlspecialchars($trabalho['alt_text']); ?>">
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center;">Nenhum trabalho recente encontrado.</p>
        <?php endif; ?>

    </div>
    <a href="<?php echo BASE_URL; ?>portfolio" class="cta-link">Ver todos os trabalhos &rarr;</a>
</section>

<section class="cta-section">
    <h2>Pronto para contar sua história?</h2>
    <p>Vamos conversar sobre seu evento ou ensaio. Peça um orçamento sem compromisso.</p>
    <a href="<?php echo BASE_URL; ?>contato" class="cta-button-secundario">Solicitar Orçamento</a>
</section>