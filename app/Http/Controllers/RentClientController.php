<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\RentClient;
use App\Models\Warehouse;
use App\Models\Notification;
use App\Models\User;
class RentClientController extends Controller
{
    //

    public function handleRentResponse(Request $request)
    {
        try {
            $validated = $request->validate([
                'rent_client_id' => 'required|exists:rent_clients,id',
                'response' => 'required|string|in:accept,decline', // Accept or decline the rent request
            ]);
    
            $rentClient = RentClient::find($validated['rent_client_id']);
            $warehouse = Warehouse::find($rentClient->warehouse_id);
    
            if ($validated['response'] === 'accept') {
                // Mark the warehouse as busy
                $warehouse->status = 'busy';
                $warehouse->save();
    
                // Transfer the amount to the warehouse owner's wallet
                $ownerWallet = Wallet::firstOrCreate(
                    ['user_id' => $warehouse->user_id], // Search condition
                    ['balance' => 0] // Default balance if wallet doesn't exist
                );
                $ownerWallet->balance += $rentClient->total_price;
                $ownerWallet->save();
    
                // Mark the rent client as active
                $rentClient->active = true;
                $rentClient->save();
            } else {
                // Return the amount to the client's wallet
                $clientWallet = Wallet::firstOrCreate(
                    ['user_id' => $rentClient->user_id], // Search condition
                    ['balance' => 0] // Default balance if wallet doesn't exist
                );
                $clientWallet->balance += $rentClient->total_price;
                $clientWallet->save();
    
                // Mark the rent client as inactive
                $rentClient->active = false;
                $rentClient->save();
            }
    
            // Mark the notification as read
            Notification::where('rent_client_id', $rentClient->id)->update(['is_read' => true]);
    
            return response()->json([
                'message' => 'Rent request response handled successfully.',
                'rent_client' => $rentClient,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error occurred.',
            ], 500);
        }
    }

    // RentClientController.php
public function getRentRequests($warehouseId)
{
    try {
        $rentRequests = RentClient::with(['user', 'warehouse'])
            ->where('warehouse_id', $warehouseId)
            ->get();

        return response()->json([
            'message' => 'Rent requests fetched successfully.',
            'data' => $rentRequests,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
        ], 500);
    }
}


// UserController.php
public function updateUserStatus(Request $request, $userId)
{
    try {
        $validated = $request->validate([
            'status' => 'required|in:pending,active,inactive', // Validate the status
        ]);

        $user = User::findOrFail($userId);
        $user->status = $validated['status'];
        $user->save();

        return response()->json([
            'message' => 'User status updated successfully.',
            'user' => $user,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
        ], 500);
    }
}

public function updateUserPaymentStatus(Request $request, $userId)
{
    try {
        $validated = $request->validate([
            'is_paid' => 'required|boolean', // Validate the is_paid field
        ]);

        $user = User::findOrFail($userId);
        $user->is_paid = $validated['is_paid'];
        $user->save();

        return response()->json([
            'message' => 'User payment status updated successfully.',
            'user' => $user,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
        ], 500);
    }
}
}
