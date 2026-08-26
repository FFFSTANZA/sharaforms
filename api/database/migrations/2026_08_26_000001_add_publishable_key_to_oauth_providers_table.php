<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Add a publishable key column for Stripe "own keys" connections.
     *
     * Creators who connect Stripe via their own API keys (instead of OAuth
     * Connect) store their publishable key here in plaintext (it is public
     * by design); the secret key lives encrypted in access_token.
     */
    public function up()
    {
        Schema::table('oauth_providers', function (Blueprint $table) {
            $table->string('publishable_key')->nullable()->after('access_token');
        });
    }

    public function down()
    {
        Schema::table('oauth_providers', function (Blueprint $table) {
            $table->dropColumn('publishable_key');
        });
    }
};
