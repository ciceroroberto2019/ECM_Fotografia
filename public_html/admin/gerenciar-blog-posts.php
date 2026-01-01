<?php
// admin/gerenciar-blog-posts.php

require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$mensagem = '';

// 1. EXCLUIR POST
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    
    // Primeiro, pega a imagem para deletar o arquivo físico
    $stmt_img = $pdo->prepare("SELECT imagem_destaque FROM blog_posts WHERE id = ?");
    $stmt_img->execute([$id_excluir]);
    $post = $stmt_img->fetch();

    if ($post) {
        // Deleta do banco
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        if ($stmt->execute([$id_excluir])) {
            // Deleta a imagem física se existir
            if (!empty($post['imagem_destaque']) && file_exists('../' . $post['imagem_destaque'])) {
                unlink('../' . $post['imagem_destaque']);
            }
            $mensagem = '<div class="alerta sucesso">Artigo excluído com sucesso.</div>';
        }
    }
}

// 2. LISTAR POSTS (Com nome da categoria)
$sql = "
    SELECT p.*, c.nome as categoria_nome 
    FROM blog_posts p
    LEFT JOIN blog_categorias c ON p.id_categoria = c.id
    ORDER BY p.data_publicacao DESC
";
$posts = $pdo->query($sql)->fetchAll();

include 'admin-header.php';
?>

<h1>Gerenciar Artigos do Blog</h1>
<p>Escreva conteúdo relevante para atrair mais clientes (SEO).</p>

<?php echo $mensagem; ?>

<div style="margin-bottom: 20px;">
    <a href="editar-blog-post.php?novo=true" class="cta-button">Escrever Novo Artigo</a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th width="50">Img</th>
            <th>Título</th>
            <th>Categoria</th>
            <th>Data</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($posts as $post): ?>
            <tr>
                <td>
                    <?php if ($post['imagem_destaque']): ?>
                        <img src="../<?php echo htmlspecialchars($post['imagem_destaque']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                    <?php else: ?>
                        <span style="color: #ccc;">Sem img</span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($post['titulo']); ?></strong><br>
                    <small>/blog/<?php echo htmlspecialchars($post['slug']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($post['categoria_nome']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?></td>
                <td>
                    <a href="editar-blog-post.php?id=<?php echo $post['id']; ?>" class="action-link edit">Editar</a> | 
                    <a href="?excluir=<?php echo $post['id']; ?>" class="action-link delete" onclick="return confirm('Tem certeza?');">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        
        <?php if (empty($posts)): ?>
            <tr><td colspan="5">Nenhum artigo encontrado. Comece a escrever!</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'admin-footer.php'; ?>