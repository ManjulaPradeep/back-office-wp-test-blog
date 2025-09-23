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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wp_id')->unique()->index();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('status')->default('draft');
            $table->integer('priority')->default(0);
            $table->timestamp('wp_created_at')->nullable();
            $table->timestamp('wp_modified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
