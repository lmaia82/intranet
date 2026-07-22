

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

Alpine.start();
