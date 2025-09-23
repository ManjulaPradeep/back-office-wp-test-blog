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
            $table->bigInteger('wp_id')->nullable()->after('id');
            $table->bigInteger('wp_site_id')->nullable()->after('wp_id');
            $table->string('wp_site_url')->nullable()->after('wp_site_id');
            $table->string('wp_site_name')->nullable()->after('wp_site_url');
            $table->string('wp_role')->nullable()->after('wp_site_name');
            $table->text('wp_token')->nullable()->after('wp_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'wp_id',
                'wp_site_id',
                'wp_site_url',
                'wp_site_name',
                'wp_role',
                'wp_token',
            ]);
        });
    }
};
