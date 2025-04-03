<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RecordTrackerService;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    protected RecordTrackerService $recordTracker;

    public function __construct(RecordTrackerService $recordTracker)
    {
        $this->recordTracker = $recordTracker;
    }

    public function updateRecords(Request $request, User $user)
    {
        $updatedRecords = $this->recordTracker->updateUserRecords($user);
        return response()->json([
            'message' => 'Rekordy aktualizovány',
            'data' => $updatedRecords,
        ]);
    }
}
