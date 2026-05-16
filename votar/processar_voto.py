#!/usr/bin/env python3
from __future__ import annotations

import json
import os
import sys
from pathlib import Path


APP_DIR = Path("/home/entidades/camecc/sistemas/delibera")
sys.path.insert(0, str(APP_DIR))

from lib_assembleia import AssembleiaError, get_estado_publico


def responder_json(data: dict, status: int = 200) -> None:
    print("Content-Type: application/json; charset=utf-8")
    print(f"Status: {status}")
    print()
    print(json.dumps(data, ensure_ascii=False))


def handle_get() -> None:
    estado = get_estado_publico()
    responder_json(estado)


def main() -> None:
    try:
        method = os.environ.get("REQUEST_METHOD", "GET").upper()

        if method == "GET":
            handle_get()
            return

        responder_json({
            "ok": False,
            "erro": "Método ainda não implementado.",
        }, status=405)

    except AssembleiaError as exc:
        responder_json({
            "ok": False,
            "erro": str(exc),
        }, status=400)

    except Exception:
        responder_json({
            "ok": False,
            "erro": "Erro interno ao consultar votação.",
        }, status=500)


if __name__ == "__main__":
    main()