<?php
// Endpoint para salvar conteúdo de arquivos
header('Content-Type: text/plain; charset=utf-8');

$folder = $_POST['folder'] ?? '';
$file = $_POST['file'] ?? '';
$content = $_POST['content'] ?? '';

// Mapeamento de pastas
$folderMap = [
    'maps' => __DIR__ . '/../../maps',
    'triggers' => __DIR__ . '/../../triggers',
    'configs' => __DIR__ . '/../../configs'
];

if (!isset($folderMap[$folder])) {
    echo "Pasta inválida";
    exit;
}

$filePath = $folderMap[$folder] . '/' . basename($file);

// Verifica se o arquivo existe
if (!file_exists($filePath)) {
    echo "Arquivo não encontrado";
    exit;
}

// Salva o conteúdo
$result = file_put_contents($filePath, $content);

if ($result !== false) {
    echo "Arquivo salvo com sucesso!";
} else {
    echo "Erro ao salvar o arquivo";
}