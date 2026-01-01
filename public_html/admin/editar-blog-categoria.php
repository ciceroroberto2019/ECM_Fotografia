<?php
// admin/editar-blog-categoria.php

require_once 'auth-check.php';
require_once '../includes/db.php';

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

if (!isset($_GET['id'])) header("Location: gerenciar-blog-categorias.php");
$id = $_GET['id'];

// ATUALIZAR
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $slug = slugify($nome);
    
    try {
        $stmt = $pdo->prepare("UPDATE blog_categorias SET nome=?, slug=? WHERE id=?");
        $stmt->execute([$nome, $slug, $id]);
        header("Location: gerenciar-blog-categorias.php");
        exit;
    } catch (PDOException $e) {
        $mensagem = '<div class="alerta erro">Erro ao atualizar.</div>';
    }
}

// BUSCAR DADOS
$stmt = $pdo->prepare("SELECT * FROM blog_categorias WHERE id=?");
$stmt->execute([$id]);
$categoria = $stmt->fetch();

if (!$categoria) header("Location: gerenciar-blog-categorias.php");

include 'admin-header.php';
?>

<h1>Editar Categoria do Blog</h1>
<p><a href="gerenciar-blog-categorias.php">&larr; Voltar</a></p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <form action="" method="POST">
        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="nome" value="<?php echo htmlspecialchars($categoria['nome']); ?>" required>
        </div>
        <button type="submit" class="cta-button">Salvar Alterações</button>
    </form>
</div>

<?php include 'admin-footer.php'; ?>