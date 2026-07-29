<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use App\Models\NotificationQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPendingSms extends Command
{
    protected $signature = 'sms:send-pending {--force : Ignore scheduled_at and send all pending SMS}';

    protected $description = 'Send pending SMS notifications from notification_queues';

    public function handle(): int
    {
        $now = now();

        $query = NotificationQueue::where('channel', 'sms')
            ->where('status', 'pending');

        if (! $this->option('force')) {
            $query->where('scheduled_at', '<=', $now);
        }

        $items = $query->orderBy('id')->limit(50)->get();

        if ($items->isEmpty()) {
            $this->info('No pending SMS notifications.');
            return self::SUCCESS;
        }

        foreach ($items as $item) {
$booking = $item->booking_id ? Booking::find($item->booking_id) : null;

if ($booking) {
    $bs = strtolower((string) $booking->status);

    $reminderTypes = ['booking_reminder_1d', 'booking_reminder_1h'];

    if (in_array($item->type, $reminderTypes, true) && $bs !== 'scheduled') {
        $item->status = 'cancelled';
        $item->error_message = 'Skipped: reminder not valid (booking not scheduled)';
        $item->attempts = ($item->attempts ?? 0) + 1;
        $item->last_attempt_at = now();
        $item->save();
        continue;
    }
}

            $this->info("Sending SMS #{$item->id} to {$item->phone}");

            try {

                // Normalize phone number for Dialog
                $mobile = $this->normalizePhone($item->phone);

                // Dialog API Key
                $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MTQwMTIsImN1c3RvbWVyX3JvbGUiOjAsImlhdCI6MTcyNzI1NDE3NSwiZXhwIjo0ODUxNDU2NTc1fQ.hfbRBIgkyngpkazPJpo1hO0Ebc1FO-w2BiJ168VuOMY';


                // Endpoint
                $apiUrl = "https://e-sms.dialog.lk/api/v1/message-via-url/create/url-campaign";

                // Build Request URL
                $fullUrl = $apiUrl . '?' . http_build_query([
                    'esmsqk'         => $api_key,
                    'list'           => $mobile,
                    'source_address' => 'DezeStore',
                    'message'        => $item->message,
                ]);

                // Send GET request
                $response = Http::timeout(15)->get($fullUrl);

                // Log raw response
                Log::info("Dialog SMS Response", [
                    'queue_id' => $item->id,
                    'url'      => $fullUrl,
                    'response' => $response->body(),
                ]);

                // Validate Dialog success (response "1")
                if (trim($response->body()) == "1") {
                    $item->status = 'sent';
                } else {
                    $item->status = 'failed';
                    $item->error_message = "Dialog returned: " . $response->body();
                }
            } catch (\Throwable $e) {
                // Log error
                Log::error("Dialog SMS Error for Queue {$item->id}", [
                    'phone' => $item->phone,
                    'error' => $e->getMessage(),
                ]);

                $item->status = 'failed';
                $item->error_message = $e->getMessage();
            }

            // Update attempt info
            $item->attempts = ($item->attempts ?? 0) + 1;
            $item->last_attempt_at = now();
            if ($item->status === 'sent') {
                $item->sent_at = now();
            }

            $item->save();
        }

        return self::SUCCESS;
    }

    /**
     * Normalize phone number to 94XXXXXXXXX
     */
    private function normalizePhone($phone)
    {
        $phone = preg_replace('/\D/', '', $phone); // Remove non-numerics

        if (substr($phone, 0, 1) === '0') {
            return '94' . substr($phone, 1);
        }

        if (substr($phone, 0, 2) === '94') {
            return $phone;
        }

        return '94' . $phone;
    }
}
