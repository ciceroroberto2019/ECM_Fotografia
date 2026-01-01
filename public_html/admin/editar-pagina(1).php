<?php
// admin/editar-pagina.php

require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$mensagem = '';
$pagina = null;
$uploads_dir = '../uploads/paginas/';

// 2. Validar o ID da URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gerenciar-paginas.php");
    exit;
}
$id = $_GET['id'];

// --- 3. LÓGICA DE ATUALIZAÇÃO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'editar') {
    
    $titulo = trim($_POST['titulo']);
    $conteudo = $_POST['conteudo']; 
    
    $caminho_imagem_atual = $_POST['imagem_atual'];

    // --- Lógica de Upload de Nova Imagem ---
    if (isset($_FILES['imagem_destaque']) && $_FILES['imagem_destaque']['error'] == UPLOAD_ERR_OK) {
        
        $imagem = $_FILES['imagem_destaque'];
        $check = getimagesize($imagem["tmp_name"]);
        if ($check !== false) {
            $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
            $nome_arquivo_unico = uniqid('pagina_' . $id . '_', true) . '.' . $extensao;
            $caminho_destino = $uploads_dir . $nome_arquivo_unico;

            if (move_uploaded_file($imagem['tmp_name'], $caminho_destino)) {
                if (!empty($caminho_imagem_atual) && file_exists('../' . $caminho_imagem_atual)) {
                    unlink('../' . $caminho_imagem_atual);
                }
                $caminho_imagem_atual = 'uploads/paginas/' . $nome_arquivo_unico;
            } else {
                $mensagem = '<div class="alerta erro">Erro ao mover o arquivo de upload.</div>';
            }
        } else {
             $mensagem = '<div class="alerta erro">O arquivo enviado não é uma imagem válida.</div>';
        }
    }
    // --- Fim da Lógica de Upload ---

    if (empty($mensagem)) {
        try {
            $stmt = $pdo->prepare("UPDATE paginas SET titulo = ?, conteudo = ?, imagem_destaque_url = ? WHERE id = ?");
            $stmt->execute([$titulo, $conteudo, $caminho_imagem_atual, $id]);
            
            header("Location: gerenciar-paginas.php?status=editado");
            exit;
        } catch (PDOException $e) {
            $mensagem = '<div class="alerta erro">Erro ao atualizar a página.</div>';
        }
    }
}

// --- 4. LÓGICA DE LEITURA (GET) ---
$stmt = $pdo->prepare("SELECT * FROM paginas WHERE id = ?");
$stmt->execute([$id]);
$pagina = $stmt->fetch();

if (!$pagina) {
    header("Location: gerenciar-paginas.php");
    exit;
}

// 5. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Editar Página: <?php echo htmlspecialchars($pagina['titulo']); ?></h1>
<p>
    <a href="gerenciar-paginas.php">&larr; Voltar para a lista de páginas</a>
</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <form action="editar-pagina.php?id=<?php echo $pagina['id']; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="editar">
        <input type="hidden" name="imagem_atual" value="<?php echo htmlspecialchars($pagina['imagem_destaque_url']); ?>">
        
        <div class="form-group">
            <label for="titulo">Título da Página</label>
            <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($pagina['titulo']); ?>" required>
        </div>
        
        <?php if ($pagina['slug'] == 'sobre'): ?>
            <div class="form-group">
                <label>Foto de Destaque (Redonda)</label>
                <?php if (!empty($pagina['imagem_destaque_url'])): ?>
                    <img src="../<?php echo htmlspecialchars($pagina['imagem_destaque_url']); ?>" alt="Imagem Atual" style="max-width: 200px; height: auto; display: block; margin-bottom: 10px; border-radius: 50%;">
                <?php endif; ?>
                <label for="imagem_destaque">Trocar/Adicionar Foto:</label>
                <input type="file" id="imagem_destaque" name="imagem_destaque" accept="image/jpeg,image/png,image/webp">
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="conteudo">Conteúdo Principal</label>
            <textarea id="conteudo" name="conteudo" rows="15"><?php echo htmlspecialchars($pagina['conteudo']); ?></textarea>
        </div>
        
        <button type="submit" class="cta-button">Salvar Alterações</button>
    </form>
</div>

<script>
    tinymce.init({
        selector: '#conteudo',
        plugins: 'lists link help wordcount', // Apenas plugins 100% gratuitos
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | link',
        menubar: false // Remove a barra de menu para um visual limpo
    });
</script>

<?php
// 7. O RODAPÉ
include 'admin-footer.php';
?>