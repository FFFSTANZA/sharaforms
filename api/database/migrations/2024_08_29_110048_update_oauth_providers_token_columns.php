<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('oauth_providers', function (Blueprint $table) {
            // Explicit nullability: plain ->change() drops the nullable flag
            // when the table is rebuilt (observed on sqlite).
            $table->text('access_token')->nullable()->change();
            $table->text('refresh_token')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('oauth_providers', function (Blueprint $table) {
            $table->string('access_token')->nullable()->change();
            $table->string('refresh_token')->nullable()->change();
        });
    }
};
