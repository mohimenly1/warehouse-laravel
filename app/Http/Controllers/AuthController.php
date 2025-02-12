<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\RentClient;
use App\Models\Wallet;
use App\Models\Notification;
class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'email' => 'nullable|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'user_type' => 'required|string|in:trader,normal',
            ]);
    
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'user_type' => $validated['user_type'],
                'status' => 'inactive', // Default status
                'is_paid' => 0, // Default payment status
            ]);
    
            return response()->json([
                'message' => 'User registered successfully. Awaiting payment and admin approval.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Server error occurred.',
            ], 500);
        }
    }
    
    
    
    // Login user
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }
    
        $user = Auth::user();
    
        // Check if user is active and has paid
        if ($user->status !== 'active' || $user->is_paid !== 1) {
            return response()->json([
                'message' => 'Account is not active or payment is pending.',
            ], 403);
        }
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        $warehouse = Warehouse::where('user_id', $user->id)->first();
        $warehouseStaff = WarehouseStaff::where('user_id', $user->id)->first();
    
        // Fetch subscription details
        $subscription = $user->subscription; // Assuming a one-to-one relationship between User and Subscription
    
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'user_type' => $user->user_type,
                'warehouse_id' => $warehouse ? $warehouse->id : ($warehouseStaff ? $warehouseStaff->warehouse_id : null), // Handle warehouse_id gracefully
                'warehouse_name' => $warehouse ? $warehouse->name : ($warehouseStaff ? $warehouseStaff->warehouse->name : null), // Return warehouse name
                'subscription_type' => $subscription ? $subscription->subscription_type : null, // Include subscription type
                'package_id' => $subscription ? $subscription->package_id : null, // Include package ID
            ],
            'auth_token' => $token,
        ]);
    }
    
    
    // Logout user
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }


    public function createWarehouseStaff(Request $request)
{
    // Validate input
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'username' => 'required|string|unique:users',
        'password' => 'required|string|min:3',
        'warehouse_id' => 'required|exists:warehouses,id',
    ]);

    // Create the user
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'username' => $validated['username'],
        'password' => $validated['password'],
        'user_type' => 'staff', // Set user_type as trader
    ]);

    // Assign the user to the warehouse
    WarehouseStaff::create([
        'warehouse_id' => $validated['warehouse_id'], // From request
        'user_id' => $user->id, // Newly created user
    ]);

    return response()->json([
        'message' => 'Staff member created and assigned to warehouse successfully.',
        'user' => $user,
    ], 201);
}



public function indexStaff(Request $request)
{
    $warehouseId = $request->header('warehouse_id'); // Get warehouse_id from the request header

    if (!$warehouseId) {
        return response()->json(['error' => 'Warehouse ID is required'], 400);
    }

    $staff = WarehouseStaff::where('warehouse_id', $warehouseId)->with('user')->get();

    return response()->json($staff, 200);
}



public function registerClient(Request $request)
{
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'user_type' => 'required|string|in:trader,normal,client',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'user_type' => $validated['user_type'],
            'status' => 'inactive', // Default status
            'is_paid' => 0, // Default payment status
        ]);

        // Create a wallet for the user
        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
        ], 201);
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation Failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
        ], 500);
    }
}



public function storeRentClient(Request $request)
{
    try {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'width' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
        ]);

        // Create the rent client with active = false
        $rentClient = RentClient::create([
            'user_id' => $validated['user_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'width' => $validated['width'],
            'height' => $validated['height'],
            'total_price' => $validated['total_price'],
            'active' => false, // Default to false
        ]);

        // Create a notification for the warehouse owner
        Notification::create([
            'user_id' => $rentClient->warehouse->user_id,
            'rent_client_id' => $rentClient->id,
            'message' => 'طلب اجار جديد قد وصل: ' . $rentClient->warehouse->name,
        ]);

        return response()->json([
            'message' => 'Rent client created successfully.',
            'rent_client' => $rentClient,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
        ], 500);
    }
}



public function checkUser(Request $request)
{
    try {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
        ]);

        // Check if the user exists by username or email
        $user = User::where('username', $validated['username'])
            ->orWhere('email', $validated['email'])
            ->first();

        if ($user) {
            return response()->json([
                'exists' => true,
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'exists' => false,
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Server error occurred.',
        ], 500);
    }
}


}
