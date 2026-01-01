<?php
// admin/gerenciar-blog-categorias.php

require_once 'auth-check.php';
require_once '../includes/db.php';

// Função slugify
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

$pdo = getDb();
$mensagem = '';

// 1. CRIAR CATEGORIA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'criar') {
    $nome = trim($_POST['nome']);
    $slug = slugify($nome);

    if (!empty($nome)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO blog_categorias (nome, slug) VALUES (?, ?)");
            $stmt->execute([$nome, $slug]);
            $mensagem = '<div class="alerta sucesso">Categoria de Blog criada!</div>';
        } catch (PDOException $e) {
            $mensagem = '<div class="alerta erro">Erro: Categoria já existe.</div>';
        }
    } else {
        $mensagem = '<div class="alerta erro">Nome obrigatório.</div>';
    }
}

// 2. EXCLUIR CATEGORIA
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    try {
        $stmt = $pdo->prepare("DELETE FROM blog_categorias WHERE id = ?");
        if ($stmt->execute([$id_excluir])) {
            $mensagem = '<div class="alerta sucesso">Categoria excluída.</div>';
        }
    } catch (PDOException $e) {
        // Erro comum: tentar excluir categoria que tem posts (RESTRICT no banco)
        $mensagem = '<div class="alerta erro">Não é possível excluir: Existem posts usando esta categoria.</div>';
    }
}

// 3. LISTAR CATEGORIAS
$categorias = $pdo->query("SELECT * FROM blog_categorias ORDER BY nome ASC")->fetchAll();

include 'admin-header.php';
?>

<h1>Gerenciar Categorias do Blog</h1>
<p>Defina os assuntos sobre os quais você vai escrever (ex: Dicas, Casamentos, Making Of).</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <h2>Nova Categoria</h2>
    <form action="" method="POST">
        <input type="hidden" name="acao" value="criar">
        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="nome" required placeholder="Ex: Dicas para Noivas">
        </div>
        <button type="submit" class="cta-button">Criar</button>
    </form>
</div>

<h2>Categorias Existentes</h2>
<table class="admin-table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Slug</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categorias as $cat): ?>
            <tr>
                <td><?php echo htmlspecialchars($cat['nome']); ?></td>
                <td><?php echo htmlspecialchars($cat['slug']); ?></td>
                <td>
                    <a href="editar-blog-categoria.php?id=<?php echo $cat['id']; ?>" class="action-link edit">Editar</a> |
                    <a href="?excluir=<?php echo $cat['id']; ?>" class="action-link delete" onclick="return confirm('Tem certeza?');">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($categorias)): ?>
            <tr><td colspan="3">Nenhuma categoria encontrada.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'admin-footer.php'; ?>