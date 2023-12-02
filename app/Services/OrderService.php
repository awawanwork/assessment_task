<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method

        // Check if the order_id already exists, ignore duplicates
        $existingOrder = Order::where('id', $data['order_id'])->first();
        if ($existingOrder) {
            return; // Order already processed
            // exit;
        }

        
       
        // Find the associated merchant
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        $commission_owed = 0.00;

        // Find or create an affiliate based on customer email
        $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], $commission_owed);

        // Create the order
        $order = new Order([
            'id' => $data['order_id'],
            'subtotal' => $data['subtotal_price'],
            'commission_owed' => $commission_owed,
            'payout_status' => Order::STATUS_UNPAID, // Assuming initially unpaid
            'discount_code' => $data['discount_code'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
        ]);

        $order->save();


        

    }
}
