<?php
// paginas/blog.php
// As variáveis globais (como $blog_posts) já foram definidas no index.php
?>

<section class="page-hero-section">
    <div class="page-hero-content">
        <h1>Blog & Dicas</h1>
        <p>Inspirações, histórias e dicas para o seu ensaio.</p>
    </div>
</section>

<section class="blog-list-section">
    <div class="blog-grid">
        
        <?php if (!empty($blog_posts)): ?>
            <?php foreach ($blog_posts as $post): ?>
                <article class="blog-card">
                    <a href="<?php echo BASE_URL; ?>blog/<?php echo htmlspecialchars($post['slug']); ?>" class="blog-card-link">
                        
                        <div class="blog-card-image">
                            <?php if (!empty($post['imagem_destaque'])): ?>
                                <img src="<?php echo BASE_URL . htmlspecialchars($post['imagem_destaque']); ?>" alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                            <?php else: ?>
                                <div class="blog-no-image">ECM</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="blog-card-content">
                            <span class="blog-category"><?php echo htmlspecialchars($post['categoria_nome']); ?></span>
                            <h2><?php echo htmlspecialchars($post['titulo']); ?></h2>
                            <p class="blog-excerpt">
                                <?php 
                                    // Cria um resumo removendo HTML e cortando em 120 caracteres
                                    echo substr(strip_tags($post['conteudo']), 0, 120) . '...'; 
                                ?>
                            </p>
                            <span class="read-more">Ler mais &rarr;</span>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%;">Ainda não há artigos publicados.</p>
        <?php endif; ?>

    </div>
</section>