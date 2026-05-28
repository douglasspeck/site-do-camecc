<?php
declare(strict_types=1);

final class AssembleiaError extends Exception
{
}

/* =========================================================
 * Environment / paths
 * ========================================================= */

function project_root(): string
{
    return dirname(__DIR__, 2);
}

function resolve_data_base_dir(): string
{
    $root = project_root();

    $candidates = [
        // Layout real do backend Python (app/caminhos.py → DATA_DIR = dados/).
        $root . '/delibera/dados',
        $root . '/sistemas/delibera/dados',
        '/home/sistemas/delibera/dados',
        '/home/public_html/sistemas/delibera/dados',

        // Layouts antigos (compatibilidade).
        $root . '/delibera/api',
        $root . '/sistemas/delibera/api',
        '/home/sistemas/delibera/api',
        '/home/public_html/sistemas/delibera/api',
    ];

    foreach ($candidates as $candidate) {
        if (is_dir($candidate)) {
            return $candidate;
        }
    }

    return $root . '/delibera/dados';
}

function data_base_dir(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $cached = resolve_data_base_dir();
    return $cached;
}

function estado_path(): string
{
    return data_base_dir() . DIRECTORY_SEPARATOR . 'estado.json';
}

function listas_dir(): string
{
    return data_base_dir() . DIRECTORY_SEPARATOR . 'listas';
}

function deliberacoes_dir(): string
{
    return data_base_dir() . DIRECTORY_SEPARATOR . 'deliberacoes';
}

function votacao_dir(string $listaAtiva): string
{
    return data_base_dir()
        . DIRECTORY_SEPARATOR . 'votacao'
        . DIRECTORY_SEPARATOR . $listaAtiva;
}

function deliberacoes_path(string $listaAtiva): string
{
    return deliberacoes_dir() . DIRECTORY_SEPARATOR . $listaAtiva . '.json';
}

/**
 * Caminho das pautas no layout do backend Python:
 * dados/deliberacoes/<deliberacao>/pautas.json
 */
function pautas_file(string $deliberacao): string
{
    return deliberacoes_dir()
        . DIRECTORY_SEPARATOR . $deliberacao
        . DIRECTORY_SEPARATOR . 'pautas.json';
}

function votos_path(string $listaAtiva): string
{
    return votacao_dir($listaAtiva) . DIRECTORY_SEPARATOR . 'votos.json';
}

function usados_path(string $listaAtiva): string
{
    return votacao_dir($listaAtiva) . DIRECTORY_SEPARATOR . 'usados.json';
}

/* =========================================================
 * HTTP helpers
 * ========================================================= */

function responder_json(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function ler_body(): string
{
    $raw = file_get_contents('php://input');
    return $raw === false ? '' : $raw;
}

function agora_iso(): string
{
    $tz = new DateTimeZone('America/Sao_Paulo');
    return (new DateTimeImmutable('now', $tz))->format('Y-m-d\TH:i:sP');
}

/* =========================================================
 * JSON / HTTP helpers
 * ========================================================= */

function load_json(string $path, mixed $default = []): mixed
{
    if (!is_file($path)) {
        return $default;
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return $default;
    }

    try {
        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return $default;
    }
}

function save_json_atomic(string $path, mixed $data): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException("Não foi possível criar o diretório: {$dir}");
    }

    try {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
        );
    } catch (JsonException $e) {
        throw new RuntimeException('Não foi possível serializar JSON.', 0, $e);
    }

    $tmpPath = tempnam($dir, 'tmp_');
    if ($tmpPath === false) {
        throw new RuntimeException("Não foi possível criar arquivo temporário para: {$path}");
    }

    $bytes = file_put_contents($tmpPath, $json, LOCK_EX);
    if ($bytes === false) {
        @unlink($tmpPath);
        throw new RuntimeException("Não foi possível gravar arquivo temporário para: {$path}");
    }

    if (!rename($tmpPath, $path)) {
        @unlink($tmpPath);
        throw new RuntimeException("Não foi possível substituir o arquivo: {$path}");
    }
}

function fetch_json_http(string $url): ?array
{
    $raw = null;
    $httpCode = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch !== false) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
            ]);

            $raw = curl_exec($ch);
            if ($raw !== false) {
                $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            }

            curl_close($ch);
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 4,
                'header' => "Accept: application/json\r\n",
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        $httpCode = 200;
    }

    if ($raw === false || $raw === null || trim($raw) === '') {
        return null;
    }

    if ($httpCode !== 0 && ($httpCode < 200 || $httpCode >= 300)) {
        return null;
    }

    try {
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return null;
    }

    return is_array($decoded) ? $decoded : null;
}

/* =========================================================
 * State helpers
 * ========================================================= */

function load_estado(): array
{
    $estado = load_json(estado_path(), []);
    return is_array($estado) ? $estado : [];
}

function extrair_pautas_ativas(array $deliberacao): array
{
    if (isset($deliberacao['pautas_ativas']) && is_array($deliberacao['pautas_ativas'])) {
        return array_values(array_filter($deliberacao['pautas_ativas'], 'is_array'));
    }

    if (isset($deliberacao['pauta_ativa']) && is_array($deliberacao['pauta_ativa'])) {
        return [$deliberacao['pauta_ativa']];
    }

    return [];
}

/**
 * Converte um slug ("assembleia-geral-2026") em um título legível
 * ("Assembleia Geral 2026") para exibição na página de votação.
 */
function slug_para_titulo(string $slug): string
{
    $texto = str_replace(['-', '_'], ' ', $slug);
    $texto = trim(preg_replace('/\s+/', ' ', $texto));

    if ($texto === '') {
        return $slug;
    }

    // Usa mbstring se disponível; caso contrário, ucwords (slugs são ASCII).
    // Evita "Call to undefined function mb_convert_case()" em PHP sem mbstring.
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords($texto);
}

/**
 * Normaliza uma lista de pautas para o formato consumido pelo votar.js,
 * descartando pautas encerradas e garantindo os campos esperados.
 */
function normalizar_pautas($pautas): array
{
    if (!is_array($pautas)) {
        return [];
    }

    $saida = [];

    foreach ($pautas as $pauta) {
        if (!is_array($pauta)) {
            continue;
        }

        $status = strtolower((string)($pauta['status'] ?? 'aberta'));

        if (in_array($status, ['fechada', 'encerrada'], true)) {
            continue;
        }

        $opcoes = $pauta['opcoes'] ?? [];
        if (!is_array($opcoes)) {
            $opcoes = [];
        }

        $opcoes = array_values(array_filter(
            array_map(
                static fn($o) => is_string($o) || is_numeric($o) ? (string)$o : null,
                $opcoes
            ),
            static fn($o) => $o !== null && $o !== ''
        ));

        $saida[] = [
            'id' => $pauta['id'] ?? null,
            'titulo' => (string)($pauta['titulo'] ?? ''),
            'descricao' => (string)($pauta['descricao'] ?? ''),
            'opcoes' => $opcoes,
            'limite' => (int)($pauta['limite'] ?? 1),
            'status' => $status !== '' ? $status : 'aberta',
        ];
    }

    return $saida;
}

/**
 * Mapeia o estado público (do backend Python ou do fallback de arquivos)
 * para o contrato único consumido pelo votar.js:
 *   { ok, ativa, mensagem, lista_ativa, deliberacao:{id,titulo,descricao}|null, pautas_ativas[] }
 *
 * Tolera dois formatos de "deliberacao":
 *   - string (slug) → modelo atual do backend Python;
 *   - objeto {titulo, descricao, ...} → modelo antigo.
 */
function normalizar_estado_publico(array $response): array
{
    $publico = $response['publico'] ?? $response;

    if (!is_array($publico)) {
        $publico = [];
    }

    $ok = (bool)($publico['ok'] ?? $response['ok'] ?? true);
    $ativa = (bool)($publico['ativa'] ?? false);

    // "lista" (novo) ou "lista_ativa" (antigo).
    $lista_ativa = $publico['lista_ativa'] ?? $publico['lista'] ?? null;

    $delibRaw = $publico['deliberacao'] ?? null;

    $deliberacao = null;

    if (is_array($delibRaw)) {
        $idDelib = $delibRaw['id'] ?? $lista_ativa ?? '';
        $deliberacao = [
            'id' => $idDelib,
            'titulo' => (string)($delibRaw['titulo'] ?? slug_para_titulo((string)$idDelib)),
            'descricao' => (string)($delibRaw['descricao'] ?? ''),
        ];
    } elseif (is_string($delibRaw) && trim($delibRaw) !== '') {
        $deliberacao = [
            'id' => $delibRaw,
            'titulo' => slug_para_titulo($delibRaw),
            'descricao' => '',
        ];
    }

    // Pautas: "pautas" (novo), "pautas_ativas" (antigo) ou aninhadas na deliberação.
    $pautasRaw = $publico['pautas'] ?? $publico['pautas_ativas'] ?? [];

    if ((!is_array($pautasRaw) || $pautasRaw === []) && is_array($delibRaw)) {
        if (isset($delibRaw['pautas']) && is_array($delibRaw['pautas'])) {
            $pautasRaw = $delibRaw['pautas'];
        } elseif (isset($delibRaw['pautas_ativas']) && is_array($delibRaw['pautas_ativas'])) {
            $pautasRaw = $delibRaw['pautas_ativas'];
        } elseif (isset($delibRaw['pauta_ativa']) && is_array($delibRaw['pauta_ativa'])) {
            $pautasRaw = [$delibRaw['pauta_ativa']];
        }
    }

    $pautas_ativas = normalizar_pautas($pautasRaw);

    return [
        'ok' => $ok,
        'ativa' => $ativa && $deliberacao !== null,
        'mensagem' => (string)($publico['mensagem'] ?? ''),
        'lista_ativa' => $lista_ativa,
        'deliberacao' => $deliberacao,
        'pautas_ativas' => $pautas_ativas,
    ];
}

function build_public_state_from_files(): array
{
    $estado = load_estado();

    $lista_ativa = trim((string)($estado['lista_ativa'] ?? ''));
    $deliberacao_atual = trim((string)($estado['deliberacao_atual'] ?? ''));

    if ($lista_ativa === '' || $deliberacao_atual === '') {
        return [
            'ok' => true,
            'ativa' => false,
            'mensagem' => 'Aguarde a gestão abrir uma deliberação.',
            'lista_ativa' => $lista_ativa !== '' ? $lista_ativa : null,
            'deliberacao' => null,
            'pautas_ativas' => [],
        ];
    }

    // Backend Python: dados/deliberacoes/<deliberacao>/pautas.json (lista achatada).
    $pautasFile = pautas_file($deliberacao_atual);

    $pautas = is_file($pautasFile)
        ? load_json($pautasFile, [])
        : [];

    if (!is_array($pautas)) {
        $pautas = [];
    }

    return [
        'ok' => true,
        'ativa' => true,
        'mensagem' => 'Deliberação ativa.',
        'lista_ativa' => $lista_ativa,
        'deliberacao' => [
            'id' => $deliberacao_atual,
            'titulo' => slug_para_titulo($deliberacao_atual),
            'descricao' => '',
        ],
        'pautas_ativas' => normalizar_pautas($pautas),
    ];
}

function get_estado_publico(): array
{
    $urls = [
        'http://127.0.0.1:3000/api/estado',
        'http://localhost:3000/api/estado',
    ];

    foreach ($urls as $url) {
        $remote = fetch_json_http($url);
        if (is_array($remote)) {
            return normalizar_estado_publico($remote);
        }
    }

    return build_public_state_from_files();
}

/* =========================================================
 * Request handlers
 * ========================================================= */

function handle_get(): void
{
    responder_json(get_estado_publico(), 200);
}

function handle_post(): void
{
    $raw = ler_body();
    if (trim($raw) === '') {
        throw new AssembleiaError('Corpo da requisição vazio.');
    }

    try {
        $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        throw new AssembleiaError('JSON inválido no corpo da requisição.');
    }

    if (!is_array($payload)) {
        throw new AssembleiaError('JSON inválido no corpo da requisição.');
    }

    $codigo = trim((string)($payload['codigo'] ?? ''));
    $voto = trim((string)($payload['voto'] ?? ''));

    if ($codigo === '') {
        throw new AssembleiaError("Campo 'codigo' é obrigatório.");
    }
    if ($voto === '') {
        throw new AssembleiaError("Campo 'voto' é obrigatório.");
    }

    if (!ctype_digit($codigo) || strlen($codigo) !== 8) {
        throw new AssembleiaError('Código inválido.');
    }

    $estado = load_estado();
    $lista_ativa = $estado['lista_ativa'] ?? null;
    $deliberacao_atual = $estado['deliberacao_atual'] ?? null;

    if (!$lista_ativa) {
        throw new AssembleiaError('Nenhuma lista ativa no momento.');
    }
    if (!$deliberacao_atual) {
        throw new AssembleiaError('Nenhuma deliberação ativa no momento.');
    }

    $delibPath = deliberacoes_path((string)$lista_ativa);
    if (!is_file($delibPath)) {
        throw new AssembleiaError('Dados da deliberação não encontrados.');
    }

    $deliberacoesData = load_json($delibPath, ['deliberacoes' => []]);
    if (!is_array($deliberacoesData)) {
        $deliberacoesData = ['deliberacoes' => []];
    }

    $deliberacao = null;
    foreach (($deliberacoesData['deliberacoes'] ?? []) as $item) {
        if (is_array($item) && (($item['id'] ?? null) == $deliberacao_atual)) {
            $deliberacao = $item;
            break;
        }
    }

    if ($deliberacao === null) {
        throw new AssembleiaError('Deliberação não encontrada.');
    }

    if (!($deliberacao['aberta'] ?? false)) {
        throw new AssembleiaError('Esta deliberação está encerrada.');
    }

    $pauta_ativa = $deliberacao['pauta_ativa'] ?? null;
    if (!is_array($pauta_ativa) || empty($pauta_ativa)) {
        throw new AssembleiaError('Nenhuma pauta ativa no momento.');
    }

    $opcoes_validas = $pauta_ativa['opcoes'] ?? [];
    if (!is_array($opcoes_validas)) {
        $opcoes_validas = [];
    }

    if (!in_array($voto, $opcoes_validas, true)) {
        throw new AssembleiaError(
            'Opção de voto inválida. Opções permitidas: ' . implode(', ', $opcoes_validas) . '.'
        );
    }

    $lockPath = votacao_dir((string)$lista_ativa) . DIRECTORY_SEPARATOR . '.lock';
    $lockDir = dirname($lockPath);

    if (!is_dir($lockDir) && !mkdir($lockDir, 0775, true) && !is_dir($lockDir)) {
        throw new RuntimeException("Não foi possível criar o diretório de votação: {$lockDir}");
    }

    $lockFile = fopen($lockPath, 'c+');
    if ($lockFile === false) {
        throw new RuntimeException('Não foi possível abrir o arquivo de lock.');
    }

    try {
        if (!flock($lockFile, LOCK_EX)) {
            throw new RuntimeException('Não foi possível obter o lock da votação.');
        }

        $listaPath = listas_dir() . DIRECTORY_SEPARATOR . $lista_ativa . '.json';
        if (!is_file($listaPath)) {
            throw new AssembleiaError('Lista de presença não encontrada.');
        }

        $listaData = load_json($listaPath, ['presentes' => [], 'codigos' => []]);
        if (!is_array($listaData)) {
            $listaData = ['presentes' => [], 'codigos' => []];
        }

        $codigosLista = $listaData['codigos'] ?? [];
        if (!is_array($codigosLista)) {
            $codigosLista = [];
        }

        if (!in_array($codigo, $codigosLista, true)) {
            throw new AssembleiaError('Código não encontrado.');
        }

        $pautaId = $pauta_ativa['id'] ?? null;
        if ($pautaId === null || $pautaId === '') {
            throw new AssembleiaError('Pauta ativa inválida.');
        }

        $usadosData = load_json(usados_path((string)$lista_ativa), []);
        if (!is_array($usadosData)) {
            $usadosData = [];
        }

        $usadosDaPauta = $usadosData[$pautaId] ?? [];
        if (!is_array($usadosDaPauta)) {
            $usadosDaPauta = [];
        }

        if (in_array($codigo, $usadosDaPauta, true)) {
            throw new AssembleiaError('Este código já votou nesta pauta.');
        }

        $votosData = load_json(votos_path((string)$lista_ativa), []);
        if (!is_array($votosData)) {
            $votosData = [];
        }

        $votosDaPauta = $votosData[$pautaId] ?? [];
        if (!is_array($votosDaPauta)) {
            $votosDaPauta = [];
        }

        $votosDaPauta[] = [
            'voto' => $voto,
            'registrado_em' => agora_iso(),
        ];
        $votosData[$pautaId] = $votosDaPauta;

        $usadosDaPauta[] = $codigo;
        $usadosData[$pautaId] = $usadosDaPauta;

        save_json_atomic(votos_path((string)$lista_ativa), $votosData);
        save_json_atomic(usados_path((string)$lista_ativa), $usadosData);
    } finally {
        flock($lockFile, LOCK_UN);
        fclose($lockFile);
    }

    responder_json([
        'ok' => true,
        'mensagem' => 'Voto registrado com sucesso.',
    ], 200);
}

/* =========================================================
 * Main
 * ========================================================= */

try {
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

    if ($method === 'GET') {
        handle_get();
    } elseif ($method === 'POST') {
        handle_post();
    } else {
        responder_json([
            'ok' => false,
            'erro' => 'Método não permitido.',
        ], 405);
    }
} catch (AssembleiaError $e) {
    responder_json([
        'ok' => false,
        'erro' => $e->getMessage(),
    ], 400);
} catch (Throwable) {
    responder_json([
        'ok' => false,
        'erro' => 'Erro interno ao processar voto.',
    ], 500);
}