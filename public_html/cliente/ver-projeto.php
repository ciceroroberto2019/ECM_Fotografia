<?php
// cliente/ver-projeto.php

require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$id_cliente_logado = $_SESSION['cliente_id'];

// 2. VERIFICAR O ID DO PROJETO
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: area-cliente.php?erro=projeto_invalido");
    exit;
}
$id_projeto = $_GET['id'];

// 3. Buscar dados do projeto
$stmt_projeto = $pdo->prepare("SELECT * FROM projetos_clientes WHERE id = ? AND id_cliente = ?");
$stmt_projeto->execute([$id_projeto, $id_cliente_logado]);
$projeto = $stmt_projeto->fetch();

if (!$projeto) {
    header("Location: area-cliente.php?erro=acesso_negado");
    exit;
}

// 4. Buscar TODAS as fotos
$stmt_fotos = $pdo->prepare("SELECT * FROM fotos_clientes WHERE id_projeto = ? ORDER BY nome_arquivo ASC");
$stmt_fotos->execute([$id_projeto]);
$fotos = $stmt_fotos->fetchAll();

// 5. Buscar as fotos JÁ SELECIONADAS
$stmt_selecao = $pdo->prepare("SELECT id_foto FROM selecoes_clientes WHERE id_cliente = ?");
$stmt_selecao->execute([$id_cliente_logado]);
$ids_selecionados = $stmt_selecao->fetchAll(PDO::FETCH_COLUMN, 0);

$total_fotos = count($fotos);
$total_selecionadas = count($ids_selecionados);

// 6. O CABEÇALHO
include 'cliente-header.php';
?>

<h1><?php echo htmlspecialchars($projeto['titulo']); ?></h1>
<p>
    <a href="area-cliente.php">&larr; Voltar para meus projetos</a>
</p>

<?php if ($projeto['status'] == 'Finalizado'): ?>
    <div class="alerta sucesso" style="margin-bottom: 20px;"><strong>Seleção Finalizada!</strong></div>
    <p>Clique na foto para ampliar. O download das fotos continua disponível.</p>
<?php else: ?>
    <p>Clique na foto (<i class="fas fa-search-plus"></i>) para ampliar e selecionar. Quando terminar, confirme o envio.</p>
<?php endif; ?>

<div class="contador-fotos">
    Fotos Selecionadas (para o álbum): 
    <span id="contador-selecionadas"><?php echo $total_selecionadas; ?></span> / 
    <span id="contador-total"><?php echo $total_fotos; ?></span>
</div>

<div class="confirmar-selecao-wrapper" id="confirmar-wrapper">
    <?php if ($projeto['status'] == 'Em Seleção'): ?>
        <button id="btn-confirmar-selecao" class="cta-button" data-id-projeto="<?php echo $id_projeto; ?>">
            Confirmar e Enviar Seleção para o Álbum
        </button>
        <p class="confirmar-aviso">Atenção: Após confirmar, a seleção será travada.</p>
    <?php endif; ?>
</div>

<div class="download-zip-wrapper">
    <h3>Download das Fotos</h3>
    <p>Baixe um arquivo .zip contendo todas as (<?php echo $total_fotos; ?>) fotos deste projeto.</p>
    <a href="baixar-zip.php?id_projeto=<?php echo $id_projeto; ?>&tipo=todas" class="cta-button download-todas" target="_blank">
        Baixar Todas as Fotos (.zip)
    </a>
</div>

<div class="fotos-grid popup-gallery" data-status="<?php echo $projeto['status']; ?>"> 
    
    <?php foreach ($fotos as $foto): ?>
        <?php
            $classe_selecionada = in_array($foto['id'], $ids_selecionados) ? 'selecionada' : '';
            $icone_selecao = $classe_selecionada ? '✔' : '＋';
        ?>

        <a href="../<?php echo htmlspecialchars($foto['caminho_arquivo']); ?>" 
           class="foto-item-wrapper <?php echo $classe_selecionada; ?>" 
           data-id-foto="<?php echo $foto['id']; ?>" 
           data-id-projeto="<?php echo $id_projeto; ?>"
           data-titulo-foto="<?php echo htmlspecialchars($foto['nome_arquivo']); ?>"> 
            
            <img src="../<?php echo htmlspecialchars($foto['caminho_arquivo']); ?>" 
                 alt="<?php echo htmlspecialchars($foto['nome_arquivo']); ?>">
            
            <div class="view-overlay">
                <i class="fas fa-search-plus"></i>
            </div>
            
            <div class="selection-overlay">
                <?php echo $icone_selecao; ?>
            </div>
        </a>
    <?php endforeach; ?>

    <?php if (empty($fotos)): ?>
        <p style="grid-column: 1 / -1; text-align: center;">Nenhuma foto foi adicionada a este projeto ainda.</p>
    <?php endif; ?>
</div>

<div class="pagination">
    <?php /* (O código da paginação, se existir, vai aqui) */ ?>
</div>

<?php
// 9. O RODAPÉ
include 'cliente-footer.php';
?>