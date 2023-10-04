<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class SpinWheelController extends Controller
{
    public function refreshUser(Request $request)
    {
        $extUserId = $request->input('extUserId');

        $spinwheelApiUrl = config('services.spinwheel.api_url');
        $spinwheelApiToken = config('services.spinwheel.api_token');


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'bearer ' . $spinwheelApiToken
        ])->get($spinwheelApiUrl . '/v1/users', [
            'extUserId' => $extUserId
        ]);

        if ($response->successful()) {
            $json = $response->json();
            // Do something with the $json data
            return $json;
        } else {
            // Handle the error
            $statusCode = $response->status();
            $errorMessage = 'Error: ' . $response->body();

            // Return the error message along with the status code
            return response()->json(['message' => $errorMessage], $statusCode);
        }
    }

    public function getUser (Request $request) {
        $userId = $request->input('userId');

        $spinwheelApiBaseUrl = config('services.spinwheel.api_url');
        $spinwheelApiUrl = $spinwheelApiBaseUrl . '/v1/users?userId=' . $userId;
        $spinwheelApiToken = config('services.spinwheel.api_token');

        $client = new Client();

        $response = $client->request('GET', $spinwheelApiUrl, [
            'headers' => [
                'Authorization' => 'bearer ' . $spinwheelApiToken,
                'accept' => 'application/json',
            ],
        ]);

        return $response->getBody();
    }

    public function liabilityPdf(Request $request)
    {
        $userId = $request->input('userId');

        $spinwheelApiBaseUrl = config('services.spinwheel.api_url');
        $spinwheelApiUrl = $spinwheelApiBaseUrl . '/v1/users/' . $userId . '/creditReport';
        $spinwheelApiToken = config('services.spinwheel.api_token');

        $client = new Client();

        $response = $client->request('GET', $spinwheelApiUrl, [
            'headers' => [
                'Authorization' => 'bearer ' . $spinwheelApiToken,
                'accept' => 'application/json',
            ],
        ]);

        return $response->getBody();
    }
}
