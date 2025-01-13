<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
class VoucherController extends Controller
{
    //

    public function validateVoucher(Request $request)
{
    $validated = $request->validate([
        'code' => 'required|string|exists:vouchers,code',
        'amount' => 'required|numeric',
    ]);

    $voucher = Voucher::where('code', $validated['code'])->first();

    if ($voucher->is_used) {
        return response()->json([
            'valid' => false,
            'message' => 'الكوبون مستخدم بالفعل.',
        ]);
    }

    if ($voucher->amount != $validated['amount']) {
        return response()->json([
            'valid' => false,
            'message' => 'مبلغ الكوبون غير مطابق.',
        ]);
    }

    // Mark voucher as used
    $voucher->update(['is_used' => true]);

    return response()->json([
        'valid' => true,
        'message' => 'الكوبون صالح.',
    ]);
}
}
