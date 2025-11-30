<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_devices')) {
        Schema::create('user_devices', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->string('device_fingerprint', 255);
                $table->string('browser', 100)->nullable();
                $table->string('browser_version', 50)->nullable();
                $table->string('os', 100)->nullable();
                $table->string('device_type', 50)->nullable(); // desktop, mobile, tablet
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->boolean('is_trusted')->default(0);
            $table->timestamps();
                
                // Index for faster lookups (foreign key removed to avoid constraint issues)
                $table->index(['user_id', 'device_fingerprint']);
                $table->unique(['user_id', 'device_fingerprint']); // Unique per user per device
        });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_devices');
    }
}
