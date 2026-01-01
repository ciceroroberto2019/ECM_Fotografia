<?php
// admin/ver-selecao.php (VERSÃO v3.0 COM GALERIA E LIGHTBOX)

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();

// 2. VERIFICAR O ID DO PROJETO
if (!isset($_GET['id_projeto']) || !is_numeric($_GET['id_projeto'])) {
    header("Location: gerenciar-clientes.php?erro=projeto_invalido");
    exit;
}
$id_projeto = $_GET['id_projeto'];

// 3. Buscar dados do projeto e do cliente
$stmt_projeto = $pdo->prepare("
    SELECT p.titulo AS titulo_projeto, c.nome AS nome_cliente, p.id_cliente, p.status
    FROM projetos_clientes p
    JOIN clientes c ON p.id_cliente = c.id
    WHERE p.id = ?
");
$stmt_projeto->execute([$id_projeto]);
$projeto = $stmt_projeto->fetch();

if (!$projeto) {
    header("Location: gerenciar-clientes.php?erro=projeto_nao_encontrado");
    exit;
}

// 4. Buscar as FOTOS SELECIONADAS
$stmt_fotos = $pdo->prepare("
    SELECT f.id, f.nome_arquivo, f.caminho_arquivo
    FROM selecoes_clientes s
    JOIN fotos_clientes f ON s.id_foto = f.id
    WHERE f.id_projeto = ?
    ORDER BY f.nome_arquivo ASC
");
$stmt_fotos->execute([$id_projeto]);
$fotos_selecionadas = $stmt_fotos->fetchAll();
$total_selecionadas = count($fotos_selecionadas);

// 5. O CABEÇALHO (CORRIGIDO)
// Carrega o header do ADMIN, não do cliente
include 'admin-header.php';
?>

<h1>Seleção do Cliente: <?php echo htmlspecialchars($projeto['titulo_projeto']); ?></h1>
<p>
    <a href="gerenciar-galerias.php?id_cliente=<?php echo $projeto['id_cliente']; ?>">&larr; Voltar para os projetos de <?php echo htmlspecialchars($projeto['nome_cliente']); ?></a>
</p>

<?php if ($projeto['status'] == 'Finalizado'): ?>
    <div class="alerta sucesso">O cliente finalizou esta seleção.</div>
<?php else: ?>
    <div class="alerta erro">Atenção: O cliente ainda está trabalhando nesta seleção. A lista pode mudar.</div>
<?php endif; ?>


<div class="admin-form">
    <h2>Exportar Seleção (<?php echo $total_selecionadas; ?> fotos)</h2>
    <p>Use estes botões para automatizar seu fluxo de trabalho.</p>
    
    <div class="botoes-exportacao-wrapper">
        <a href="exportar-selecao.php?id_projeto=<?php echo $id_projeto; ?>" class="cta-button" target="_blank">
            Exportar Lista (.txt)
        </a>
        <a href="exportar-bat.php?id_projeto=<?php echo $id_projeto; ?>" class="cta-button botao-secundario" target="_blank">
            Exportar Script de Cópia (.bat)
        </a>
    </div>
    <small style="display: block; margin-top: 15px;">
        <strong>Como usar o Script .bat:</strong> Salve este arquivo dentro da sua pasta de fotos originais (em alta resolução) no seu PC e execute-o. Ele criará uma pasta "ALBUM_SELECIONADO" e copiará apenas as fotos selecionadas.
    </small>
</div>

<h2>Visualização das Selecionadas</h2>
<div class="portfolio-admin-grid popup-gallery">
    
    <?php foreach ($fotos_selecionadas as $foto): ?>
        <div class="foto-item">
            <a href="../<?php echo htmlspecialchars($foto['caminho_arquivo']); ?>" 
               class="admin-foto-link"
               title="<?php echo htmlspecialchars($foto['nome_arquivo']); ?>"> <img src="../<?php echo htmlspecialchars($foto['caminho_arquivo']); ?>" 
                     alt="<?php echo htmlspecialchars($foto['nome_arquivo']); ?>">
            </a>
            <div class="foto-info">
                <strong><?php echo htmlspecialchars($foto['nome_arquivo']); ?></strong>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($fotos_selecionadas)): ?>
        <p>O cliente ainda não selecionou nenhuma foto para este projeto.</p>
    <?php endif; ?>
</div>

<?php
// 7. O RODAPÉ (CORRIGIDO)
// Carrega o footer do ADMIN
include 'admin-footer.php';
?>