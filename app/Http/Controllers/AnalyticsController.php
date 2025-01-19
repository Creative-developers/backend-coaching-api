<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function fetchAnalytics(Request $request)
    {
        $coachId = $request->user()->id;
        $analytics = $this->analyticsService->getAnalytics($coachId);

        return response()->json($analytics);
    }
}
