@php
    $reserva = $reserva ?? null;
    $salasJson = $salas->keyBy('id')->map(fn ($s) => ['restrita' => $s->restrita, 'permite_arrumacao' => $s->permite_arrumacao])->toArray();
@endphp
<form
    method="POST"
    action="{{ $action }}"
    x-data="reservaSalaForm({
        salas: @js($salasJson),
        salaId: '{{ old('sala_id', $reserva->sala_id ?? '') }}',
        dataInicio: '{{ old('data', optional($reserva?->data)->format('Y-m-d')) }}',
        equipamentos: @js(old('equipamentos', $reserva->equipamentos ?? [])),
        servicos: @js(old('servicos', $reserva->servicos ?? [])),
        comunicacao: @js(old('comunicacao', $reserva->comunicacao ?? [])),
        wifiTipo: '{{ old('wifi_tipo', $reserva->wifi_tipo ?? 'evento') }}',
        wifiVisitantes: @js(old('wifi_visitantes', $reserva->wifi_visitantes ?? [])),
    })"
    @submit="onSubmit"
    class="bg-white shadow rounded p-6 space-y-5"
>
    @csrf
    @if($method ?? null)
        @method($method)
    @endif

    <div>
        <label class="block text-sm font-medium">Nome do Evento/Reunião</label>
        <input type="text" name="titulo" value="{{ old('titulo', $reserva->titulo ?? '') }}" required maxlength="150" class="mt-1 block w-full border-gray-300 rounded">
        @error('titulo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium">Sala/Auditório</label>
            <select name="sala_id" x-model="salaId" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Selecione a sala...</option>
                @foreach($salas as $sala)
                    <option value="{{ $sala->id }}">{{ $sala->nome }} ({{ $sala->capacidade }} lugares)</option>
                @endforeach
            </select>
            @error('sala_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

            <div x-show="salaRestrita" x-cloak class="mt-2 p-3 bg-yellow-50 border border-yellow-300 rounded">
                <p class="text-xs text-yellow-800 mb-2">
                    Esta sala é de uso restrito e só é permitida mediante autorização prévia da Diretoria ou Coordenação.
                </p>
                <label class="flex items-start gap-2 text-sm font-medium text-gray-800">
                    <input type="checkbox" name="permissao_especial" value="1" {{ old('permissao_especial', $reserva->permissao_especial ?? false) ? 'checked' : '' }} :required="salaRestrita" class="mt-1">
                    Declaro que possuo autorização prévia da chefia para reservar esta sala.
                </label>
                @error('permissao_especial') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium">Data Início</label>
            <input type="date" name="data" x-model="dataInicio" min="{{ now()->toDateString() }}" required
                   @change="$refs.dataTermino.value = dataInicio" value="{{ old('data', optional($reserva?->data)->format('Y-m-d')) }}"
                   class="mt-1 block w-full border-gray-300 rounded">
            @error('data') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Data Fim</label>
            <input type="date" name="data_termino" x-ref="dataTermino" min="{{ now()->toDateString() }}" required
                   value="{{ old('data_termino', optional($reserva?->dataFim())->format('Y-m-d')) }}"
                   class="mt-1 block w-full border-gray-300 rounded">
            @error('data_termino') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Horário de Início</label>
            <input type="time" name="horario_inicio" value="{{ old('horario_inicio', optional($reserva)?->horaInicioFormatada()) }}" required class="mt-1 block w-full border-gray-300 rounded">
            @error('horario_inicio') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Horário de Fim</label>
            <input type="time" name="horario_fim" value="{{ old('horario_fim', optional($reserva)?->horaFimFormatada()) }}" required class="mt-1 block w-full border-gray-300 rounded">
            @error('horario_fim') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-gray-50 p-4 rounded border">
        <div>
            <label class="block text-sm font-medium text-gray-600">Solicitante</label>
            <input type="text" name="solicitante" value="{{ old('solicitante', $reserva->solicitante ?? auth()->user()->name) }}" maxlength="100" placeholder="Responsável pelo evento" class="mt-1 block w-full border-gray-300 rounded">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-600">Setor/Coordenação</label>
            <select name="sector_id" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Selecione...</option>
                @foreach($sectors as $sector)
                    <option value="{{ $sector->id }}" {{ (string) old('sector_id', $reserva->sector_id ?? auth()->user()->sector_id) === (string) $sector->id ? 'selected' : '' }}>{{ $sector->caminhoHierarquico() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-600">Tipo de Evento</label>
            <input type="text" name="tipo_evento" value="{{ old('tipo_evento', $reserva->tipo_evento ?? '') }}" required maxlength="100" placeholder="Ex: Palestra, Reunião" class="mt-1 block w-full border-gray-300 rounded">
            @error('tipo_evento') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-600">Público</label>
            <select name="publico" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="" disabled {{ old('publico', $reserva->publico ?? '') ? '' : 'selected' }}>Selecione...</option>
                <option value="presencial" {{ old('publico', $reserva->publico ?? '') === 'presencial' ? 'selected' : '' }}>Presencial</option>
                <option value="virtual" {{ old('publico', $reserva->publico ?? '') === 'virtual' ? 'selected' : '' }}>Virtual</option>
                <option value="hibrido" {{ old('publico', $reserva->publico ?? '') === 'hibrido' ? 'selected' : '' }}>Híbrido</option>
            </select>
            @error('publico') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-600">Visita Externa?</label>
            <select name="visita_externa" class="mt-1 block w-full border-gray-300 rounded">
                <option value="0" {{ !old('visita_externa', $reserva->visita_externa ?? false) ? 'selected' : '' }}>Não</option>
                <option value="1" {{ old('visita_externa', $reserva->visita_externa ?? false) ? 'selected' : '' }}>Sim</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium">Observações (Opcional)</label>
        <textarea name="observacoes" rows="2" placeholder="Detalhes adicionais..." class="mt-1 block w-full border-gray-300 rounded">{{ old('observacoes', $reserva->observacoes ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Equipamentos TI --}}
        <div class="border rounded p-3">
            <p class="font-bold text-blue-700 mb-2 text-sm">Equipamentos TI (SEIN)</p>
            @php $opcoesEquip = ['Nenhum' => 'Não será necessário', 'Computador' => 'Computador', 'Projetor' => 'Projetor', 'Transmissão' => 'Transmissão YouTube (OBS)', 'Cronometro' => 'Cronômetro']; @endphp
            @foreach($opcoesEquip as $valor => $label)
                <label class="flex items-center gap-2 text-sm mb-1">
                    <input type="checkbox" name="equipamentos[]" value="{{ $valor }}" x-model="equipamentos" @change="onGrupoChange('equipamentos', '{{ $valor }}', {{ $valor === 'Nenhum' ? 'true' : 'false' }})" :disabled="{{ $valor === 'Nenhum' ? 'false' : "equipamentos.includes('Nenhum')" }}">
                    {{ $label }}
                </label>
            @endforeach

            <label class="flex items-center gap-2 text-sm mb-1">
                <input type="checkbox" name="equipamentos[]" value="WiFi Visitante" x-model="equipamentos" @change="onGrupoChange('equipamentos', 'WiFi Visitante', false)" :disabled="equipamentos.includes('Nenhum')">
                Senha de WiFi para Visitante
            </label>

            <div x-show="equipamentos.includes('WiFi Visitante')" x-cloak class="mt-2 mb-2 p-3 bg-gray-100 rounded border">
                <p class="text-xs text-red-600 font-bold mb-2">O preenchimento abaixo é obrigatório.</p>
                <div class="flex gap-3 mb-2 text-sm">
                    <label class="flex items-center gap-1"><input type="radio" name="wifi_tipo" value="evento" x-model="wifiTipo"> 🎟️ Evento</label>
                    <label class="flex items-center gap-1"><input type="radio" name="wifi_tipo" value="individual" x-model="wifiTipo"> 👤 Individual</label>
                </div>

                <div x-show="wifiTipo === 'evento'">
                    <input type="text" name="wifi_nome_evento" value="{{ old('wifi_nome_evento', $reserva->wifi_nome_evento ?? '') }}" placeholder="Nome do Evento/Grupo" class="block w-full text-sm border-gray-300 rounded">
                    @error('wifi_nome_evento') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <div x-show="wifiTipo === 'individual'">
                    <template x-for="(visitante, indice) in wifiVisitantes" :key="indice">
                        <div class="flex gap-1 mb-1">
                            <input type="text" :name="'wifi_visitantes[' + indice + '][nome]'" x-model="visitante.nome" placeholder="Nome" class="w-1/2 text-sm border-gray-300 rounded">
                            <input type="email" :name="'wifi_visitantes[' + indice + '][email]'" x-model="visitante.email" placeholder="E-mail" class="w-1/2 text-sm border-gray-300 rounded">
                            <button type="button" @click="removerVisitante(indice)" class="text-red-600 text-xs px-1">X</button>
                        </div>
                    </template>
                    <button type="button" @click="adicionarVisitante()" class="text-blue-600 text-xs border border-blue-300 rounded px-2 py-1 w-full">+ Novo Visitante</button>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm mb-1">
                <input type="checkbox" name="equipamentos[]" value="Videoconferência" x-model="equipamentos" @change="onGrupoChange('equipamentos', 'Videoconferência', false); onVideoconferenciaChange($event.target.checked)" :disabled="equipamentos.includes('Nenhum')">
                Equipamento de Videoconferência
            </label>
            <label class="flex items-center gap-2 text-sm mb-2">
                <input type="checkbox" name="equipamentos[]" value="Virtual" x-model="equipamentos" @change="onGrupoChange('equipamentos', 'Virtual', false)" :disabled="equipamentos.includes('Nenhum')">
                Sala Virtual
            </label>
            @error('equipamentos') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

            <select name="salavirtual" :disabled="!precisaSalaVirtual" :required="precisaSalaVirtual" class="block w-full text-sm border-gray-300 rounded">
                <option value="">Selecione a plataforma...</option>
                @foreach(['rnppessoal' => 'RNP Pessoal', 'rnpinstitucional' => 'RNP Institucional', 'outraplataforma' => 'Outra Plataforma', 'salaexterna' => 'Sala Virtual Externa'] as $valor => $label)
                    <option value="{{ $valor }}" {{ old('salavirtual', $reserva->salavirtual ?? '') === $valor ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('salavirtual') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Serviços Gerais --}}
        <div class="border rounded p-3">
            <p class="font-bold text-green-700 mb-2 text-sm">Serviços Gerais / Copa (SESG)</p>
            @php $opcoesServ = ['Nenhum' => 'Não será necessário', 'Agua/Cafe' => 'Água e Café', 'Suco' => 'Sucos', 'Biscoito/Bolo' => 'Biscoitos e Bolos']; @endphp
            @foreach($opcoesServ as $valor => $label)
                <label class="flex items-center gap-2 text-sm mb-1">
                    <input type="checkbox" name="servicos[]" value="{{ $valor }}" x-model="servicos" @change="onGrupoChange('servicos', '{{ $valor }}', {{ $valor === 'Nenhum' ? 'true' : 'false' }})" :disabled="{{ $valor === 'Nenhum' ? 'false' : "servicos.includes('Nenhum')" }}">
                    {{ $label }}
                </label>
            @endforeach
            @error('servicos') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

            <label class="block text-sm font-medium text-green-700 mt-3 mb-1">Arrumação da Sala / Cadeiras</label>
            <select name="arrumacao" :disabled="!permiteArrumacao" :required="permiteArrumacao" class="block w-full text-sm border-gray-300 rounded">
                <option value="" disabled selected>Selecione a arrumação...</option>
                <option value="interna" {{ old('arrumacao', $reserva->arrumacao ?? '') === 'interna' ? 'selected' : '' }}>Modo Reunião</option>
                <option value="externa" {{ old('arrumacao', $reserva->arrumacao ?? '') === 'externa' ? 'selected' : '' }}>Modo Palestra</option>
            </select>
            <p class="text-xs text-gray-500 mt-1" x-show="!permiteArrumacao">Só se aplica à sala Dias Leite (VIP).</p>
            @error('arrumacao') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        {{-- Comunicação --}}
        <div class="border rounded p-3">
            <p class="font-bold text-red-700 mb-2 text-sm">Comunicação / Divulgação (COPGI)</p>
            @php $opcoesCom = ['Nenhum' => 'Não será necessário', 'Arte' => 'Arte', 'Fotográfico' => 'Registro Fotográfico', 'Cerimonial' => 'Cerimonial', 'Divulgação' => 'Divulgação']; @endphp
            @foreach($opcoesCom as $valor => $label)
                <label class="flex items-center gap-2 text-sm mb-1">
                    <input type="checkbox" name="comunicacao[]" value="{{ $valor }}" x-model="comunicacao" @change="onGrupoChange('comunicacao', '{{ $valor }}', {{ $valor === 'Nenhum' ? 'true' : 'false' }})" :disabled="{{ $valor === 'Nenhum' ? 'false' : "comunicacao.includes('Nenhum')" }}">
                    {{ $label }}
                </label>
            @endforeach
            @error('comunicacao') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

            <select name="divulgacao" :disabled="!comunicacao.includes('Divulgação')" :required="comunicacao.includes('Divulgação')" class="block w-full text-sm border-gray-300 rounded mt-2">
                <option value="">Onde será divulgado?</option>
                <option value="interna" {{ old('divulgacao', $reserva->divulgacao ?? '') === 'interna' ? 'selected' : '' }}>Interna</option>
                <option value="externa" {{ old('divulgacao', $reserva->divulgacao ?? '') === 'externa' ? 'selected' : '' }}>Externa</option>
                <option value="ambas" {{ old('divulgacao', $reserva->divulgacao ?? '') === 'ambas' ? 'selected' : '' }}>Ambas</option>
            </select>
            @error('divulgacao') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
    </div>

    <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700">
        {{ $reserva ? 'Salvar Alterações' : 'Confirmar Reserva' }}
    </button>
</form>
