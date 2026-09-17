<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function __construct(
        private string $apiKey = '',
        private string $model = 'gemini-2.0-flash',
    ) {
        $this->apiKey = (string) config('gemini.api_key', env('GEMINI_API_KEY', ''));
        $this->model = (string) config('gemini.model', 'gemini-2.0-flash');
    }

    public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
    {
        if ($this->apiKey === '') {
            return 'Chat is not configured. Please set GEMINI_API_KEY in the environment.';
        }
        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            $this->model,
            urlencode($this->apiKey)
        );

        $maxCandidates = (int) config('gemini.chat_max_candidates', 6);
        $contextCandidates = array_slice($productCandidates, 0, max(1, $maxCandidates));

        $policy = "You are a fashion shopping assistant for this website.\n"
            ."Rules:\n"
            ."1) Only recommend products from the provided PRODUCT_CANDIDATES list.\n"
            ."2) Never invent product names, prices, stock, or links.\n"
            ."3) If relevant products exist, recommend 2-3 items with a short reason and include their exact product_url.\n"
            ."4) If SIZE_RECOMMENDATION is present, clearly state the recommended size and explain that it is only a reference; do not present it as a guarantee. Only suggest a product size when it appears in that product's available_sizes.\n"
            ."5) If the customer has not provided both height and weight but asks about sizing, ask for both values in cm and kg.\n"
            ."6) If no relevant product exists, say so and ask one follow-up question about style, category, or budget.\n"
            ."7) Keep the answer concise, natural Vietnamese.\n";

        $text = $policy;
        if ($systemContext) {
            $text .= "\nSTORE_CONTEXT:\n{$systemContext}\n";
        }

        $text .= "\nPRODUCT_CANDIDATES_JSON:\n".json_encode($contextCandidates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
        $text .= "\nCUSTOMER_MESSAGE:\n{$userMessage}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $text]],
                ],
            ],
            'generationConfig' => [
                'maxOutputTokens' => 1024,
                'temperature' => (float) config('gemini.chat_temperature', 0.35),
            ],
        ];
        try {
            $res = Http::timeout(30)->withHeaders(['Content-Type' => 'application/json'])->post($url, $payload);
            if (! $res->successful()) {
                Log::warning('Gemini API error', ['body' => $res->body(), 'status' => $res->status()]);

                return 'Sorry, the assistant is temporarily unavailable. Please try again later.';
            }
            $data = $res->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if (! is_string($text) || $text === '') {
                return 'Sorry, I could not generate a response.';
            }

            return $text;
        } catch (\Throwable $e) {
            Log::error('Gemini exception', ['e' => $e->getMessage()]);

            return 'Sorry, something went wrong. Please try again in a moment.';
        }
    }
}
