<?php
// cliente/area-cliente.php (VERSÃO COM CAPA)

require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$id_cliente_logado = $_SESSION['cliente_id'];

// 2. Buscar todos os projetos E a URL da foto de capa (A MUDANÇA)
$stmt_projetos = $pdo->prepare("
    SELECT p.*, f.caminho_arquivo AS capa_url
    FROM projetos_clientes p
    LEFT JOIN fotos_clientes f ON p.id_foto_capa = f.id
    WHERE p.id_cliente = ?
    ORDER BY p.id DESC
");
$stmt_projetos->execute([$id_cliente_logado]);
$projetos = $stmt_projetos->fetchAll();

// 3. O CABEÇALHO
include 'cliente-header.php';
?>

<h1>Minhas Galerias</h1>
<p>Olá, <?php echo htmlspecialchars($_SESSION['cliente_nome']); ?>. Selecione um projeto abaixo para visualizar e selecionar suas fotos.</p>

<div class="projeto-grid">
    
    <?php foreach ($projetos as $projeto): ?>
        <div class="projeto-card">
            
            <div class="projeto-card-capa">
                <?php if (!empty($projeto['capa_url'])): ?>
                    <img src="../<?php echo htmlspecialchars($projeto['capa_url']); ?>" alt="Capa do projeto <?php echo htmlspecialchars($projeto['titulo']); ?>">
                <?php else: ?>
                    <span>(Capa do Álbum)</span>
                <?php endif; ?>
            </div>
            
            <div class="projeto-card-body">
                <h2><?php echo htmlspecialchars($projeto['titulo']); ?></h2>
                <a href="ver-projeto.php?id=<?php echo $projeto['id']; ?>" class="cta-button">
                    Ver Fotos
                </a>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($projetos)): ?>
        <p>Você ainda não tem nenhum projeto de fotos disponível.</p>
    <?php endif; ?>
    
</div>

<?php
// 5. O RODAPÉ
include 'cliente-footer.php';
?>