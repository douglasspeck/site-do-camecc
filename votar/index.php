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
                animation: fadeUp .55s cubicolor-bezier(.22,1,.36,1) both;
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
                color: var(--color-text);
            }
            .vote-brand__tag {
                font-size: .7rem;
                font-weight: 600;
                letter-spacing: .12em;
                text-transform: uppercase;
                color: var(--color-accent);
                background: var(--color-accent-lo);
                border: 1px solid var(--color-accent-md);
                padding: 2px 8px;
                border-radius: 99px;
            }

            /* ── Card ──────────────────────────────────────────────── */
            .card {
                background: var(--color-surface);
                border: 1px solid var(--color-border);
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
                color: var(--color-muted);
                margin-bottom: .5rem;
            }
            .delib-title {
                font-family: var(--font-display);
                font-size: 1.35rem;
                line-height: 1.3;
                color: var(--color-text);
                margin-bottom: .5rem;
            }
            .delib-desc {
                font-size: .875rem;
                color: var(--color-muted);
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
                background: var(--color-accent-lo);
                border: 1px solid var(--color-accent-md);
                color: var(--color-accent);
            }
            .status-banner.success {
                background: rgba(46,204,138,.1);
                border: 1px solid rgba(46,204,138,.3);
                color: var(--color-success);
            }
            .status-banner.error {
                background: rgba(224,92,92,.1);
                border: 1px solid rgba(224,92,92,.3);
                color: var(--color-error);
            }
            .status-banner.warn {
                background: rgba(240,160,69,.1);
                border: 1px solid rgba(240,160,69,.3);
                color: var(--color-warn);
            }
            .status-banner svg { flex-shrink: 0; }

            /* ── Divider ───────────────────────────────────────────── */
            hr.divider {
                border: none;
                border-top: 1px solid var(--color-border);
                margin: 1.25rem 0;
            }

            /* ── Options ───────────────────────────────────────────── */
            .options-label {
                font-size: .7rem;
                font-weight: 600;
                letter-spacing: .12em;
                text-transform: uppercase;
                color: var(--color-muted);
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
                border: 1.5px solid var(--color-border);
                background: var(--color-bg);
                color: var(--color-muted);
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
                border-color: var(--color-accent);
                color: var(--color-text);
                background: var(--color-accent-lo);
            }
            .option-btn.selected {
                border-color: var(--color-accent);
                background: var(--color-accent-lo);
                color: var(--color-accent);
                box-shadow: 0 0 0 3px var(--color-accent-md);
            }
            .option-btn.selected::after {
                content: '✓';
                position: absolute;
                top: 4px; right: 7px;
                font-size: .65rem;
                color: var(--color-accent);
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
                color: var(--color-muted);
            }
            .field input[type="text"] {
                width: 100%;
                padding: .8rem 1rem;
                border-radius: var(--r-input);
                border: 1.5px solid var(--color-border);
                background: var(--color-bg);
                color: var(--color-text);
                font-family: var(--font-mono);
                font-size: 1.4rem;
                letter-spacing: .18em;
                text-align: center;
                transition: border-color .18s, box-shadow .18s;
                outline: none;
                -webkit-appearance: none;
            }
            .field input[type="text"]::placeholder { color: var(--color-muted); letter-spacing: .1em; font-size: 1rem; }
            .field input[type="text"]:focus {
                border-color: var(--color-accent);
                box-shadow: 0 0 0 3px var(--color-accent-md);
            }
            .field-hint {
                font-size: .75rem;
                color: var(--color-muted);
                text-align: center;
            }

            /* ── Submit ────────────────────────────────────────────── */
            .btn-submit {
                width: 100%;
                margin-top: 1.5rem;
                padding: .9rem 1.5rem;
                border-radius: var(--r-btn);
                border: none;
                background: var(--color-accent);
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
                color: var(--color-success);
                font-size: 1.8rem;
                animation: popIn .4s cubicolor-bezier(.34,1.56,.64,1) both;
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
                color: var(--color-muted);
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
                color: var(--color-muted);
            }

            /* ── Footer note ───────────────────────────────────────── */
            .vote-footer {
                text-align: center;
                font-size: .72rem;
                color: var(--color-muted);
                line-height: 1.6;
            }
            .vote-footer a { color: var(--color-muted); text-decoration: underline dotted; }

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
            <div class="vote-brand">
                <span class="vote-brand__name">CAMECC</span>
                <span class="vote-brand__tag">Assembleia</span>
            </div>

            <div class="card" id="main-card">
                <div id="screen-inactive">
                    <div class="clock-icon">🕐</div>
                    <h2>Nenhuma votação em andamento</h2>
                    <p id="inactive-msg">Aguarde a gestão abrir uma deliberação.</p>
                </div>

                <div id="screen-form" style="display:none">
                    <div class="delib-label">Deliberação em andamento</div>
                    <div class="delib-title" id="delib-titulo"></div>
                    <div class="delib-desc" id="delib-descricao"></div>

                    <hr class="divider">

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

                    <div id="pautas-container" style="margin-top: 1.5rem;"></div>
                </div>
            </div>

            <p class="vote-footer">
                Seu voto é <strong>anônimo</strong>. O código não é associado ao seu nome.<br>
                Dúvidas? Fale com a gestão do CAMECC.
            </p>
        </div>
    </main>

    <script src="votar.js"></script>
</body>
</html>