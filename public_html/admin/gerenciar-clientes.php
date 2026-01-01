<?php
// admin/gerenciar-clientes.php

// 1. O SEGURANÇA E CONEXÃO
require_once 'auth-check.php';
require_once '../includes/db.php';

$pdo = getDb();
$mensagem = '';

// --- LÓGICA DE PROCESSAMENTO (POST) ---

// 2. CADASTRAR NOVO CLIENTE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'criar') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha']; // Não usar trim() em senhas

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = '<div class="alerta erro">Erro: O email fornecido é inválido.</div>';
        } else {
            try {
                // Criptografa a senha do novo cliente
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, senha) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $email, $senha_hash]);
                $mensagem = '<div class="alerta sucesso">Cliente criado com sucesso!</div>';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Violação de chave única
                    $mensagem = '<div class="alerta erro">Erro: Este email já está cadastrado.</div>';
                } else {
                    $mensagem = '<div class="alerta erro">Erro ao criar cliente.</div>';
                }
            }
        }
    } else {
        $mensagem = '<div class="alerta erro">Todos os campos (Nome, Email, Senha) são obrigatórios.</div>';
    }
}

// 3. EXCLUIR CLIENTE (GET)
if (isset($_GET['excluir'])) {
    $id_para_excluir = $_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
    if ($stmt->execute([$id_para_excluir])) {
        $mensagem = '<div class="alerta sucesso">Cliente e todos os seus projetos/fotos associados foram excluídos.</div>';
    } else {
        $mensagem = '<div class="alerta erro">Erro ao excluir cliente.</div>';
    }
}

// 4. Mensagem de Sucesso (vinda da página de edição)
if (isset($_GET['status']) && $_GET['status'] == 'editado') {
     $mensagem = '<div class="alerta sucesso">Cliente atualizado com sucesso!</div>';
}

// --- LÓGICA DE LEITURA (GET) ---
// 5. Buscar todos os clientes existentes
$stmt_clientes = $pdo->query("SELECT id, nome, email FROM clientes ORDER BY nome ASC");
$clientes = $stmt_clientes->fetchAll();


// 6. O CABEÇALHO
include 'admin-header.php';
?>

<h1>Gerenciar Clientes</h1>
<p>Cadastre novos clientes e gerencie suas galerias de seleção de fotos.</p>

<?php echo $mensagem; ?>

<div class="admin-form">
    <h2>Adicionar Novo Cliente</h2>
    <form action="gerenciar-clientes.php" method="POST">
        <input type="hidden" name="acao" value="criar">
        
        <div class="form-group">
            <label for="nome">Nome do Cliente</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email (Será o login do cliente)</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="senha">Senha (Para o cliente acessar)</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        
        <button type="submit" class="cta-button">Criar Cliente</button>
    </form>
</div>

<h2>Clientes Cadastrados</h2>
<table class="admin-table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Email (Login)</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                <td>
                    <a href="gerenciar-galerias.php?id_cliente=<?php echo $cliente['id']; ?>" class="action-link gerenciar">
                       Gerenciar Galerias
                    </a> | 
                    <a href="editar-cliente.php?id=<?php echo $cliente['id']; ?>" class="action-link edit">
                       Editar
                    </a> | 
                    <a href="gerenciar-clientes.php?excluir=<?php echo $cliente['id']; ?>" 
                       class="action-link delete" 
                       onclick="return confirm('ATENÇÃO: Excluir este cliente também apagará TODOS os seus projetos, fotos e seleções. Esta ação não pode ser desfeita. Tem certeza?');">
                       Excluir
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        
        <?php if (empty($clientes)): ?>
            <tr>
                <td colspan="3">Nenhum cliente cadastrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
// 8. O RODAPÉ
include 'admin-footer.php';
?>