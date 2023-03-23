const SENSIBILIDADE = 32;
const RATING_BASE = 1000;
const RATING_MIN = 100;
const DISPARIDADE = 400;

class Jogador {
    constructor(id, nome="") {
        this.id = id;
        this.rating = RATING_BASE;
        this.nome = nome;
    }
}

class Partida {
    constructor(vencedor, perdedor) {
        this.timestamp = Date.now();
        this.vencedor = vencedor;
        this.perdedor = perdedor;
        this.pontuar();
    }

    pontuar() {
        this.q_vencedor = Math.pow(10, this.vencedor.rating / DISPARIDADE);
        this.q_perdedor = Math.pow(10, this.perdedor.rating / DISPARIDADE);

        this.expected = this.q_vencedor / (this.q_vencedor + this.q_perdedor);

        this.vencedor.rating += Math.round(SENSIBILIDADE * (1 - this.expected));
        this.perdedor.rating += Math.round(SENSIBILIDADE * (0 - this.expected));

        this.perdedor.rating = this.perdedor.rating < 100 ? 100 : this.perdedor.rating;

        console.log(`  > VENCEDOR: ${this.vencedor.nome} (${this.vencedor.rating}) \n  > PERDEDOR: ${this.perdedor.nome} (${this.perdedor.rating})`);
    }
}

function testar(n) {

    let j1 = new Jogador(1,"Alberto");
    let j2 = new Jogador(2,"Bruno");

    for (let i = 0; i < n; i++) {

        console.log(`[PARTIDA ${i+1}]`);
        Math.random() > 0.5 ? new Partida(j1, j2) : new Partida(j2, j1);

    }

}