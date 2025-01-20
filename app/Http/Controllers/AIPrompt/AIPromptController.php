<?php

namespace App\Http\Controllers\AIPrompt;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PromptLog;
use App\Services\EvaluteAIPromptResponseService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AIPromptController extends Controller
{
    private $api_key;
    private $api_url;

    public function __construct()
    {
        $this->api_key = env('OPENAI_API_KEY');
        $this->api_url = env('OPENAI_API_URL') ?? 'https://api.openai.com/v1/chat/completions';
        ;
    }

    public function processAiPrompt(Request $request, EvaluteAIPromptResponseService $evaluteAIService)
    {
        try {
            $validate = $request->validate([
             'prompt' => 'required|string'
            ]);

            $response = Http::retry(3, 100, throw: true)->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ])->timeout(500)
            ->post($this->api_url, [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $validate['prompt']],
                ],
                'max_tokens' => 100,
                'temperature' => 0.5,
                'stop' => ["\n"],
            ]);
            if ($response->failed()) {
                return response()->json(['error' => 'Failed to connect to OpenAI API'], 500);
            }

            $prompt_response = $response->json()['choices'][0]['message']['content'] ?? '';

            //Evalute the response for  (clarity, tone, relevance)
            $evaluationResponse = Http::retry(3, 100, throw: true)
    ->withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $this->api_key,
    ])
    ->timeout(30)
    ->post($this->api_url, [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a critical evaluator assessing clarity, tone, and relevance of an AI-generated response. Use the following strict guidelines:
                - Clarity: Rate from 1 to 10 based on how well the response answers the question. For vague, nonsensical, or logically incoherent prompts, clarity **must** score between 1-4, even if the response is grammatically correct.
                - Tone: Rate from 1 to 10 based on the appropriateness of the tone for the question. Playful tones may score higher (4-6) for absurd prompts, while overly serious tones score lower (1-4).
                - Relevance: Rate from 1 to 10 based on how closely the response addresses the prompt. For nonsensical or irrelevant prompts, relevance **must** score between 1-4.
            
                Be objective, avoid inflating scores, and penalize nonsensical or irrelevant prompts heavily.Questions that doesnt make sense and conveys incorrect meanings should have a average score less then 5. Output only in this format:
                Clarity: [score]
                Tone: [score]
                Relevance: [score]
                Average Score: [score]'
                ],

            [
                'role' => 'user',
                'content' => "Prompt: {$validate['prompt']}\nResponse: {$prompt_response}\nEvaluate this response in terms of clarity, tone, and relevance."
            ],
        ],
    ]);



            Log::info('AI Evaluation Response:', $evaluationResponse->json());


            if ($evaluationResponse->failed()) {
                return response()->json(['error' => 'Failed to connect to OpenAI API for evaluation'], 500);
            }

            $evaluation = $evaluationResponse->json()['choices'][0]['message']['content'] ?? '';
            $evaluationData = $evaluteAIService->parseEvaluation($evaluation);

            $promptLog = PromptLog::create([
                'prompt' => $validate['prompt'],
                'response' => $prompt_response,
                'relevance' => $evaluationData['relevance'],
                'clarity' => $evaluationData['clarity'],
                'tone' => $evaluationData['tone'],
                'average_score' => $evaluationData['average_score'],
            ]);

            return response()->json([
                'message' => 'Prompt Response generated successfully.',
                'response' => $prompt_response,
                'evaluation' => $evaluationData
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed' ,'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong '. $e->getMessage()], 500);
        }



    }
}
