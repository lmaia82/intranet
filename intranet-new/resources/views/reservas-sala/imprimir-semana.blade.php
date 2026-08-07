<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda Semanal — Reserva de Sala</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px; color: #333; margin: 0; }
        .header-print { text-align: center; margin-bottom: 20px; }
        .btn-imprimir { background: #0d6efd; color: white; border: none; padding: 10px 20px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer; margin-bottom: 10px; }
        .grid-semana { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; max-width: 1200px; margin: 0 auto; }
        .dia-card { background: #fff; border: 1px solid #ccc; border-radius: 8px; min-height: 200px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column; }
        .dia-titulo { background: #e9ecef; padding: 8px; text-align: center; font-weight: bold; font-size: 13px; border-bottom: 1px solid #ccc; }
        .dia-corpo { padding: 8px; flex-grow: 1; }
        .evento-pill { margin-bottom: 8px; padding: 6px 8px; font-size: 11px; background: #f8f9fa; border: 1px solid #e9ecef; border-left: 6px solid #000; border-radius: 4px; line-height: 1.4; }
        .evento-hora { font-weight: bold; }
        @media print {
            @page { size: landscape; margin: 10mm; }
            body { background: white; padding: 0; }
            .no-print { display: none !important; }
            .dia-card { break-inside: avoid; border-color: #999; }
        }
    </style>
</head>
<body>
    <div class="header-print no-print">
        <h2>Reserva de Sala — Agenda Semanal</h2>
        <p>Próximos 7 dias.</p>
        <button class="btn-imprimir" onclick="window.print()">Imprimir Agenda</button>
        <br>
        <a href="{{ route('reservas-sala.index') }}">&larr; Voltar</a>
    </div>

    <div class="grid-semana">
        @foreach($dias as $dia)
            <div class="dia-card">
                <div class="dia-titulo">{{ ucfirst($dia['data']->translatedFormat('l, d/m')) }}</div>
                <div class="dia-corpo">
                    @forelse($dia['reservas'] as $reserva)
                        <div class="evento-pill" style="border-left-color: {{ $reserva->sala->cor }}">
                            <div class="evento-hora">{{ $reserva->horaInicioFormatada() }} às {{ $reserva->horaFimFormatada() }}</div>
                            <div style="font-weight: bold; color: {{ $reserva->sala->cor }}">{{ $reserva->sala->nome }}</div>
                            <div>{{ $reserva->titulo }}</div>
                            <div style="font-size: 10px; color: #666;">Por: {{ $reserva->solicitante }}</div>
                        </div>
                    @empty
                        <div style="text-align: center; color: #999; margin-top: 15px; font-size: 12px; font-style: italic;">Livre</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
