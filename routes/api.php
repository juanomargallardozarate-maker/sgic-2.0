<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Customer;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Endpoint público para verificar estado del teléfono de un cliente
Route::get('/customers/{id}/phone-status', function($id) {
    $customer = Customer::find($id);
    
    if (!$customer) {
        return response()->json(['error' => 'Cliente no encontrado'], 404);
    }
    
    return response()->json([
        'has_phone' => !empty($customer->phone),
        'phone' => $customer->phone,
        'is_verified' => $customer->isPhoneVerified(),
    ]);
});
