<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class OnlyOfficeController extends Controller
{
    private const EXTENSOES_SUPORTADAS = ['doc', 'docx', 'odt', 'xls', 'xlsx', 'ods', 'ppt', 'pptx', 'odp'];

    public function editor(Arquivo $arquivo)
    {
        abort_unless(in_array($arquivo->extensao, self::EXTENSOES_SUPORTADAS), 404, 'Este tipo de arquivo não pode ser aberto no editor.');

        $urlOriginal = URL::to('/');
        URL::forceRootUrl('http://app');
        $documentUrl = URL::temporarySignedRoute('onlyoffice.documento', now()->addHours(2), ['arquivo' => $arquivo->id]);
        $callbackUrl = URL::temporarySignedRoute('onlyoffice.callback', now()->addHours(2), ['arquivo' => $arquivo->id]);
        URL::forceRootUrl($urlOriginal);

        $documentType = match ($arquivo->extensao) {
            'doc', 'docx', 'odt' => 'word',
            'xls', 'xlsx', 'ods' => 'cell',
            'ppt', 'pptx', 'odp' => 'slide',
        };

        $config = [
            'document' => [
                'fileType' => $arquivo->extensao,
                'key' => md5($arquivo->id . $arquivo->updated_at),
                'title' => $arquivo->nome_original,
                'url' => $documentUrl,
                'permissions' => ['edit' => true, 'download' => true],
            ],
            'documentType' => $documentType,
            'editorConfig' => [
                'callbackUrl' => $callbackUrl,
                'user' => ['id' => (string) auth()->id(), 'name' => auth()->user()->name],
                'lang' => 'pt-BR',
            ],
        ];

        $config['token'] = JWT::encode($config, config('services.onlyoffice.jwt_secret'), 'HS256');

        return view('repositorio.editor', compact('arquivo', 'config'));
    }

    public function documento(Arquivo $arquivo)
    {
        return Storage::disk('arquivos')->download($arquivo->caminho, $arquivo->nome_original);
    }

    public function callback(Request $request, Arquivo $arquivo)
    {
        $status = $request->input('status');

        if (in_array($status, [2, 6]) && $request->filled('url')) {
            $conteudo = Http::get($request->input('url'))->body();
            Storage::disk('arquivos')->put($arquivo->caminho, $conteudo);
            $arquivo->update(['tamanho' => Storage::disk('arquivos')->size($arquivo->caminho)]);
        }

        return response()->json(['error' => 0]);
    }
}
