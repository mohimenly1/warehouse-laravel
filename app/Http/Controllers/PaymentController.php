<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Package;
class PaymentController extends Controller
{

    public function index()
{
    $subscriptions = Subscription::with('user')->get();
    return response()->json(['subscriptions' => $subscriptions]);
}

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'subscription_type' => 'required|in:normal,trader',
    //         'payment_method' => 'required|in:credit_card,voucher,bank_transfer',
    //         'voucher_code' => 'nullable:payment_method,voucher|string',
    //         'warehouse_name' => 'required|string|max:255',
    //         'warehouse_location' => 'required|string|max:255',
    //     ]);
    
    //     try {
    //         $subscriptionAmount = $this->getSubscriptionAmount($validated['subscription_type']);
    //         if (!$subscriptionAmount) {
    //             return response()->json(['message' => 'Invalid subscription type.'], 400);
    //         }
    
    //         if ($validated['payment_method'] === 'voucher' && !$this->validateVoucher($validated['voucher_code'])) {
    //             return response()->json(['message' => 'Invalid voucher code.'], 400);
    //         }
    
    //         $subscription = null;
    //         $warehouse = null;
    
    //         DB::transaction(function () use ($validated, $subscriptionAmount, &$subscription, &$warehouse) {
    //             $subscription = Subscription::create([
    //                 'user_id' => $validated['user_id'],
    //                 'subscription_type' => $validated['subscription_type'],
    //                 'amount' => $subscriptionAmount,
    //                 'payment_method' => $validated['payment_method'],
    //                 'voucher_code' => $validated['voucher_code'],
    //                 'paid_at' => now(),
    //                 'is_paid' => true,
    //             ]);
    
    //             $warehouse = Warehouse::create([
    //                 'name' => $validated['warehouse_name'],
    //                 'location' => $validated['warehouse_location'],
    //                 'user_id' => $validated['user_id'],
    //             ]);
    //         });
    
    //         return response()->json([
    //             'message' => 'Subscription and warehouse created successfully',
    //             'subscription' => $subscription,
    //             'warehouse' => $warehouse,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Error storing subscription:', ['error' => $e->getMessage()]);
    //         return response()->json(['message' => 'An error occurred. Please try again later.'], 500);
    //     }
    // }
    



    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id', // Validate package_id
            'subscription_type' => 'required|string',
            'payment_method' => 'required|string|in:credit_card,voucher,bank_transfer', // Validate payment method
            'voucher_code' => 'nullable|string',
            'warehouse_name' => 'required|string|max:255',
            'warehouse_location' => 'required|string|max:255',
            'amount' => 'nullable|numeric|min:0', // Add amount validation
            'card_number' => 'nullable|string|max:16',
            'expiration_date' => 'nullable|string|max:5',
            'cvv' => 'nullable|string|max:4',
            'cardholder_name' => 'nullable|string|max:255',
            'width' => 'nullable|numeric|min:0', // New field for width (required for enterprise package)
            'height' => 'nullable|numeric|min:0', // New field for height (required for enterprise package)
            'price_per_meter' => 'nullable|numeric|min:0', // New field for price per meter (required for enterprise package)
        ]);
    
        // Fetch the selected package
        $package = Package::findOrFail($validatedData['package_id']);
    
        // Initialize amount and payment status
        $amount = $package->amount; // Default to package amount
        $isPaid = false;
        $paidAt = null;
    
        // If payment method is 'voucher', validate the voucher code
        if ($validatedData['payment_method'] === 'voucher') {
            $voucher = Voucher::where('code', $validatedData['voucher_code'])->first();
    
            // Check if voucher exists and is not used
            if (!$voucher) {
                return response()->json(['message' => 'Invalid voucher code.'], 400);
            }
    
            if ($voucher->is_used) {
                return response()->json(['message' => 'This voucher has already been used.'], 400);
            }
    
            // Validate that the voucher amount matches the package amount
            if ($voucher->amount != $package->amount) {
                return response()->json(['message' => 'The voucher amount does not match the package amount.'], 400);
            }
    
            // Mark the voucher as used
            $voucher->is_used = true;
            $voucher->save();
    
            // Use the voucher amount as the subscription amount
            $amount = $voucher->amount;
            $isPaid = true; // Consider the payment as completed
            $paidAt = now();
        } elseif ($validatedData['payment_method'] === 'credit_card') {
            // Validate the amount for credit card payments
            if (!isset($validatedData['amount'])) {
                return response()->json(['message' => 'The amount field is required for credit card payments.'], 400);
            }
    
            // Check if the entered amount matches the package amount
            if ($validatedData['amount'] != $package->amount) {
                return response()->json(['message' => 'The entered amount does not match the package amount.'], 400);
            }
    
            // For credit card payments, mark as paid
            $isPaid = true;
            $paidAt = now();
        }
    
        // Validate rent service fields for enterprise package
        if ($package->id == 3) { // Enterprise package
            if (!isset($validatedData['width']) || !isset($validatedData['height']) || !isset($validatedData['price_per_meter'])) {
                return response()->json(['message' => 'Width, height, and price per meter are required for the enterprise package.'], 400);
            }
        }
    
        DB::beginTransaction();
    
        try {
            // Create the subscription
            $subscription = Subscription::create([
                'user_id' => $validatedData['user_id'],
                'package_id' => $package->id, // Store package_id
                'subscription_type' => $validatedData['subscription_type'],
                'amount' => $amount, // Use the calculated amount
                'payment_method' => $validatedData['payment_method'],
                'voucher_code' => $validatedData['voucher_code'] ?? null,
                'card_number' => $validatedData['card_number'] ?? null,
                'expiration_date' => $validatedData['expiration_date'] ?? null,
                'cvv' => $validatedData['cvv'] ?? null,
                'cardholder_name' => $validatedData['cardholder_name'] ?? null,
                'paid_at' => $paidAt,
                'is_paid' => $isPaid,
            ]);
    
            // Create the warehouse
            $warehouseData = [
                'name' => $validatedData['warehouse_name'],
                'location' => $validatedData['warehouse_location'],
                'user_id' => $validatedData['user_id'],
            ];
    
            // Add rent service fields for enterprise package
            if ($package->id == 3) {
                $warehouseData['width'] = $validatedData['width'];
                $warehouseData['height'] = $validatedData['height'];
                $warehouseData['price_per_meter'] = $validatedData['price_per_meter'];
            }
    
            $warehouse = Warehouse::create($warehouseData);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Subscription and warehouse created successfully.',
                'subscription' => $subscription,
                'warehouse' => $warehouse,
                'voucher_amount' => $amount, // Return the amount used
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred. Please try again later.'], 500);
        }
    }


    private function getSubscriptionAmount($subscriptionType)
    {
        $pricing = [
            'normal' => 50.00,
            'trader' => 100.00,
        ];

        return $pricing[$subscriptionType] ?? null;
    }

    public function validateVoucher($code)
    {
        $voucher = Voucher::where('code', $code)->where('is_used', false)->first();
        if ($voucher) {
            return response()->json(['valid' => true]);
        }
        return response()->json(['valid' => false], 400);
    }


    public function generateVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required_without:quantity|string|unique:vouchers',
            'quantity' => 'required_without:code|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'user_id' => 'required|exists:users,id',
        ]);
    
        if ($request->has('quantity')) {
            $vouchers = [];
            for ($i = 0; $i < $request->quantity; $i++) {
                $vouchers[] = Voucher::create([
                    'code' => str::random(10),
                    'created_by' => $request->user_id,
                    'amount' => $request->amount,
                ]);
            }
            return response()->json(['message' => 'Vouchers generated successfully', 'vouchers' => $vouchers]);
        } else {
            $voucher = Voucher::create([
                'code' => $request->code,
                'created_by' => $request->user_id,
                'amount' => $request->amount,
            ]);
            return response()->json(['message' => 'Voucher created successfully', 'voucher' => $voucher]);
        }
    }
    
    

    public function getVouchers()
    {
        $vouchers = Voucher::with('creator')->get();
        return response()->json(['vouchers' => $vouchers]);
    }



    public function getProfitsData()
    {
        $totalAmount = Subscription::where('is_paid', true)->sum('amount'); // Sum of all paid subscriptions' amounts
        $subscriptionsByType = Subscription::where('is_paid', true)
            ->selectRaw('subscription_type, count(*) as count')
            ->groupBy('subscription_type')
            ->get();
        $subscriptionsByPaymentMethod = Subscription::where('is_paid', true)
            ->selectRaw('payment_method, count(*) as count')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'total_amount' => $totalAmount,
            'subscriptions_by_type' => $subscriptionsByType,
            'subscriptions_by_payment_method' => $subscriptionsByPaymentMethod
        ]);
    }




    public function storeClientPay(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'payment_method' => 'required|string|in:credit_card,voucher,bank_transfer', // Validate payment method
            'voucher_code' => 'nullable|string',
            'warehouse_name' => 'required|string|max:255',
            'warehouse_location' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0', // Validate amount range
            'card_number' => 'nullable|string|max:16',
            'expiration_date' => 'nullable|string|max:5',
            'cvv' => 'nullable|string|max:4',
            'cardholder_name' => 'nullable|string|max:255',
        ]);
    
        // Initialize payment status
        $isPaid = false;
        $paidAt = null;
    
        // If payment method is 'voucher', validate the voucher code
        if ($validatedData['payment_method'] === 'voucher') {
            $voucher = Voucher::where('code', $validatedData['voucher_code'])->first();
    
            // Check if voucher exists and is not used
            if (!$voucher) {
                return response()->json(['message' => 'Invalid voucher code.'], 400);
            }
    
            if ($voucher->is_used) {
                return response()->json(['message' => 'This voucher has already been used.'], 400);
            }
    
            // Validate that the voucher amount matches the requested amount
            if ($voucher->amount != $validatedData['amount']) {
                return response()->json(['message' => 'The voucher amount does not match the requested amount.'], 400);
            }
    
            // Mark the voucher as used
            $voucher->is_used = true;
            $voucher->save();
    
            // Use the voucher amount as the subscription amount
            $amount = $voucher->amount;
            $isPaid = true; // Consider the payment as completed
            $paidAt = now();
        } elseif ($validatedData['payment_method'] === 'credit_card') {
            // For credit card payments, mark as paid
            $isPaid = true;
            $paidAt = now();
            $amount = $validatedData['amount']; // Use the provided amount
        } else {
            // For bank transfer or other methods, use the provided amount
            $amount = $validatedData['amount'];
        }
    
        DB::beginTransaction();
    
        try {
            // Check if a subscription already exists for the user
            $existingSubscription = Subscription::where('user_id', $request->user_id)->first();
    
            if ($existingSubscription) {
                // Subscription already exists, skip creating a new one
                $subscription = $existingSubscription;
            } else {
                // Create the subscription
                $subscription = Subscription::create([
                    'user_id' => $request->user_id, // Use the user_id from the request
                    'package_id' => null, // package_id is now null
                    'subscription_type' => 'client', // Hardcoded to "client"
                    'amount' => $amount, // Use the calculated amount
                    'payment_method' => $validatedData['payment_method'],
                    'voucher_code' => $validatedData['voucher_code'] ?? null,
                    'card_number' => $validatedData['card_number'] ?? null,
                    'expiration_date' => $validatedData['expiration_date'] ?? null,
                    'cvv' => $validatedData['cvv'] ?? null,
                    'cardholder_name' => $validatedData['cardholder_name'] ?? null,
                    'paid_at' => $paidAt,
                    'is_paid' => $isPaid,
                ]);
            }
    
            // Create the warehouse
            $warehouse = Warehouse::create([
                'name' => $validatedData['warehouse_name'],
                'location' => $validatedData['warehouse_location'],
                'user_id' => $request->user_id, // Use the user_id from the request
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Subscription and warehouse created successfully.',
                'subscription' => $subscription,
                'warehouse' => $warehouse,
                'voucher_amount' => $amount, // Return the amount used
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            Log::error('Error in storeClientPay:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'An error occurred. Please try again later.'], 500);
        }
    }
}