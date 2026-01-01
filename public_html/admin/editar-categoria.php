<?php
// admin/editar-categoria.php

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

// Função para criar "slugs" (copiada do outro arquivo)
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
$categoria = null;

// 2. Validar o ID da URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gerenciar-categorias.php"); // Redireciona se o ID for inválido
    exit;
}
$id = $_GET['id'];

// --- 3. LÓGICA DE ATUALIZAÇÃO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'editar') {
    $nome = trim($_POST['nome']);
    $slug = slugify(trim($_POST['slug'])); // Pega o slug do form, não gera auto

    if (!empty($nome) && !empty($slug)) {
        try {
            $stmt = $pdo->prepare("UPDATE portfolio_categorias SET nome = ?, slug = ? WHERE id = ?");
            $stmt->execute([$nome, $slug, $id]);
            // Redireciona de volta para a lista com mensagem de sucesso
            header("Location: gerenciar-categorias.php?status=editado");
            exit;
        } catch (PDOException $e) {
            $mensagem = '<div class="alerta erro">Erro ao atualizar. O "slug" já pode estar em uso.</div>';
        }
    } else {
        $mensagem = '<div class="alerta erro">Nome e Slug não podem estar vazios.</div>';
    }
}

// --- 4. LÓGICA DE LEITURA (GET) ---
// Buscar os dados atuais da categoria para preencher o formulário
$stmt = $pdo->prepare("SELECT * FROM portfolio_categorias WHERE id = ?");
$stmt->execute([$id]);
$categoria = $stmt->fetch();

// Se a categoria não existir, volta para a lista
if (!$categoria) {
    header("Location: gerenciar-categorias.php");
    exit;
}

// 5. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Editar Categoria</h1>
<p>
    <a href="gerenciar-categorias.php">&larr; Voltar para a lista de categorias</a>
</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <h2>Editando: <?php echo htmlspecialchars($categoria['nome']); ?></h2>
    <form action="editar-categoria.php?id=<?php echo $categoria['id']; ?>" method="POST">
        <input type="hidden" name="acao" value="editar">
        
        <div class="form-group">
            <label for="nome">Nome da Categoria</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($categoria['nome']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="slug">Slug (Usado na URL)</label>
            <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($categoria['slug']); ?>" required>
            <small>O "slug" deve ser único, em minúsculas, e sem espaços (use hífens).</small>
        </div>
        
        <button type="submit" class="cta-button">Salvar Alterações</button>
    </form>
</div>

<?php
// 7. O RODAPÉ
include 'admin-footer.php';
?>