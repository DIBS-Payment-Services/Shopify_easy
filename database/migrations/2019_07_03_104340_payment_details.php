<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaymentDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('payment_details', function (Blueprint $table) {
         $table->bigIncrements('id');
         $table->string('checkout_id')->nullable();
         $table->string('dibs_paymentid')->nullable();
         $table->string('shop_url')->nullable();
         $table->string('account_id')->nullable();
         $table->string('amount')->nullable();
         $table->string('currency')->nullable();
         $table->string('test',1)->nullable();
         $table->text('capture_request_params')->nullable();
         $table->text('create_payment_items_params')->nullable();
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
        Schema::dropIfExists('payment_details');
    }
}
