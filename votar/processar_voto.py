#!/usr/bin/env python3
from __future__ import annotations

import fcntl
import json
import os
import sys
from datetime import datetime, timezone, timedelta
from pathlib import Path

APP_DIR = Path("/home/entidades/camecc/sistemas/delibera")
sys.path.insert(0, str(APP_DIR))

from lib_assembleia import (
    AssembleiaError,
    get_estado_publico,
    load_estado,
    load_json,
    save_json_atomic,
    votacao_dir,
    deliberacoes_path,
    votos_path,
    usados_path,
    LISTAS_DIR,
)

# Fuso horário de Brasília (UTC-3)
TZ_BR = timezone(timedelta(hours=-3))


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def responder_json(data: dict, status: int = 200) -> None:
    print("Content-Type: application/json; charset=utf-8")
    print(f"Status: {status}")
    print()
    print(json.dumps(data, ensure_ascii=False))


def ler_body() -> bytes:
    """Lê o corpo da requisição a partir de stdin (CGI)."""
    try:
        length = int(os.environ.get("CONTENT_LENGTH", 0) or 0)
    except ValueError:
        length = 0
    if length <= 0:
        return b""
    return sys.stdin.buffer.read(length)


def agora_iso() -> str:
    return datetime.now(TZ_BR).isoformat(timespec="seconds")


# ---------------------------------------------------------------------------
# GET — estado público
# ---------------------------------------------------------------------------

def handle_get() -> None:
    estado = get_estado_publico()
    responder_json(estado)


# ---------------------------------------------------------------------------
# POST — registrar voto
# ---------------------------------------------------------------------------

def handle_post() -> None:
    # 1. Ler e decodificar body
    raw = ler_body()
    if not raw:
        raise AssembleiaError("Corpo da requisição vazio.")
    try:
        payload = json.loads(raw.decode("utf-8"))
    except (json.JSONDecodeError, UnicodeDecodeError):
        raise AssembleiaError("JSON inválido no corpo da requisição.")

    codigo = str(payload.get("codigo", "")).strip()
    voto   = str(payload.get("voto",   "")).strip()

    if not codigo:
        raise AssembleiaError("Campo 'codigo' é obrigatório.")
    if not voto:
        raise AssembleiaError("Campo 'voto' é obrigatório.")

    # Validação básica de formato: 8 dígitos
    if not codigo.isdigit() or len(codigo) != 8:
        raise AssembleiaError("Código inválido.")

    # 2. Consultar estado.json
    estado = load_estado()
    lista_ativa      = estado.get("lista_ativa")
    deliberacao_atual = estado.get("deliberacao_atual")

    if not lista_ativa:
        raise AssembleiaError("Nenhuma lista ativa no momento.")
    if not deliberacao_atual:
        raise AssembleiaError("Nenhuma deliberação ativa no momento.")

    # 3. Carregar deliberações e validar
    delib_path = deliberacoes_path(lista_ativa)
    if not delib_path.exists():
        raise AssembleiaError("Dados de votação não encontrados.")

    deliberacoes_data = load_json(delib_path, default={"deliberacoes": []})
    deliberacao = next(
        (d for d in deliberacoes_data.get("deliberacoes", [])
         if d.get("id") == deliberacao_atual),
        None,
    )
    if deliberacao is None:
        raise AssembleiaError("Deliberação não encontrada.")
    if not deliberacao.get("aberta", False):
        raise AssembleiaError("Esta deliberação está encerrada.")

    opcoes_validas = deliberacao.get("opcoes", [])
    if voto not in opcoes_validas:
        raise AssembleiaError(
            f"Opção de voto inválida. Opções permitidas: {', '.join(opcoes_validas)}."
        )

    # 4. Adquirir lock exclusivo antes de qualquer leitura/escrita crítica
    lock_path = votacao_dir(lista_ativa) / ".lock"
    lock_path.parent.mkdir(parents=True, exist_ok=True)

    with open(lock_path, "w") as lock_file:
        try:
            fcntl.flock(lock_file, fcntl.LOCK_EX)

            # 5. Verificar se o código consta na lista de presença
            lista_path = LISTAS_DIR / f"{lista_ativa}.json"
            if not lista_path.exists():
                raise AssembleiaError("Lista de presença não encontrada.")
            lista_data = load_json(lista_path, default={"presentes": [], "codigos": []})

            if codigo not in lista_data.get("codigos", []):
                raise AssembleiaError("Código não encontrado.")

            # 6. Verificar voto duplicado
            usados_data = load_json(usados_path(lista_ativa), default={})
            ja_usados = usados_data.get(deliberacao_atual, [])
            if codigo in ja_usados:
                raise AssembleiaError("Este código já votou nesta deliberação.")

            # 7. Registrar voto (sem salvar o código junto)
            votos_data = load_json(votos_path(lista_ativa), default={})
            votos_deliberacao = votos_data.get(deliberacao_atual, [])
            votos_deliberacao.append({
                "voto": voto,
                "registrado_em": agora_iso(),
            })
            votos_data[deliberacao_atual] = votos_deliberacao

            # 8. Marcar código como usado nesta deliberação
            ja_usados.append(codigo)
            usados_data[deliberacao_atual] = ja_usados

            # 9. Salvar ambos os arquivos atomicamente
            save_json_atomic(votos_path(lista_ativa),  votos_data)
            save_json_atomic(usados_path(lista_ativa), usados_data)

        finally:
            fcntl.flock(lock_file, fcntl.LOCK_UN)

    responder_json({
        "ok": True,
        "mensagem": "Voto registrado com sucesso.",
    })


# ---------------------------------------------------------------------------
# Dispatcher principal
# ---------------------------------------------------------------------------

def main() -> None:
    try:
        method = os.environ.get("REQUEST_METHOD", "GET").upper()

        if method == "GET":
            handle_get()
        elif method == "POST":
            handle_post()
        else:
            responder_json({
                "ok": False,
                "erro": "Método não permitido.",
            }, status=405)

    except AssembleiaError as exc:
        responder_json({
            "ok": False,
            "erro": str(exc),
        }, status=400)
    except Exception:
        # Não vazar detalhes internos para o cliente
        responder_json({
            "ok": False,
            "erro": "Erro interno ao processar voto.",
        }, status=500)


if __name__ == "__main__":
    main()