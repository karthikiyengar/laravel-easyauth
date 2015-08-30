<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Easyauth extends Migration
{
    /**
     * Schema changes to accommodate fields from social logins
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropColumn('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('avatar');
            $table->string('provider');
            $table->string('provider_key');
            $table->boolean('verified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array('first_name', 'last_name', 'avatar', 'provider', 'provider_key', 'verified','deleted_at'));
        });
    }
}
