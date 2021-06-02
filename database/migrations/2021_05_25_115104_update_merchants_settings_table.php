<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMerchantsSettingsTable extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchants_settings', function (Blueprint $table) {
            // adding merchant terms url column
			$table->string('merchant_terms_url')->nullable()->after('language');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchants_settings', function (Blueprint $table) {
            //
			$table->dropColumn('merchant_terms_url');
        });
    }
}
