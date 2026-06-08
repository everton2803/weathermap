<?php
// Endpoint para carregar conteúdo de arquivos
header('Content-Type: text/plain; charset=utf-8');

$folder = $_GET['folder'] ?? '';
$file = $_GET['file'] ?? '';

// Mapeamento de pastas
$folderMap = [
    'maps' => __DIR__ . '/../maps',
    'triggers' => __DIR__ . '/../triggers',
    'configs' => __DIR__ . '/../../configs'
];

if (!isset($folderMap[$folder])) {
    http_response_code(400);
    echo "Pasta inválida";
    exit;
}

$filePath = $folderMap[$folder] . '/' . basename($file);

// Verifica se o arquivo existe e é seguro
if (!file_exists($filePath)) {
    http_response_code(404);
    echo "Arquivo não encontrado";
    exit;
}

// Lê o conteúdo do arquivo
$content = file_get_contents($filePath);
echo $content;
