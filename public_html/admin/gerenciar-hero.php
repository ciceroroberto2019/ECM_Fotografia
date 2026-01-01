<?php
// admin/gerenciar-hero.php

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$mensagem = '';
$uploads_dir = '../uploads/hero/'; // Nova pasta de upload

// --- PRÉ-REQUISITO: CRIAR A PASTA ---
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// --- LÓGICA DE PROCESSAMENTO (POST) ---

// 2. UPLOAD DE NOVA IMAGEM
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'upload') {
    
    $estilo_texto = $_POST['estilo_texto']; // 'texto-claro' ou 'texto-escuro'
    $alt_text = trim($_POST['alt_text']);
    $imagem = $_FILES['imagem'];

    if ($imagem['error'] != UPLOAD_ERR_OK) {
        $mensagem = '<div class="alerta erro">Erro: Nenhuma imagem selecionada.</div>';
    } else {
        $check = getimagesize($imagem["tmp_name"]);
        if ($check === false) {
            $mensagem = '<div class="alerta erro">Erro: O arquivo enviado não é uma imagem.</div>';
        } else {
            $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
            $nome_arquivo_unico = uniqid('hero_', true) . '.' . $extensao;
            $caminho_destino = $uploads_dir . $nome_arquivo_unico;

            if (move_uploaded_file($imagem['tmp_name'], $caminho_destino)) {
                $caminho_db = 'uploads/hero/' . $nome_arquivo_unico;
                try {
                    $stmt = $pdo->prepare("INSERT INTO hero_sliders (caminho_imagem, alt_text, estilo_texto) VALUES (?, ?, ?)");
                    $stmt->execute([$caminho_db, $alt_text, $estilo_texto]);
                    $mensagem = '<div class="alerta sucesso">Imagem do carrossel adicionada!</div>';
                } catch (PDOException $e) {
                    $mensagem = '<div class="alerta erro">Erro ao salvar no banco: ' . $e->getMessage() . '</div>';
                }
            } else {
                $mensagem = '<div class="alerta erro">Erro ao mover o arquivo.</div>';
            }
        }
    }
}

// 3. EXCLUIR IMAGEM (GET)
if (isset($_GET['excluir'])) {
    $id_para_excluir = $_GET['excluir'];
    
    $stmt_select = $pdo->prepare("SELECT caminho_imagem FROM hero_sliders WHERE id = ?");
    $stmt_select->execute([$id_para_excluir]);
    $img = $stmt_select->fetch();

    if ($img) {
        $caminho_arquivo_fisico = '../' . $img['caminho_imagem'];
        $pdo->prepare("DELETE FROM hero_sliders WHERE id = ?")->execute([$id_para_excluir]);
        if (file_exists($caminho_arquivo_fisico)) {
            unlink($caminho_arquivo_fisico);
        }
        $mensagem = '<div class="alerta sucesso">Imagem do carrossel excluída.</div>';
    }
}

// --- LÓGICA DE LEITURA (GET) ---
// 4. Buscar todas as imagens do carrossel
$stmt_sliders = $pdo->query("SELECT * FROM hero_sliders ORDER BY ordem ASC, id DESC");
$imagens_hero = $stmt_sliders->fetchAll();

// 5. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Gerenciar Carrossel da Home</h1>
<p>Adicione ou remova as imagens que aparecem no banner principal (Hero) da sua página inicial.</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <h2>Adicionar Nova Imagem ao Carrossel</h2>
    <form action="gerenciar-hero.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="upload">
        
        <div class="form-group">
            <label for="imagem">Arquivo da Imagem (JPG, PNG, WebP)</label>
            <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/webp" required>
        </div>

        <div class="form-group">
            <label for="alt_text">Texto Alternativo (SEO)</label>
            <input type="text" id="alt_text" name="alt_text" placeholder="Ex: Casal se abraçando na praia" required>
        </div>

        <div class="form-group">
            <label>Estilo do Texto do Menu (Contraste)</label>
            <p style="font-size: 0.9em; color: #555;">Escolha a cor do texto/logo que ficará sobre esta imagem.</p>
            <select id="estilo_texto" name="estilo_texto" required>
                <option value="texto-claro">Texto Claro (Para imagens de fundo escuras)</option>
                <option value="texto-escuro">Texto Escuro (Para imagens de fundo claras)</option>
            </select>
        </div>
        
        <button type="submit" class="cta-button">Adicionar Imagem</button>
    </form>
</div>

<h2>Imagens Atuais no Carrossel</h2>
<div class="portfolio-admin-grid">
    
    <?php foreach ($imagens_hero as $img): ?>
        <div class="foto-item">
            <img src="../<?php echo htmlspecialchars($img['caminho_imagem']); ?>" alt="<?php echo htmlspecialchars($img['alt_text']); ?>">
            <div class="foto-info">
                <strong>Estilo do Texto:</strong> <?php echo htmlspecialchars($img['estilo_texto']); ?><br>
                <strong>Alt Text:</strong> <?php echo htmlspecialchars($img['alt_text']); ?>
            </div>
            <a href="gerenciar-hero.php?excluir=<?php echo $img['id']; ?>" 
               class="action-link delete" 
               onclick="return confirm('Tem certeza que quer excluir esta imagem do carrossel?');">
               Excluir
            </a>
        </div>
    <?php endforeach; ?>

    <?php if (empty($imagens_hero)): ?>
        <p>Nenhuma imagem no carrossel. Adicione uma acima.</p>
    <?php endif; ?>
    
</div>

<?php
// 7. O RODAPÉ
include 'admin-footer.php';
?>