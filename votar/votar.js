(() => {
    "use strict";

    const API = "processar_voto.php";
    const POLL_INTERVAL = 8000;

    const screenInactive = document.getElementById("screen-inactive");
    const screenForm = document.getElementById("screen-form");
    const inactiveMsg = document.getElementById("inactive-msg");
    const deliberTitulo = document.getElementById("delib-titulo");
    const deliberDesc = document.getElementById("delib-descricao");
    const pautasContainer = document.getElementById("pautas-container");
    const codigoInput = document.getElementById("codigo-input");
    const formFeedback = document.getElementById("form-feedback");

    let estadoAtual = null;
    let pollingTimer = null;

    const selecoes = new Map();
    const votados = new Set();
    const cards = new Map();

    function showScreen(name) {
        screenInactive.style.display = name === "inactive" ? "flex" : "none";
        screenForm.style.display = name === "form" ? "block" : "none";
    }

    function showFeedback(msg, type) {
        formFeedback.innerHTML = `<div class="status-banner ${type}">${msg}</div>`;
        formFeedback.style.display = "block";
    }

    function hideFeedback() {
        formFeedback.style.display = "none";
        formFeedback.innerHTML = "";
    }

    function somenteDigitos(valor) {
        return valor.replace(/\D/g, "").slice(0, 8);
    }

    function codigoValido() {
        return /^\d{8}$/.test(codigoInput.value.trim());
    }

    function getSelecao(pautaId) {
        if (!selecoes.has(pautaId)) {
            selecoes.set(pautaId, new Set());
        }
        return selecoes.get(pautaId);
    }

    function atualizarEstadoCard(pautaId) {
        const card = cards.get(pautaId);
        if (!card) return;

        const { optionButtons, voteButton, selectionCount, statusText, limite } = card;
        const selecionados = getSelecao(pautaId);

        selectionCount.textContent = `${selecionados.size}/${limite}`;

        const podeVotar = !votados.has(pautaId) && codigoValido() && selecionados.size > 0;
        voteButton.disabled = !podeVotar;

        optionButtons.forEach(btn => {
            btn.classList.toggle("selected", selecionados.has(btn.dataset.opcao));
            btn.disabled = votados.has(pautaId);
        });

        if (votados.has(pautaId)) {
            statusText.textContent = "Voto registrado";
            voteButton.textContent = "Registrado";
            voteButton.disabled = true;
        }
    }

    function selecionarOpcao(pautaId, opcao, limite) {
        if (votados.has(pautaId)) return;

        const selecionados = getSelecao(pautaId);

        /**
         * 🔥 IMPORTANTE:
         * Backend agora aceita APENAS UM voto.
         * Então ignoramos multi-seleção e mantemos apenas 1 item.
         */
        selecionados.clear();
        selecionados.add(opcao);

        hideFeedback();
        atualizarEstadoCard(pautaId);
    }

    function criarCardPauta(pauta) {
        const card = document.createElement("article");
        card.className = "card-pauta";

        const titulo = document.createElement("h3");
        titulo.textContent = pauta.titulo || "Pauta sem título";

        const descricao = document.createElement("p");
        descricao.textContent = pauta.descricao || "";

        const optionsGrid = document.createElement("div");
        optionsGrid.className = "options-grid";

        const selectionCount = document.createElement("span");

        const statusText = document.createElement("div");
        statusText.className = "status-banner info";
        statusText.textContent = "Aguardando voto";

        const voteButton = document.createElement("button");
        voteButton.type = "button";
        voteButton.textContent = "Registrar voto";

        const optionButtons = [];

        (pauta.opcoes || []).forEach(opcao => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "option-btn";
            btn.textContent = opcao;
            btn.dataset.opcao = opcao;

            btn.addEventListener("click", () => {
                selecionarOpcao(pauta.id, opcao, pauta.limite || 1);
            });

            optionButtons.push(btn);
            optionsGrid.appendChild(btn);
        });

        voteButton.addEventListener("click", async () => {
            await registrarVoto(pauta);
        });

        card.appendChild(titulo);
        card.appendChild(descricao);
        card.appendChild(optionsGrid);
        card.appendChild(selectionCount);
        card.appendChild(statusText);
        card.appendChild(voteButton);

        cards.set(pauta.id, {
            card,
            optionButtons,
            voteButton,
            selectionCount,
            statusText,
            limite: pauta.limite || 1,
        });

        return card;
    }

    function renderizarPautas(pautas) {
        pautasContainer.innerHTML = "";
        cards.clear();

        if (!pautas.length) {
            pautasContainer.textContent = "Nenhuma pauta ativa no momento.";
            return;
        }

        pautas.forEach(pauta => {
            if (!selecoes.has(pauta.id)) {
                selecoes.set(pauta.id, new Set());
            }

            const card = criarCardPauta(pauta);
            pautasContainer.appendChild(card);
            atualizarEstadoCard(pauta.id);
        });
    }

    async function buscarEstado() {
        try {
            const res = await fetch(`${API}?_=${Date.now()}`, {
                cache: "no-store",
            });

            if (!res.ok) throw new Error();

            return await res.json();
        } catch {
            return null;
        }
    }

    function aplicarEstado(data) {
        if (!data || !data.ok) {
            showScreen("inactive");
            inactiveMsg.textContent = "Não foi possível conectar ao servidor.";
            return;
        }

        if (!data.ativa || !data.deliberacao) {
            showScreen("inactive");
            inactiveMsg.textContent = data.mensagem || "Aguarde abertura da votação.";
            return;
        }

        estadoAtual = data;

        deliberTitulo.textContent = data.deliberacao.titulo || "";
        deliberDesc.textContent = data.deliberacao.descricao || "";

        showScreen("form");

        renderizarPautas(data.pautas_ativas || []);
    }

    async function poll() {
        const data = await buscarEstado();
        aplicarEstado(data);
    }

    async function registrarVoto(pauta) {
        const codigo = codigoInput.value.trim();
        const selecionados = Array.from(getSelecao(pauta.id));

        if (!/^\d{8}$/.test(codigo)) {
            showFeedback("Código inválido.", "warn");
            return;
        }

        if (!selecionados.length) {
            showFeedback("Selecione uma opção.", "warn");
            return;
        }

        const voto = selecionados[0]; // 🔥 compatível com backend PHP

        const card = cards.get(pauta.id);
        card.voteButton.disabled = true;

        try {
            const res = await fetch(API, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    codigo,
                    voto, // 🔥 agora compatível com PHP
                }),
            });

            const json = await res.json();

            if (!res.ok || !json.ok) {
                throw new Error(json.erro || "Erro ao registrar voto.");
            }

            votados.add(pauta.id);
            showFeedback("Voto registrado com sucesso.", "success");

            atualizarEstadoCard(pauta.id);

        } catch (err) {
            showFeedback(err.message || "Erro de conexão.", "error");
        } finally {
            atualizarEstadoCard(pauta.id);
        }
    }

    codigoInput.addEventListener("input", () => {
        codigoInput.value = somenteDigitos(codigoInput.value);
        hideFeedback();
        codigoInput.addEventListener("input", () => {
            codigoInput.value = somenteDigitos(codigoInput.value);
            hideFeedback();
            cards.forEach((_, pautaId) => atualizarEstadoCard(pautaId));
        });
    });

    pollingTimer = setInterval(poll, POLL_INTERVAL);
    poll();
})();