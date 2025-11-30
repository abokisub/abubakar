<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBellAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bell_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('external_id')->unique();
            $table->string('account_number')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('currency')->default('NGN');
            $table->string('status')->default('active'); // active, closed, pending
            $table->json('metadata')->nullable();
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
        Schema::dropIfExists('bell_accounts');
    }
}
