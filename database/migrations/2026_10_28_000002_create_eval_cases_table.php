<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eval_cases', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->json('relevant_ids');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eval_cases');
    }
};
