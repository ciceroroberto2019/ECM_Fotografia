<?php
// paginas/home.php
// Variáveis $titulo_hero, $subtitulo_hero, $imagem_hero_url e $trabalhos_recentes são definidas no index.php

// Define a URL da imagem de fundo
$url_imagem_fundo = !empty($imagem_hero_url) 
    ? BASE_URL . $imagem_hero_url 
    : BASE_URL . 'imagens/home-hero.png'; // URL de fallback se o DB estiver vazio
?>

<section class="hero-section" style="background-image: url('<?php echo $url_imagem_fundo; ?>');">
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
            <p style="grid-column: 1 / -1; text-align: center;">Nenhum trabalho recente encontrado. Adicione fotos no Painel Admin!</p>
        <?php endif; ?>

    </div>
    <a href="<?php echo BASE_URL; ?>portfolio" class="cta-link">Ver todos os trabalhos &rarr;</a>
</section>

<section class="cta-section">
    <h2>Pronto para contar sua história?</h2>
    <p>Vamos conversar sobre seu evento ou ensaio. Peça um orçamento sem compromisso.</p>
    <a href="<?php echo BASE_URL; ?>contato" class="cta-button-secundario">Solicitar Orçamento</a>
</section>