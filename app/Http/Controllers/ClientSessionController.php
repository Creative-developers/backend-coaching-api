<?php

namespace App\Http\Controllers;

use App\Models\CoachingSession;
use Illuminate\Http\Request;

class ClientSessionController extends Controller
{
    public function pendingSessions(Request $request)
    {
        $sessions = CoachingSession::where('client_id', $request->user()->id)
                                    ->where('status', collect(config('enums.client_session_status'))->search('scheduled'))
                                    ->get();

        return response()->json(['data' => $sessions], 201);

    }

    public function updateSession(Request $request, CoachingSession $coachingSession)
    {
        if ($coachingSession->status === collect(config('enums.client_session_status'))->search('completed')) {
            return response()->json(['message' => 'Session is already completed'], 400);
        }

        $coachingSession->update(['status' => collect(config('enums.client_session_status'))->search('completed')]);

        return response()->json(['message' => 'Session marked as completed', 'data' => $coachingSession], 200);
    }
}
