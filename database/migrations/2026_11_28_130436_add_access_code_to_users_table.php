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
            $table->string('access_code_hash')->nullable()->after('estado');
            $table->timestamp('access_code_expires_at')->nullable()->after('access_code_hash');
            $table->timestamp('access_code_used_at')->nullable()->after('access_code_expires_at');
            $table->unsignedTinyInteger('access_code_attempts')->default(0)->after('access_code_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'access_code_hash',
                'access_code_expires_at',
                'access_code_used_at',
                'access_code_attempts',
            ]);
        });
    }
};
