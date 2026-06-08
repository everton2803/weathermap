<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weathermap - Gerar Configurações</title>
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
        <h1>Gerar Arquivos de Configuração</h1>
        
        <div class="card">
            <div class="config-info">
                <strong>Nota:</strong> Este script gera arquivos <code>.conf</code> a partir dos arquivos JSON 
                na pasta <code>maps/</code>. Selecione um arquivo ou deixe em branco para processar todos.
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="maps_dir">Diretório dos Mapas</label>
                    <input type="text" id="maps_dir" name="maps_dir" value="maps" placeholder="maps">
                </div>
                
                <div class="form-group">
                    <label for="selected_file">Arquivo JSON (opcional)</label>
                    <select id="selected_file" name="selected_file">
                        <option value="">-- Todos os arquivos --</option>
                        <?php
                        $default_maps_dir = __DIR__ . '/../maps';
                        if (is_dir($default_maps_dir)) {
                            $files = glob($default_maps_dir . '/*.json');
                            foreach ($files as $file) {
                                $basename = basename($file);
                                echo "<option value=\"$basename\">$basename</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="output_dir">Diretório de Saída</label>
                    <input type="text" id="output_dir" name="output_dir" value="configs" placeholder="configs">
                </div>
                
                <button type="submit">Gerar Configurações</button>
            </form>
            
            <?php
            if ($_POST) {
                // Usa caminhos relativos baseados no diretório do script atual
                $project_root = __DIR__ . '/..';
                $maps_dir = $_POST['maps_dir'] ?? 'maps';
                $output_dir = $_POST['output_dir'] ?? 'configs';
                $abs_maps_dir = $project_root . '/' . $maps_dir;
                $abs_output_dir = $project_root . '/' . $output_dir;
                $selected_file = $_POST['selected_file'] ?? '';
                
                // Executa o Python com o diretório de trabalho correto
                $cmd = "cd " . escapeshellarg($project_root) . " && python3 " . escapeshellarg($project_root . "/web/generate_conf.py");
                $cmd .= " -m " . escapeshellarg($abs_maps_dir);
                $cmd .= " -o " . escapeshellarg($abs_output_dir);
                
                if ($selected_file) {
                    $cmd .= " -f " . escapeshellarg($abs_maps_dir . '/' . $selected_file);
                }
                $cmd .= " 2>&1";
                
                $output = shell_exec($cmd);
                
                echo '<div class="output"><pre>' . htmlspecialchars($output) . '</pre></div>';
            }
            ?>
        </div>
    </div>
</body>
</html>