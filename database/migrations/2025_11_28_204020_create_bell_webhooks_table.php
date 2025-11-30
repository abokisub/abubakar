<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBellWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bell_webhooks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event');
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->timestamp('received_at');
            $table->boolean('processed')->default(false);
            $table->text('error')->nullable();
            $table->timestamps();
            
            $table->index(['processed', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bell_webhooks');
    }
}
