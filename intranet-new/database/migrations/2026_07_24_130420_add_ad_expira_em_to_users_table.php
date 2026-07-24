<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data de expiração da conta no AD (accountExpires) — nula quando a
     * conta nunca expira ou o usuário ainda não teve essa data trazida do AD.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('ad_expira_em')->nullable()->after('ad_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ad_expira_em');
        });
    }
};
