<?php

namespace App\Http\Controllers;

use App\Models\Sector;

class OrganogramaController extends Controller
{
    /**
     * Monta o organograma inteiramente a partir da hierarquia configurada
     * em Admin > Setores (Sector::parent_id) — sem conteúdo próprio para
     * editar aqui. A Diretoria é tratada como o topo da árvore; os demais
     * setores sem coordenação (parent_id nulo) formam a linha de
     * coordenações, cada uma com seus serviços subordinados abaixo.
     */
    public function index()
    {
        $diretoria = Sector::where('sigla', 'DIRETORIA')->first();

        $coordenacoes = Sector::whereNull('parent_id')
            ->where('sigla', '!=', 'DIRETORIA')
            ->with(['children.users' => fn ($query) => $query->orderBy('name'), 'users' => fn ($query) => $query->orderBy('name')])
            ->orderBy('sigla')
            ->get();

        return view('organograma.index', compact('diretoria', 'coordenacoes'));
    }
}
