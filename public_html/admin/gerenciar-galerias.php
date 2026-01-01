<?php
// admin/gerenciar-galerias.php (VERSÃO CORRIGIDA COM PRG)

require_once 'auth-check.php';
require_once '../includes/db.php';

// Função para criar "slugs"
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
$uploads_dir = '../uploads/clientes/'; 

// 2. VERIFICAR O ID DO CLIENTE
if (!isset($_GET['id_cliente']) || !is_numeric($_GET['id_cliente'])) {
    header("Location: gerenciar-clientes.php?erro=cliente_invalido");
    exit;
}
$id_cliente = $_GET['id_cliente'];

// 3. Buscar dados do cliente
$stmt_cliente = $pdo->prepare("SELECT nome FROM clientes WHERE id = ?");
$stmt_cliente->execute([$id_cliente]);
$cliente = $stmt_cliente->fetch();

if (!$cliente) {
    header("Location: gerenciar-clientes.php?erro=cliente_nao_encontrado");
    exit;
}
$nome_cliente = $cliente['nome'];


// --- 4. LER MENSAGENS DE STATUS (DA URL) ---
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'criado') {
        $mensagem = '<div class="alerta sucesso">Projeto criado com sucesso! A pasta no servidor foi criada.</div>';
    }
    if ($_GET['status'] == 'excluido') {
        $mensagem = '<div class="alerta sucesso">Projeto excluído com sucesso.</div>';
    }
}

// --- LÓGICA DE PROCESSAMENTO (POST) ---

// 5. CRIAR NOVO PROJETO (COM CRIAÇÃO DE PASTA)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'criar_projeto') {
    $titulo_projeto = trim($_POST['titulo_projeto']);
    
    if (!empty($titulo_projeto)) {
        
        $slug_projeto = slugify($titulo_projeto);
        $nome_pasta = $id_cliente . '-' . $slug_projeto . '-' . time();
        $caminho_pasta_fisica = $uploads_dir . $nome_pasta;
        
        if (!file_exists($caminho_pasta_fisica) && mkdir($caminho_pasta_fisica, 0755, true)) {
            try {
                $caminho_pasta_db = 'uploads/clientes/' . $nome_pasta;
                
                $stmt = $pdo->prepare("INSERT INTO projetos_clientes (id_cliente, titulo, caminho_pasta) VALUES (?, ?, ?)");
                $stmt->execute([$id_cliente, $titulo_projeto, $caminho_pasta_db]);
                
                // A CORREÇÃO (PRG): Redireciona em vez de mostrar mensagem
                header("Location: gerenciar-galerias.php?id_cliente=" . $id_cliente . "&status=criado");
                exit; // Para o script

            } catch (PDOException $e) {
                $mensagem = '<div class="alerta erro">Erro ao salvar no banco de dados.</div>';
                rmdir($caminho_pasta_fisica);
            }
        } else {
            $mensagem = '<div class="alerta erro">Erro ao criar a pasta do projeto no servidor.</div>';
        }
    } else {
        $mensagem = '<div class="alerta erro">O título do projeto não pode estar vazio.</div>';
    }
}

// 6. EXCLUIR PROJETO (GET)
if (isset($_GET['excluir_projeto'])) {
    $id_projeto_excluir = $_GET['excluir_projeto'];
    
    // (Falta deletar a pasta física, faremos depois)
    
    $stmt = $pdo->prepare("DELETE FROM projetos_clientes WHERE id = ? AND id_cliente = ?");
    if ($stmt->execute([$id_projeto_excluir, $id_cliente])) {
        
        // A CORREÇÃO (PRG): Redireciona
        header("Location: gerenciar-galerias.php?id_cliente=" . $id_cliente . "&status=excluido");
        exit;
        
    } else {
        $mensagem = '<div class="alerta erro">Erro ao excluir o projeto.</div>';
    }
}

// --- LÓGICA DE LEITURA (GET) ---
// 7. Buscar todos os projetos deste cliente
$stmt_projetos = $pdo->prepare("SELECT * FROM projetos_clientes WHERE id_cliente = ? ORDER BY id DESC");
$stmt_projetos->execute([$id_cliente]);
$projetos = $stmt_projetos->fetchAll();

// 8. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Gerenciar Galerias de: <?php echo htmlspecialchars($nome_cliente); ?></h1>
<p>
    <a href="gerenciar-clientes.php">&larr; Voltar para a lista de clientes</a>
</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <h2>Criar Novo Projeto/Galeria</h2>
    <form action="gerenciar-galerias.php?id_cliente=<?php echo $id_cliente; ?>" method="POST">
        <input type="hidden" name="acao" value="criar_projeto">
        <div class="form-group">
            <label for="titulo_projeto">Título do Projeto (Ex: Casamento, Ensaio Pré-Wedding, Batizado)</label>
            <input type="text" id="titulo_projeto" name="titulo_projeto" required>
        </div>
        <button type="submit" class="cta-button">Criar Projeto</button>
    </form>
</div>

<h2>Projetos de <?php echo htmlspecialchars($nome_cliente); ?></h2>
<table class="admin-table">
    <thead>
        <tr>
            <th>Título do Projeto</th>
            <th>Caminho da Pasta</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projetos as $projeto): ?>
            <tr>
                <td><?php echo htmlspecialchars($projeto['titulo']); ?></td>
                <td><?php echo htmlspecialchars($projeto['caminho_pasta']); ?></td>
                <td>
                    <a href="adicionar-fotos.php?id_projeto=<?php echo $projeto['id']; ?>" class="action-link edit">
                       Adicionar/Ver Fotos
                    </a> | 
                    <a href="ver-selecao.php?id_projeto=<?php echo $projeto['id']; ?>" class="action-link gerenciar">
                       Ver Seleção
                    </a> |
                    <a href="gerenciar-galerias.php?id_cliente=<?php echo $id_cliente; ?>&excluir_projeto=<?php echo $projeto['id']; ?>" 
                       class="action-link delete" 
                       onclick="return confirm('ATENÇÃO: Excluir este projeto apagará TODAS as fotos e seleções. Tem certeza?');">
                       Excluir
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        
        <?php if (empty($projetos)): ?>
            <tr>
                <td colspan="3">Nenhum projeto criado para este cliente.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
// 10. O RODAPÉ
include 'admin-footer.php';
?>