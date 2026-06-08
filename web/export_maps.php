<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weathermap - Exportar Mapas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            color: #eee;
            padding: 20px;
        }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #00d9ff; margin-bottom: 20px; }
        .card {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #aaa; }
        input, select {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
        }
        .row { display: flex; gap: 15px; }
        .row .form-group { flex: 1; }
        button {
            background: #00d9ff;
            color: #1a1a2e;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover { background: #00b8d9; }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: rgba(0,255,0,0.1);
            border-radius: 6px;
            border-left: 4px solid #00ff00;
        }
        .error { background: rgba(255,0,0,0.1); border-left-color: #ff4444; }
        .output { margin-top: 20px; max-height: 400px; overflow-y: auto; }
        pre {
            background: #0d1117;
            padding: 15px;
            border-radius: 6px;
            color: #aaa;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .back-link { display: inline-block; margin-bottom: 20px; color: #00d9ff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">&larr; Voltar ao Dashboard</a>
        <h1>Exportar Mapas do Zabbix</h1>
        
        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="url">URL da API do Zabbix *</label>
                    <input type="url" id="url" name="url" placeholder="https://zabbix.example.com/api_jsonrpc.php" required>
                </div>
                
                <div class="form-group">
                    <label for="token">Token de API do Zabbix *</label>
                    <input type="password" id="token" name="token" placeholder="SEU_TOKEN_AQUI" required>
                </div>
                
                <div class="form-group">
                    <label for="map_name">Nome do Mapa (opcional)</label>
                    <input type="text" id="map_name" name="map_name" placeholder="Filtrar por nome">
                </div>
                
                <button type="submit">Exportar Mapas</button>
            </form>
            
            <?php
            if ($_POST) {
                // Diretório de saída fixo: maps/
                $outdir = "maps";
                
                $map_name = $_POST['map_name'] ?? '';
                
                // Executa o Python com o diretório de trabalho correto
                $cmd = "cd " . escapeshellarg(__DIR__ . '/..') . " && python3 " . escapeshellarg(__DIR__ . "/export_zabbix_maps.py");
                $cmd .= " --url " . escapeshellarg($_POST['url'] ?? '');
                $cmd .= " --token " . escapeshellarg($_POST['token'] ?? '');
                if ($map_name) $cmd .= " --map-name " . escapeshellarg($map_name);
                $cmd .= " 2>&1";
                
                $output = shell_exec($cmd);
                
                echo '<div class="output"><pre>' . htmlspecialchars($output) . '</pre></div>';
            }
            ?>
        </div>
    </div>
</body>
</html>