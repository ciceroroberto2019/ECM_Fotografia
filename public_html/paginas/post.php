<?php
// paginas/post.php
// A variável $post_atual foi definida no index.php
?>

<?php if ($post_atual): ?>
    
    <article class="single-post-container">
        
        <header class="post-header">
            <span class="post-category"><?php echo htmlspecialchars($post_atual['categoria_nome']); ?></span>
            <h1><?php echo htmlspecialchars($post_atual['titulo']); ?></h1>
            <div class="post-meta">
                Publicado em <?php echo date('d/m/Y', strtotime($post_atual['data_publicacao'])); ?>
            </div>
        </header>

        <?php if (!empty($post_atual['imagem_destaque'])): ?>
            <div class="post-featured-image">
                <img src="<?php echo BASE_URL . htmlspecialchars($post_atual['imagem_destaque']); ?>" alt="<?php echo htmlspecialchars($post_atual['titulo']); ?>">
            </div>
        <?php endif; ?>

        <div class="post-content">
            <?php echo $post_atual['conteudo']; ?>
        </div>

        <div class="post-footer">
            <a href="<?php echo BASE_URL; ?>blog" class="cta-button-secundario">&larr; Voltar para o Blog</a>
        </div>

    </article>

<?php else: ?>
    <section class="container" style="padding: 50px; text-align: center;">
        <h1>Artigo não encontrado</h1>
        <a href="<?php echo BASE_URL; ?>blog">Voltar para o Blog</a>
    </section>
<?php endif; ?>