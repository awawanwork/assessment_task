<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Merchant;
use App\Models\User;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // TODO: Complete this method
        try {

            //Retrive the user's email
            $merchant_id = $this->order->merchant_id;
            $merchant = Merchant::find($merchant_id);
            $user = User::find($merchant->user_id);

            // Perform the payout using the API service
            $payoutResult = $apiService->sendPayout($user->email, $this->order->subtotal);

            // Successful payout
            // if ($payoutResult) {
                $this->order->update(['payout_status' => Order::STATUS_PAID]);
            // }
        } catch (\Exception $e) {
            \Log::error('Payout failed for order: ' . $this->order->id . ' Error: ' . $e->getMessage());
        }
    }
}
