

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('carrossel', (total) => ({
    atual: 0,
    total,
    iniciar() {
        setInterval(() => {
            this.atual = (this.atual + 1) % this.total;
        }, 5000);
    },
}));

Alpine.data('ocrStatus', (status, erro, url) => ({
    status,
    erro,
    tentativas: 0,
    iniciarPolling() {
        const intervalo = setInterval(() => {
            this.tentativas++;
            if (this.tentativas > 120) {
                clearInterval(intervalo);
                return;
            }
            fetch(url)
                .then((r) => r.json())
                .then((dados) => {
                    this.status = dados.status;
                    this.erro = dados.erro;
                    if (this.status !== 'pendente') {
                        clearInterval(intervalo);
                    }
                })
                .catch(() => {});
        }, 5000);
    },
}));

Alpine.store('loginModal', { aberto: false });

// Menu superior "priority nav": mostra quantos itens couberem na barra e
// joga o restante (na ordem de prioridade) dentro do dropdown "Mais",
// recalculando ao redimensionar a janela — em vez de uma lista fixa de
// itens visíveis, que ou deixa espaço vazio ou quebra linha dependendo da
// largura real da tela.
Alpine.data('priorityNav', (items) => ({
    items,
    visibleCount: items.length,
    maisOpen: false,

    get hasOverflow() {
        return this.visibleCount < this.items.length;
    },
    get itensVisiveis() {
        return this.items.slice(0, this.visibleCount);
    },
    get itensOverflow() {
        return this.items.slice(this.visibleCount);
    },

    init() {
        this.$nextTick(() => this.recalcular());

        let debounce;
        window.addEventListener('resize', () => {
            clearTimeout(debounce);
            debounce = setTimeout(() => this.recalcular(), 100);
        });
    },

    // Mede num "clone" invisível (visibility:hidden, fora do fluxo) que
    // sempre renderiza TODOS os itens em tamanho real — assim dá pra
    // medir a largura de cada um mesmo depois de ele ter saído da lista
    // visível (um item com display:none, ao contrário de invisible, mede
    // largura zero e a gente nunca mais conseguiria "devolvê-lo" pra
    // barra quando a tela crescesse de novo).
    recalcular() {
        const caixa = this.$refs.itemsBox;
        const medidor = this.$refs.medidor;
        if (!caixa || !medidor) {
            return;
        }

        // O botão "Mais" já reserva espaço no layout (sempre renderizado,
        // só fica "invisible" quando não precisa) — então caixa.clientWidth
        // já reflete o espaço realmente disponível pros itens, sem o
        // vaivém de recalcular de novo depois de mostrar o botão.
        const larguraDisponivel = caixa.clientWidth;
        const elementos = Array.from(medidor.querySelectorAll('[data-nav-item]'));

        let larguraAcumulada = 0;
        let contagem = 0;
        const espacamento = 16; // gap-4

        for (let i = 0; i < elementos.length; i++) {
            const larguraItem = elementos[i].offsetWidth;
            const proximaLargura = larguraAcumulada + larguraItem + (i > 0 ? espacamento : 0);
            if (proximaLargura > larguraDisponivel) {
                break;
            }
            larguraAcumulada = proximaLargura;
            contagem++;
        }

        this.visibleCount = Math.max(1, contagem);
    },
}));

// Formulário de Reserva de Sala — reproduz as regras de negócio do
// formulário original (SEIN): sala restrita exige confirmação de
// autorização; grupos de checkbox com "Nenhum" são mutuamente exclusivos;
// videoconferência liga a Sala Virtual; WiFi Visitante abre um sub-formulário;
// arrumação da sala só é liberada para a sala VIP; aviso de feriado antes
// de enviar.
Alpine.data('reservaSalaForm', (config) => ({
    salas: config.salas || {},
    salaId: config.salaId ?? '',
    dataInicio: config.dataInicio ?? '',
    equipamentos: config.equipamentos ?? [],
    servicos: config.servicos ?? [],
    comunicacao: config.comunicacao ?? [],
    wifiTipo: config.wifiTipo ?? 'evento',
    wifiVisitantes: (config.wifiVisitantes && config.wifiVisitantes.length) ? config.wifiVisitantes : [{ nome: '', email: '' }],

    feriadosFixos: [
        '01-01', '01-20', '04-21', '04-23', '05-01', '09-07', '10-12', '11-02', '11-15', '11-20', '12-25',
    ],

    feriadosMoveis(ano) {
        const f = Math.floor;
        const G = ano % 19, C = f(ano / 100);
        const H = (C - f(C / 4) - f((8 * C + 13) / 25) + 19 * G + 15) % 30;
        const I = H - f(H / 28) * (1 - f(29 / (H + 1)) * f((21 - G) / 11));
        const J = (ano + f(ano / 4) + I + 2 - C + f(C / 4)) % 7;
        const L = I - J;
        const mes = 3 + f((L + 40) / 44);
        const dia = L + 28 - 31 * f(mes / 4);

        const pascoa = new Date(ano, mes - 1, dia);
        const sextaSanta = new Date(pascoa); sextaSanta.setDate(pascoa.getDate() - 2);
        const carnaval = new Date(pascoa); carnaval.setDate(pascoa.getDate() - 47);
        const corpusChristi = new Date(pascoa); corpusChristi.setDate(pascoa.getDate() + 60);

        const formata = (d) => String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        return [formata(carnaval), formata(sextaSanta), formata(corpusChristi)];
    },

    get salaAtual() {
        return this.salaId && this.salas[this.salaId] ? this.salas[this.salaId] : null;
    },
    get salaRestrita() {
        return this.salaAtual ? !!this.salaAtual.restrita : false;
    },
    get permiteArrumacao() {
        return this.salaAtual ? !!this.salaAtual.permite_arrumacao : false;
    },
    get precisaSalaVirtual() {
        return this.equipamentos.includes('Virtual') || this.equipamentos.includes('Videoconferência');
    },

    onGrupoChange(prop, valor, ehNenhum) {
        if (ehNenhum) {
            if (this[prop].includes('Nenhum')) {
                this[prop] = ['Nenhum'];
            }
        } else if (this[prop].includes('Nenhum')) {
            this[prop] = this[prop].filter((v) => v !== 'Nenhum');
        }
    },

    onVideoconferenciaChange(marcado) {
        if (marcado && !this.equipamentos.includes('Virtual')) {
            this.equipamentos.push('Virtual');
            this.onGrupoChange('equipamentos', 'Virtual', false);
        }
    },

    adicionarVisitante() {
        this.wifiVisitantes.push({ nome: '', email: '' });
    },
    removerVisitante(indice) {
        this.wifiVisitantes.splice(indice, 1);
        if (this.wifiVisitantes.length === 0) {
            this.wifiVisitantes.push({ nome: '', email: '' });
        }
    },

    onSubmit(event) {
        if (!this.dataInicio) {
            return;
        }
        const ano = parseInt(this.dataInicio.substring(0, 4), 10);
        const mesDia = this.dataInicio.substring(5);
        const todosFeriados = this.feriadosFixos.concat(this.feriadosMoveis(ano));

        if (todosFeriados.includes(mesDia)) {
            const prosseguir = confirm(
                'Atenção: a data escolhida cai em um feriado (nacional, estadual ou municipal). Tem certeza que deseja agendar para este dia?'
            );
            if (!prosseguir) {
                event.preventDefault();
            }
        }
    },
}));

Alpine.start();
