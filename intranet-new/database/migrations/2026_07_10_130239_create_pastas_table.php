<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pastas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->foreignId('parent_id')->nullable()->constrained('pastas')->cascadeOnDelete();
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->nullOnDelete();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('pastas');
    }
};
