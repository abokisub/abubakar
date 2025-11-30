<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBellKycTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bell_kyc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->unique()->index();
            $table->text('bvn_encrypted'); // encrypted BVN
            $table->enum('kyc_status', ['none', 'pending', 'verified', 'rejected'])->default('none');
            $table->string('kyc_level')->nullable(); // tier1, tier2, etc.
            $table->json('meta')->nullable(); // responses, timestamps
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
        Schema::dropIfExists('bell_kyc');
    }
}

