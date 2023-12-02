<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected AffiliateService $affiliateService
    ) {
        $this->orderService = $orderService;
        $this->affiliateService = $affiliateService;
    }

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Complete this method

        // Extract necessary data from the incoming request
        $requestData = $request->all();

        $orderData = [
            'order_id' => $requestData['order_id'],
            'subtotal_price' => $requestData['subtotal_price'],
            'merchant_domain' => $requestData['merchant_domain'],
            'discount_code' => $requestData['discount_code'],
            // 'customer_email' => $requestData['customer_email'],
            // 'customer_name' => $requestData['customer_name'],
        ];

        // Process the order
        $this->orderService->processOrder($orderData);

        return response()->json(['message' => 'Order processed successfully']);
        }
}
