<?php

namespace App\Services;

use App\Models\CosmeticProduct;
use App\Models\FashionProduct;
use App\Models\HomeNeedProduct;
use App\Models\MotorcycleProduct;
use App\Models\Product;
use App\Models\ShoeProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class DezeStoreAiChatService
{
    private const MAX_HISTORY_MESSAGES = 8;
    private const PRODUCT_INTENT_NONE = 'none';
    private const PRODUCT_INTENT_CATALOG = 'catalog';
    private const PRODUCT_INTENT_SPECIFIC = 'specific';

    public function answer(string $message, array $history = []): array
    {
        $message = trim($message);
        $history = $this->cleanHistory($history);
        $knowledge = $this->buildKnowledge($message);
        $provider = $this->provider();

        if (!$this->providerHasApiKey($provider)) {
            return $this->localFallback($message, $knowledge, 'local');
        }

        try {
            if ($provider === 'gemini') {
                $payload = $this->requestGemini($message, $history, $knowledge);
                $answer = $this->extractGeminiResponseText($payload);
            } else {
                $payload = $this->requestOpenAi($message, $history, $knowledge);
                $answer = $this->extractOpenAiResponseText($payload);
            }

            if (!filled($answer)) {
                return $this->localFallback($message, $knowledge, 'local');
            }

            return [
                'message' => $answer,
                'products' => $knowledge['show_cards'] ? $knowledge['cards'] : [],
                'source' => $provider,
            ];
        } catch (Throwable $exception) {
            Log::warning('DezeStore AI chat failed.', [
                'provider' => $provider,
                'message' => $exception->getMessage(),
            ]);

            return $this->localFallback($message, $knowledge, 'local');
        }
    }

    private function provider(): string
    {
        $provider = Str::lower(trim((string) config('services.ai.provider', 'openai')));

        return in_array($provider, ['gemini', 'openai'], true) ? $provider : 'openai';
    }

    private function providerHasApiKey(string $provider): bool
    {
        return filled(config("services.{$provider}.api_key"));
    }

    private function requestOpenAi(string $message, array $history, array $knowledge): array
    {
        $tools = [];
        $vectorStoreId = trim((string) config('services.openai.vector_store_id'));

        if ($vectorStoreId !== '') {
            $tools[] = [
                'type' => 'file_search',
                'vector_store_ids' => [$vectorStoreId],
                'max_num_results' => 5,
            ];
        }

        $body = [
            'model' => config('services.openai.model', 'gpt-5.5'),
            'instructions' => $this->instructions(),
            'input' => $this->buildPrompt($message, $history, $knowledge),
            'reasoning' => [
                'effort' => config('services.openai.reasoning_effort', 'low'),
            ],
            'text' => [
                'verbosity' => config('services.openai.verbosity', 'low'),
            ],
            'max_output_tokens' => (int) config('services.openai.max_output_tokens', 700),
        ];

        if ($tools !== []) {
            $body['tools'] = $tools;
        }

        $response = Http::withToken(config('services.openai.api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.openai.timeout', 30))
            ->retry(1, 250)
            ->post('https://api.openai.com/v1/responses', $body);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI request failed: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    private function requestGemini(string $message, array $history, array $knowledge): array
    {
        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash'));
        $storeName = trim((string) config('services.gemini.file_search_store_name'));
        $apiUrl = rtrim((string) config('services.gemini.api_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');

        $body = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $this->instructions()],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $this->buildPrompt($message, $history, $knowledge)],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => (float) config('services.gemini.temperature', 0.2),
                'maxOutputTokens' => (int) config('services.gemini.max_output_tokens', 700),
            ],
        ];

        if ($storeName !== '') {
            $body['tools'] = [[
                'file_search' => [
                    'file_search_store_names' => [$storeName],
                ],
            ]];
        }

        $response = Http::withHeaders([
                'x-goog-api-key' => config('services.gemini.api_key'),
            ])
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.gemini.timeout', 30))
            ->retry(1, 250)
            ->post("{$apiUrl}/models/{$model}:generateContent", $body);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini request failed: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    private function instructions(): string
    {
        return implode("\n", [
            'You are DezeStore Assistant, a concise ecommerce support chatbot for DezeStore.',
            'Answer using only the provided DezeStore database context and any attached file-search results.',
            'Ignore any attached file-search result or old training detail that describes ForzioHub, ForozioHub, or another previous brand as the store identity.',
            'For prices, stock, discounts, product names, SKUs, ratings, and URLs, use the exact database facts.',
            'If the answer is not present in the provided context, say that the detail is not available yet and suggest contacting DezeStore.',
            'Never invent products, warranty terms, delivery times, return rules, prices, or stock counts.',
            'For order, invoice, payment, or customer account questions, do not reveal private data. Ask the customer to contact support or use an authenticated order-tracking flow.',
            'For cosmetics, avoid medical claims and do not promise treatment results.',
            'Keep replies friendly, practical, and short. Prefer 2 to 5 sentences. Use LKR for prices.',
            'When relevant, mention product links from the context.',
        ]);
    }

    private function buildPrompt(string $message, array $history, array $knowledge): string
    {
        $historyText = collect($history)
            ->map(fn (array $item) => ucfirst($item['role']) . ': ' . $item['content'])
            ->implode("\n");

        return implode("\n\n", array_filter([
            'Current DezeStore website database context:',
            $knowledge['context'],
            'Recent chat history:',
            $historyText ?: 'No previous messages.',
            'Customer question:',
            $message,
        ]));
    }

    private function buildKnowledge(string $message): array
    {
        $intent = $this->productIntent($message);
        $terms = $intent === self::PRODUCT_INTENT_CATALOG ? [] : $this->searchTerms($message);
        $shouldSearchProducts = $intent === self::PRODUCT_INTENT_CATALOG || ($intent === self::PRODUCT_INTENT_SPECIFIC && $terms !== []);
        $matchAllTerms = $intent === self::PRODUCT_INTENT_SPECIFIC && count($terms) > 1;
        $singleProductQuestion = $intent === self::PRODUCT_INTENT_SPECIFIC && $this->expectsSingleProduct($message, $terms);

        $tech = $shouldSearchProducts ? $this->techProducts($terms, $matchAllTerms) : collect();
        $shoes = $shouldSearchProducts ? $this->shoeProducts($terms, $matchAllTerms) : collect();
        $cosmetics = $shouldSearchProducts ? $this->cosmeticProducts($terms, $matchAllTerms) : collect();
        $motorcycle = $shouldSearchProducts ? $this->moduleProducts('motorcycle', $terms, $matchAllTerms) : collect();
        $fashion = $shouldSearchProducts ? $this->moduleProducts('fashion', $terms, $matchAllTerms) : collect();
        $homeNeeds = $shouldSearchProducts ? $this->moduleProducts('home-needs', $terms, $matchAllTerms) : collect();

        $matches = $tech
            ->concat($shoes)
            ->concat($cosmetics)
            ->concat($motorcycle)
            ->concat($fashion)
            ->concat($homeNeeds);

        if ($intent === self::PRODUCT_INTENT_SPECIFIC) {
            $matches = $this->rankProductMatches($matches, $message, $terms);
        }

        $cardLimit = $singleProductQuestion ? 1 : ($intent === self::PRODUCT_INTENT_SPECIFIC ? 4 : 6);

        $cards = $matches
            ->map(fn (array $item) => $item['card'])
            ->take($cardLimit)
            ->values()
            ->all();

        $lines = collect([
            'Store name: ' . config('app.name', 'DezeStore'),
            'Contact page: ' . url('/contact-us'),
            'Catalog note: prices and stock below are live database facts from this Laravel site.',
            'Policy and FAQ knowledge base:',
            $this->localKnowledgeBase(),
            'Privacy note: order and invoice data must not be disclosed in public chat.',
        ]);

        $productLines = $matches
            ->map(fn (array $item) => $item['context'])
            ->take($singleProductQuestion ? 1 : 8)
            ->values();

        if (!$shouldSearchProducts) {
            $lines->push('The customer is not asking for catalog product results, so no product records are attached to this answer.');
        } elseif ($productLines->isEmpty()) {
            $lines->push('No matching active product records were found for the customer question.');
        } else {
            $lines->push('Matching active product records:');
            $lines = $lines->concat($productLines);
        }

        return [
            'context' => $lines->implode("\n"),
            'cards' => $cards,
            'intent' => $intent,
            'show_cards' => $shouldSearchProducts,
        ];
    }

    private function techProducts(array $terms, bool $matchAllTerms = false): Collection
    {
        return Product::query()
            ->with(['category:id,name', 'brand:id,name,logo_path', 'warrantyOption:id,name', 'variants'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->when($terms !== [], fn ($query) => $this->applyProductSearch($query, $terms, [
                'model',
                'sku',
                'os',
                'short_description',
                'long_description',
            ], ['brand', 'category'], $matchAllTerms))
            ->oldest('id')
            ->limit($terms === [] ? 6 : 8)
            ->get()
            ->map(fn (Product $product) => $this->mapTechProduct($product));
    }

    private function shoeProducts(array $terms, bool $matchAllTerms = false): Collection
    {
        return ShoeProduct::query()
            ->with(['brand:id,name', 'category:id,name', 'subcategory:id,name'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'published')
            ->when($terms !== [], fn ($query) => $this->applyProductSearch($query, $terms, [
                'name',
                'slug',
                'sku',
                'model_code',
                'short_description',
                'full_description',
            ], ['brand', 'category', 'subcategory'], $matchAllTerms))
            ->oldest('id')
            ->limit($terms === [] ? 4 : 6)
            ->get()
            ->map(fn (ShoeProduct $product) => $this->mapShoeProduct($product));
    }

    private function cosmeticProducts(array $terms, bool $matchAllTerms = false): Collection
    {
        return CosmeticProduct::query()
            ->with(['brand:id,name,logo_path', 'category:id,name', 'productType:id,name', 'countryOfOrigin:id,name,code', 'variants'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->when($terms !== [], fn ($query) => $this->applyProductSearch($query, $terms, [
                'name',
                'slug',
                'batch_number',
                'short_description',
                'long_description',
            ], ['brand', 'category', 'productType'], $matchAllTerms))
            ->oldest('id')
            ->limit($terms === [] ? 4 : 6)
            ->get()
            ->map(fn (CosmeticProduct $product) => $this->mapCosmeticProduct($product));
    }

    private function moduleProducts(string $section, array $terms, bool $matchAllTerms = false): Collection
    {
        $config = $this->moduleChatConfig($section);

        return $config['model']::query()
            ->with($config['relations'])
            ->when($config['with_reviews'], fn ($query) => $query->withCount('reviews')->withAvg('reviews', 'rating'))
            ->where('status', 'active')
            ->when($terms !== [], fn ($query) => $this->applyProductSearch(
                $query,
                $terms,
                $config['search_columns'],
                $config['search_relations'],
                $matchAllTerms
            ))
            ->oldest('id')
            ->limit($terms === [] ? $config['catalog_limit'] : $config['search_limit'])
            ->get()
            ->map(fn ($product) => $this->mapModuleProduct($product, $config));
    }

    private function mapTechProduct(Product $product): array
    {
        $price = (float) ($product->price_lkr ?? 0);
        $discount = $this->discountedPrice($price, $product->discount_type, $product->discount_value, ['percent', 'price']);
        $stockCount = $product->stock_count !== null ? (int) $product->stock_count : null;
        $inStock = (bool) $product->in_stock && ($stockCount === null || $stockCount > 0);
        $url = route('frontend.tech-products.show', ['product' => $this->routeIdentifier($product)]);

        $facts = [
            'Type: Tech product',
            'Name: ' . ($product->model ?: 'Unnamed product'),
            'Brand: ' . ($product->brand?->name ?: 'Not specified'),
            'Category: ' . ($product->category?->name ?: 'Not specified'),
            'SKU: ' . ($product->sku ?: 'Not specified'),
            'Price: ' . $this->formatMoney($discount['current']),
            'Regular price: ' . $this->formatMoney($price),
            'Stock: ' . ($inStock ? 'In stock' : 'Out of stock'),
            'Stock count: ' . ($stockCount === null ? 'Not specified' : $stockCount),
            'Warranty: ' . (trim(implode(' ', array_filter([$product->warrantyOption?->name, $product->warranty_period]))) ?: 'Not specified'),
            'Reviews: ' . (int) ($product->reviews_count ?? 0),
            'Average rating: ' . ($product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : 'Not rated'),
            'URL: ' . $url,
            'Short description: ' . ($product->short_description ?: 'Not provided'),
        ];

        return [
            'context' => '- ' . implode(' | ', $facts),
            'card' => [
                'id' => 'tech-' . $product->id,
                'type' => 'tech',
                'name' => $product->model ?: 'Product',
                'subtitle' => trim(implode(' / ', array_filter([$product->brand?->name, $product->category?->name]))),
                'price' => $this->formatMoney($discount['current']),
                'stock' => $inStock ? 'In stock' : 'Out of stock',
                'image_url' => $product->main_image_url ?: $product->hover_image_url,
                'url' => $url,
            ],
        ];
    }

    private function mapShoeProduct(ShoeProduct $product): array
    {
        $regularPrice = (float) ($product->regular_price ?? 0);
        $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
        $currentPrice = ($salePrice && $salePrice > 0 && $salePrice < $regularPrice) ? $salePrice : $regularPrice;
        $stockCount = $product->stock_quantity !== null ? (int) $product->stock_quantity : 0;
        $inStock = $product->stock_status !== 'out_of_stock' && $stockCount > 0;
        $url = route('frontend.shoe-products.show', ['product' => $this->routeIdentifier($product)]);

        $facts = [
            'Type: Shoe product',
            'Name: ' . $product->name,
            'Brand: ' . ($product->brand?->name ?: 'Not specified'),
            'Category: ' . ($product->category?->name ?: 'Not specified'),
            'Subcategory: ' . ($product->subcategory?->name ?: 'Not specified'),
            'SKU: ' . ($product->sku ?: 'Not specified'),
            'Price: ' . $this->formatMoney($currentPrice),
            'Regular price: ' . $this->formatMoney($regularPrice),
            'Stock: ' . ($inStock ? 'In stock' : 'Out of stock'),
            'Stock count: ' . $stockCount,
            'Gender: ' . ($product->gender ?: 'Not specified'),
            'URL: ' . $url,
            'Short description: ' . ($product->short_description ?: 'Not provided'),
        ];

        return [
            'context' => '- ' . implode(' | ', $facts),
            'card' => [
                'id' => 'shoe-' . $product->id,
                'type' => 'shoe',
                'name' => $product->name,
                'subtitle' => trim(implode(' / ', array_filter([$product->brand?->name, $product->category?->name]))),
                'price' => $this->formatMoney($currentPrice),
                'stock' => $inStock ? 'In stock' : 'Out of stock',
                'image_url' => $product->thumbnail_url ?: $product->hover_image_url,
                'url' => $url,
            ],
        ];
    }

    private function mapCosmeticProduct(CosmeticProduct $product): array
    {
        $price = (float) ($product->price ?? 0);
        $discount = $this->discountedPrice($price, $product->discount_type, $product->discount_value, ['percentage', 'fixed']);
        $stockCount = $product->stock !== null ? (int) $product->stock : null;
        $inStock = $stockCount === null || $stockCount > 0;
        $url = route('frontend.cosmetic-products.show', ['product' => $this->routeIdentifier($product)]);

        $facts = [
            'Type: Cosmetic product',
            'Name: ' . $product->name,
            'Brand: ' . ($product->brand?->name ?: 'Not specified'),
            'Category: ' . ($product->category?->name ?: 'Not specified'),
            'Product type: ' . ($product->productType?->name ?: 'Not specified'),
            'Country of origin: ' . ($product->countryOfOrigin?->name ?: 'Not specified'),
            'Batch number: ' . ($product->batch_number ?: 'Not specified'),
            'Price: ' . $this->formatMoney($discount['current']),
            'Regular price: ' . $this->formatMoney($price),
            'Stock: ' . ($inStock ? 'In stock' : 'Out of stock'),
            'Stock count: ' . ($stockCount === null ? 'Not specified' : $stockCount),
            'Expiry date: ' . ($product->expiry_date?->format('Y-m-d') ?: 'Not specified'),
            'URL: ' . $url,
            'Short description: ' . ($product->short_description ?: 'Not provided'),
        ];

        return [
            'context' => '- ' . implode(' | ', $facts),
            'card' => [
                'id' => 'cosmetic-' . $product->id,
                'type' => 'cosmetic',
                'name' => $product->name,
                'subtitle' => trim(implode(' / ', array_filter([$product->brand?->name, $product->category?->name]))),
                'price' => $this->formatMoney($discount['current']),
                'stock' => $inStock ? 'In stock' : 'Out of stock',
                'image_url' => $product->main_image_url,
                'url' => $url,
            ],
        ];
    }

    private function mapModuleProduct(object $product, array $config): array
    {
        $regularPrice = (float) ($product->{$config['price_column']} ?? 0);
        $salePrice = is_numeric($product->sale_price ?? null) ? (float) $product->sale_price : null;
        $currentPrice = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice
            ? $salePrice
            : $regularPrice;
        $stockCount = $product->stock_quantity !== null ? (int) $product->stock_quantity : null;
        $inStock = $product->stock_status !== 'out_of_stock' && ($stockCount === null || $stockCount > 0);
        $url = route($config['show_route'], ['product' => $this->routeIdentifier($product)]);
        $brandName = $this->moduleBrandName($product, $config);
        $categoryName = $product->category?->name;
        $typeName = $this->moduleTypeName($product, $config);
        $galleryUrls = collect($product->gallery_urls ?? [])->filter()->values();
        $imageUrl = $product->main_image_url ?: $product->hover_image_url ?: $galleryUrls->first();

        $facts = [
            'Type: ' . $config['label'],
            'Name: ' . ($product->{$config['name_column']} ?: 'Unnamed product'),
            'Brand: ' . ($brandName ?: 'Not specified'),
            'Category: ' . ($categoryName ?: 'Not specified'),
            'Product type: ' . ($typeName ?: 'Not specified'),
            'SKU: ' . ($product->sku ?: 'Not specified'),
            'Price: ' . $this->formatMoney($currentPrice),
            'Regular price: ' . $this->formatMoney($regularPrice),
            'Stock: ' . ($inStock ? 'In stock' : 'Out of stock'),
            'Stock count: ' . ($stockCount === null ? 'Not specified' : $stockCount),
            'Warranty: ' . (trim(implode(' ', array_filter([$product->warrantyOption?->name ?? null, $product->warranty_period ?? null, $product->warranty ?? null]))) ?: 'Not specified'),
            'URL: ' . $url,
            'Short description: ' . ($product->short_description ?: 'Not provided'),
        ];

        if ($config['with_reviews']) {
            $facts[] = 'Reviews: ' . (int) ($product->reviews_count ?? 0);
            $facts[] = 'Average rating: ' . ($product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : 'Not rated');
        }

        return [
            'context' => '- ' . implode(' | ', $facts),
            'card' => [
                'id' => $config['card_prefix'] . '-' . $product->id,
                'type' => $config['card_type'],
                'name' => $product->{$config['name_column']} ?: 'Product',
                'subtitle' => trim(implode(' / ', array_filter([$brandName, $categoryName, $typeName]))),
                'price' => $this->formatMoney($currentPrice),
                'stock' => $inStock ? 'In stock' : 'Out of stock',
                'image_url' => $imageUrl,
                'url' => $url,
            ],
        ];
    }

    private function moduleChatConfig(string $section): array
    {
        return match ($section) {
            'motorcycle' => [
                'model' => MotorcycleProduct::class,
                'relations' => ['category:id,name', 'helmetBrand:id,name,logo_path,status', 'warrantyOption:id,name'],
                'search_relations' => ['category', 'helmetBrand', 'warrantyOption'],
                'search_columns' => ['name', 'slug', 'sku', 'product_type', 'short_description', 'full_description'],
                'name_column' => 'name',
                'price_column' => 'regular_price',
                'brand_relation' => 'helmetBrand',
                'brand_name_column' => null,
                'type_relation' => null,
                'type_column' => 'product_type',
                'show_route' => 'frontend.motorcycle-products.show',
                'label' => 'Motorcycle product',
                'card_prefix' => 'motorcycle',
                'card_type' => 'motorcycle',
                'catalog_limit' => 4,
                'search_limit' => 6,
                'with_reviews' => true,
            ],
            'fashion' => [
                'model' => FashionProduct::class,
                'relations' => ['category:id,name', 'brand:id,name,logo_path,status', 'productType:id,name', 'warrantyOption:id,name'],
                'search_relations' => ['category', 'brand', 'productType', 'warrantyOption'],
                'search_columns' => ['name', 'slug', 'sku', 'brand_name', 'target_gender', 'size_label', 'color', 'material', 'style', 'short_description', 'full_description'],
                'name_column' => 'name',
                'price_column' => 'price',
                'brand_relation' => 'brand',
                'brand_name_column' => 'brand_name',
                'type_relation' => 'productType',
                'type_column' => null,
                'show_route' => 'frontend.fashion.show',
                'label' => 'Fashion product',
                'card_prefix' => 'fashion',
                'card_type' => 'fashion',
                'catalog_limit' => 4,
                'search_limit' => 6,
                'with_reviews' => false,
            ],
            'home-needs' => [
                'model' => HomeNeedProduct::class,
                'relations' => ['category:id,name', 'brand:id,name,logo_path,status', 'warrantyOption:id,name'],
                'search_relations' => ['category', 'brand', 'warrantyOption'],
                'search_columns' => ['name', 'slug', 'sku', 'brand_name', 'unit_label', 'material', 'color', 'short_description', 'full_description'],
                'name_column' => 'name',
                'price_column' => 'price',
                'brand_relation' => 'brand',
                'brand_name_column' => 'brand_name',
                'type_relation' => null,
                'type_column' => null,
                'show_route' => 'frontend.home-needs.show',
                'label' => 'Home needs product',
                'card_prefix' => 'home-need',
                'card_type' => 'home-needs',
                'catalog_limit' => 4,
                'search_limit' => 6,
                'with_reviews' => false,
            ],
            default => throw new \InvalidArgumentException('Unsupported chatbot section.'),
        };
    }

    private function moduleBrandName(object $product, array $config): ?string
    {
        $relation = $config['brand_relation'];
        $brandNameColumn = $config['brand_name_column'];

        return ($relation ? ($product->{$relation}?->name ?? null) : null)
            ?: ($brandNameColumn ? ($product->{$brandNameColumn} ?? null) : null);
    }

    private function moduleTypeName(object $product, array $config): ?string
    {
        if ($config['type_relation']) {
            return $product->{$config['type_relation']}?->name;
        }

        if ($config['type_column'] && filled($product->{$config['type_column']} ?? null)) {
            return str((string) $product->{$config['type_column']})->replace('_', ' ')->title()->toString();
        }

        return null;
    }

    private function discountedPrice(float $price, ?string $type, mixed $value, array $supportedTypes): array
    {
        $discountValue = is_numeric($value) ? (float) $value : 0.0;

        if ($price <= 0 || $discountValue <= 0 || !in_array($type, $supportedTypes, true)) {
            return ['current' => $price, 'has_discount' => false];
        }

        $percentageTypes = ['percent', 'percentage'];
        $current = in_array($type, $percentageTypes, true)
            ? max(0, $price - (($price * $discountValue) / 100))
            : max(0, $price - $discountValue);

        return [
            'current' => round($current, 2),
            'has_discount' => $current < $price,
        ];
    }

    private function applyProductSearch($query, array $terms, array $columns, array $relations, bool $matchAllTerms)
    {
        if ($matchAllTerms) {
            return $query->where(function ($outer) use ($terms, $columns, $relations) {
                foreach ($terms as $term) {
                    $outer->where(function ($inner) use ($term, $columns, $relations) {
                        $this->applyTermSearch($inner, $term, $columns, $relations);
                    });
                }
            });
        }

        return $query->where(function ($inner) use ($terms, $columns, $relations) {
            foreach ($terms as $term) {
                $this->applyTermSearch($inner, $term, $columns, $relations);
            }
        });
    }

    private function applyTermSearch($query, string $term, array $columns, array $relations): void
    {
        $like = '%' . $term . '%';

        foreach ($columns as $column) {
            $query->orWhereRaw("LOWER({$column}) like ?", [$like]);
        }

        foreach ($relations as $relation) {
            $query->orWhereHas($relation, fn ($related) => $related->whereRaw('LOWER(name) like ?', [$like]));
        }
    }

    private function rankProductMatches(Collection $matches, string $message, array $terms): Collection
    {
        $phrase = $this->normalizedProductPhrase($message);

        return $matches
            ->sortByDesc(function (array $item) use ($phrase, $terms) {
                $name = Str::lower((string) ($item['card']['name'] ?? ''));
                $subtitle = Str::lower((string) ($item['card']['subtitle'] ?? ''));
                $haystack = trim($name . ' ' . $subtitle);
                $score = 0;

                if ($phrase !== '' && $name === $phrase) {
                    $score += 100;
                } elseif ($phrase !== '' && Str::contains($name, $phrase)) {
                    $score += 80;
                }

                foreach ($terms as $term) {
                    if (Str::contains($name, $term)) {
                        $score += 12;
                    } elseif (Str::contains($haystack, $term)) {
                        $score += 5;
                    }
                }

                if ($terms !== [] && collect($terms)->every(fn (string $term) => Str::contains($name, $term))) {
                    $score += 30;
                }

                return $score;
            })
            ->values();
    }

    private function localFallback(string $message, array $knowledge, string $source): array
    {
        if (!($knowledge['show_cards'] ?? false)) {
            return [
                'message' => $this->localSupportAnswer($message),
                'products' => [],
                'source' => $source,
            ];
        }

        if ($knowledge['cards'] === []) {
            return [
                'message' => 'I could not find a matching active product in the current DezeStore catalog. Please try a product name like Memo CX07, or contact DezeStore for details that are not listed yet.',
                'products' => [],
                'source' => $source,
            ];
        }

        $product = $knowledge['cards'][0];
        $text = sprintf(
            'I found %s. Current database price is %s and stock status is %s. You can view it here: %s',
            $product['name'],
            $product['price'],
            strtolower($product['stock']),
            $product['url']
        );

        if (Str::contains(Str::lower($message), ['deliver', 'delivery', 'return', 'warranty'])) {
            $text .= ' Delivery, return, and warranty details are not fully configured in the chatbot data yet, so please confirm those with DezeStore before ordering.';
        }

        return [
            'message' => $text,
            'products' => $knowledge['show_cards'] ? $knowledge['cards'] : [],
            'source' => $source,
        ];
    }

    private function localSupportAnswer(string $message): string
    {
        $normalized = Str::lower($message);

        if ($this->isGreetingOrSmallTalk($normalized)) {
            return 'Hi, I can help with DezeStore products, prices, stock, delivery, warranty, returns, and product links.';
        }

        if (Str::contains($normalized, ['deliver', 'delivery', 'shipping'])) {
            return 'Delivery details are confirmed after order placement. Availability, cost, and timing may depend on your location and the product availability.';
        }

        if (Str::contains($normalized, ['return', 'refund'])) {
            return 'Return or refund eligibility depends on the product condition, warranty terms, and issue type. Please contact DezeStore support to confirm eligibility.';
        }

        if (Str::contains($normalized, ['warranty', 'guarantee'])) {
            return 'Warranty depends on the product, brand, and supplier. Please check the product page or contact DezeStore support before purchase.';
        }

        if (Str::contains($normalized, ['order', 'invoice', 'payment', 'account'])) {
            return 'For order, invoice, payment, or account questions, please contact DezeStore support or use an authenticated order-tracking flow.';
        }

        if (Str::contains($normalized, ['contact', 'support', 'phone', 'email', 'location'])) {
            return 'You can reach DezeStore through the contact page: ' . url('/contact-us');
        }

        return 'That detail is not available yet in the chatbot data. Please contact DezeStore support for confirmation.';
    }

    private function searchTerms(string $message): array
    {
        $stopWords = [
            'about', 'after', 'also', 'am', 'an', 'and', 'another', 'any', 'are', 'as', 'at', 'available',
            'be', 'best', 'by', 'can', 'could', 'deliver', 'delivery', 'details', 'do', 'does',
            'email', 'find', 'for', 'from', 'get', 'give', 'guarantee', 'have', 'hello', 'help',
            'hi', 'how', 'in', 'info', 'information', 'into', 'invoice', 'is', 'it', 'item',
            'items', 'know', 'like', 'list', 'me', 'need', 'of', 'on', 'or', 'order', 'other', 'payment',
            'please', 'policy', 'price', 'product', 'products', 'refund', 'return', 'show',
            'some', 'stock', 'support', 'tell', 'thanks', 'thank', 'that', 'the', 'there',
            'this', 'to', 'warranty', 'what', 'when', 'where', 'with', 'you', 'your',
        ];

        return collect(preg_split('/[^a-z0-9]+/i', Str::lower($message)) ?: [])
            ->map(fn (string $term) => trim($term))
            ->filter(fn (string $term) => strlen($term) >= 2 && !in_array($term, $stopWords, true))
            ->unique()
            ->take(8)
            ->values()
            ->all();
    }

    private function productIntent(string $message): string
    {
        $normalized = Str::lower($message);
        $terms = $this->searchTerms($message);

        if ($this->isSupportOnlyQuestion($normalized) && !$this->hasModelLikeToken($message)) {
            return self::PRODUCT_INTENT_NONE;
        }

        if ($terms !== [] && $this->hasSpecificProductClue($message, $normalized, $terms)) {
            return self::PRODUCT_INTENT_SPECIFIC;
        }

        if ($this->isCatalogQuestion($normalized)) {
            return self::PRODUCT_INTENT_CATALOG;
        }

        if ($terms !== [] && ($this->hasProductSignal($normalized) || $this->looksLikeShortProductSearch($message, $terms))) {
            return self::PRODUCT_INTENT_SPECIFIC;
        }

        return self::PRODUCT_INTENT_NONE;
    }

    private function isCatalogQuestion(string $normalized): bool
    {
        if (Str::contains($normalized, [
            'all product',
            'about products',
            'about your products',
            'available product',
            'browse products',
            'catalog',
            'list products',
            'product list',
            'products available',
            'products do you have',
            'tell me about products',
            'what products',
            'what do you sell',
            'show products',
        ])) {
            return true;
        }

        return Str::contains($normalized, ['show', 'available', 'in stock', 'sell', 'selling'])
            && $this->containsProductCategoryWord($normalized);
    }

    private function hasSpecificProductClue(string $message, string $normalized, array $terms): bool
    {
        return $this->hasModelLikeToken($message)
            || Str::contains($normalized, [
                'about ',
                'available',
                'cost of',
                'details of',
                'do you have',
                'have you got',
                'how much',
                'price for',
                'price of',
                'stock of',
                'tell me about',
            ])
            || ($this->hasProductSignal($normalized) && $terms !== []);
    }

    private function hasProductSignal(string $normalized): bool
    {
        return Str::contains($normalized, [
            'available',
            'buy',
            'catalog',
            'cost',
            'do you have',
            'have you got',
            'model',
            'price',
            'product',
            'sku',
            'stock',
        ]) || $this->containsProductCategoryWord($normalized);
    }

    private function containsProductCategoryWord(string $normalized): bool
    {
        return Str::contains($normalized, [
            'accessory',
            'accessories',
            'bag',
            'bags',
            'bike',
            'cosmetic',
            'cosmetics',
            'cream',
            'fashion',
            'gadget',
            'gadgets',
            'helmet',
            'helmets',
            'home',
            'kitchen',
            'makeup',
            'mobile',
            'motorcycle',
            'phone',
            'phones',
            'serum',
            'shoe',
            'shoes',
            'sneaker',
            'sneakers',
            'sunglasses',
        ]);
    }

    private function isSupportOnlyQuestion(string $normalized): bool
    {
        if (Str::contains($normalized, [
            'contact number',
            'email address',
            'phone number',
            'where are you',
            'where is your shop',
        ])) {
            return true;
        }

        if (Str::contains($normalized, [
            'account',
            'contact',
            'deliver',
            'delivery',
            'invoice',
            'location',
            'payment',
            'refund',
            'return',
            'shipping',
            'support',
        ]) && !Str::contains($normalized, [
            'available',
            'buy',
            'catalog',
            'do you have',
            'have you got',
            'price',
            'show',
            'stock',
        ])) {
            return true;
        }

        return Str::contains($normalized, [
            'account',
            'contact',
            'deliver',
            'delivery',
            'invoice',
            'location',
            'payment',
            'refund',
            'return',
            'shipping',
            'support',
            'warranty policy',
        ]) && !$this->containsProductCategoryWord($normalized);
    }

    private function isGreetingOrSmallTalk(string $normalized): bool
    {
        $words = collect(preg_split('/[^a-z0-9]+/i', $normalized) ?: [])
            ->filter()
            ->values();

        if ($words->isEmpty() || $words->count() > 4) {
            return false;
        }

        return $words->every(fn (string $word) => in_array($word, [
            'good',
            'hello',
            'hey',
            'hi',
            'morning',
            'thanks',
            'thank',
            'there',
            'you',
        ], true));
    }

    private function looksLikeShortProductSearch(string $message, array $terms): bool
    {
        $normalized = Str::lower($message);

        if ($this->isGreetingOrSmallTalk($normalized) || $this->isSupportOnlyQuestion($normalized)) {
            return false;
        }

        return count($terms) <= 4 && str_word_count($normalized) <= 6;
    }

    private function hasModelLikeToken(string $message): bool
    {
        return (bool) preg_match('/\b(?=[a-z0-9-]*\d)[a-z]{1,}[a-z0-9-]{1,}\b/i', $message);
    }

    private function expectsSingleProduct(string $message, array $terms): bool
    {
        $normalized = Str::lower($message);

        return $this->hasModelLikeToken($message)
            || count($terms) >= 2
            || Str::contains($normalized, [
                'cost of',
                'details of',
                'how much',
                'price for',
                'price of',
                'stock of',
                'tell me about',
            ]);
    }

    private function normalizedProductPhrase(string $message): string
    {
        $normalized = Str::lower($message);

        foreach ([
            'tell me about',
            'details of',
            'price for',
            'price of',
            'stock of',
            'cost of',
            'how much is',
            'how much',
            'do you have',
            'have you got',
            'is',
            'the',
            'a',
            'an',
        ] as $phrase) {
            $normalized = preg_replace('/\b' . preg_quote($phrase, '/') . '\b/', ' ', $normalized) ?? $normalized;
        }

        return trim(preg_replace('/[^a-z0-9]+/i', ' ', $normalized) ?? '');
    }

    private function cleanHistory(array $history): array
    {
        return collect($history)
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) {
                $role = $item['role'] ?? '';
                $content = trim((string) ($item['content'] ?? ''));

                return [
                    'role' => in_array($role, ['user', 'assistant'], true) ? $role : 'user',
                    'content' => Str::limit($content, 800, ''),
                ];
            })
            ->filter(fn (array $item) => $item['content'] !== '')
            ->take(-self::MAX_HISTORY_MESSAGES)
            ->values()
            ->all();
    }

    private function extractOpenAiResponseText(array $payload): ?string
    {
        if (isset($payload['output_text']) && is_string($payload['output_text'])) {
            return trim($payload['output_text']);
        }

        $parts = [];

        foreach (($payload['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (isset($content['text']) && is_string($content['text'])) {
                    $parts[] = $content['text'];
                }

                if (isset($content['value']) && is_string($content['value'])) {
                    $parts[] = $content['value'];
                }
            }
        }

        $text = trim(implode("\n", $parts));

        return $text !== '' ? $text : null;
    }

    private function extractGeminiResponseText(array $payload): ?string
    {
        $parts = [];

        foreach (($payload['candidates'] ?? []) as $candidate) {
            foreach (($candidate['content']['parts'] ?? []) as $part) {
                if (isset($part['text']) && is_string($part['text'])) {
                    $parts[] = $part['text'];
                }
            }
        }

        $text = trim(implode("\n", $parts));

        return $text !== '' ? $text : null;
    }

    private function localKnowledgeBase(): string
    {
        $path = base_path('docs/ai-knowledge/dezestore-knowledge-base.txt');

        if (!is_file($path)) {
            return 'No local policy knowledge file is available.';
        }

        $content = file_get_contents($path);

        return filled($content)
            ? Str::limit(trim((string) $content), 6000, '')
            : 'The local policy knowledge file is empty.';
    }

    private function formatMoney(float $amount): string
    {
        return 'LKR ' . number_format($amount, 2);
    }

    private function routeIdentifier(object $product): int|string
    {
        return filled($product->slug ?? null) ? $product->slug : $product->id;
    }
}
