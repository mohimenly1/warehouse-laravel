<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
    
            $notifications = Notification::where('user_id', $validated['user_id'])
                ->with(['rentClient.user']) // Eager load rentClient and its associated user
                ->orderBy('created_at', 'desc')
                ->get();
    
            // Format the response to include rent client details
            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'user_id' => $notification->user_id,
                    'rent_client_id' => $notification->rent_client_id,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                    'rent_client' => [
                        'name' => $notification->rentClient->user->name,
                        'email' => $notification->rentClient->user->email,
                        'total_price' => $notification->rentClient->total_price,
                    ],
                ];
            });
    
            return response()->json($formattedNotifications);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error occurred.',
            ], 500);
        }
    }


public function markAsRead($id)
{
    try {
        $notification = Notification::findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
        ], 500);
    }
}
}
