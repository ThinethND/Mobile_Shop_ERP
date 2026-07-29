<?php

namespace App\Http\Controllers;

use App\Models\DeliveryChargeSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryChargeSettingController extends Controller
{
    public function edit(): Response
    {
        $fees = DeliveryChargeSetting::currentFees();

        return Inertia::render('OtherCMS/DeliveryCharges/index', [
            'cashOnDeliveryFee' => $fees['cash_on_delivery_fee'],
            'bankTransferDeliveryFee' => $fees['bank_transfer_delivery_fee'],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'cash_on_delivery_fee' => ['required', 'numeric', 'min:0'],
            'bank_transfer_delivery_fee' => ['required', 'numeric', 'min:0'],
        ]);

        DeliveryChargeSetting::updateFees(
            $validated['cash_on_delivery_fee'],
            $validated['bank_transfer_delivery_fee']
        );

        return redirect()
            ->route('delivery-charges.edit')
            ->with('success', 'Delivery charges updated.');
    }
}
