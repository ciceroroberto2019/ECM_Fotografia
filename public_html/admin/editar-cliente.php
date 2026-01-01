<?php
// admin/editar-cliente.php

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$mensagem = '';
$cliente = null;

// 2. Validar o ID da URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gerenciar-clientes.php");
    exit;
}
$id = $_GET['id'];

// --- 3. LÓGICA DE ATUALIZAÇÃO (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'editar') {
    
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha']; // Senha (opcional)

    if (!empty($nome) && !empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = '<div class="alerta erro">Erro: O email fornecido é inválido.</div>';
        } else {
            try {
                // Lógica da Senha: Só atualiza se for preenchida
                if (!empty($senha)) {
                    // Atualiza com nova senha
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, email = ?, senha = ? WHERE id = ?");
                    $stmt->execute([$nome, $email, $senha_hash, $id]);
                } else {
                    // Atualiza sem mexer na senha
                    $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, email = ? WHERE id = ?");
                    $stmt->execute([$nome, $email, $id]);
                }
                
                header("Location: gerenciar-clientes.php?status=editado");
                exit;
                
            } catch (PDOException $e) {
                 if ($e->getCode() == 23000) {
                    $mensagem = '<div class="alerta erro">Erro: Este email já está em uso por outro cliente.</div>';
                } else {
                    $mensagem = '<div class="alerta erro">Erro ao atualizar o cliente.</div>';
                }
            }
        }
    } else {
        $mensagem = '<div class="alerta erro">Nome e Email são obrigatórios.</div>';
    }
}

// --- 4. LÓGICA DE LEITURA (GET) ---
// Buscar os dados atuais do cliente para preencher o formulário
$stmt = $pdo->prepare("SELECT nome, email FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header("Location: gerenciar-clientes.php");
    exit;
}

// 5. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Editar Cliente</h1>
<p>
    <a href="gerenciar-clientes.php">&larr; Voltar para a lista de clientes</a>
</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <h2>Editando: <?php echo htmlspecialchars($cliente['nome']); ?></h2>
    <form action="editar-cliente.php?id=<?php echo $id; ?>" method="POST">
        <input type="hidden" name="acao" value="editar">
        
        <div class="form-group">
            <label for="nome">Nome do Cliente</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email (Login do cliente)</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="senha">Nova Senha</label>
            <input type="password" id="senha" name="senha">
            <small>Deixe em branco para não alterar a senha atual.</small>
        </div>
        
        <button type="submit" class="cta-button">Salvar Alterações</button>
    </form>
</div>

<?php
// 7. O RODAPÉ
include 'admin-footer.php';
?>