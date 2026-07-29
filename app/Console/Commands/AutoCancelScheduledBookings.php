<?php

namespace App\Console\Commands;

use App\Events\BookingCancel;
use App\Http\Helpers;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCancelScheduledBookings extends Command
{
    /**
     * Command signature.
     * Run: php artisan bookings:auto-cancel
     */
    protected $signature = 'bookings:auto-cancel';

    /**
     * Description.
     */
    protected $description = 'Auto-cancel scheduled bookings that are 10 minutes past their start time and send SMS.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        $bookings = Booking::query()
            ->where('status', 'scheduled')
            ->whereNull('auto_cancelled_at')
            ->where('starts_at', '<=', $now->subMinutes(10))
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings to auto-cancel.');
            return Command::SUCCESS;
        }

        foreach ($bookings as $booking) {

            $booking->update([
                'status'             => 'cancel',
                'auto_cancelled'     => true,
                'auto_cancelled_at'  => now(),
                'auto_cancel_reason' => 'Late arrival – automatically cancelled.',
            ]);

            broadcast(new BookingCancel($booking))->toOthers();
            $client      = optional($booking->client);
            $clientName  = $client->first_name ?? 'Customer';
            $clientPhone = $client->phone ?? null;

            if ($clientPhone) {

                  $message = "Hi {$clientName}, your booking #{$booking->id} has been cancelled because of late arrival.";

                try {
                    Log::info($clientPhone);
                    Helpers::sendSMSAlerts($message, $clientPhone, '94');
                } catch (\Throwable $e) {
                    Log::error("SMS Auto-Cancel Error for booking {$booking->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("Auto-cancelled {$bookings->count()} booking(s) and SMS sent.");
        Log::info("Auto-cancelled {$bookings->count()} booking(s) via scheduler.");

        return Command::SUCCESS;
    }
}
