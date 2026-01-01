<?php
// paginas/portfolio.php
// As variáveis $categorias e $fotos vêm do index.php
?>

<section class="page-hero-section">
    <div class="page-hero-content">
        <h1>Meu Portfólio</h1>
        <p>Uma seleção dos meus melhores trabalhos.</p>
    </div>
</section>

<section class="portfolio-section">
    
    <div class="portfolio-filtros">
        <button class="filtro-btn ativo" data-filter="todos">Todos</button>
        <?php foreach ($categorias as $cat): ?>
            <button class="filtro-btn" data-filter="<?php echo htmlspecialchars($cat['slug']); ?>">
                <?php echo htmlspecialchars($cat['nome']); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="portfolio-grid">
        
        <?php foreach ($fotos as $foto): ?>
            <a href="<?php echo BASE_URL . htmlspecialchars($foto['caminho_imagem']); ?>" 
               class="portfolio-item <?php echo htmlspecialchars($foto['categoria_slug']); ?>">
                
                <img src="<?php echo BASE_URL . htmlspecialchars($foto['caminho_imagem']); ?>" 
                     alt="<?php echo htmlspecialchars($foto['alt_text']); ?>">
                
                <div class="item-overlay">
                    <p>Ver Foto</p>
                </div>
            </a>
        <?php endforeach; ?>

        <?php if (empty($fotos)): ?>
            <p style="text-align: center; grid-column: 1 / -1;">Nenhuma foto encontrada no portfólio ainda.</p>
        <?php endif; ?>
        
    </div> </section>