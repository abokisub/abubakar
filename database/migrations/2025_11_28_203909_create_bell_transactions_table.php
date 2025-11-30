<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBellTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bell_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('external_id')->nullable()->index();
            $table->enum('type', ['credit', 'debit', 'transfer'])->default('transfer');
            $table->decimal('amount', 16, 2);
            $table->string('currency')->default('NGN');
            $table->string('status')->default('pending'); // pending, successful, failed
            $table->string('reference')->unique()->index();
            $table->string('idempotency_key')->nullable()->unique();
            $table->json('response')->nullable();
            $table->text('description')->nullable();
            $table->string('source_account_number')->nullable();
            $table->string('source_account_name')->nullable();
            $table->string('source_bank_code')->nullable();
            $table->string('source_bank_name')->nullable();
            $table->string('destination_account_number')->nullable();
            $table->string('destination_account_name')->nullable();
            $table->string('destination_bank_code')->nullable();
            $table->string('destination_bank_name')->nullable();
            $table->decimal('charge', 16, 2)->default(0);
            $table->decimal('net_amount', 16, 2)->nullable();
            $table->string('session_id')->nullable();
            $table->string('transaction_type_name')->nullable();
            $table->timestamp('completed_at')->nullable();
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
        Schema::dropIfExists('bell_transactions');
    }
}
