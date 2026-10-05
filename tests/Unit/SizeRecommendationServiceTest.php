<?php

namespace Tests\Unit;

use App\Services\SizeRecommendationService;
use Tests\TestCase;

class SizeRecommendationServiceTest extends TestCase
{
    public function test_it_recommends_a_child_size_from_height_and_weight(): void
    {
        $recommendation = app(SizeRecommendationService::class)
            ->recommend('Bé trai cao 125cm, nặng 25kg');

        $this->assertSame('children', $recommendation['audience']);
        $this->assertSame('6 tuổi', $recommendation['size']);
    }

    public function test_it_keeps_adult_size_recommendation_compatible(): void
    {
        $recommendation = app(SizeRecommendationService::class)
            ->recommend('Cao 1m70, nặng 60kg');

        $this->assertSame('adult', $recommendation['audience']);
        $this->assertSame('M', $recommendation['size']);
    }
}
