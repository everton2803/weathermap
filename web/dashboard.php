<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weathermap - Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            color: #eee;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { text-align: center; margin-bottom: 40px; color: #00d9ff; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .card {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,217,255,0.2);
        }
        .card h3 { color: #00d9ff; margin-bottom: 10px; }
        .card p { color: #888; margin-bottom: 20px; }
        .card a {
            display: inline-block;
            background: #00d9ff;
            color: #1a1a2e;
            padding: 10px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .card a:hover { background: #00b8d9; }
        .footer { text-align: center; margin-top: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Weathermap Tools</h1>
        <div class="grid">
            <div class="card">
                <h3>Exportar Mapas</h3>
                <p>Exporta mapas do Zabbix para arquivos JSON</p>
                <a href="export_maps.php">Executar</a>
            </div>
            <div class="card">
                <h3>Obter Triggers</h3>
                <p>Busca dados dos triggers associados aos mapas</p>
                <a href="get_triggers.php">Executar</a>
            </div>
            <div class="card">
                <h3>Gerar Configurações</h3>
                <p>Gera arquivos .conf a partir dos mapas JSON</p>
                <a href="generate_conf.php">Executar</a>
            </div>
            <div class="card">
                <h3>Visualizar Arquivos</h3>
                <p>Navega e visualiza arquivos de maps, triggers e configs</p>
                <a href="view_files.php">Abrir</a>
            </div>
        </div>
        <div class="footer">
            <p>Weathermap - Ferramentas de exportação e configuração do Zabbix</p>
        </div>
    </div>
</body>
</html>