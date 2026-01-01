<?php
// admin/tinymce-upload.php

// 1. O SEGURANÇA
require_once 'auth-check.php';

// 2. Definir a pasta de upload para as imagens do editor
// (Vamos criar uma pasta separada para manter organizado)
$pasta_uploads = '../uploads/conteudo/'; // Relativo ao arquivo
$url_base_uploads = BASE_URL . 'uploads/conteudo/'; // URL absoluta

// 3. Criar a pasta se não existir
if (!file_exists($pasta_uploads)) {
    mkdir($pasta_uploads, 0755, true);
}

// 4. Lógica de Upload (fornecida pelo TinyMCE)
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    
    $temp_name = $_FILES['file']['tmp_name'];
    $nome_original = $_FILES['file']['name'];
    $extensao = pathinfo($nome_original, PATHINFO_EXTENSION);
    
    // Cria um nome de arquivo seguro e único
    $nome_arquivo_unico = 'img_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $extensao;
    $caminho_destino = $pasta_uploads . $nome_arquivo_unico;

    // Move o arquivo
    if (move_uploaded_file($temp_name, $caminho_destino)) {
        // 5. Responde ao TinyMCE com o JSON que ele espera
        // O TinyMCE precisa de um JSON com a 'location' da imagem
        echo json_encode(['location' => $url_base_uploads . $nome_arquivo_unico]);
    } else {
        // Erro ao mover
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Falha ao mover o arquivo para o servidor.']);
    }
} else {
    // Erro no upload
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Nenhum arquivo enviado ou erro no upload.']);
}
?>