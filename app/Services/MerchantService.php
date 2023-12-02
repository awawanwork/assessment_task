<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method

        // Start a database transaction
        DB::beginTransaction();

        try {

            // Create a new user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['api_key']), // Storing API key as password
                'type' => User::TYPE_MERCHANT
            ]);

            // Create a new merchant associated with the user
            $merchant = Merchant::create([
                'user_id' => $user->id,
                'domain' => $data['domain'],
                'display_name' => $data['name'],
            ]);

            // Commit the transaction
            DB::commit();

            return $merchant;
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method

        // Check if the new domain already exists for another merchant
        $existingMerchant = Merchant::where('domain', $data['domain'])->where('id', '!=', $user->merchant->id)->first();

        if ($existingMerchant) {
            throw new \Exception('Domain already exists for another merchant');
            return;
        }

        // Update the associated merchant details
        $user->merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],

        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method

        // Find the user by email
        $user = User::where('email', $email)->first();

        if ($user) {
            // If the user is found, return the associated merchant
            return $user->merchant;
        }

        return null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method

        // Get all unpaid orders of the affiliate
        $unpaidOrders = $affiliate->orders()->where('payout_status', Order::STATUS_UNPAID)->get();

        // Dispatch a job for each unpaid order
        foreach ($unpaidOrders as $order) {
            PayoutOrderJob::dispatch($order);
        }
    }

    /**
     *
     * @param date $from and $to
     * @return array $orderStats
     */
    public function getOrderStatistics($from, $to)
    {
        $orderStat = array (
            "count" => Order::whereBetween('created_at', [$from, $to])->count(),
            "commission_owed" => Order::whereBetween('created_at', [$from, $to])->whereNotNull('affiliate_id')->where('payout_status', '!=', Order::STATUS_PAID)->sum('commission_owed'),
            "count" => Order::whereBetween('created_at', [$from, $to])->sum('subtotal')
        );

        // $orderStat["commission_owed"] = Order::whereBetween('created_at', [$from, $to])->whereNotNull('affiliate_id')->where('payout_status', '!=', Order::STATUS_PAID)->sum('commission_owed');
        // $orderStat["revenue"] = Order::whereBetween('created_at', [$from, $to])->sum('subtotal');

        return $orderStat;
    }
}