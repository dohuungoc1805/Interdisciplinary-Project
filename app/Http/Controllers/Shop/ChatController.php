<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ChatRequest;
use App\Services\ChatbotProductContextService;
use App\Services\GeminiService;
use App\Services\SizeRecommendationService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __construct(
        private GeminiService $geminiService,
        private ChatbotProductContextService $productContextService,
        private SizeRecommendationService $sizeRecommendationService,
    ) {}

    public function store(ChatRequest $request): JsonResponse
    {
        $message = $request->validated('message');

        $ctx = 'Store: '.config('app.name').'. We sell fashion products online in Vietnam. Product links are internal store links. Payment: cash on delivery and online methods if available at checkout.';
        $candidates = $this->productContextService->findCandidates(
            $message,
            (int) config('gemini.chat_max_candidates', 6)
        );
        $sizeRecommendation = $this->sizeRecommendationService->recommend($message);
        $isSizingRequest = $this->sizeRecommendationService->isSizingRequest($message);
        if ($sizeRecommendation !== null) {
            $ctx .= sprintf(
                "\nSIZE_RECOMMENDATION: Với chiều cao %.0f cm và cân nặng %.1f kg, size tham khảo là %s. %s",
                $sizeRecommendation['height_cm'],
                $sizeRecommendation['weight_kg'],
                $sizeRecommendation['size'],
                $sizeRecommendation['note']
            );
        } elseif ($isSizingRequest) {
            $ctx .= "\nSIZE_INPUT_REQUIRED: Ask the customer for both height in cm and weight in kg before recommending a size.";
        }

        $text = $this->geminiService->generateChatReply($message, $ctx, $candidates);

        if (str_contains(strtolower($text), 'temporarily unavailable') || str_contains(strtolower($text), 'something went wrong')) {
            $text = $this->productContextService->fallbackReply($message, $candidates, $sizeRecommendation, $isSizingRequest);
        }

        return response()->json(['reply' => $text]);
    }
}
