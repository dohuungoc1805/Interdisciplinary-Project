<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductSearchService
{
    /**
     * Rank products by exact and typo-tolerant matches in their catalog data.
     *
     * @param  Collection<int, \App\Models\Product>  $products
     * @return Collection<int, \App\Models\Product>
     */
    public function rank(Collection $products, string $searchTerm): Collection
    {
        $queryTokens = $this->tokens($searchTerm);
        if ($queryTokens === []) {
            return $products->values();
        }

        $normalizedQuery = implode(' ', $queryTokens);

        return $products
            ->map(function ($product) use ($queryTokens, $normalizedQuery) {
                $searchableText = implode(' ', array_filter([
                    $product->name,
                    $product->sku,
                    $product->description,
                    $product->category?->name,
                ]));
                $normalizedText = $this->normalize($searchableText);
                $productTokens = $this->tokens($searchableText);
                $collapsedProductTokens = array_map(
                    fn (string $token) => $this->collapseRepeatedCharacters($token),
                    $productTokens,
                );
                $score = str_contains($normalizedText, $normalizedQuery) ? 80 : 0;
                $hasFuzzyMatch = false;

                foreach ($queryTokens as $queryToken) {
                    if (in_array($queryToken, $productTokens, true)) {
                        $score += 36;

                        continue;
                    }

                    if (str_contains($normalizedText, $queryToken)) {
                        $score += 28;

                        continue;
                    }

                    $collapsedQueryToken = $this->collapseRepeatedCharacters($queryToken);
                    if (
                        $collapsedQueryToken !== $queryToken
                        && in_array($collapsedQueryToken, $collapsedProductTokens, true)
                    ) {
                        $score += 28;
                        $hasFuzzyMatch = true;

                        continue;
                    }

                    $similarity = $this->bestTokenSimilarity($queryToken, $productTokens);
                    if (mb_strlen($queryToken) >= 4 && $similarity >= 0.55) {
                        $score += (int) round(30 * $similarity);
                        $hasFuzzyMatch = true;
                    }
                }

                if ($score < 22) {
                    return null;
                }

                $product->setAttribute('search_score', $score);
                $product->setAttribute('is_fuzzy_match', $hasFuzzyMatch);

                return $product;
            })
            ->filter()
            ->sortByDesc('search_score')
            ->values();
    }

    private function bestTokenSimilarity(string $needle, array $haystack): float
    {
        $best = 0.0;
        foreach ($haystack as $candidate) {
            $length = max(strlen($needle), strlen($candidate));
            if ($length === 0) {
                continue;
            }

            $best = max($best, 1 - (levenshtein($needle, $candidate) / $length));
        }

        return $best;
    }

    private function tokens(string $value): array
    {
        return array_values(array_filter(explode(' ', $this->normalize($value))));
    }

    private function collapseRepeatedCharacters(string $value): string
    {
        return preg_replace('/(.)\\1+/', '$1', $value) ?? $value;
    }

    private function normalize(?string $value): string
    {
        $value = Str::lower(Str::ascii((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';

        return trim($value);
    }
}
