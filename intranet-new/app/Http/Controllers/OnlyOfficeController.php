<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;

class OnlyOfficeController extends Controller
{
    private const EXTENSOES_SUPORTADAS = ['doc', 'docx', 'odt', 'xls', 'xlsx', 'ods', 'ppt', 'pptx', 'odp', 'pdf'];

    public function editor(Arquivo $arquivo)
    {
        abort_unless(in_array($arquivo->extensao, self::EXTENSOES_SUPORTADAS), 404, 'Este tipo de arquivo não pode ser aberto no editor.');
        abort_unless($arquivo->visivelPara(auth()->user()), 403, 'Você não tem acesso a este arquivo.');

        $urlOriginal = URL::to('/');
        URL::forceRootUrl('http://app');
        $documentUrl = URL::temporarySignedRoute('onlyoffice.documento', now()->addHours(2), ['arquivo' => $arquivo->id]);
        $callbackUrl = URL::temporarySignedRoute('onlyoffice.callback', now()->addHours(2), ['arquivo' => $arquivo->id]);
        URL::forceRootUrl($urlOriginal);

        $documentType = match ($arquivo->extensao) {
            'doc', 'docx', 'odt', 'pdf' => 'word',
            'xls', 'xlsx', 'ods' => 'cell',
            'ppt', 'pptx', 'odp' => 'slide',
        };

        $config = [
            'document' => [
                'fileType' => $arquivo->extensao,
                'key' => md5($arquivo->id . $arquivo->updated_at),
                'title' => $arquivo->nome_original,
                'url' => $documentUrl,
                'permissions' => ['edit' => $arquivo->extensao !== 'pdf', 'download' => true],
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

    public function aplicacoes()
    {
        return view('repositorio.aplicacoes');
    }

    public function criar(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:docx,xlsx,pptx',
            'titulo' => 'nullable|string|max:100',
        ]);

        $sector = auth()->user()->sector;
        abort_unless($sector, 422, 'Você precisa estar vinculado a um setor (lotação) para criar documentos pelas Aplicações. Atualize seu perfil.');

        $pastaTemporaria = $sector->pastaTemporaria();

        $titulo = $request->input('titulo') ?: 'Documento sem título';
        $nomeArquivo = $titulo . '.' . $request->tipo;
        $caminhoTemp = sys_get_temp_dir() . '/' . uniqid('doc') . '.' . $request->tipo;

        switch ($request->tipo) {
            case 'docx':
                $phpWord = new PhpWord();
                $phpWord->addSection();
                WordIOFactory::createWriter($phpWord, 'Word2007')->save($caminhoTemp);
                break;
            case 'xlsx':
                $spreadsheet = new Spreadsheet();
                (new Xlsx($spreadsheet))->save($caminhoTemp);
                break;
            case 'pptx':
                $presentation = new PhpPresentation();
                PresentationIOFactory::createWriter($presentation, 'PowerPoint2007')->save($caminhoTemp);
                break;
        }

        $caminhoStorage = 'uploads/' . uniqid() . '_' . $nomeArquivo;
        Storage::disk('arquivos')->put($caminhoStorage, file_get_contents($caminhoTemp));
        unlink($caminhoTemp);

        $arquivo = Arquivo::create([
            'pasta_id' => $pastaTemporaria->id,
            'nome_original' => $nomeArquivo,
            'caminho' => $caminhoStorage,
            'extensao' => $request->tipo,
            'tamanho' => Storage::disk('arquivos')->size($caminhoStorage),
            'sector_id' => $sector->id,
            'is_private' => true,
        ]);

        return redirect()->route('onlyoffice.editor', $arquivo);
    }
}
