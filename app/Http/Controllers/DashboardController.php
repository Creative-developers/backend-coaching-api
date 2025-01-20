<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EvaluteAIPromptResponseService;
use App\Models\PromptLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(EvaluteAIPromptResponseService $evaluteAIService)
    {
        // Most Used Prompts
        $mostUsedPrompts = PromptLog::select('prompt', DB::raw('COUNT(*) as usage_count'))
        ->groupBy('prompt')
        ->orderByDesc('usage_count')
        ->take(10)
        ->get();

        // Average Scores
        $averageScores = PromptLog::select(
            DB::raw('AVG(relevance) as avg_relevance'),
            DB::raw('AVG(clarity) as avg_clarity'),
            DB::raw('AVG(tone) as avg_tone'),
            DB::raw('AVG(average_score) as avg_overall')
        )->first();

        // Suggestions for Improving Prompt
        $lowScoringPrompts = PromptLog::where('average_score', '<', 6)
        ->select('prompt', 'average_score', 'clarity', 'tone', 'relevance')
        ->orderBy('average_score')
        ->take(10)
        ->get()
        ->map(function ($promptLog) use ($evaluteAIService) {
            $suggestions = $evaluteAIService->generatePromptSuggestions($promptLog->toArray());
            return [
                'prompt' => $promptLog->prompt,
                'average_score' => $promptLog->average_score,
                'suggestions' => $suggestions,
            ];
        });

        return view('admin.dashboard', [
            'mostUsedPrompts' => $mostUsedPrompts,
            'averageScores' => $averageScores,
            'lowScoringPrompts' => $lowScoringPrompts,
        ]);
    }
}
