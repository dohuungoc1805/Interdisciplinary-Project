<?php

namespace Tests\Feature\Shop;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_registration_and_login_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Đăng ký')
            ->assertSee('Đăng nhập');
    }

    public function test_authenticated_customer_sees_their_name_in_the_header(): void
    {
        $user = User::factory()->create(['name' => 'qunbi377']);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('qunbi377')
            ->assertDontSee(route('register'));
    }
}
