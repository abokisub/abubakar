<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBellBeneficiariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bell_beneficiaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();
            $table->string('account_number');
            $table->string('account_name');
            $table->string('bank_code');
            $table->string('bank_name')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->integer('transfer_count')->default(0); // Track how many times this beneficiary was used
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_favorite']);
            $table->index(['user_id', 'last_used_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bell_beneficiaries');
    }
}

