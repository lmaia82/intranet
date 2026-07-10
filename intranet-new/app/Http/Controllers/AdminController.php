<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Artigo;
use App\Models\Evento;
use App\Models\Informativo;
use App\Models\Sector;
use App\Models\Telefone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'usuarios' => User::count(),
            'setores' => Sector::count(),
            'ramais' => Telefone::count(),
            'informativos' => Informativo::count(),
            'eventos' => Evento::count(),
            'artigos' => Artigo::count(),
            'arquivos' => Arquivo::count(),
        ];

        return view('admin.index', compact('stats'));
    }

    public function setores()
    {
        $setores = Sector::orderBy('name')->get();
        return view('admin.setores', compact('setores'));
    }

    public function storeSetor(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:sectors,name']);
        Sector::create($request->only('name'));
        return redirect()->route('admin.setores')->with('status', 'Setor criado com sucesso.');
    }

    public function updateSetor(Request $request, Sector $setor)
    {
        $request->validate(['name' => 'required|string|max:100|unique:sectors,name,' . $setor->id]);
        $setor->update($request->only('name'));
        return redirect()->route('admin.setores')->with('status', 'Setor atualizado com sucesso.');
    }

    public function destroySetor(Sector $setor)
    {
        $setor->delete();
        return redirect()->route('admin.setores')->with('status', 'Setor removido.');
    }

    public function usuarios()
    {
        $usuarios = User::orderBy('name')->get();
        return view('admin.usuarios', compact('usuarios'));
    }

    public function criarUsuarioForm()
    {
        return view('admin.criar-usuario');
    }

    public function storeUsuario(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()->route('admin.usuarios')->with('status', 'Usuário criado com sucesso.');
    }

    public function toggleAdmin(User $usuario)
    {
        abort_if($usuario->id === auth()->id(), 403, 'Você não pode alterar sua própria permissão.');
        $usuario->update(['is_admin' => !$usuario->is_admin]);
        return redirect()->route('admin.usuarios')->with('status', 'Permissão atualizada.');
    }

    public function destroyUsuario(User $usuario)
    {
        abort_if($usuario->id === auth()->id(), 403, 'Você não pode remover a si mesmo.');
        $usuario->delete();
        return redirect()->route('admin.usuarios')->with('status', 'Usuário removido.');
    }
}
