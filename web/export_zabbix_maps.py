#!/usr/bin/env python3
"""Exporta todos os mapas do Zabbix para arquivos JSON individuais.

Conecta a um servidor Zabbix 7.0 via API JSON-RPC, recupera as definições
dos mapas e elementos/links associados (quando disponíveis), e escreve
um arquivo JSON por mapa na pasta maps/.

Uso:
  python export_zabbix_maps.py --url https://zabbix.example.com/api_jsonrpc.php \
    --token SEU_TOKEN_AQUI

Depois execute sem argumentos:
  python export_zabbix_maps.py
"""
from __future__ import annotations

import argparse
import json
import os
import re
import sys
from typing import Any, Dict, Optional

import requests


# Diretório de saída fixo (web/maps/)
OUTPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "maps")


class ZabbixExporter:
    def __init__(self, api_url: str, token: str = None) -> None:
        self.api_url = api_url
        self.token = token
        self._auth: Optional[str] = None
        self._req_id = 1

    def _rpc(self, method: str, params: Dict[str, Any]) -> Any:
        payload = {
            "jsonrpc": "2.0",
            "method": method,
            "params": params,
            "id": self._req_id,
        }
        if self._auth is not None:
            payload["auth"] = self._auth

        self._req_id += 1
        resp = requests.post(self.api_url, json=payload, timeout=30)
        resp.raise_for_status()
        data = resp.json()
        if "error" in data:
            raise RuntimeError(f"Erro na API do Zabbix: {data['error']}")
        return data.get("result")

    def login(self) -> None:
        # Se token foi fornecido, usa diretamente sem precisar fazer login
        if self.token:
            self._auth = self.token
            return
        
        raise RuntimeError("É necessário fornecer token")

    def get_maps_basic(self) -> Any:
        return self._rpc("map.get", {"output": "extend"})

    def get_map_by_name(self, name: str) -> Any:
        """Busca um mapa diretamente pelo nome na API do Zabbix."""
        selects_variants = [
            {"selectLinks": "extend", "selectSelements": "extend", "selectMapItems": "extend"},
            {"selectLinks": "extend", "selectSelements": "extend"},
            {"selectLinks": "extend"},
        ]

        for sel in selects_variants:
            params = {"filter": {"name": name}, "output": "extend"}
            params.update(sel)
            try:
                return self._rpc("map.get", params)
            except Exception:
                continue

        # fallback: map.get básico com filtro por nome
        return self._rpc("map.get", {"filter": {"name": name}, "output": "extend"})

    def get_map_details(self, mapid: str) -> Any:
        # Tenta buscar objetos relacionados extras; algumas instalações do Zabbix
        # podem não aceitar todos os parâmetros select* dependendo da versão e módulos.
        # Abordagem best-effort.
        selects_variants = [
            {"selectLinks": "extend", "selectSelements": "extend", "selectMapItems": "extend"},
            {"selectLinks": "extend", "selectSelements": "extend"},
            {"selectLinks": "extend"},
        ]

        for sel in selects_variants:
            params = {"mapids": [mapid], "output": "extend"}
            params.update(sel)
            try:
                return self._rpc("map.get", params)
            except Exception:
                # tenta próxima variação
                continue

        # fallback: map.get básico
        return self._rpc("map.get", {"mapids": [mapid], "output": "extend"})


def sanitize_filename(name: str) -> str:
    name = name.strip()
    # substitui caracteres inválidos para caminhos
    name = re.sub(r"[\\/:*?\"<>|]+", "-", name)
    # colapsa espaços
    name = re.sub(r"\s+", "_", name)
    return name


def main() -> int:
    parser = argparse.ArgumentParser(description="Exporta mapas do Zabbix para arquivos JSON")
    parser.add_argument("--url", required=True, help="URL da API do Zabbix, ex: https://zabbix.example.com/api_jsonrpc.php")
    parser.add_argument("--token", required=True, help="Token de API do Zabbix")
    parser.add_argument("--map-name", default=None, help="Filtrar e exportar apenas o mapa com este nome (case-insensitive)")

    args = parser.parse_args()

    url = args.url
    token = args.token
    map_name_filter = args.map_name

    # Diretório de saída fixo
    outdir = OUTPUT_DIR

    os.makedirs(outdir, exist_ok=True)

    print(f"Conectando ao Zabbix em {url}...")
    exporter = ZabbixExporter(url, token=token)
    try:
        exporter.login()
    except Exception as e:
        print(f"Falha na autenticação: {e}")
        return 2

    # Se --map-name foi informado, busca diretamente pelo nome na API
    if map_name_filter:
        try:
            maps = exporter.get_map_by_name(map_name_filter)
        except Exception as e:
            print(f"Falha ao buscar mapa '{map_name_filter}': {e}")
            return 3

        if not maps:
            print(f"Nenhum mapa encontrado com o nome '{map_name_filter}'.")
            return 0

        for m in maps:
            name = m.get("name") or map_name_filter
            fname = f"{sanitize_filename(name)}.json"
            path = os.path.join(outdir, fname)
            with open(path, "w", encoding="utf-8") as fh:
                json.dump(m, fh, ensure_ascii=False, indent=2)
            print(f"Salvo {path}")
    else:
        try:
            maps = exporter.get_maps_basic()
        except Exception as e:
            print(f"Falha ao listar mapas: {e}")
            return 3

        if not maps:
            print("Nenhum mapa encontrado no servidor.")
            return 0

        seen_names: set = set()

        for m in maps:
            mapid = m.get("sysmapid") or m.get("mapid") or m.get("id")
            if mapid is None:
                continue

            try:
                details = exporter.get_map_details(str(mapid))
            except Exception as e:
                print(f"Aviso: falha ao obter detalhes do mapa {mapid}: {e}")
                details = [m]

            if isinstance(details, list) and details:
                detail = details[0]
            else:
                detail = details

            name = detail.get("name") or f"map_{mapid}"
            name_key = name.strip().lower()
            if name_key in seen_names:
                print(f"Mapa duplicado ignorado (nome='{name}', mapid original={mapid})")
                continue
            seen_names.add(name_key)

            fname = f"{sanitize_filename(name)}.json"
            path = os.path.join(outdir, fname)
            with open(path, "w", encoding="utf-8") as fh:
                json.dump(detail, fh, ensure_ascii=False, indent=2)
            print(f"Salvo {path}")

    print("Exportação concluída.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())