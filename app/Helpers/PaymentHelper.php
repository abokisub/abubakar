<?php

namespace App\Helpers;

use App\Models\BellAccount;
use App\Models\User;

class PaymentHelper
{
    /**
     * Get default gateway virtual account for user
     * 
     * @param User|int $user User model or user ID
     * @return BellAccount|null
     */
    public static function getDefaultGatewayVirtualAccount($user)
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        return BellAccount::where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get or create virtual account for user
     * 
     * @param User $user
     * @return BellAccount|null
     */
    public static function getOrCreateVirtualAccount(User $user)
    {
        $account = self::getDefaultGatewayVirtualAccount($user);
        
        if (!$account) {
            // Dispatch job to create account
            \App\Jobs\CreateBellBankVirtualAccount::dispatch($user);
            return null; // Account is being created
        }
        
        return $account;
    }

    /**
     * Check if user has virtual account
     * 
     * @param User|int $user
     * @return bool
     */
    public static function hasVirtualAccount($user)
    {
        return self::getDefaultGatewayVirtualAccount($user) !== null;
    }

    /**
     * Get default payment gateway
     * 
     * @return string
     */
    public static function getDefaultGateway()
    {
        return config('payments.default_gateway', 'bellbank');
    }
}



