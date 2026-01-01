<?php
// admin/editar-blog-post.php

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
$uploads_dir = '../uploads/blog/';
$id = $_GET['id'] ?? null;
$is_new = isset($_GET['novo']);

// Dados padrão (vazio)
$post = [
    'titulo' => '', 'slug' => '', 'id_categoria' => '', 'conteudo' => '', 'imagem_destaque' => ''
];

// SE FOR EDIÇÃO, BUSCA DADOS
if ($id && !$is_new) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
    $post_encontrado = $stmt->fetch();
    if ($post_encontrado) $post = $post_encontrado;
}

// --- PROCESSAR FORMULÁRIO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    // Se o slug vier vazio, cria a partir do título
    $slug = !empty($_POST['slug']) ? slugify($_POST['slug']) : slugify($titulo);
    $id_categoria = $_POST['id_categoria'];
    $conteudo = $_POST['conteudo']; // HTML do TinyMCE
    $imagem_atual = $_POST['imagem_atual'] ?? '';

    // Upload de Imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid('blog_', true) . '.' . $ext;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $uploads_dir . $nome_arquivo)) {
            // Deleta a antiga se existir
            if (!empty($imagem_atual) && file_exists('../' . $imagem_atual)) {
                unlink('../' . $imagem_atual);
            }
            $imagem_atual = 'uploads/blog/' . $nome_arquivo;
        }
    }

    if (!empty($titulo)) {
        try {
            if ($id) {
                // UPDATE
                $sql = "UPDATE blog_posts SET titulo=?, slug=?, id_categoria=?, conteudo=?, imagem_destaque=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$titulo, $slug, $id_categoria, $conteudo, $imagem_atual, $id]);
                $mensagem = '<div class="alerta sucesso">Artigo atualizado!</div>';
            } else {
                // INSERT
                $sql = "INSERT INTO blog_posts (titulo, slug, id_categoria, conteudo, imagem_destaque, autor_id) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$titulo, $slug, $id_categoria, $conteudo, $imagem_atual, $_SESSION['admin_id']]);
                $id = $pdo->lastInsertId(); // Pega o ID novo para continuar editando
                // Recarrega a página para modo edição
                header("Location: editar-blog-post.php?id=$id&status=criado");
                exit;
            }
            // Atualiza os dados da variável para exibir no form
            $post = ['titulo' => $titulo, 'slug' => $slug, 'id_categoria' => $id_categoria, 'conteudo' => $conteudo, 'imagem_destaque' => $imagem_atual];
            
        } catch (PDOException $e) {
            $mensagem = '<div class="alerta erro">Erro no banco (talvez Slug duplicado).</div>';
        }
    } else {
        $mensagem = '<div class="alerta erro">Título é obrigatório.</div>';
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'criado') {
    $mensagem = '<div class="alerta sucesso">Artigo criado com sucesso! Continue editando.</div>';
}

// Buscar Categorias para o Select
$categorias = $pdo->query("SELECT * FROM blog_categorias ORDER BY nome ASC")->fetchAll();

include 'admin-header.php';
?>

<h1><?php echo $id ? 'Editar Artigo' : 'Novo Artigo'; ?></h1>
<p><a href="gerenciar-blog-posts.php">&larr; Voltar para a lista</a></p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="imagem_atual" value="<?php echo htmlspecialchars($post['imagem_destaque']); ?>">

        <div class="form-group">
            <label>Título do Artigo</label>
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($post['titulo']); ?>" required>
        </div>

        <div class="form-group">
            <label>Slug (URL amigável - deixe em branco para gerar automático)</label>
            <input type="text" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>">
        </div>

        <div class="form-group">
            <label>Categoria</label>
            <select name="id_categoria" required>
                <option value="">Selecione...</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($post['id_categoria'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Imagem de Capa</label>
            <?php if ($post['imagem_destaque']): ?>
                <img src="../<?php echo htmlspecialchars($post['imagem_destaque']); ?>" style="max-width: 200px; display: block; margin: 10px 0; border-radius: 5px;">
            <?php endif; ?>
            <input type="file" name="imagem" accept="image/*">
        </div>

        <div class="form-group">
            <label>Conteúdo</label>
            <textarea id="conteudo" name="conteudo" rows="20"><?php echo $post['conteudo']; ?></textarea>
        </div>

        <button type="submit" class="cta-button">Salvar Artigo</button>
    </form>
</div>

<script>
    tinymce.init({
        selector: '#conteudo',
        plugins: 'lists link help wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | link',
        menubar: false
    });
</script>

<?php include 'admin-footer.php'; ?>