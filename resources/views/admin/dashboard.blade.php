<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                <h3 class="mb-6 text-2xl font-bold text-gray-800 dark:text-gray-100">Most Used Prompts</h3>
                <table class="mb-12 w-full table-auto border-collapse border border-gray-300 text-left dark:border-gray-700">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-gray-600 dark:border-gray-700 dark:text-gray-300">Prompt</th>
                            <th class="border border-gray-300 px-4 py-2 text-gray-600 dark:border-gray-700 dark:text-gray-300">Usage Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mostUsedPrompts as $prompt)
                            <tr>
                                <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">{{ $prompt->prompt }}</td>
                                <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">{{ $prompt->usage_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <h3 class="mb-6 text-2xl font-bold text-gray-800 dark:text-gray-100">Average Response Quality Scores</h3>
                <div class="mb-12 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg bg-gray-100 p-4 shadow dark:bg-gray-700">
                        <p class="text-lg text-gray-600 dark:text-gray-300">Relevance</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($averageScores->avg_relevance, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-100 p-4 shadow dark:bg-gray-700">
                        <p class="text-lg text-gray-600 dark:text-gray-300">Clarity</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($averageScores->avg_clarity, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-100 p-4 shadow dark:bg-gray-700">
                        <p class="text-lg text-gray-600 dark:text-gray-300">Tone</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($averageScores->avg_tone, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-100 p-4 shadow dark:bg-gray-700">
                        <p class="text-lg text-gray-600 dark:text-gray-300">Overall</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($averageScores->avg_overall, 2) }}</p>
                    </div>
                </div>

                <h3 class="mb-6 text-2xl font-bold text-gray-800 dark:text-gray-100">Suggestions for Improving Prompt Design</h3>
                <table class="w-full table-auto border-collapse border border-gray-300 text-left dark:border-gray-700">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-gray-600 dark:border-gray-700 dark:text-gray-300">Prompt</th>
                            <th class="border border-gray-300 px-4 py-2 text-gray-600 dark:border-gray-700 dark:text-gray-300">Average Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lowScoringPrompts as $prompt)
                            <tr>
                                <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">
                                    {{ $prompt['prompt'] }}
                                </td>
                                <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">
                                    {{ number_format($prompt['average_score'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-gray-300 bg-gray-100 px-4 py-2 dark:border-gray-700 dark:bg-gray-800">
                                    <strong>Suggestions:</strong>
                                    <ul class="list-disc pl-5">
                                        @foreach ($prompt['suggestions'] as $suggestion)
                                            <li>{{ $suggestion }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</x-app-layout>
