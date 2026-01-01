<?php
// index.php (CÓDIGO CORRIGIDO E DEFINITIVO)

// INICIA A SESSÃO EM TODAS AS PÁGINAS
session_start();

// 1. Carrega as configurações (ESSENCIAL)
require_once 'config.php';
require_once 'includes/db.php'; 

$pdo = getDb(); 

// --- 2. DEFINIÇÃO DINÂMICA DO TÍTULO E METADADOS ---

$url = $_GET['url'] ?? 'home';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);

// Define valores padrão
$titulo_da_pagina = "ECM Fotografia | Fotógrafo de Casamentos e Ensaios";
$meta_descricao = "Capture momentos únicos com ECM Fotografia. Especializado em casamentos, ensaios e eventos em São Paulo.";
$pagina_dados = null; 
$categorias = []; 
$fotos = []; 
$trabalhos_recentes = [];
$pagina_home = null; 

// --- BUSCA GLOBAL DE DADOS (PARA HOME E SEO) ---

try {
    // Busca dados da Home (Hero)
    $stmt_home = $pdo->prepare("SELECT titulo, conteudo, imagem_destaque_url FROM paginas WHERE slug = 'home-hero'");
    $stmt_home->execute();
    $pagina_home = $stmt_home->fetch(PDO::FETCH_ASSOC);

    if ($pagina_home) {
        $titulo_hero = htmlspecialchars($pagina_home['titulo']);
        $subtitulo_hero = $pagina_home['conteudo']; 
        $imagem_hero_url = $pagina_home['imagem_destaque_url'];
    } else {
        $titulo_hero = "Transformando Momentos em Memórias";
        $subtitulo_hero = "Fotografia profissional...";
        $imagem_hero_url = '';
    }
    
    // Busca as 3 fotos mais recentes para a Home (TRABALHOS RECENTES)
    $stmt_recentes = $pdo->prepare("
        SELECT 
            pf.caminho_imagem, pf.alt_text
        FROM 
            portfolio_fotos pf
        ORDER BY 
            pf.id DESC 
        LIMIT 3
    ");
    $stmt_recentes->execute();
    $trabalhos_recentes = $stmt_recentes->fetchAll();
    
} catch (PDOException $e) {
    error_log("Erro no DB ao carregar dados globais: " . $e->getMessage());
}


// 3. Define o título, metadados E busca dados específicos da página
switch ($url) {
    case 'home':
        $titulo_da_pagina = $titulo_hero;
        $meta_descricao = $subtitulo_hero;
        break;

    case 'sobre':
        $titulo_da_pagina = "Sobre Mim | ECM Fotografia";
        $meta_descricao = "Conheça a história e a filosofia por trás das lentes da ECM Fotografia.";
        $stmt_sobre = $pdo->prepare("SELECT * FROM paginas WHERE slug = 'sobre'");
        $stmt_sobre->execute();
        $pagina_dados = $stmt_sobre->fetch(PDO::FETCH_ASSOC);
        break;

    case 'portfolio':
        $titulo_da_pagina = "Portfólio | ECM Fotografia";
        $meta_descricao = "Veja uma seleção dos melhores trabalhos de casamentos, ensaios e eventos realizados pela ECM Fotografia.";
        
        // CORRIGIDO: Busca das categorias (SEM ORDER BY 'ordem')
        $stmt_categorias = $pdo->prepare("SELECT * FROM portfolio_categorias ORDER BY nome ASC");
        $stmt_categorias->execute();
        $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC); 

        // CORRIGIDO: Busca de todas as fotos (usando o JOIN correto)
        $stmt_fotos = $pdo->prepare("
            SELECT 
                pf.*, pc.slug AS categoria_slug 
            FROM 
                portfolio_fotos pf 
            JOIN 
                portfolio_categorias pc ON pf.id_categoria = pc.id 
            ORDER BY 
                pf.ordem DESC, pf.id DESC
        ");
        $stmt_fotos->execute();
        $fotos = $stmt_fotos->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'contato':
        $titulo_da_pagina = "Contato | ECM Fotografia";
        $meta_descricao = "Vamos conversar sobre seu projeto. Envie uma mensagem e solicite um orçamento.";
        break;

    case 'login':
        $titulo_da_pagina = "Login | Área Restrita | ECM Fotografia";
        $meta_descricao = "Acesse sua área de cliente ou o painel de administração.";
        break;

    case 'ajuda': // <-- ADICIONE ESTE BLOCO
        $titulo_da_pagina = "Ajuda | ECM Fotografia";
        $meta_descricao = "Guia rápido para seleção e download de fotos.";
        // Busca os dados da página 'ajuda'
        $stmt_ajuda = $pdo->prepare("SELECT * FROM paginas WHERE slug = 'ajuda'");
        $stmt_ajuda->execute();
        $pagina_dados = $stmt_ajuda->fetch(PDO::FETCH_ASSOC);
        break;        

    default:
        $titulo_da_pagina = "Página não encontrada | ECM Fotografia";
        $meta_descricao = "O conteúdo que você procura não foi encontrado.";
        break;
}


// 4. Incluir o cabeçalho (header.php)
include 'includes/header.php';


// 5. Incluir o CONTEÚDO da página
switch ($url) {
    case 'home':
        include 'paginas/home.php'; 
        break;
    case 'sobre':
        include 'paginas/sobre.php'; 
        break;
    case 'portfolio':
        include 'paginas/portfolio.php'; 
        break;
    case 'contato':
        include 'paginas/contato.php';
        break;
    case 'login':
        include 'paginas/login.php';
        break;
    case 'ajuda': // <-- ADICIONE ESTE
        include 'paginas/ajuda.php';
        break;        
    default:
        include 'paginas/404.php'; 
        break;
}


// 6. Incluir o rodapé (footer.php)
include 'includes/footer.php';
?>