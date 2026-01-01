<?php
// paginas/ajuda.php
// A variável $pagina_dados vem do index.php
?>

<section class="page-hero-section">
    <div class="page-hero-content">
        <h1><?php echo htmlspecialchars($pagina_dados['titulo'] ?? 'Ajuda'); ?></h1>
        <p>Tire suas dúvidas sobre o processo de seleção e download.</p>
    </div>
</section>

<section class="conteudo-pagina-section">
    <div class="conteudo-wrapper">
        <?php
        if ($pagina_dados) {
            // Imprime o HTML/Imagens que você salvou no TinyMCE
            echo $pagina_dados['conteudo'];
        } else {
            echo "<p>Conteúdo da ajuda não encontrado. Por favor, configure no painel de admin.</p>";
        }
        ?>
    </div>
</section>