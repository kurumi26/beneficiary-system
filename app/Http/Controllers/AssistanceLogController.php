<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssistanceLogRequest;
use App\Models\Beneficiary;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;

class AssistanceLogController extends Controller
{
    public function store(StoreAssistanceLogRequest $request, Beneficiary $beneficiary, ActivityLogger $activityLogger): RedirectResponse
    {
        $log = $beneficiary->assistanceLogs()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $activityLogger->log(
            'assistance.created',
            "Logged assistance for {$beneficiary->full_name}.",
            $beneficiary,
            ['assistance_type' => $log->assistance_type, 'amount' => $log->amount],
        );

        return redirect()->route('beneficiaries.show', $beneficiary)->with('status', 'Assistance log added successfully.');
    }
}
