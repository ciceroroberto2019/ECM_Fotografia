<?php
// admin/adicionar-fotos.php (VERSÃO COM SET-CAPA)

require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$mensagem = '';

// 2. VERIFICAR O ID DO PROJETO (da URL)
if (!isset($_GET['id_projeto']) || !is_numeric($_GET['id_projeto'])) {
    header("Location: gerenciar-clientes.php?erro=projeto_invalido");
    exit;
}
$id_projeto = $_GET['id_projeto'];

// 3. Buscar dados do projeto
$stmt_projeto = $pdo->prepare("SELECT * FROM projetos_clientes WHERE id = ?");
$stmt_projeto->execute([$id_projeto]);
$projeto = $stmt_projeto->fetch();

if (!$projeto) {
    header("Location: gerenciar-clientes.php?erro=projeto_nao_encontrado");
    exit;
}

// --- LÓGICA DE AÇÕES (GET) ---

// 4. AÇÃO: DEFINIR COMO CAPA (NOVO)
if (isset($_GET['set_capa'])) {
    $id_foto_capa = $_GET['set_capa'];
    
    // Atualiza o projeto, definindo o ID da foto como capa
    $stmt_capa = $pdo->prepare("UPDATE projetos_clientes SET id_foto_capa = ? WHERE id = ?");
    if ($stmt_capa->execute([$id_foto_capa, $id_projeto])) {
        // Redireciona para limpar a URL (Padrão PRG)
        header("Location: adicionar-fotos.php?id_projeto=" . $id_projeto . "&status=capa_ok");
        exit;
    }
}

// 5. AÇÃO: EXCLUIR FOTO
if (isset($_GET['excluir_foto'])) {
    $id_foto_excluir = $_GET['excluir_foto'];
    
    $stmt_select = $pdo->prepare("SELECT caminho_arquivo FROM fotos_clientes WHERE id = ? AND id_projeto = ?");
    $stmt_select->execute([$id_foto_excluir, $id_projeto]);
    $foto = $stmt_select->fetch();

    if ($foto) {
        $caminho_arquivo_fisico = '../' . $foto['caminho_arquivo'];

        // CORREÇÃO DE SEGURANÇA: Se a foto for a capa, limpa a capa do projeto
        if ($projeto['id_foto_capa'] == $id_foto_excluir) {
            $pdo->prepare("UPDATE projetos_clientes SET id_foto_capa = NULL WHERE id = ?")->execute([$id_projeto]);
        }

        // Deleta do banco
        $pdo->prepare("DELETE FROM fotos_clientes WHERE id = ?")->execute([$id_foto_excluir]);

        // Deleta o arquivo físico
        if (file_exists($caminho_arquivo_fisico)) {
            unlink($caminho_arquivo_fisico);
        }
        $mensagem = '<div class="alerta sucesso">Foto excluída com sucesso.</div>';
        
        // Recarrega os dados do projeto (para o caso da capa ter mudado)
        $stmt_projeto->execute([$id_projeto]);
        $projeto = $stmt_projeto->fetch();
    }
}

// 6. Mensagem de Sucesso (vinda da URL)
if (isset($_GET['status']) && $_GET['status'] == 'capa_ok') {
    $mensagem = '<div class="alerta sucesso">Foto definida como capa do projeto!</div>';
}

// 7. Buscar todas as fotos JÁ ENVIADAS
$stmt_fotos = $pdo->prepare("SELECT * FROM fotos_clientes WHERE id_projeto = ? ORDER BY id DESC");
$stmt_fotos->execute([$id_projeto]);
$fotos_enviadas = $stmt_fotos->fetchAll();

// 8. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Adicionar Fotos: <?php echo htmlspecialchars($projeto['titulo']); ?></h1>
<p>
    <a href="gerenciar-galerias.php?id_cliente=<?php echo $projeto['id_cliente']; ?>">&larr; Voltar para os projetos deste cliente</a>
</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <h2>Fazer Upload de Fotos</h2>
    <p>Arraste e solte as fotos na caixa abaixo.</p>
    <form action="upload-handler.php" class="dropzone" id="upload-fotos-dropzone">
        <input type="hidden" name="id_projeto" value="<?php echo $id_projeto; ?>">
    </form>
</div>

<h2>Fotos Enviadas (<?php echo count($fotos_enviadas); ?>)</h2>
<div class="portfolio-admin-grid">
    
    <?php foreach ($fotos_enviadas as $foto): ?>
        <?php
            // Verifica se esta é a capa atual
            $is_capa = ($projeto['id_foto_capa'] == $foto['id']);
        ?>
        
        <div class="foto-item <?php echo $is_capa ? 'capa-atual' : ''; ?>">
            <img src="../<?php echo htmlspecialchars($foto['caminho_arquivo']); ?>" alt="<?php echo htmlspecialchars($foto['nome_arquivo']); ?>">
            
            <div class="foto-info">
                <strong>Arquivo:</strong> <?php echo htmlspecialchars(substr($foto['nome_arquivo'], 0, 30)); ?>...
                
                <div class="capa-acao">
                    <?php if ($is_capa): ?>
                        <span class="capa-tag">✔ Capa Atual</span>
                    <?php else: ?>
                        <a href="adicionar-fotos.php?id_projeto=<?php echo $id_projeto; ?>&set_capa=<?php echo $foto['id']; ?>" 
                           class="action-link edit">
                           Definir como Capa
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <a href="adicionar-fotos.php?id_projeto=<?php echo $id_projeto; ?>&excluir_foto=<?php echo $foto['id']; ?>" 
               class="action-link delete" 
               onclick="return confirm('Tem certeza que quer excluir esta foto?');">
               Excluir
            </a>
        </div>
    <?php endforeach; ?>

    <?php if (empty($fotos_enviadas)): ?>
        <p>Nenhuma foto enviada para este projeto ainda.</p>
    <?php endif; ?>
</div>

<script>
    Dropzone.autoDiscover = false;
    var myDropzone = new Dropzone("#upload-fotos-dropzone", {
        paramName: "file", maxFilesize: 15, acceptedFiles: "image/jpeg,image/png,image/webp",
        init: function() {
            this.on("queuecomplete", function() {
                location.reload(); 
            });
        }
    });
</script>

<?php
// 10. O RODAPÉ
include 'admin-footer.php';
?>