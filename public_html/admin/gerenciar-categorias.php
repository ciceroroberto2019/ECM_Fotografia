<?php
// admin/gerenciar-categorias.php

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

// Função para criar "slugs" amigáveis (Ex: "Ensaios de Casal" -> "ensaios-de-casal")
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

// --- LÓGICA DE PROCESSAMENTO (POST) ---

// 2. CADASTRAR NOVA CATEGORIA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'criar') {
    $nome = trim($_POST['nome']);
    $slug = slugify($nome); // Cria o slug automaticamente

    if (!empty($nome)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO portfolio_categorias (nome, slug) VALUES (?, ?)");
            $stmt->execute([$nome, $slug]);
            $mensagem = '<div class="alerta sucesso">Categoria criada com sucesso!</div>';
        } catch (PDOException $e) {
            $mensagem = '<div class="alerta erro">Erro ao criar categoria. O nome ou "slug" já pode existir.</div>';
        }
    } else {
        $mensagem = '<div class="alerta erro">O nome da categoria não pode estar vazio.</div>';
    }
}

// 3. EXCLUIR CATEGORIA (GET)
if (isset($_GET['excluir'])) {
    $id_para_excluir = $_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM portfolio_categorias WHERE id = ?");
    if ($stmt->execute([$id_para_excluir])) {
        $mensagem = '<div class="alerta sucesso">Categoria e todas as fotos associadas foram excluídas.</div>';
    } else {
        $mensagem = '<div class="alerta erro">Erro ao excluir categoria.</div>';
    }
}

// 4. Mensagem de Sucesso (vinda da página de edição)
if (isset($_GET['status']) && $_GET['status'] == 'editado') {
     $mensagem = '<div class="alerta sucesso">Categoria atualizada com sucesso!</div>';
}

// --- LÓGICA DE LEITURA (GET) ---
// 5. Buscar todas as categorias existentes para exibir na tabela
$stmt_categorias = $pdo->query("SELECT * FROM portfolio_categorias ORDER BY nome ASC");
$categorias = $stmt_categorias->fetchAll();


// 6. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Gerenciar Categorias do Portfólio</h1>
<p>Crie e gerencie as categorias (filtros) que aparecem na sua página pública de portfólio.</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <h2>Adicionar Nova Categoria</h2>
    <form action="gerenciar-categorias.php" method="POST">
        <input type="hidden" name="acao" value="criar">
        <div class="form-group">
            <label for="nome">Nome da Categoria (Ex: Casamentos)</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        <button type="submit" class="cta-button">Criar Categoria</button>
    </form>
</div>

<h2>Categorias Existentes</h2>
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Slug (Usado na URL)</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categorias as $cat): ?>
            <tr>
                <td><?php echo $cat['id']; ?></td>
                <td><?php echo htmlspecialchars($cat['nome']); ?></td>
                <td><?php echo htmlspecialchars($cat['slug']); ?></td>
                <td>
                    <a href="editar-categoria.php?id=<?php echo $cat['id']; ?>" class="action-link edit">
                       Editar
                    </a> | 
                    <a href="gerenciar-categorias.php?excluir=<?php echo $cat['id']; ?>" 
                       class="action-link delete" 
                       onclick="return confirm('ATENÇÃO: Excluir esta categoria também apagará TODAS as fotos do portfólio associadas a ela. Tem certeza?');">
                       Excluir
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        
        <?php if (empty($categorias)): ?>
            <tr>
                <td colspan="4">Nenhuma categoria encontrada. Crie uma acima.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
// 8. O RODAPÉ
include 'admin-footer.php';
?>