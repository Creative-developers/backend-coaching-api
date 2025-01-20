<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EvaluteAIPromptResponseService
{
    public function parseEvaluation(string $evaluation): array
    {
        preg_match('/Clarity:\s*(\d+)/i', $evaluation, $clarityMatch);
        preg_match('/Tone:\s*(\d+)/i', $evaluation, $toneMatch);
        preg_match('/Relevance:\s*(\d+)/i', $evaluation, $relevanceMatch);
        preg_match('/Average Score:\s*(\d+)/i', $evaluation, $averageScoreMatch);

        return [
            'clarity' => $clarityMatch[1] ?? null,
            'tone' => $toneMatch[1] ?? null,
            'relevance' => $relevanceMatch[1] ?? null,
            'average_score' => $averageScoreMatch[1] ?? null,
        ];
    }

    //provide suggestions based on data
    public function generatePromptSuggestions(array $evaluationData): array
    {
        $suggestions = [];

        if ($evaluationData['relevance'] < 6) {
            $suggestions[] = 'Ensure your prompt includes specific context and is focused on a clear topic.';
        }

        if ($evaluationData['clarity'] < 6) {
            $suggestions[] = 'Simplify your prompt by using clear and direct language. Avoid ambiguous or negative phrases.';
        }

        if ($evaluationData['tone'] < 6) {
            $suggestions[] = 'Adjust the tone of your prompt to be more professional or neutral for better responses.';
        }

        if (empty($suggestions)) {
            $suggestions[] = 'Your prompt is well-designed! Keep crafting prompts with clear context and intent.';
        }

        return $suggestions;
    }

    // public function analyzeResponse(string $prompt, string $response): array
    // {
    //     $relevance = $this->evaluateRelevance($prompt, $response);
    //     $clarity = $this->evaluateClarity($response);
    //     $tone = $this->evaluateTone($response);

    //     $averageScore = round(($relevance + $clarity + $tone) / 3, 2);

    //     return [
    //         'relevance' => $relevance,
    //         'clarity' => $clarity,
    //         'tone' => $tone,
    //         'average_score' => $averageScore,
    //     ];
    // }

    // private function evaluateRelevance(string $prompt, string $response): int
    // {
    //     $promptTokens = explode(' ', strtolower($prompt));
    //     $responseTokens = explode(' ', strtolower($response));
    //     $matchedTerms = array_intersect($promptTokens, $responseTokens);
    //     $matchRatio = count($matchedTerms) / max(1, count($promptTokens));

    //     return $matchRatio > 0.7 ? 10 : ($matchRatio > 0.4 ? 7 : 4);
    // }


    // private function evaluateClarity(string $response): int
    // {
    //     $sentenceCount = preg_match_all('/[.!?]/', $response);
    //     $wordCount = str_word_count($response);

    //     $averageWordsPerSentence = $wordCount / max(1, $sentenceCount);

    //     if ($averageWordsPerSentence > 5 && $averageWordsPerSentence < 15) {
    //         return 9; // Clear and concise
    //     } elseif ($averageWordsPerSentence >= 15) {
    //         return 6; // Somewhat clear
    //     } else {
    //         return 4; // Lacks clarity
    //     }
    // }

    // private function evaluateTone(string $response): int
    // {
    //     $positiveKeywords = ['friendly', 'professional', 'engaging', 'helpful', 'informative', 'accurate', 'correct'];
    //     $negativeKeywords = ['rude', 'unprofessional', 'dismissive'];
    //     $positiveCount = 0;
    //     $negativeCount = 0;

    //     foreach ($positiveKeywords as $keyword) {
    //         $positiveCount += substr_count(strtolower($response), $keyword);
    //     }

    //     foreach ($negativeKeywords as $keyword) {
    //         $negativeCount += substr_count(strtolower($response), $keyword);
    //     }

    //     // Calculate tone score
    //     if ($positiveCount > 2) {
    //         return 10; // Highly positive tone
    //     } elseif ($positiveCount > 0) {
    //         return 8; // Positive tone
    //     } elseif ($negativeCount > 0) {
    //         return 4; // Negative tone
    //     } else {
    //         return 6; // Neutral tone
    //     }
}
