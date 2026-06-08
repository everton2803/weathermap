<?php
// Endpoint para carregar configuração do Zabbix
header('Content-Type: application/json');

$configPath = __DIR__ . '/zabbix_config.json';

if (file_exists($configPath)) {
    $config = json_decode(file_get_contents($configPath), true);
    echo json_encode($config);
} else {
    echo json_encode(['error' => 'Arquivo de configuração não encontrado']);
}
