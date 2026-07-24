<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DesativarUsuariosExpiradosNoAd extends Command
{
    protected $signature = 'usuarios:desativar-expirados-ad';

    protected $description = 'Desativa usuários cuja data de expiração da conta no AD (ad_expira_em) já passou';

    public function handle(): int
    {
        $usuarios = User::where('is_active', true)
            ->whereNotNull('ad_expira_em')
            ->where('ad_expira_em', '<', now())
            ->get();

        foreach ($usuarios as $usuario) {
            $usuario->update(['is_active' => false]);
        }

        $this->info("{$usuarios->count()} usuário(s) desativado(s) por expiração da conta no AD.");

        return self::SUCCESS;
    }
}
