<?php

namespace App\Http\Controllers;

use App\Models\KokoPaySetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KokoPaySettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('KokoPay/index', [
            'percentage' => KokoPaySetting::currentPercentage(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        KokoPaySetting::updatePercentage($validated['percentage'] ?? 0);

        return redirect()
            ->route('koko-pay.edit')
            ->with('success', 'Koko Pay percentage updated.');
    }
}
