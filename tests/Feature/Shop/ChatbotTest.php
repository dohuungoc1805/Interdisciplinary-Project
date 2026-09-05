<?php

namespace Tests\Feature\Shop;

use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_endpoint_returns_reply_contract(): void
    {
        $this->app->instance(GeminiService::class, new class extends GeminiService {
            public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
            {
                return 'Xin chào, mình có thể giúp bạn chọn sản phẩm.';
            }
        });

        $response = $this->postJson(route('chat'), [
            'message' => 'Tư vấn cho mình sản phẩm mới',
        ]);

        $response->assertOk()->assertJsonStructure(['reply']);
    }

    public function test_chat_endpoint_enforces_rate_limit(): void
    {
        $this->app->instance(GeminiService::class, new class extends GeminiService {
            public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
            {
                return 'ok';
            }
        });

        for ($i = 0; $i < 20; $i++) {
            $this->postJson(route('chat'), ['message' => 'hello '.$i])->assertOk();
        }

        $this->postJson(route('chat'), ['message' => 'hello overflow'])
            ->assertStatus(429);
    }

    public function test_chat_fallback_returns_product_links_when_ai_is_unavailable(): void
    {
        \DB::table('categories')->insert([
            'name' => 'Áo thun',
            'slug' => 'ao-thun',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryId = (int) \DB::table('categories')->where('slug', 'ao-thun')->value('id');

        \DB::table('products')->insert([
            'name' => 'Áo thun basic nam',
            'slug' => 'ao-thun-basic-nam',
            'description' => 'Mẫu basic dễ mặc',
            'price' => 299000,
            'compare_price' => 359000,
            'main_image' => null,
            'category_id' => $categoryId,
            'is_featured' => true,
            'is_hot' => false,
            'is_on_sale' => true,
            'is_new' => true,
            'is_active' => true,
            'review_count' => 10,
            'average_rating' => 4.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = (int) \DB::table('products')->where('slug', 'ao-thun-basic-nam')->value('id');

        \DB::table('product_variants')->insert([
            'product_id' => $productId,
            'size' => 'M',
            'color' => 'Đen',
            'sku' => 'ATBN-M-DEN',
            'price' => null,
            'stock' => 12,
            'avg_cost' => 0,
            'last_cost' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->app->instance(GeminiService::class, new class extends GeminiService {
            public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
            {
                return 'Sorry, the assistant is temporarily unavailable. Please try again later.';
            }
        });

        $response = $this->postJson(route('chat'), [
            'message' => 'Gợi ý áo thun',
        ]);

        $response->assertOk();
        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('Áo thun basic nam', $reply);
        $this->assertStringContainsString('/products/ao-thun-basic-nam', $reply);
    }
}
