<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weathermap - Visualizar Arquivos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            color: #eee;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #00d9ff; margin-bottom: 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #00d9ff; text-decoration: none; }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .tab {
            padding: 12px 24px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-bottom: none;
            border-radius: 6px 6px 0 0;
            cursor: pointer;
            color: #aaa;
            transition: all 0.3s;
        }
        .tab.active { color: #00d9ff; border-color: #00d9ff; background: rgba(0,217,255,0.1); }
        .tab-content {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .file-list { display: grid; gap: 10px; }
        .file-item {
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-item:hover { background: rgba(0,217,255,0.2); }
        .file-name { color: #00d9ff; }
        .file-size { color: #888; font-size: 12px; }
        .file-content { margin-top: 20px; }
        .file-content pre {
            background: #0d1117;
            padding: 15px;
            border-radius: 6px;
            color: #aaa;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
        }
        .no-files { color: #666; text-align: center; padding: 40px; }
        .close-btn {
            float: right;
            color: #00d9ff;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">&larr; Voltar ao Dashboard</a>
        <h1>Visualizar Arquivos</h1>
        
        <div class="tabs">
            <div class="tab active" onclick="showTab('maps')">Maps</div>
            <div class="tab" onclick="showTab('triggers')">Triggers</div>
            <div class="tab" onclick="showTab('configs')">Configs</div>
        </div>
        
        <div class="tab-content" id="tab-maps">
            <?php
            $mapsDir = __DIR__ . '/maps';
            $files = glob($mapsDir . '/*.json');
            if (empty($files)): ?>
                <div class="no-files">Nenhum arquivo encontrado na pasta maps/</div>
            <?php else: ?>
                <div class="file-list">
                    <?php foreach ($files as $file): 
                        $basename = basename($file);
                        $size = round(filesize($file) / 1024, 1);
                    ?>
                        <div class="file-item" onclick="viewFile('maps', '<?= htmlspecialchars($basename) ?>', <?= $size ?>)">
                            <span class="file-name"><?= htmlspecialchars($basename) ?></span>
                            <span class="file-size"><?= $size ?> KB</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="tab-content" id="tab-triggers" style="display: none;">
            <?php
            $triggersDir = __DIR__ . '/triggers';
            $files = glob($triggersDir . '/*.json');
            if (empty($files)): ?>
                <div class="no-files">Nenhum arquivo encontrado na pasta triggers/</div>
            <?php else: ?>
                <div class="file-list">
                    <?php foreach ($files as $file): 
                        $basename = basename($file);
                        $size = round(filesize($file) / 1024, 1);
                    ?>
                        <div class="file-item" onclick="viewFile('triggers', '<?= htmlspecialchars($basename) ?>', <?= $size ?>)">
                            <span class="file-name"><?= htmlspecialchars($basename) ?></span>
                            <span class="file-size"><?= $size ?> KB</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="tab-content" id="tab-configs" style="display: none;">
            <?php
            $configsDir = __DIR__ . '/../configs';
            $files = glob($configsDir . '/*.conf');
            if (empty($files)): ?>
                <div class="no-files">Nenhum arquivo encontrado na pasta configs/</div>
            <?php else: ?>
                <div class="file-list">
                    <?php foreach ($files as $file): 
                        $basename = basename($file);
                        $size = round(filesize($file) / 1024, 1);
                    ?>
                        <div class="file-item" onclick="viewFile('configs', '<?= htmlspecialchars($basename) ?>', <?= $size ?>)">
                            <span class="file-name"><?= htmlspecialchars($basename) ?></span>
                            <span class="file-size"><?= $size ?> KB</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div id="file-viewer" style="display: none;">
            <a href="#" class="close-btn" onclick="closeViewer(); return false;">Fechar &times;</a>
            <div class="file-content">
                <div id="file-actions" style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <button onclick="saveFile()" style="background: #00d9ff; color: #1a1a2e; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Salvar</button>
                        <span id="save-status" style="margin-left: 10px; color: #00ff00; display: none;">Salvo!</span>
                    </div>
                    <div style="color: #888; font-size: 12px;" id="file-info"></div>
                </div>
                <textarea id="file-content" style="width: 100%; height: 500px; background: #0d1117; color: #aaa; font-family: monospace; padding: 15px; border-radius: 6px; border: 1px solid #333; resize: none;"></textarea>
            </div>
        </div>
    </div>
    
        <script>
        let currentFolder = '';
        let currentFile = '';
        
        function showTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
            event.target.classList.add('active');
            document.getElementById('tab-' + tabName).style.display = 'block';
            document.getElementById('file-viewer').style.display = 'none';
        }
        
        function viewFile(folder, filename, size) {
            currentFolder = folder;
            currentFile = filename;
            fetch('api/load_file.php?folder=' + folder + '&file=' + encodeURIComponent(filename))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('file-content').value = data;
                    document.getElementById('file-viewer').style.display = 'block';
                    document.getElementById('tab-' + folder).style.display = 'none';
                    // Atualiza info do arquivo
                    document.getElementById('file-info').textContent = filename + ' (' + Math.round(size * 100) / 100 + ' KB) - ' + (folder === 'configs' ? 'Modo edição' : 'Modo visualização');
                    // Mostra botão de salvar apenas para arquivos .conf
                    document.getElementById('file-actions').style.display = folder === 'configs' ? 'flex' : 'none';
                });
        }
        
        function saveFile() {
            const content = document.getElementById('file-content').value;
            document.getElementById('save-status').style.display = 'inline';
            document.getElementById('save-status').textContent = 'Salvando...';
            fetch('api/save_file.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'folder=' + encodeURIComponent(currentFolder) + '&file=' + encodeURIComponent(currentFile) + '&content=' + encodeURIComponent(content)
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('save-status').textContent = 'Salvo!';
                setTimeout(() => { document.getElementById('save-status').style.display = 'none'; }, 2000);
            });
        }
        
        function closeViewer() {
            document.getElementById('file-viewer').style.display = 'none';
        }
    </script>
</body>
</html>
