<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\SpinWheelController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('dashboard', function (Illuminate\Http\Client\Factory $http) {
    $extUserId = Str::uuid();

    $spinwheelApiUrl = config('services.spinwheel.api_url');
    $spinwheelApiToken = config('services.spinwheel.api_token');

    try {
        $response = $http->withHeaders([
            'Authorization' => 'Bearer ' . $spinwheelApiToken,
            'Content-Type' => 'application/json',
        ])->post($spinwheelApiUrl . '/v1/dim/token', [
            'extUserId' => $extUserId,
        ]);

        $spinwheelToken = $response->json()['data']['token'];

        return view('dashboard', ['spinwheelToken' => $spinwheelToken, 'extUserId' => $extUserId]);

    } catch (RequestException $e) {
        // Handle the exception, maybe log it or return a default view/message
        // For simplicity, redirecting back with an error in this example
        return redirect()->back()->withErrors(['error' => 'Failed to retrieve Spinwheel token.']);
    }
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/spinwheel/refresh-user', [SpinWheelController::class, 'refreshUser'])
    ->middleware(['auth', 'verified'])
    ->name('spinwheel.refresh-user');

Route::get('/spinwheel/liability-pdf', [SpinWheelController::class, 'liabilityPdf'])
    ->middleware(['auth', 'verified'])
    ->name('spinwheel.liability-pdf');

Route::get('/spinwheel/get-user', [SpinWheelController::class, 'getUser'])
    ->middleware(['auth', 'verified'])
    ->name('spinwheel.get-user');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
