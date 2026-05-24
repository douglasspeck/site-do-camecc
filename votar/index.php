<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <title>CAMECC — Votação</title>

        <?php include('../assets/php/head.php'); ?>

        <meta name="author" content="CAMECC">
        <meta name="description" content="Sistema de votação das assembleias do CAMECC">
        <meta name="robots" content="noindex, nofollow">
        <link rel="canonical" href="https://ime.unicamp.br/~camecc/votar">

        <style>
            /* ── Tokens ────────────────────────────────────────────── */
            :root {
                --c-bg:        #0d0f14;
                --c-surface:   #13161e;
                --c-border:    #1f2333;
                --c-accent:    #4f6ef7;
                --c-accent-lo: rgba(79, 110, 247, 0.12);
                --c-accent-md: rgba(79, 110, 247, 0.30);
                --c-text:      #e8eaf2;
                --c-muted:     #5c6080;
                --c-success:   #2ecc8a;
                --c-error:     #e05c5c;
                --c-warn:      #f0a045;

                --r-card:  16px;
                --r-btn:   10px;
                --r-input: 10px;

                --shadow-card: 0 4px 32px rgba(0,0,0,.45);
                --shadow-glow: 0 0 40px rgba(79,110,247,.18);

                --font-display: 'DM Serif Display', Georgia, serif;
                --font-body:    'DM Sans', system-ui, sans-serif;
                --font-mono:    'JetBrains Mono', 'Fira Code', monospace;
            }

            /* ── Reset / Base ──────────────────────────────────────── */
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

            html { scroll-behavior: smooth; }

            body {
                background: var(--c-bg);
                color: var(--c-text);
                font-family: var(--font-body);
                font-size: 16px;
                line-height: 1.6;
                min-height: 100dvh;
                display: flex;
                flex-direction: column;
                -webkit-font-smoothing: antialiased;
            }

            /* Google Fonts */
            @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;600&display=swap');

            /* ── Layout ────────────────────────────────────────────── */
            main {
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem 1.25rem 4rem;
            }

            .vote-shell {
                width: 100%;
                max-width: 520px;
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                animation: fadeUp .55s cubic-bezier(.22,1,.36,1) both;
            }

            @keyframes fadeUp {
                from { opacity: 0; transform: translateY(22px); }
                to   { opacity: 1; transform: translateY(0);    }
            }

            /* ── Branding ──────────────────────────────────────────── */
            .vote-brand {
                display: flex;
                align-items: baseline;
                gap: .6rem;
                padding-bottom: .25rem;
            }
            .vote-brand__name {
                font-family: var(--font-display);
                font-size: 1.55rem;
                letter-spacing: -.01em;
                color: var(--c-text);
            }
            .vote-brand__tag {
                font-size: .7rem;
                font-weight: 600;
                letter-spacing: .12em;
                text-transform: uppercase;
                color: var(--c-accent);
                background: var(--c-accent-lo);
                border: 1px solid var(--c-accent-md);
                padding: 2px 8px;
                border-radius: 99px;
            }

            /* ── Card ──────────────────────────────────────────────── */
            .card {
                background: var(--c-surface);
                border: 1px solid var(--c-border);
                border-radius: var(--r-card);
                padding: 1.75rem;
                box-shadow: var(--shadow-card);
                position: relative;
                overflow: hidden;
            }
            .card::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg,
                    rgba(79,110,247,.05) 0%,
                    transparent 60%);
                pointer-events: none;
            }

            /* ── Deliberation header ───────────────────────────────── */
            .delib-label {
                font-size: .7rem;
                font-weight: 600;
                letter-spacing: .12em;
                text-transform: uppercase;
                color: var(--c-muted);
                margin-bottom: .5rem;
            }
            .delib-title {
                font-family: var(--font-display);
                font-size: 1.35rem;
                line-height: 1.3;
                color: var(--c-text);
                margin-bottom: .5rem;
            }
            .delib-desc {
                font-size: .875rem;
                color: var(--c-muted);
                line-height: 1.55;
            }

            /* ── Status banner ─────────────────────────────────────── */
            .status-banner {
                display: flex;
                align-items: center;
                gap: .65rem;
                padding: .9rem 1.2rem;
                border-radius: var(--r-card);
                font-size: .875rem;
                font-weight: 500;
            }
            .status-banner.info {
                background: var(--c-accent-lo);
                border: 1px solid var(--c-accent-md);
                color: var(--c-accent);
            }
            .status-banner.success {
                background: rgba(46,204,138,.1);
                border: 1px solid rgba(46,204,138,.3);
                color: var(--c-success);
            }
            .status-banner.error {
                background: rgba(224,92,92,.1);
                border: 1px solid rgba(224,92,92,.3);
                color: var(--c-error);
            }
            .status-banner.warn {
                background: rgba(240,160,69,.1);
                border: 1px solid rgba(240,160,69,.3);
                color: var(--c-warn);
            }
            .status-banner svg { flex-shrink: 0; }

            /* ── Divider ───────────────────────────────────────────── */
            hr.divider {
                border: none;
                border-top: 1px solid var(--c-border);
                margin: 1.25rem 0;
            }

            /* ── Options ───────────────────────────────────────────── */
            .options-label {
                font-size: .7rem;
                font-weight: 600;
                letter-spacing: .12em;
                text-transform: uppercase;
                color: var(--c-muted);
                margin-bottom: .75rem;
            }
            .options-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: .625rem;
            }
            .option-btn {
                position: relative;
                padding: .875rem .75rem;
                border-radius: var(--r-btn);
                border: 1.5px solid var(--c-border);
                background: var(--c-bg);
                color: var(--c-muted);
                font-family: var(--font-body);
                font-size: .85rem;
                font-weight: 500;
                cursor: pointer;
                text-align: center;
                transition: border-color .18s, color .18s, background .18s, transform .12s;
                -webkit-tap-highlight-color: transparent;
                text-transform: capitalize;
            }
            .option-btn:hover {
                border-color: var(--c-accent);
                color: var(--c-text);
                background: var(--c-accent-lo);
            }
            .option-btn.selected {
                border-color: var(--c-accent);
                background: var(--c-accent-lo);
                color: var(--c-accent);
                box-shadow: 0 0 0 3px var(--c-accent-md);
            }
            .option-btn.selected::after {
                content: '✓';
                position: absolute;
                top: 4px; right: 7px;
                font-size: .65rem;
                color: var(--c-accent);
                opacity: .7;
            }
            .option-btn:active { transform: scale(.97); }

            /* ── Code input ────────────────────────────────────────── */
            .field {
                display: flex;
                flex-direction: column;
                gap: .45rem;
                margin-top: 1.25rem;
            }
            .field label {
                font-size: .7rem;
                font-weight: 600;
                letter-spacing: .12em;
                text-transform: uppercase;
                color: var(--c-muted);
            }
            .field input[type="text"] {
                width: 100%;
                padding: .8rem 1rem;
                border-radius: var(--r-input);
                border: 1.5px solid var(--c-border);
                background: var(--c-bg);
                color: var(--c-text);
                font-family: var(--font-mono);
                font-size: 1.4rem;
                letter-spacing: .18em;
                text-align: center;
                transition: border-color .18s, box-shadow .18s;
                outline: none;
                -webkit-appearance: none;
            }
            .field input[type="text"]::placeholder { color: var(--c-muted); letter-spacing: .1em; font-size: 1rem; }
            .field input[type="text"]:focus {
                border-color: var(--c-accent);
                box-shadow: 0 0 0 3px var(--c-accent-md);
            }
            .field-hint {
                font-size: .75rem;
                color: var(--c-muted);
                text-align: center;
            }

            /* ── Submit ────────────────────────────────────────────── */
            .btn-submit {
                width: 100%;
                margin-top: 1.5rem;
                padding: .9rem 1.5rem;
                border-radius: var(--r-btn);
                border: none;
                background: var(--c-accent);
                color: #fff;
                font-family: var(--font-body);
                font-size: .95rem;
                font-weight: 600;
                letter-spacing: .02em;
                cursor: pointer;
                transition: opacity .18s, transform .12s, box-shadow .18s;
                box-shadow: 0 4px 20px rgba(79,110,247,.35);
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .5rem;
            }
            .btn-submit:hover:not(:disabled) {
                opacity: .9;
                box-shadow: 0 6px 28px rgba(79,110,247,.45);
            }
            .btn-submit:active:not(:disabled) { transform: scale(.98); }
            .btn-submit:disabled {
                opacity: .45;
                cursor: not-allowed;
                box-shadow: none;
            }

            /* Loading spinner */
            .spinner {
                width: 16px; height: 16px;
                border: 2.5px solid rgba(255,255,255,.35);
                border-top-color: #fff;
                border-radius: 50%;
                animation: spin .7s linear infinite;
                display: none;
            }
            @keyframes spin { to { transform: rotate(360deg); } }
            .loading .spinner { display: block; }
            .loading .btn-text { display: none; }

            /* ── Success screen ────────────────────────────────────── */
            #screen-success {
                text-align: center;
                padding: .5rem 0;
                display: none;
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }
            #screen-success .check-circle {
                width: 64px; height: 64px;
                background: rgba(46,204,138,.12);
                border: 2px solid rgba(46,204,138,.4);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--c-success);
                font-size: 1.8rem;
                animation: popIn .4s cubic-bezier(.34,1.56,.64,1) both;
            }
            @keyframes popIn {
                from { transform: scale(.4); opacity: 0; }
                to   { transform: scale(1);  opacity: 1; }
            }
            #screen-success h2 {
                font-family: var(--font-display);
                font-size: 1.4rem;
            }
            #screen-success p {
                font-size: .875rem;
                color: var(--c-muted);
                max-width: 320px;
            }

            /* ── Inactive screen ───────────────────────────────────── */
            #screen-inactive {
                text-align: center;
                padding: 1rem 0;
                display: none;
                flex-direction: column;
                align-items: center;
                gap: .75rem;
            }
            #screen-inactive .clock-icon {
                font-size: 2.4rem;
                animation: pulse 2.8s ease-in-out infinite;
            }
            @keyframes pulse {
                0%,100% { opacity: 1; }
                50%      { opacity: .45; }
            }
            #screen-inactive h2 {
                font-family: var(--font-display);
                font-size: 1.3rem;
            }
            #screen-inactive p {
                font-size: .875rem;
                color: var(--c-muted);
            }

            /* ── Footer note ───────────────────────────────────────── */
            .vote-footer {
                text-align: center;
                font-size: .72rem;
                color: var(--c-muted);
                line-height: 1.6;
            }
            .vote-footer a { color: var(--c-muted); text-decoration: underline dotted; }

            /* ── Responsive ────────────────────────────────────────── */
            @media (max-width: 480px) {
                .card { padding: 1.35rem; }
                .delib-title { font-size: 1.2rem; }
                .field input[type="text"] { font-size: 1.25rem; }
            }
        </style>
    </head>
    <body id="votar-page">
        <?php include '../assets/php/header.php'; ?>

        <main>
            <div class="vote-shell">

                <!-- Branding -->
                <div class="vote-brand">
                    <span class="vote-brand__name">CAMECC</span>
                    <span class="vote-brand__tag">Assembleia</span>
                </div>

                <!-- Card principal -->
                <div class="card" id="main-card">

                    <!-- Tela: inativa (sem deliberação aberta) -->
                    <div id="screen-inactive">
                        <div class="clock-icon">🕐</div>
                        <h2>Nenhuma votação em andamento</h2>
                        <p id="inactive-msg">Aguarde a gestão abrir uma deliberação.</p>
                    </div>

                    <!-- Tela: formulário de voto -->
                    <div id="screen-form" style="display:none">
                        <div class="delib-label">Deliberação em votação</div>
                        <div class="delib-title" id="delib-titulo"></div>
                        <div class="delib-desc"  id="delib-descricao"></div>

                        <hr class="divider">

                        <div class="options-label">Escolha sua opção</div>
                        <div class="options-grid" id="options-grid"></div>

                        <div class="field">
                            <label for="codigo-input">Seu código de votação</label>
                            <input
                                type="text"
                                id="codigo-input"
                                inputmode="numeric"
                                maxlength="8"
                                placeholder="00000000"
                                autocomplete="off"
                                autocorrect="off"
                                spellcheck="false"
                            >
                            <span class="field-hint">Código de 8 dígitos recebido por e-mail</span>
                        </div>

                        <div id="form-feedback" style="display:none; margin-top:.85rem;"></div>

                        <button class="btn-submit" id="btn-votar" disabled>
                            <div class="spinner"></div>
                            <span class="btn-text">Registrar voto</span>
                        </button>
                    </div>

                    <!-- Tela: sucesso -->
                    <div id="screen-success">
                        <div class="check-circle">✓</div>
                        <h2>Voto registrado!</h2>
                        <p>Seu voto foi contabilizado de forma anônima. Obrigado por participar.</p>
                    </div>

                </div><!-- /.card -->

                <!-- Nota de rodapé -->
                <p class="vote-footer">
                    Seu voto é <strong>anônimo</strong>. O código não é associado ao seu nome.<br>
                    Dúvidas? Fale com a gestão do CAMECC.
                </p>

            </div><!-- /.vote-shell -->
        </main>

        <footer></footer>

        <script>
        (() => {
            'use strict';

            const API = 'processar_voto.py';
            const POLL_INTERVAL = 8000; // ms entre verificações de estado

            // ── Elementos ────────────────────────────────────────────
            const screenInactive = document.getElementById('screen-inactive');
            const screenForm     = document.getElementById('screen-form');
            const screenSuccess  = document.getElementById('screen-success');
            const inactiveMsg    = document.getElementById('inactive-msg');
            const deliberTitulo  = document.getElementById('delib-titulo');
            const deliberDesc    = document.getElementById('delib-descricao');
            const optionsGrid    = document.getElementById('options-grid');
            const codigoInput    = document.getElementById('codigo-input');
            const btnVotar       = document.getElementById('btn-votar');
            const formFeedback   = document.getElementById('form-feedback');

            let estadoAtual = null;
            let opcaoSelecionada = null;
            let votando = false;
            let pollingTimer = null;

            // ── Utilitários ──────────────────────────────────────────
            function showScreen(name) {
                screenInactive.style.display = name === 'inactive' ? 'flex' : 'none';
                screenForm.style.display     = name === 'form'     ? 'block' : 'none';
                screenSuccess.style.display  = name === 'success'  ? 'flex' : 'none';
            }

            function showFeedback(msg, type) {
                // type: 'error' | 'warn' | 'info'
                const icons = {
                    error: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M8 4.5v4M8 11h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                    warn:  '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2L14.5 13.5H1.5L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M8 6.5v3.5M8 12h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                    info:  '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M8 7v4M8 5h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                };
                formFeedback.innerHTML = `<div class="status-banner ${type}">${icons[type] || ''}<span>${msg}</span></div>`;
                formFeedback.style.display = 'block';
            }

            function hideFeedback() {
                formFeedback.style.display = 'none';
                formFeedback.innerHTML = '';
            }

            function setLoading(on) {
                votando = on;
                btnVotar.classList.toggle('loading', on);
                btnVotar.disabled = on;
                codigoInput.disabled = on;
                document.querySelectorAll('.option-btn').forEach(b => b.disabled = on);
            }

            // ── Lógica de opções ─────────────────────────────────────
            function buildOptions(opcoes) {
                optionsGrid.innerHTML = '';
                opcaoSelecionada = null;
                opcoes.forEach(op => {
                    const btn = document.createElement('button');
                    btn.className = 'option-btn';
                    btn.textContent = op;
                    btn.type = 'button';
                    btn.dataset.op = op;
                    btn.addEventListener('click', () => selectOption(op));
                    optionsGrid.appendChild(btn);
                });
            }

            function selectOption(op) {
                if (votando) return;
                opcaoSelecionada = op;
                document.querySelectorAll('.option-btn').forEach(b => {
                    b.classList.toggle('selected', b.dataset.op === op);
                });
                updateSubmitState();
                hideFeedback();
            }

            function updateSubmitState() {
                const codigoOk = /^\d{8}$/.test(codigoInput.value.trim());
                btnVotar.disabled = !(opcaoSelecionada && codigoOk);
            }

            // ── Polling de estado ────────────────────────────────────
            async function fetchEstado() {
                try {
                    const res = await fetch(API + '?_=' + Date.now(), { cache: 'no-store' });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return await res.json();
                } catch {
                    return null;
                }
            }

            function aplicarEstado(data) {
                if (!data || !data.ok) {
                    showScreen('inactive');
                    inactiveMsg.textContent = 'Não foi possível conectar ao servidor.';
                    return;
                }

                const mudouDelib =
                    !estadoAtual ||
                    estadoAtual.deliberacao?.id !== data.deliberacao?.id;

                estadoAtual = data;

                if (!data.ativa || !data.deliberacao) {
                    showScreen('inactive');
                    inactiveMsg.textContent = data.mensagem || 'Aguarde a gestão abrir uma deliberação.';
                    return;
                }

                // Se a deliberação mudou, limpa seleção e feedback
                if (mudouDelib) {
                    deliberTitulo.textContent  = data.deliberacao.titulo;
                    deliberDesc.textContent    = data.deliberacao.descricao || '';
                    buildOptions(data.deliberacao.opcoes || []);
                    codigoInput.value = '';
                    hideFeedback();
                }

                showScreen('form');
            }

            async function poll() {
                const data = await fetchEstado();
                aplicarEstado(data);
            }

            function startPolling() {
                poll();
                pollingTimer = setInterval(poll, POLL_INTERVAL);
            }

            // ── Envio do voto ────────────────────────────────────────
            async function submitVoto() {
                if (votando) return;
                const codigo = codigoInput.value.trim();
                if (!/^\d{8}$/.test(codigo)) {
                    showFeedback('Digite seu código de 8 dígitos.', 'warn');
                    codigoInput.focus();
                    return;
                }
                if (!opcaoSelecionada) {
                    showFeedback('Selecione uma opção antes de votar.', 'warn');
                    return;
                }

                setLoading(true);
                hideFeedback();

                try {
                    const res = await fetch(API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ codigo, voto: opcaoSelecionada }),
                    });
                    const json = await res.json();

                    if (json.ok) {
                        clearInterval(pollingTimer);
                        showScreen('success');
                    } else {
                        setLoading(false);
                        showFeedback(json.erro || 'Não foi possível registrar o voto.', 'error');
                    }
                } catch {
                    setLoading(false);
                    showFeedback('Erro de conexão. Tente novamente.', 'error');
                }
            }

            // ── Eventos ──────────────────────────────────────────────
            codigoInput.addEventListener('input', () => {
                // Deixa apenas dígitos
                codigoInput.value = codigoInput.value.replace(/\D/g, '').slice(0, 8);
                updateSubmitState();
                hideFeedback();
            });

            btnVotar.addEventListener('click', submitVoto);

            // Enter no campo de código também submete
            codigoInput.addEventListener('keydown', e => {
                if (e.key === 'Enter' && !btnVotar.disabled) submitVoto();
            });

            // ── Init ─────────────────────────────────────────────────
            startPolling();

        })();
        </script>
    </body>
</html>