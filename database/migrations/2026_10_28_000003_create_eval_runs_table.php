<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eval_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('k');
            $table->float('recall');
            $table->json('hits');
            $table->json('misses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eval_runs');
    }
};
