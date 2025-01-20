<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AnalyticsService
{
    protected string $tag = 'analytics';


    /**
     * Update Redis when a session is created.
     *
     * @param int $coachId
     * @param int $clientId
     * @return void
     */
    public function updateSession(int $coachId, int $clientId): void
    {

        $coachKey = "{$this->tag}:session-analytics:{$coachId}";

        Redis::incr("{$coachKey}:total_sessions");
        Redis::hIncrBy("{$coachKey}:clients", "client:{$clientId}:total_sessions", 1);

        Log::info("{$this->tag}: Session created for coach: {$coachId}, client: {$clientId}");
    }

    /**
     * Update Redis when a session is marked as completed.
     *
     * @param int $coachId
     * @param int $clientId
     * @return void
     */
    public function markSessionAsCompleted(int $coachId, int $clientId): void
    {

        $coachKey = "{$this->tag}:session-analytics:{$coachId}";

        // Increment completed sessions for coach and client
        Redis::incr("{$coachKey}:completed_sessions");
        Redis::hIncrBy("{$coachKey}:clients", "client:{$clientId}:completed_sessions", 1);
    }

    /**
     * Fetch analytics data from Redis.
     *
     * @param int $coachId
     * @return array
     */
    public function getAnalytics(int $coachId): array
    {

        $coachKey = "{$this->tag}:session-analytics:{$coachId}";

        $totalSessions = Redis::get("{$coachKey}:total_sessions") ?? 0;
        $completedSessions = Redis::get("{$coachKey}:completed_sessions") ?? 0;



        $clientsData = Redis::hGetAll("{$coachKey}:clients");

        $clients = [];
        foreach ($clientsData as $key => $value) {

            $clientId = explode(':', $key)[1];

            $clients[$clientId]['total_sessions']  = 0;
            $clients[$clientId]['completed_sessions'] = 0;

            if (str_contains($key, ':total_sessions')) {
                $clients[$clientId]['total_sessions'] = (int) $value;
            } elseif (str_contains($key, ':completed_sessions')) {
                $clients[$clientId]['completed_sessions'] = (int) $value;
            }
        }

        foreach ($clients as $clientId => $data) {
            $total = $data['total_sessions'] ?? 0;
            $completed = $data['completed_sessions'] ?? 0;
            $clients[$clientId]['progress'] = $total > 0 ? round(($completed / $total) * 100, 2) . '%' : '0%';
        }

        return [
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions ?? 0,
            'clients' => $clients,
        ];
    }
}
