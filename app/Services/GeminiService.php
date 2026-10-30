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
            ."5) Only mention colors from available_colors and sizes from available_sizes; never invent variants.\n"
            ."6) If the customer asks about sizing and has provided only one measurement, ask only for the missing height or weight; never ask again for a value already given.\n"
            ."7) If no relevant product exists, say so and ask one follow-up question about style, category, or budget.\n"
            ."8) For greetings, thanks, casual conversation, or follow-up questions, reply naturally and briefly; do not force a product list.\n"
            ."9) Use RECENT_CONVERSATION to understand pronouns and follow-up requests, but do not repeat the whole history.\n"
            ."10) Understand natural Vietnamese even when the customer omits tone marks, uses abbreviations, typos, slang, or an incomplete sentence. Infer the intended product category from meaningful nouns instead of requiring a perfectly structured query.\n"
            ."11) Be warm and helpful: acknowledge what the customer is looking for, recommend the closest matching items, and ask at most one simple follow-up question when a preference is still unclear.\n"
            ."12) If AGE_CLARIFICATION_REQUIRED is present, ask the customer for the intended age before listing specific products; you may briefly acknowledge that suitable items were found for different age groups.\n"
            ."13) When the customer asks for a seasonal or functional need such as winter clothing or warm items, recommend only candidates that clearly match that need. Do not fill the list with generic shirts, jeans, or unrelated products just because they share a broad word such as áo or quần.\n"
            ."14) Use Markdown links with a short label such as [Xem sản phẩm](exact_product_url); never print long raw URLs in the answer.\n"
            ."15) Keep the answer concise, natural Vietnamese.\n";

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
            $http = Http::timeout(30)->withHeaders(['Content-Type' => 'application/json']);
            if ($caBundle = config('gemini.ca_bundle')) {
                $http = $http->withOptions(['verify' => $caBundle]);
            }
            $res = $http->post($url, $payload);
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
