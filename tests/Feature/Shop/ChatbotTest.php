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
        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
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
        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
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

        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
            public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
            {
                return 'Sorry, the assistant is temporarily unavailable. Please try again later.';
            }
        });

        $response = $this->postJson(route('chat'), [
            'message' => 'Mình cao 1m70, nặng 60kg, gợi ý áo thun',
        ]);

        $response->assertOk();
        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('Áo thun basic nam', $reply);
        $this->assertStringContainsString('/products/ao-thun-basic-nam', $reply);
        $this->assertStringContainsString('Size còn hàng: M', $reply);
        $this->assertStringContainsString('Size M đang phù hợp', $reply);
    }

    public function test_chat_recommends_a_size_from_height_and_weight_when_ai_is_unavailable(): void
    {
        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
            public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
            {
                return 'Sorry, the assistant is temporarily unavailable. Please try again later.';
            }
        });

        $response = $this->postJson(route('chat'), [
            'message' => 'Mình cao 1m70 và nặng 60kg, tư vấn size áo giúp mình',
        ]);

        $response->assertOk();
        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('size tham khảo của bạn là M', $reply);
        $this->assertStringContainsString('170 cm', $reply);
        $this->assertStringContainsString('60.0 kg', $reply);
    }

    public function test_chat_asks_for_measurements_when_a_size_request_is_incomplete(): void
    {
        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
            public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
            {
                return 'Sorry, the assistant is temporarily unavailable. Please try again later.';
            }
        });

        $response = $this->postJson(route('chat'), [
            'message' => 'Tư vấn size áo giúp mình',
        ]);

        $response->assertOk();
        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('chiều cao', $reply);
        $this->assertStringContainsString('cân nặng', $reply);
    }

    public function test_chat_limits_children_requests_to_children_categories(): void
    {
        \DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Trẻ em', 'slug' => 'tre-em', 'parent_id' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Bé trai', 'slug' => 'be-trai', 'parent_id' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Thời trang nam', 'slug' => 'thoi-trang-nam', 'parent_id' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        \DB::table('products')->insert([
            [
                'name' => 'Quần short kaki bé trai', 'slug' => 'quan-short-kaki-be-trai', 'description' => 'Quần trẻ em',
                'price' => 199000, 'category_id' => 2, 'is_active' => true, 'is_featured' => false, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Quần kaki nam ống đứng', 'slug' => 'quan-kaki-nam-ong-dung', 'description' => 'Quần người lớn',
                'price' => 399000, 'category_id' => 3, 'is_active' => true, 'is_featured' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
            public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
            {
                return 'Sorry, the assistant is temporarily unavailable. Please try again later.';
            }
        });

        $response = $this->postJson(route('chat'), ['message' => 'Mình muốn mua quần cho trẻ em']);

        $response->assertOk();
        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('Quần short kaki bé trai', $reply);
        $this->assertStringNotContainsString('Quần kaki nam ống đứng', $reply);
    }

    public function test_chat_suggests_products_when_the_product_name_contains_a_typo(): void
    {
        \DB::table('categories')->insert([
            'name' => 'Bé trai',
            'slug' => 'be-trai',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('products')->insert([
            'name' => 'Áo hoodie nỉ bé trai',
            'slug' => 'ao-hoodie-ni-be-trai',
            'description' => 'Áo hoodie mềm ấm cho bé',
            'price' => 259000,
            'category_id' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
            public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
            {
                return 'Sorry, the assistant is temporarily unavailable. Please try again later.';
            }
        });

        $response = $this->postJson(route('chat'), ['message' => 'Mình muốn mua áo hoodlly']);

        $response->assertOk();
        $this->assertStringContainsString('Áo hoodie nỉ bé trai', (string) $response->json('reply'));
    }

    public function test_chat_suggests_products_when_a_short_product_word_has_extra_letters(): void
    {
        \DB::table('categories')->insert([
            'name' => 'Áo & áo thun',
            'slug' => 'ao-ao-thun',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('products')->insert([
            'name' => 'Áo thun cotton cơ bản',
            'slug' => 'ao-thun-cotton-co-ban',
            'description' => 'Áo thun mềm, dễ mặc',
            'price' => 279000,
            'category_id' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->app->instance(GeminiService::class, new class extends GeminiService
        {
            public function generateChatReply(string $userMessage, ?string $systemContext = null, array $productCandidates = []): string
            {
                return 'Sorry, the assistant is temporarily unavailable. Please try again later.';
            }
        });

        $response = $this->postJson(route('chat'), ['message' => 'Tôi muốn mua áooo']);

        $response->assertOk();
        $this->assertStringContainsString('Áo thun cotton cơ bản', (string) $response->json('reply'));
    }
}
