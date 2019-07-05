<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchants_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('shop_name')->nullable();
            $table->string('shop_id')->nullable();
            $table->string('shop_url')->nullable();
            $table->string('access_token')->nullable();
            $table->string('easy_secret_key')->nullable();
            $table->string('easy_test_secret_key')->nullable();
            $table->string('allowed_customer_type')->nullable();
            $table->string('language')->nullable();
            $table->string('terms_and_conditions_url')->nullable();
            $table->string('gateway_password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchants_settings');
    }
}
