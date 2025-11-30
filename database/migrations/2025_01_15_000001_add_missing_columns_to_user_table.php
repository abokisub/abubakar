<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            // OTP for email verification (6 digits)
            if (!Schema::hasColumn('user', 'otp')) {
                $table->string('otp', 6)->nullable()->after('about');
            }
            
            // OTP expiration timestamp
            if (!Schema::hasColumn('user', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp');
            }
            
            // Reason for account actions (banned/deactivated)
            if (!Schema::hasColumn('user', 'reason')) {
                $table->text('reason')->nullable()->after('otp');
            }
            
            // Autofund setting
            if (!Schema::hasColumn('user', 'autofund')) {
                $table->string('autofund', 50)->nullable()->after('reason');
            }
            
            // App token for mobile app authentication
            if (!Schema::hasColumn('user', 'app_token')) {
                $table->string('app_token', 255)->nullable()->after('autofund');
            }
            
            // BVN verification failure flag
            if (!Schema::hasColumn('user', 'is_bvn_fail')) {
                $table->boolean('is_bvn_fail')->default(0)->after('app_token');
            }
            
            // NIN (National Identification Number) - 11 digits for Nigeria
            if (!Schema::hasColumn('user', 'nin')) {
                $table->string('nin', 11)->nullable()->after('is_bvn_fail');
            }
            
            // Gender field for registration
            if (!Schema::hasColumn('user', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('nin');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            if (Schema::hasColumn('user', 'otp')) {
                $table->dropColumn('otp');
            }
            if (Schema::hasColumn('user', 'reason')) {
                $table->dropColumn('reason');
            }
            if (Schema::hasColumn('user', 'autofund')) {
                $table->dropColumn('autofund');
            }
            if (Schema::hasColumn('user', 'app_token')) {
                $table->dropColumn('app_token');
            }
            if (Schema::hasColumn('user', 'is_bvn_fail')) {
                $table->dropColumn('is_bvn_fail');
            }
            if (Schema::hasColumn('user', 'nin')) {
                $table->dropColumn('nin');
            }
            if (Schema::hasColumn('user', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
}

