#!/usr/bin/env python3
"""
Script que busca os dados dos triggers associados aos links dos mapas Zabbix.
Extrai os triggerid dos arquivos JSON e busca os dados completos via API do Zabbix.
Salva o resultado de cada mapa em um arquivo separado na pasta triggers/.
"""

import json
import os
import glob
import argparse
import requests

# Diretório de saída padrão relativo ao diretório do script (web/triggers)
DEFAULT_OUTPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "triggers")


def get_zabbix_trigger(url, token, trigger_id):
    """Obtém os dados de um trigger específico do Zabbix via API"""
    headers = {
        'Content-Type': 'application/json'
    }
    
    payload = {
        "jsonrpc": "2.0",
        "method": "trigger.get",
        "params": {
            "output": ["triggerid", "description", "priority", "status", "value", "hostid", "expression"],
            "selectHosts": ["hostid", "host"],
            "selectFunctions": "extend",
            "triggerids": [trigger_id]
        },
        "id": 1,
        "auth": token
    }
    
    try:
        response = requests.post(
            url,
            headers=headers,
            json=payload
        )
        result = response.json()
        print(f"DEBUG: Response for trigger {trigger_id}: {result}")
        triggers = result.get('result', [])
        return triggers[0] if triggers else None
    except Exception as e:
        print(f"Erro ao obter trigger {trigger_id}: {e}")
        return None


def get_zabbix_triggers(url, token, trigger_ids):
    """Obtém os dados dos triggers do Zabbix via API (busca individual)"""
    triggers = []
    for trigger_id in trigger_ids:
        trigger = get_zabbix_trigger(url, token, trigger_id)
        if trigger:
            triggers.append(trigger)
    return triggers


def extract_trigger_ids_from_json(json_path):
    """Extrai os triggerids dos arquivos JSON"""
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    trigger_ids = []
    links = data.get('links', [])
    
    for link in links:
        linktriggers = link.get('linktriggers', [])
        for lt in linktriggers:
            triggerid = lt.get('triggerid')
            if triggerid:
                trigger_ids.append(triggerid)
    
    return trigger_ids


def main():
    """Processa todos os arquivos JSON na pasta maps/"""
    parser = argparse.ArgumentParser(
        description="Obtém dados dos triggers do Zabbix a partir dos arquivos JSON",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Exemplos de uso:
  python get_trigger_data.py --url URL --token TOKEN                    # Com URL e token
  python get_trigger_data.py -m maps/ -o triggers/                      # Com diretórios
  python get_trigger_data.py --url URL --token TOKEN -m maps/ -o triggers/  # Todos os parâmetros
        """
    )
    parser.add_argument(
        '--url',
        type=str,
        required=True,
        help='URL da API do Zabbix (ex: https://zabbix.example.com/api_jsonrpc.php)'
    )
    parser.add_argument(
        '--token',
        type=str,
        required=True,
        help='Token de autenticação do Zabbix'
    )
    parser.add_argument(
        '-m', '--maps-dir',
        type=str,
        default='maps',
        help='Diretório contendo os arquivos JSON dos mapas (padrão: maps)'
    )
    parser.add_argument(
        '-o', '--output-dir',
        type=str,
        default=None,
        help='Diretório de saída para os arquivos de triggers (padrão: web/triggers)'
    )
    
    args = parser.parse_args()
    
    url = args.url
    token = args.token
    maps_dir = args.maps_dir
    output_dir = args.output_dir or DEFAULT_OUTPUT_DIR
    
    # Cria pasta de saída se não existir
    os.makedirs(output_dir, exist_ok=True)
    
    # Procura por arquivos JSON na pasta maps/
    json_files = glob.glob(os.path.join(maps_dir, "*.json"))
    
    if not json_files:
        print(f"Nenhum arquivo JSON encontrado na pasta {maps_dir}/")
        return 0
    
    for json_file in json_files:
        print(f"\nProcessando {json_file}...")
        
        # Extrai trigger IDs deste arquivo
        trigger_ids = extract_trigger_ids_from_json(json_file)
        print(f"Extraídos {len(trigger_ids)} trigger IDs")
        
        if not trigger_ids:
            print(f"Nenhum trigger ID encontrado em {json_file}, pulando...")
            continue
        
        # Busca os dados dos triggers
        print("Buscando dados dos triggers no Zabbix...")
        triggers = get_zabbix_triggers(url, token, trigger_ids)
        
        # Prepara nome do arquivo de saída
        base_name = os.path.splitext(os.path.basename(json_file))[0]
        output_filename = f"{base_name}_triggers.json"
        output_path = os.path.join(output_dir, output_filename)
        
        # Salva os dados em arquivo JSON individual
        output = {
            "source_map": json_file,
            "requested_trigger_ids": trigger_ids,
            "triggers": triggers,
            "count": len(triggers)
        }
        
        with open(output_path, 'w', encoding='utf-8') as f:
            json.dump(output, f, ensure_ascii=False, indent=2)
        
        print(f"Dados salvos em {output_path}")
        print(f"Total de triggers: {len(triggers)}")
    
    print("\nProcessamento concluído!")
    return 0


if __name__ == "__main__":
    exit(main())