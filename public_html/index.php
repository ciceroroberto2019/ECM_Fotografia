<?php
// index.php (VERSÃO MESTRE COM BLOG)

session_start();
require_once 'config.php';
require_once 'includes/db.php'; 

$pdo = getDb(); 
$url = $_GET['url'] ?? 'home';
$url = rtrim($url, '/');
$url_parts = explode('/', $url); // Divide a URL em partes (ex: ['blog', 'meu-post'])
$pagina_atual = $url_parts[0];   // Pega a primeira parte ('home', 'blog', etc.)

// --- 1. DEFINIÇÕES GLOBAIS ---
$titulo_da_pagina = "ECM Fotografia | Fotógrafo de Casamentos e Ensaios";
$meta_descricao = "Capture momentos únicos com ECM Fotografia.";
$pagina_dados = null; 
$categorias = []; 
$fotos = []; 
$trabalhos_recentes = [];
$pagina_home = null; 
$hero_slides = []; 
$blog_posts = []; // Para a lista
$post_atual = null; // Para o post individual

// --- 2. BUSCA GLOBAL DE DADOS ---
try {
    // Busca dados da Home
    $stmt_home = $pdo->prepare("SELECT titulo, conteudo, imagem_destaque_url FROM paginas WHERE slug = 'home-hero'");
    $stmt_home->execute();
    $pagina_home = $stmt_home->fetch(PDO::FETCH_ASSOC);
    if ($pagina_home) {
        $titulo_hero = htmlspecialchars($pagina_home['titulo']);
        $subtitulo_hero = $pagina_home['conteudo']; 
    } else {
        $titulo_hero = "Transformando Momentos em Memórias";
        $subtitulo_hero = "Fotografia profissional...";
        $imagem_hero_url = '';
    }
    
    // Busca "Trabalhos Recentes"
    $stmt_recentes = $pdo->query("SELECT pf.caminho_imagem, pf.alt_text FROM portfolio_fotos pf ORDER BY pf.id DESC LIMIT 3");
    $trabalhos_recentes = $stmt_recentes->fetchAll();
    
    // Busca Carrossel Hero
    $stmt_hero_slides = $pdo->query("SELECT * FROM hero_sliders ORDER BY ordem ASC, id DESC");
    $hero_slides = $stmt_hero_slides->fetchAll();
    
} catch (PDOException $e) {
    error_log("Erro no DB: " . $e->getMessage());
}

// --- 3. ROTEAMENTO E DADOS ESPECÍFICOS ---

switch ($pagina_atual) {
    case 'home':
        $titulo_da_pagina = $titulo_hero;
        $meta_descricao = strip_tags($subtitulo_hero); 
        break;

    case 'sobre':
        $titulo_da_pagina = "Sobre Mim | ECM Fotografia";
        $stmt = $pdo->prepare("SELECT * FROM paginas WHERE slug = 'sobre'");
        $stmt->execute();
        $pagina_dados = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pagina_dados) $meta_descricao = strip_tags(substr($pagina_dados['conteudo'], 0, 150));
        break;

    case 'portfolio':
        $titulo_da_pagina = "Portfólio | ECM Fotografia";
        $meta_descricao = "Veja uma seleção dos melhores trabalhos.";
        $categorias = $pdo->query("SELECT * FROM portfolio_categorias ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC); 
        $fotos = $pdo->query("SELECT pf.*, pc.slug AS categoria_slug FROM portfolio_fotos pf JOIN portfolio_categorias pc ON pf.id_categoria = pc.id ORDER BY pf.ordem DESC, pf.id DESC")->fetchAll(PDO::FETCH_ASSOC);
        break;

    // --- MÓDULO BLOG (NOVO) ---
    case 'blog':
        // Verifica se é um post individual (ex: /blog/meu-post)
        if (isset($url_parts[1]) && !empty($url_parts[1])) {
            $slug_post = $url_parts[1];
            $stmt_post = $pdo->prepare("
                SELECT p.*, c.nome as categoria_nome 
                FROM blog_posts p 
                LEFT JOIN blog_categorias c ON p.id_categoria = c.id
                WHERE p.slug = ?
            ");
            $stmt_post->execute([$slug_post]);
            $post_atual = $stmt_post->fetch(PDO::FETCH_ASSOC);

            if ($post_atual) {
                $titulo_da_pagina = htmlspecialchars($post_atual['titulo']) . " | Blog ECM";
                // Cria uma meta descrição a partir do conteúdo (primeiros 160 chars)
                $meta_descricao = substr(strip_tags($post_atual['conteudo']), 0, 160) . "...";
                $pagina_atual = 'post_individual'; // Define flag interna
            } else {
                $pagina_atual = '404'; // Post não encontrado
            }
        } else {
            // É a página principal do Blog (Lista)
            $titulo_da_pagina = "Blog | Dicas e Histórias | ECM Fotografia";
            $meta_descricao = "Acompanhe nossas últimas histórias, dicas de fotografia e making of.";
            // Busca todos os posts
            $blog_posts = $pdo->query("
                SELECT p.*, c.nome as categoria_nome 
                FROM blog_posts p 
                LEFT JOIN blog_categorias c ON p.id_categoria = c.id
                ORDER BY p.data_publicacao DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
        }
        break;

    case 'contato':
        $titulo_da_pagina = "Contato | ECM Fotografia";
        break;

    case 'login':
        $titulo_da_pagina = "Login | Área Restrita";
        break;

    default:
        $titulo_da_pagina = "Página não encontrada";
        $pagina_atual = '404';
        break;
}

// --- 4. RENDERIZAÇÃO ---
include 'includes/header.php';

switch ($pagina_atual) {
    case 'home': include 'paginas/home.php'; break;
    case 'sobre': include 'paginas/sobre.php'; break;
    case 'portfolio': include 'paginas/portfolio.php'; break;
    
    // Novas Views do Blog
    case 'blog': include 'paginas/blog.php'; break;
    case 'post_individual': include 'paginas/post.php'; break;
    
    case 'contato': include 'paginas/contato.php'; break;
    case 'login': include 'paginas/login.php'; break;
    default: include 'paginas/404.php'; break;
}

include 'includes/footer.php';
?>