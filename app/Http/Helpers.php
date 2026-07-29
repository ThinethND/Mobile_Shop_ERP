<?php

namespace App\Http;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Rate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

use function PHPUnit\Framework\isEmpty;

class Helpers
{
    public static function randomStringGenarator($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public static function otpGenarator($length = 5)
    {
        $characters = '0123456789';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    // public static function sendSMSAlerts($message = "",$phone, $countryCode = '94'){
    //     $api_key = '19|fDtsSXt0ZpUroSP7cQ4gCC7ML4kJ1MIDBBbcqTio';
    //     $apiUrl = 'https://sms-api.ipsova.com/api/v3/sms/send';

    //     if (substr($phone, 0, 1) === '0') {
    //         $mobileNumber = $countryCode . substr($phone, 1);
    //     } else {
    //         $mobileNumber = $countryCode . $phone;
    //     }
    //     try {
    //         $response = Http::withHeaders([
    //             'Authorization' =>  'Bearer ' . $api_key,
    //             'Content-Type' => 'application/json'
    //         ])->post($apiUrl, [
    //             'recipient' => $mobileNumber,
    //             'sender_id' => 'WEBLOOK',
    //             'type' => 'plain',
    //             'message' => $message,
    //         ]);
    //     } catch (\Throwable $e) {
    //         Log::error('SMS OTP Error: ' . $e->getMessage());
    //     }
    // }


    public static function sendSMSAlerts($message = "", $phone, $countryCode = '94')
    {
        // Dialog API key
        $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MTQwMTIsImN1c3RvbWVyX3JvbGUiOjAsImlhdCI6MTcyNzI1NDE3NSwiZXhwIjo0ODUxNDU2NTc1fQ.hfbRBIgkyngpkazPJpo1hO0Ebc1FO-w2BiJ168VuOMY';

        // Normalize phone number
        if (substr($phone, 0, 1) === '0') {
            $mobileNumber = $countryCode . substr($phone, 1);
        } else {
            $mobileNumber = $countryCode . $phone;
        }

        // Dialog URL endpoint
        $apiUrl = "https://e-sms.dialog.lk/api/v1/message-via-url/create/url-campaign";

        try {
            // Build full query URL
            $fullUrl = $apiUrl . '?' . http_build_query([
                'esmsqk'          => $api_key,
                'list'            => $mobileNumber,
                'source_address'  => 'DezeStore',           // Sender Mask
                'message'         => $message,
            ]);

            // Send GET request
            $response = Http::get($fullUrl);

            // OPTIONAL: log response for debugging
            Log::info('Dialog SMS Response:', [
                'url' => $fullUrl,
                'response' => $response->body()
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Dialog SMS Error: ' . $e->getMessage());
            return false;
        }
    }
}
