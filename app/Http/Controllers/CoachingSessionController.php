<?php

namespace App\Http\Controllers;

use App\Models\CoachingSession;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Client;

class CoachingSessionController extends Controller
{
    public function index(Request $request)
    {
        $coach_sessions = CoachingSession::where('coach_id', $request->user()->id)->with('client')->paginate(10);
        return response()->json($coach_sessions);
    }

    public function store(Request $request, AnalyticsService $analyticsService)
    {
        try {
            $validated = $request->validate([
                'client_id' => [
                     'required',
                     'exists:clients,id',
                     function ($attribute, $value, $fail) use ($request) {
                         if (!$request->user()->clients()->where('id', $value)->exists()) {
                             $fail('Client must be associated with the current coach');
                         }
                     }
                ],
                'session_date' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        $existingSession = CoachingSession::where('coach_id', $request->user()->id)
                        ->where('session_date', $value)
                        ->where('status', '!=', 'completed')
                        ->exists();

                        Log::info('coaching-session: Session is already booked on this date ' . $request->date . ' with client ' . $request->client_id . ' and coach ' . $request->user()->id);

                        if ($existingSession) {
                            $fail('Another session is already scheduled on this date.');
                        }
                    }
                ]
            ]);

            $session = DB::transaction(function () use ($validated, $request, $analyticsService) {


                $client = Client::find($validated['client_id']);
                $userId = $client->user_id;

                // Update session stats in redis
                $analyticsService->updateSession($request->user()->id, $validated['client_id']);

                return CoachingSession::create([
                    'coach_id' => $request->user()->id,
                    'client_id' => $validated['client_id'],
                    'user_id' => $userId,
                    'session_date' => $validated['session_date'],
                    'role' => collect(config('enums.client_session_status'))->search('scheduled') ?? 1,
                ]);
            });

            return response()->json(['message' => 'Session created successfully', 'session' => $session], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed' ,'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating session '. $e->getMessage()], 500);
        }
    }

    public function update(Request $request, CoachingSession $coachingSession, AnalyticsService $analyticsService)
    {
        try {
            $validated = $request->validate([
                'session_date' => [
                    'sometimes',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        $existingSession = CoachingSession::where('coach_id', $request->user()->id)
                        ->where('session_date', $value)
                        ->where('status', '!=', 'completed')
                        ->exists();

                        if ($existingSession) {
                            $fail('Another session is already scheduled on this date.');
                        }

                        Log::info('coaching-session: Session is already booked on this date ' . $request->date . ' with client ' . $request->client_id . ' and coach ' . $request->user()->id);

                    }
                ]
            ]);

            if ($coachingSession->status !== array_search('completed', config('enums.client_session_status', []))) {
                if ($request->has('status') && $request->input('status') == array_search('completed', config('enums.client_session_status', []))) {
                    $validated['status'] = array_search('completed', config('enums.client_session_status', []));
                    $validated['completed_at'] = now();

                    // Update session stats in redis
                    $analyticsService->markSessionAsCompleted($request->user()->id, $coachingSession->client_id);

                    Log::info("Client {$request->client_id} session with coach {$request->user()->id} is successfully completed at {$validated['completed_at']}");

                }
            }

            $coachingSession->update($validated);

            return response()->json(['message' => 'Session updated successfully', 'session' => $coachingSession]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed' ,'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating session'. $e->getMessage()], 500);
        }
    }

    // Delete a session
    public function destroy(CoachingSession $coachingSession)
    {
        $coachingSession->delete();

        return response()->json(['message' => 'Session deleted successfully'], 200);
    }
}
