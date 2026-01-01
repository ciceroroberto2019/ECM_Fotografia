<?php
// Script para listar arquivos e facilitar o envio para o ChatGPT
header('Content-Type: text/plain');

function listarArquivos($dir, $nivel = 0) {
    $itens = scandir($dir);
    foreach ($itens as $item) {
        if ($item == '.' || $item == '..') continue;
        
        echo str_repeat("  ", $nivel) . "- " . $item . "\n";
        
        if (is_dir($dir . '/' . $item)) {
            listarArquivos($dir . '/' . $item, $nivel + 1);
        }
    }
}

echo "ESTRUTURA DE PASTAS ATUAL:\n";
listarArquivos(__DIR__);
?>
