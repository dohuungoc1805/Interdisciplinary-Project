<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ChatRequest;
use App\Models\ChatMessage;
use App\Services\ChatbotProductContextService;
use App\Services\GeminiService;
use App\Services\SizeRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $ctx .= "\nCONVERSATION_STYLE: Respond warmly and naturally. Match the customer's intent. Do not recommend products for a simple greeting, thanks, or goodbye.";
        $recentMessages = $request->user()
            ? $request->user()->chatMessages()->latest('id')->limit(8)->get(['role', 'message'])->reverse()->values()->all()
            : collect($request->validated('history', []))->map(fn (array $item) => (object) $item)->values()->all();
        if ($recentMessages !== []) {
            $ctx .= "\nRECENT_CONVERSATION:\n".collect($recentMessages)
                ->map(fn ($item) => strtoupper($item->role).': '.$item->message)
                ->implode("\n");
        }
        $candidates = $this->productContextService->findCandidates(
            $message,
            (int) config('gemini.chat_max_candidates', 6)
        );
        $sizeRecommendation = $this->sizeRecommendationService->recommend($message);
        $isSizingRequest = $this->sizeRecommendationService->isSizingRequest($message);
        if ($sizeRecommendation !== null) {
            $ctx .= sprintf(
                "\nSIZE_RECOMMENDATION: Với chiều cao %.0f cm và cân nặng %.1f kg, size tham khảo là %s%s. %s",
                $sizeRecommendation['height_cm'],
                $sizeRecommendation['weight_kg'],
                $sizeRecommendation['size'],
                ! empty($sizeRecommendation['age_note']) ? ' ('.$sizeRecommendation['age_note'].')' : '',
                $sizeRecommendation['note']
            );
        } elseif ($isSizingRequest) {
            $ctx .= "\nSIZE_INPUT_REQUIRED: Ask the customer for both height in cm and weight in kg before recommending a size.";
        }

        $text = $this->geminiService->generateChatReply($message, $ctx, $candidates);

        if (str_contains(strtolower($text), 'temporarily unavailable') || str_contains(strtolower($text), 'something went wrong')) {
            $text = $this->productContextService->fallbackReply($message, $candidates, $sizeRecommendation, $isSizingRequest);
        }

        if ($request->user()) {
            $request->user()->chatMessages()->createMany([
                ['role' => 'user', 'message' => $message],
                ['role' => 'assistant', 'message' => $text],
            ]);
        }

        return response()->json(['reply' => $text]);
    }

    public function history(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['messages' => [], 'persisted' => false]);
        }

        $messages = $request->user()->chatMessages()
            ->latest('id')
            ->limit(100)
            ->get(['role', 'message', 'created_at'])
            ->reverse()
            ->values()
            ->map(fn (ChatMessage $message) => [
                'role' => $message->role,
                'message' => $message->message,
                'created_at' => $message->created_at?->toIso8601String(),
            ]);

        return response()->json(['messages' => $messages, 'persisted' => true]);
    }

    public function clearHistory(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->chatMessages()->delete();
        }

        return response()->json(['message' => 'Chat history cleared.']);
    }
}
