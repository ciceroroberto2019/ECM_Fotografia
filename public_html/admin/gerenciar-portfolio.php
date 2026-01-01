<?php
// admin/gerenciar-portfolio.php

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$mensagem = '';
$uploads_dir = '../uploads/portfolio/'; // Caminho a partir da pasta /admin/

// --- LÓGICA DE PROCESSAMENTO (POST) ---

// 2. UPLOAD DE NOVA FOTO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'upload') {
    
    $id_categoria = $_POST['id_categoria'];
    $alt_text = trim($_POST['alt_text']);
    $imagem = $_FILES['imagem'];

    // Validação básica
    if (empty($id_categoria) || empty($alt_text) || $imagem['error'] != UPLOAD_ERR_OK) {
        $mensagem = '<div class="alerta erro">Erro: Preencha todos os campos e selecione uma imagem válida.</div>';
    } else {
        // Verifica se é uma imagem real
        $check = getimagesize($imagem["tmp_name"]);
        if ($check === false) {
            $mensagem = '<div class="alerta erro">Erro: O arquivo enviado não é uma imagem.</div>';
        } else {
            // Cria um nome de arquivo único para evitar sobreposições
            $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
            $nome_arquivo_unico = uniqid('portfolio_', true) . '.' . $extensao;
            $caminho_destino = $uploads_dir . $nome_arquivo_unico;

            // Move o arquivo do local temporário para nosso diretório de uploads
            if (move_uploaded_file($imagem['tmp_name'], $caminho_destino)) {
                
                // Salva o caminho *relativo à raiz* no banco de dados
                $caminho_db = 'uploads/portfolio/' . $nome_arquivo_unico;
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO portfolio_fotos (id_categoria, caminho_imagem, alt_text) VALUES (?, ?, ?)");
                    $stmt->execute([$id_categoria, $caminho_db, $alt_text]);
                    $mensagem = '<div class="alerta sucesso">Foto adicionada ao portfólio com sucesso!</div>';
                } catch (PDOException $e) {
                    $mensagem = '<div class="alerta erro">Erro ao salvar no banco de dados: ' . $e->getMessage() . '</div>';
                }
            } else {
                $mensagem = '<div class="alerta erro">Erro ao mover o arquivo para o servidor.</div>';
            }
        }
    }
}

// 3. EXCLUIR FOTO (GET)
if (isset($_GET['excluir'])) {
    $id_para_excluir = $_GET['excluir'];
    
    try {
        // Primeiro, pega o caminho do arquivo no banco para podermos deletar o arquivo físico
        $stmt_select = $pdo->prepare("SELECT caminho_imagem FROM portfolio_fotos WHERE id = ?");
        $stmt_select->execute([$id_para_excluir]);
        $foto = $stmt_select->fetch();

        if ($foto) {
            $caminho_arquivo_fisico = '../' . $foto['caminho_imagem']; // Caminho a partir de /admin/

            // Deleta o registro do banco de dados
            $stmt_delete = $pdo->prepare("DELETE FROM portfolio_fotos WHERE id = ?");
            $stmt_delete->execute([$id_para_excluir]);

            // Deleta o arquivo físico do servidor
            if (file_exists($caminho_arquivo_fisico)) {
                unlink($caminho_arquivo_fisico);
            }
            
            $mensagem = '<div class="alerta sucesso">Foto excluída com sucesso.</div>';
        }
    } catch (PDOException $e) {
        $mensagem = '<div class="alerta erro">Erro ao excluir foto: ' . $e->getMessage() . '</div>';
    }
}

// --- LÓGICA DE LEITURA (GET) ---
// 4. Buscar categorias para o <select>
$stmt_categorias = $pdo->query("SELECT * FROM portfolio_categorias ORDER BY nome ASC");
$categorias = $stmt_categorias->fetchAll();

// 5. Buscar todas as fotos existentes (com o nome da categoria)
$stmt_fotos = $pdo->query("
    SELECT pf.id, pf.caminho_imagem, pf.alt_text, pc.nome AS categoria_nome
    FROM portfolio_fotos pf
    JOIN portfolio_categorias pc ON pf.id_categoria = pc.id
    ORDER BY pc.nome, pf.id DESC
");
$fotos = $stmt_fotos->fetchAll();


// 6. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Gerenciar Fotos do Portfólio</h1>
<p>Adicione ou remova as fotos do seu "Hall da Fama" (o portfólio público).</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <h2>Adicionar Nova Foto</h2>
    <form action="gerenciar-portfolio.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="upload">
        
        <div class="form-group">
            <label for="id_categoria">Categoria</label>
            <select id="id_categoria" name="id_categoria" required>
                <option value="">-- Selecione uma categoria --</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="alt_text">Texto Alternativo (SEO)</label>
            <input type="text" id="alt_text" name="alt_text" placeholder="Ex: Noivos se beijando ao pôr do sol" required>
        </div>

        <div class="form-group">
            <label for="imagem">Arquivo da Imagem (JPG, PNG, WebP)</label>
            <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/webp" required>
        </div>
        
        <button type="submit" class="cta-button">Adicionar Foto</button>
    </form>
</div>

<h2>Fotos Atuais no Portfólio</h2>
<div class="portfolio-admin-grid">
    
    <?php foreach ($fotos as $foto): ?>
        <div class="foto-item">
            <img src="../<?php echo htmlspecialchars($foto['caminho_imagem']); ?>" alt="<?php echo htmlspecialchars($foto['alt_text']); ?>">
            <div class="foto-info">
                <strong>Categoria:</strong> <?php echo htmlspecialchars($foto['categoria_nome']); ?><br>
                <strong>Alt Text:</strong> <?php echo htmlspecialchars($foto['alt_text']); ?>
            </div>
            <a href="gerenciar-portfolio.php?excluir=<?php echo $foto['id']; ?>" 
               class="action-link delete" 
               onclick="return confirm('Tem certeza que quer excluir esta foto do portfólio?');">
               Excluir
            </a>
        </div>
    <?php endforeach; ?>

    <?php if (empty($fotos)): ?>
        <p>Nenhuma foto encontrada no portfólio. Adicione uma acima.</p>
    <?php endif; ?>
    
</div>

<?php
// 8. O RODAPÉ
include 'admin-footer.php';
?>