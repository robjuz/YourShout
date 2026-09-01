<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/push-subscriptions', function (Request $request) {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'p256dh' => 'required|string',
            'auth' => 'required|string',
        ]);

        $request->user()->updatePushSubscription(
            $validated['endpoint'],
            $validated['p256dh'],
            $validated['auth'],
            'aes128gcm'
        );

        return response()->json(['status' => 'ok']);
    });

    Route::delete('/push-subscriptions/{endpoint}', function (Request $request, string $endpoint) {
        $request->user()->deletePushSubscription($endpoint);

        return response()->json(['status' => 'ok']);
    });
});
