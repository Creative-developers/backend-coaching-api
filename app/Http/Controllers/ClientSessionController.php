<?php

namespace App\Http\Controllers;

use App\Models\CoachingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AnalyticsService;

class ClientSessionController extends Controller
{
    public function pendingSessions(Request $request)
    {
        try {
            $sessions = CoachingSession::where('user_id', $request->user()->id)
                                        ->where('status', collect(config('enums.client_session_status'))->search('scheduled'))
                                        ->get();

            return response()->json(['data' => $sessions], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong '. $e->getMessage()], 500);

        }

    }

    public function updateSession(Request $request, CoachingSession $coachingSession, AnalyticsService $analyticsService)
    {
        try {
            if ($coachingSession->status === collect(config('enums.client_session_status'))->search('completed')) {
                return response()->json(['message' => 'Session is already completed'], 400);
            }

            $coachingSession->update([
                 'status' => collect(config('enums.client_session_status'))->search('completed'),
                 'completed_at' => now()
            ]);

            // Update session stats in redis
            $analyticsService->markSessionAsCompleted($coachingSession->coach_id, $coachingSession->client_id);

            Log::info("Client {$coachingSession->client_id} session with coach {$coachingSession->coach_id} is successfully completed at {$coachingSession->completed_at}");

            return response()->json(['message' => 'Session marked as completed', 'data' => $coachingSession], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong '. $e->getMessage()], 500);
        }
    }
}
