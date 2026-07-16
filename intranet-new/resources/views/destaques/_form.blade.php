@php $destaque = $destaque ?? null; @endphp
<div class="p-4 bg-blue-50 rounded text-sm space-y-2">
    <p class="font-medium text-blue-900">Modelo de imagem para o Destaque</p>
    <p class="text-blue-800">
        A imagem precisa ter exatamente <strong>1600×500 pixels</strong> (faixa larga).
        Baixe o modelo em PowerPoint, edite o Slide 2 (já no tamanho certo) e exporte
        como PNG (Arquivo &gt; Exportar &gt; Alterar o Tipo de Arquivo &gt; Imagem PNG &gt;
        "Apenas Este Slide").
    </p>
    <a href="{{ asset('templates/destaque-modelo.pptx') }}" download class="inline-block px-4 py-2 bg-blue-600 text-white rounded text-sm">
        Baixar modelo PPTX
    </a>
</div>
<div>
    <label class="block text-sm font-medium">Título (uso interno, não aparece no banner)</label>
    <input type="text" name="titulo" value="{{ old('titulo', $destaque->titulo ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('titulo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Imagem (1600×500px) {{ $destaque ? '(opcional na edição)' : '' }}</label>
    <input type="file" name="imagem" accept="image/*" class="mt-1 block w-full">
    @error('imagem') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    @if(!empty($destaque?->imagem))
        <img src="{{ Storage::url($destaque->imagem) }}" class="mt-2 h-24 rounded border">
    @endif
</div>
<div>
    <label class="block text-sm font-medium">Link ao clicar (opcional)</label>
    <input type="url" name="link" value="{{ old('link', $destaque->link ?? '') }}" placeholder="https://..." class="mt-1 block w-full border-gray-300 rounded">
    @error('link') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium">Início da exibição</label>
        <input type="datetime-local" name="inicio_em" value="{{ old('inicio_em', optional($destaque?->inicio_em ?? now())->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full border-gray-300 rounded">
        @error('inicio_em') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium">Fim da exibição</label>
        <input type="datetime-local" name="fim_em" value="{{ old('fim_em', optional($destaque?->fim_em)->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full border-gray-300 rounded">
        @error('fim_em') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>
</div>
<p class="text-xs text-gray-500 -mt-2">O destaque só aparece no carrossel da Tela Inicial dentro desse período.</p>
<div>
    <label class="block text-sm font-medium">Ordem de exibição</label>
    <input type="number" name="ordem" value="{{ old('ordem', $destaque->ordem ?? 0) }}" class="mt-1 block w-32 border-gray-300 rounded">
    @error('ordem') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div class="flex items-center gap-2">
    <input type="checkbox" name="ativo" value="1" id="ativo" @checked(old('ativo', $destaque->ativo ?? true))>
    <label for="ativo" class="text-sm">Ativo (aparece no carrossel da Tela Inicial)</label>
</div>
