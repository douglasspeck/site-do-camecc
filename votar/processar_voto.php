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

    return $root . '/delibera/api';
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

function normalizar_estado_publico(array $response): array
{
    $publico = $response['publico'] ?? $response;

    if (!is_array($publico)) {
        $publico = [];
    }

    $deliberacao = $publico['deliberacao'] ?? null;
    if (!is_array($deliberacao)) {
        $deliberacao = null;
    }

    $pautas_ativas = $publico['pautas_ativas'] ?? [];
    if (!is_array($pautas_ativas)) {
        $pautas_ativas = [];
    }

    if ($pautas_ativas === [] && is_array($deliberacao)) {
        if (isset($deliberacao['pautas_ativas']) && is_array($deliberacao['pautas_ativas'])) {
            $pautas_ativas = array_values(array_filter($deliberacao['pautas_ativas'], 'is_array'));
        } elseif (isset($deliberacao['pauta_ativa']) && is_array($deliberacao['pauta_ativa'])) {
            $pautas_ativas = [$deliberacao['pauta_ativa']];
        }
    }

    return [
        'ok' => (bool)($publico['ok'] ?? $response['ok'] ?? true),
        'ativa' => (bool)($publico['ativa'] ?? false),
        'mensagem' => (string)($publico['mensagem'] ?? ''),
        'lista_ativa' => $publico['lista_ativa'] ?? $publico['lista'] ?? null,
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

    $delibPath = deliberacoes_path($lista_ativa);
    if (!is_file($delibPath)) {
        return [
            'ok' => true,
            'ativa' => false,
            'mensagem' => 'Dados da deliberação não encontrados.',
            'lista_ativa' => $lista_ativa,
            'deliberacao' => null,
            'pautas_ativas' => [],
        ];
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
        return [
            'ok' => true,
            'ativa' => false,
            'mensagem' => 'Deliberação não encontrada.',
            'lista_ativa' => $lista_ativa,
            'deliberacao' => null,
            'pautas_ativas' => [],
        ];
    }

    if (!($deliberacao['aberta'] ?? false)) {
        return [
            'ok' => true,
            'ativa' => false,
            'mensagem' => 'Esta deliberação está encerrada.',
            'lista_ativa' => $lista_ativa,
            'deliberacao' => $deliberacao,
            'pautas_ativas' => [],
        ];
    }

    $pautasAtivas = extrair_pautas_ativas($deliberacao);

    return [
        'ok' => true,
        'ativa' => true,
        'mensagem' => (string)($deliberacao['mensagem'] ?? 'Deliberação ativa.'),
        'lista_ativa' => $lista_ativa,
        'deliberacao' => $deliberacao,
        'pautas_ativas' => $pautasAtivas,
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