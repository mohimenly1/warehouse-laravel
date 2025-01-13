<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockingRequest;
use App\Models\TempProductDetail;
use App\Models\Warehouse;
use App\Models\Wallet;
class StockingRequestController extends Controller
{


    public function getClientStockingRequests($clientId)
{
    try {
        // Fetch stocking requests for the client
        $requests = StockingRequest::with(['warehouse'])
            ->where('client_id', $clientId)
            ->get();

        return response()->json([
            'message' => 'Stocking requests fetched successfully.',
            'requests' => $requests,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function store(Request $request)
{
    $validated = $request->validate([
        'client_id' => 'required|exists:users,id',
        'warehouse_id' => 'required|exists:warehouses,id',
    ]);

    $request = StockingRequest::create($validated);

    return response()->json([
        'message' => 'Stocking request submitted successfully.',
        'request' => $request,
    ]);
}

    // Accept or reject a stocking request
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $stockingRequest = StockingRequest::findOrFail($id);
        $stockingRequest->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Stocking request updated successfully.',
            'request' => $stockingRequest,
        ]);
    }

    // Get all stocking requests for a warehouse
    public function index($warehouseId)
    {
        try {
            $requests = StockingRequest::with(['client', 'warehouse'])
                ->where('warehouse_id', $warehouseId)
                ->get();
    
            return response()->json([
                'message' => 'Stocking requests fetched successfully.',
                'requests' => $requests,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


//     public function acceptStockingRequest($id)
// {
//     try {
//         $stockingRequest = StockingRequest::findOrFail($id);
//         $stockingRequest->update(['status' => 'accepted']);

//         return response()->json([
//             'message' => 'Stocking request accepted successfully.',
//             'request' => $stockingRequest,
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'message' => 'Server error occurred.',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }

public function acceptStockingRequest(Request $request, $id)
{
    try {
        $validated = $request->validate([
            'subscription_amount' => 'required|numeric',
        ]);

        $stockingRequest = StockingRequest::findOrFail($id);
        $stockingRequest->update([
            'status' => 'accepted',
            'subscription_amount' => $validated['subscription_amount'],
        ]);

        return response()->json([
            'message' => 'Stocking request accepted successfully.',
            'request' => $stockingRequest,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function declineStockingRequest($id)
{
    try {
        $stockingRequest = StockingRequest::findOrFail($id);
        $stockingRequest->update(['status' => 'declined']);

        return response()->json([
            'message' => 'Stocking request declined successfully.',
            'request' => $stockingRequest,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function getClientStatus($clientId)
{
    $status = StockingRequest::where('client_id', $clientId)
        ->pluck('status')
        ->first();

    return response()->json([
        'status' => $status ?? 'No request found',
    ]);
}


public function submitProductDetails(Request $request)
{
    $validated = $request->validate([
        'client_id' => 'required|exists:users,id',
        'warehouse_id' => 'required|exists:warehouses,id',
        'description' => 'required|string',
        'quantity' => 'required|integer',
    ]);

    // Store product details temporarily (e.g., in a `temp_product_details` table)
    $tempDetails = TempProductDetail::create($validated);

    return response()->json([
        'message' => 'Product details submitted successfully.',
        'details' => $tempDetails,
    ]);
}



public function markAsPaid($id)
{
    try {
        // Find the stocking request
        $stockingRequest = StockingRequest::findOrFail($id);

        // Mark the request as paid
        $stockingRequest->update(['is_paid' => true]);

        // Get the warehouse owner's user_id
        $warehouse = Warehouse::findOrFail($stockingRequest->warehouse_id);
        $ownerUserId = $warehouse->user_id;

        // Get or create the owner's wallet
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $ownerUserId],
            ['balance' => 0] // Default balance if creating a new wallet
        );

        // Update the wallet balance
        $wallet->increment('balance', $stockingRequest->subscription_amount);

        return response()->json([
            'message' => 'تم تحديث حالة الدفع بنجاح.',
            'request' => $stockingRequest,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ أثناء تحديث حالة الدفع.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}