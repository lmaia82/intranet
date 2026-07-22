<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ad_guid')->nullable()->unique()->after('password');
            $table->string('ad_domain')->nullable()->after('ad_guid');
            $table->string('ad_setor')->nullable()->after('ad_domain');
            $table->timestamp('ad_synced_at')->nullable()->after('ad_setor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ad_guid', 'ad_domain', 'ad_setor', 'ad_synced_at']);
        });
    }
};
