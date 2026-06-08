<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weathermap - Obter Triggers</title>
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
        input {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
        }
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
        .config-info {
            background: rgba(0,217,255,0.1);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">&larr; Voltar ao Dashboard</a>
        <h1>Obter Dados dos Triggers</h1>
        
        <div class="card">
            <div class="config-info">
                <strong>Nota:</strong> Preencha a URL e o token do Zabbix para conectar à API.
                A URL deve ser no formato: <code>https://zabbix.example.com/api_jsonrpc.php</code>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="url">URL da API do Zabbix *</label>
                    <input type="text" id="url" name="url" placeholder="https://zabbix.example.com/api_jsonrpc.php" required>
                </div>
                
                <div class="form-group">
                    <label for="token">Token de Autenticação *</label>
                    <input type="password" id="token" name="token" placeholder="Seu token de API do Zabbix" required>
                </div>
                
                <div class="form-group">
                    <label for="maps_dir">Diretório dos Mapas</label>
                    <input type="text" id="maps_dir" name="maps_dir" value="web/maps" placeholder="web/maps">
                </div>
                
                <div class="form-group">
                    <label for="triggers_dir">Diretório de Saída para Triggers</label>
                    <input type="text" id="triggers_dir" name="triggers_dir" value="web/triggers" placeholder="web/triggers">
                </div>
                
                <button type="submit">Buscar Triggers</button>
            </form>
            
            <?php
            if ($_POST) {
                // Usa caminhos relativos baseados no diretório do script atual
                $project_root = __DIR__ . '/..';
                $url = $_POST['url'] ?? '';
                $token = $_POST['token'] ?? '';
                $maps_dir = $_POST['maps_dir'] ?? 'web/maps';
                $triggers_dir = $_POST['triggers_dir'] ?? 'triggers';
                
                $abs_maps_dir = $project_root . '/' . $maps_dir;
                $abs_triggers_dir = $project_root . '/' . $triggers_dir;
                
                // Executa o Python com o diretório de trabalho correto
                $cmd = "cd " . escapeshellarg($project_root) . " && python3 " . escapeshellarg($project_root . "/web/get_trigger_data.py");
                $cmd .= " --url " . escapeshellarg($url);
                $cmd .= " --token " . escapeshellarg($token);
                $cmd .= " -m " . escapeshellarg($abs_maps_dir);
                $cmd .= " -o " . escapeshellarg($abs_triggers_dir);
                $cmd .= " 2>&1";
                
                $output = shell_exec($cmd);
                
                echo '<div class="output"><pre>' . htmlspecialchars($output) . '</pre></div>';
            }
            ?>
        </div>
    </div>
</body>
</html>