<?php
// admin/gerenciar-paginas.php

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$mensagem = '';

// --- LÓGICA DE LEITURA (GET) ---
// 2. Mensagem de Sucesso (vinda da página de edição)
if (isset($_GET['status']) && $_GET['status'] == 'editado') {
     $mensagem = '<div classalerta sucesso">Página atualizada com sucesso!</div>';
}

// 3. Buscar todas as páginas editáveis
$stmt = $pdo->query("SELECT id, slug, titulo FROM paginas ORDER BY titulo ASC");
$paginas = $stmt->fetchAll();

// 4. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Gerenciar Conteúdo das Páginas</h1>
<p>Edite o conteúdo de texto de páginas como "Sobre Mim".</p>

<?php echo $mensagem; ?>

<h2>Páginas Editáveis</h2>
<table class="admin-table">
    <thead>
        <tr>
            <th>Título da Página</th>
            <th>Slug (Identificador)</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($paginas as $pagina): ?>
            <tr>
                <td><?php echo htmlspecialchars($pagina['titulo']); ?></td>
                <td><?php echo htmlspecialchars($pagina['slug']); ?></td>
                <td>
                    <a href="editar-pagina.php?id=<?php echo $pagina['id']; ?>" class="action-link edit">
                       Editar Conteúdo
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        
        <?php if (empty($paginas)): ?>
            <tr>
                <td colspan="3">
                    Nenhuma página editável encontrada.
                    (Você precisa inserir manualmente 'sobre' e 'contato' no banco de dados primeiro)
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
// 6. O RODAPÉ
include 'admin-footer.php';
?>