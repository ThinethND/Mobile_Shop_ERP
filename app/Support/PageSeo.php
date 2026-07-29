<?php

namespace App\Support;

use App\Models\CosmeticProduct;
use App\Models\FashionProduct;
use App\Models\HomeNeedProduct;
use App\Models\MotorcycleProduct;
use App\Models\Product;
use App\Models\ShoeProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PageSeo
{
    private string $siteName = 'DezeStore';

    private string $siteUrl;

    private string $defaultOgImage;

    private string $defaultLogoUrl;

    /**
     * @var array<int, string>
     */
    private array $defaultKeywordList = [
        'DezeStore',
        'online shopping Sri Lanka',
        'mobile phones Sri Lanka',
        'phone coolers Sri Lanka',
        'mobile accessories Sri Lanka',
        'shoes Sri Lanka',
        'cosmetics Sri Lanka',
        'motorcycle helmets Sri Lanka',
        'fashion accessories Sri Lanka',
        'home needs Sri Lanka',
        'gadgets Sri Lanka',
    ];

    public function __construct()
    {
        $this->siteUrl = rtrim((string) config('app.url', 'https://dezestore.com'), '/');
        $this->defaultOgImage = $this->siteUrl . '/images/dezeiconlogo.webp';
        $this->defaultLogoUrl = $this->siteUrl . '/images/dezeiconlogo.webp';
    }

    /**
     * @return array<string, mixed>
     */
    public function forRequest(Request $request): array
    {
        $isPublicPage = $this->isPublicPage($request);

        $context = [
            'isPublicPage' => $isPublicPage,
            'seoTitle' => config('app.name', $this->siteName),
            'seoDescription' => null,
            'canonicalUrl' => $this->siteUrl . '/',
            'ogTitle' => config('app.name', $this->siteName),
            'ogDescription' => null,
            'ogImage' => $this->defaultOgImage,
            'ogType' => 'website',
            'seoKeywords' => implode(', ', $this->defaultKeywordList),
            'robotsContent' => 'index, follow',
            'socialLinks' => $this->socialLinks(),
            'schemaBlocks' => [],
        ];

        if (! $isPublicPage) {
            return $context;
        }

        $context = array_merge($context, [
            'seoTitle' => 'DezeStore | Online Shopping for Gadgets, Fashion, Beauty & Home Needs in Sri Lanka',
            'seoDescription' => 'Shop mobile phones, phone coolers, mobile accessories, motorcycle products, shoes, cosmetics, fashion accessories, home needs, and lifestyle products from DezeStore in Sri Lanka.',
            'canonicalUrl' => $this->canonicalRootUrl(),
            'ogTitle' => 'DezeStore | Online Shopping in Sri Lanka',
            'ogDescription' => 'Shop gadgets, motorcycle products, shoes, cosmetics, fashion accessories, home needs, and lifestyle products from DezeStore.',
            'schemaBlocks' => $this->baseSchemaBlocks(),
        ]);

        $breadcrumbItems = [];

        if ($request->routeIs('frontend.root')) {
            $context['canonicalUrl'] = $this->canonicalRootUrl();

            if ($request->query()) {
                $context['robotsContent'] = 'noindex, follow';
            }
        } elseif ($request->routeIs('frontend.contact-us.index')) {
            $context['seoTitle'] = 'Contact DezeStore | Online Shopping in Sri Lanka';
            $context['seoDescription'] = 'Contact DezeStore in Sri Lanka for product inquiries, order support, delivery questions, and general customer assistance.';
            $context['ogTitle'] = $context['seoTitle'];
            $context['ogDescription'] = $context['seoDescription'];
            $context['canonicalUrl'] = $request->url();
            $breadcrumbItems = [
                ['name' => 'Home', 'url' => $this->canonicalRootUrl()],
                ['name' => 'Contact Us', 'url' => $context['canonicalUrl']],
            ];
        } elseif ($request->routeIs('frontend.tech-products.index')) {
            $context = $this->applyTechListingSeo($context, $request, $breadcrumbItems);
        } elseif ($request->routeIs('frontend.shoe-products.index')) {
            $context = $this->applyShoeListingSeo($context, $request, $breadcrumbItems);
        } elseif ($request->routeIs('frontend.cosmetic-products.index')) {
            $context = $this->applyCosmeticListingSeo($context, $request, $breadcrumbItems);
        } elseif ($request->routeIs('frontend.motorcycle-products.index')) {
            $context = $this->applyModuleListingSeo($context, $request, $breadcrumbItems, 'motorcycle');
        } elseif ($request->routeIs('frontend.fashion.index')) {
            $context = $this->applyModuleListingSeo($context, $request, $breadcrumbItems, 'fashion');
        } elseif ($request->routeIs('frontend.home-needs.index')) {
            $context = $this->applyModuleListingSeo($context, $request, $breadcrumbItems, 'home-needs');
        } elseif ($request->routeIs('frontend.tech-products.show')) {
            $context = $this->applyTechProductSeo($context, $request, $breadcrumbItems);
        } elseif ($request->routeIs('frontend.shoe-products.show')) {
            $context = $this->applyShoeProductSeo($context, $request, $breadcrumbItems);
        } elseif ($request->routeIs('frontend.cosmetic-products.show')) {
            $context = $this->applyCosmeticProductSeo($context, $request, $breadcrumbItems);
        } elseif ($request->routeIs('frontend.motorcycle-products.show')) {
            $context = $this->applyModuleProductSeo($context, $request, $breadcrumbItems, 'motorcycle');
        } elseif ($request->routeIs('frontend.fashion.show')) {
            $context = $this->applyModuleProductSeo($context, $request, $breadcrumbItems, 'fashion');
        } elseif ($request->routeIs('frontend.home-needs.show')) {
            $context = $this->applyModuleProductSeo($context, $request, $breadcrumbItems, 'home-needs');
        } elseif ($request->routeIs('frontend.cart.index')) {
            $context['seoTitle'] = 'Cart | DezeStore';
            $context['seoDescription'] = 'Review the products in your cart before you continue to checkout.';
            $context['ogTitle'] = $context['seoTitle'];
            $context['ogDescription'] = $context['seoDescription'];
            $context['canonicalUrl'] = $request->url();
            $context['robotsContent'] = 'noindex, nofollow';
        } elseif ($request->routeIs('frontend.checkout.index')) {
            $context['seoTitle'] = 'Checkout | DezeStore';
            $context['seoDescription'] = 'Complete your order securely with delivery and payment details.';
            $context['ogTitle'] = $context['seoTitle'];
            $context['ogDescription'] = $context['seoDescription'];
            $context['canonicalUrl'] = $request->url();
            $context['robotsContent'] = 'noindex, nofollow';
        }

        if (! empty($breadcrumbItems)) {
            $context['schemaBlocks'][] = $this->breadcrumbSchema($breadcrumbItems);
        }

        return $context;
    }

    private function isPublicPage(Request $request): bool
    {
        return $request->route()?->named('frontend.*') ?? false;
    }

    private function canonicalRootUrl(): string
    {
        return $this->siteUrl . '/';
    }

    /**
     * @return array<int, string>
     */
    private function socialLinks(): array
    {
        return array_values(array_filter([
            trim((string) config('services.dezestore.facebook_url')),
            trim((string) config('services.dezestore.instagram_url')),
            trim((string) config('services.dezestore.tiktok_url')),
        ]));
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array{name: string, url: string}>  $breadcrumbItems
     * @return array<string, mixed>
     */
    private function applyTechListingSeo(array $context, Request $request, array &$breadcrumbItems): array
    {
        $category = $this->cleanText($request->query('category'));
        $brand = $this->cleanText($request->query('brand'));

        $context['canonicalUrl'] = $this->buildCanonicalUrl($request->url(), [
            'category' => $category,
            'brand' => $brand,
            'page' => $this->pageQuery($request),
        ]);

        if ($this->hasNoindexTechFilters($request)) {
            $context['robotsContent'] = 'noindex, follow';
        }

        if ($category !== null && $brand !== null) {
            $context['seoTitle'] = sprintf('%s %s in Sri Lanka | DezeStore', $brand, $category);
            $context['seoDescription'] = sprintf(
                'Shop %s %s online from DezeStore in Sri Lanka. Browse products, prices, and delivery options.',
                $brand,
                $category
            );
            $context['ogTitle'] = $context['seoTitle'];
            $context['ogDescription'] = $context['seoDescription'];
        } elseif ($category !== null) {
            [$title, $description] = $this->categoryCopy($category);
            $context['seoTitle'] = $title;
            $context['seoDescription'] = $description;
            $context['ogTitle'] = $title;
            $context['ogDescription'] = $description;
        } elseif ($brand !== null) {
            $context['seoTitle'] = sprintf('%s Tech Products in Sri Lanka | DezeStore', $brand);
            $context['seoDescription'] = sprintf(
                'Shop %s mobile phones, accessories, and gadgets online from DezeStore in Sri Lanka.',
                $brand
            );
            $context['ogTitle'] = $context['seoTitle'];
            $context['ogDescription'] = $context['seoDescription'];
        } else {
            $context['seoTitle'] = 'Mobile Phones & Accessories in Sri Lanka | DezeStore';
            $context['seoDescription'] = 'Shop mobile phones, phone coolers, chargers, cases, and mobile accessories from DezeStore in Sri Lanka.';
            $context['ogTitle'] = $context['seoTitle'];
            $context['ogDescription'] = $context['seoDescription'];
        }

        $context['seoKeywords'] = $this->keywords($category, $brand, 'tech products Sri Lanka');

        $breadcrumbItems = [
            ['name' => 'Home', 'url' => $this->canonicalRootUrl()],
            ['name' => 'Tech Products', 'url' => $this->absoluteRoute('frontend.tech-products.index')],
        ];

        if ($category !== null) {
            $breadcrumbItems[] = [
                'name' => $category,
                'url' => $this->buildCanonicalUrl($this->absoluteRoute('frontend.tech-products.index'), [
                    'category' => $category,
                ]),
            ];
        }

        if ($brand !== null) {
            $breadcrumbItems[] = [
                'name' => $brand,
                'url' => $context['canonicalUrl'],
            ];
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array{name: string, url: string}>  $breadcrumbItems
     * @return array<string, mixed>
     */
    private function applyShoeListingSeo(array $context, Request $request, array &$breadcrumbItems): array
    {
        $category = $this->cleanText($request->query('shoe_category'));
        $subcategory = $this->cleanText($request->query('shoe_subcategory'));

        $context['canonicalUrl'] = $this->buildCanonicalUrl($request->url(), [
            'shoe_category' => $category,
            'shoe_subcategory' => $subcategory,
            'page' => $this->pageQuery($request),
        ]);

        if ($this->hasNoindexShoeFilters($request)) {
            $context['robotsContent'] = 'noindex, follow';
        }

        if ($subcategory !== null) {
            $context['seoTitle'] = sprintf('%s in Sri Lanka | DezeStore', $subcategory);
            $context['seoDescription'] = sprintf(
                'Shop %s online from DezeStore in Sri Lanka. Browse products, prices, and delivery options.',
                $subcategory
            );
        } elseif ($category !== null) {
            [$title, $description] = $this->categoryCopy($category);
            $context['seoTitle'] = $title;
            $context['seoDescription'] = $description;
        } else {
            $context['seoTitle'] = 'Shoes in Sri Lanka | DezeStore';
            $context['seoDescription'] = 'Shop stylish shoes and everyday footwear from DezeStore in Sri Lanka.';
        }

        $context['ogTitle'] = $context['seoTitle'];
        $context['ogDescription'] = $context['seoDescription'];
        $context['seoKeywords'] = $this->keywords($category, $subcategory, 'shoe products Sri Lanka');

        $breadcrumbItems = [
            ['name' => 'Home', 'url' => $this->canonicalRootUrl()],
            ['name' => 'Shoe Products', 'url' => $this->absoluteRoute('frontend.shoe-products.index')],
        ];

        if ($category !== null) {
            $breadcrumbItems[] = [
                'name' => $category,
                'url' => $this->buildCanonicalUrl($this->absoluteRoute('frontend.shoe-products.index'), [
                    'shoe_category' => $category,
                ]),
            ];
        }

        if ($subcategory !== null) {
            $breadcrumbItems[] = [
                'name' => $subcategory,
                'url' => $context['canonicalUrl'],
            ];
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array{name: string, url: string}>  $breadcrumbItems
     * @return array<string, mixed>
     */
    private function applyCosmeticListingSeo(array $context, Request $request, array &$breadcrumbItems): array
    {
        $category = $this->cleanText($request->query('cosmetic_category'));
        $brand = $this->cleanText($request->query('cosmetic_brand'));
        $country = $this->cleanText($request->query('cosmetic_country'));

        $context['canonicalUrl'] = $this->buildCanonicalUrl($request->url(), [
            'cosmetic_category' => $category,
            'cosmetic_brand' => $brand,
            'cosmetic_country' => $country,
            'page' => $this->pageQuery($request),
        ]);

        if ($this->hasNoindexCosmeticFilters($request)) {
            $context['robotsContent'] = 'noindex, follow';
        }

        if ($category !== null && $brand !== null) {
            $context['seoTitle'] = sprintf('%s %s in Sri Lanka | DezeStore', $brand, $category);
            $context['seoDescription'] = sprintf(
                'Shop %s %s online from DezeStore in Sri Lanka. Browse products, prices, and delivery options.',
                $brand,
                $category
            );
        } elseif ($category !== null) {
            [$title, $description] = $this->categoryCopy($category);
            $context['seoTitle'] = $title;
            $context['seoDescription'] = $description;
        } elseif ($brand !== null) {
            $context['seoTitle'] = sprintf('%s Cosmetics in Sri Lanka | DezeStore', $brand);
            $context['seoDescription'] = sprintf(
                'Shop %s cosmetics and beauty products online from DezeStore in Sri Lanka.',
                $brand
            );
        } else {
            $context['seoTitle'] = 'Cosmetics in Sri Lanka | DezeStore';
            $context['seoDescription'] = 'Shop cosmetics, beauty products, and lifestyle essentials from DezeStore in Sri Lanka.';
        }

        if ($country !== null) {
            $context['seoDescription'] = trim($context['seoDescription'] . ' Explore products from ' . $country . '.');
        }

        $context['ogTitle'] = $context['seoTitle'];
        $context['ogDescription'] = $context['seoDescription'];
        $context['seoKeywords'] = $this->keywords($category, $brand, $country, 'cosmetics Sri Lanka');

        $breadcrumbItems = [
            ['name' => 'Home', 'url' => $this->canonicalRootUrl()],
            ['name' => 'Cosmetics', 'url' => $this->absoluteRoute('frontend.cosmetic-products.index')],
        ];

        if ($category !== null) {
            $breadcrumbItems[] = [
                'name' => $category,
                'url' => $this->buildCanonicalUrl($this->absoluteRoute('frontend.cosmetic-products.index'), [
                    'cosmetic_category' => $category,
                ]),
            ];
        }

        if ($brand !== null) {
            $breadcrumbItems[] = [
                'name' => $brand,
                'url' => $this->buildCanonicalUrl($this->absoluteRoute('frontend.cosmetic-products.index'), [
                    'cosmetic_category' => $category,
                    'cosmetic_brand' => $brand,
                ]),
            ];
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array{name: string, url: string}>  $breadcrumbItems
     * @return array<string, mixed>
     */
    private function applyModuleListingSeo(array $context, Request $request, array &$breadcrumbItems, string $section): array
    {
        $config = $this->moduleSeoConfig($section);
        $category = $this->cleanText($request->query('category'));
        $brand = $this->cleanText($request->query('brand'));
        $type = $this->cleanText($request->query('type'));

        $context['canonicalUrl'] = $this->buildCanonicalUrl($request->url(), [
            'category' => $category,
            'brand' => $brand,
            'type' => $type,
            'page' => $this->pageQuery($request),
        ]);

        if ($this->hasNoindexModuleFilters($request)) {
            $context['robotsContent'] = 'noindex, follow';
        }

        if ($category !== null && $brand !== null) {
            $context['seoTitle'] = sprintf('%s %s in Sri Lanka | DezeStore', $brand, $category);
            $context['seoDescription'] = sprintf(
                'Shop %s %s online from DezeStore in Sri Lanka. Browse current products, prices, and delivery options.',
                $brand,
                $category
            );
        } elseif ($category !== null) {
            [$title, $description] = $this->categoryCopy($category);
            $context['seoTitle'] = $title;
            $context['seoDescription'] = $description;
        } elseif ($brand !== null) {
            $context['seoTitle'] = sprintf('%s %s in Sri Lanka | DezeStore', $brand, $config['short_label']);
            $context['seoDescription'] = sprintf(
                'Shop %s %s online from DezeStore in Sri Lanka. Browse prices, availability, and product details.',
                $brand,
                mb_strtolower($config['short_label'])
            );
        } elseif ($type !== null) {
            $context['seoTitle'] = sprintf('%s in Sri Lanka | DezeStore', $type);
            $context['seoDescription'] = sprintf(
                'Shop %s online from DezeStore in Sri Lanka. Browse active products, prices, and delivery options.',
                $type
            );
        } else {
            $context['seoTitle'] = $config['listing_title'];
            $context['seoDescription'] = $config['listing_description'];
        }

        $context['ogTitle'] = $context['seoTitle'];
        $context['ogDescription'] = $context['seoDescription'];
        $context['seoKeywords'] = $this->keywords($category, $brand, $type, ...$config['keywords']);

        $breadcrumbItems = [
            ['name' => 'Home', 'url' => $this->canonicalRootUrl()],
            ['name' => $config['breadcrumb'], 'url' => $this->absoluteRoute($config['index_route'])],
        ];

        if ($category !== null) {
            $breadcrumbItems[] = [
                'name' => $category,
                'url' => $this->buildCanonicalUrl($this->absoluteRoute($config['index_route']), [
                    'category' => $category,
                ]),
            ];
        }

        if ($brand !== null) {
            $breadcrumbItems[] = [
                'name' => $brand,
                'url' => $context['canonicalUrl'],
            ];
        }

        if ($brand === null && $type !== null) {
            $breadcrumbItems[] = [
                'name' => $type,
                'url' => $context['canonicalUrl'],
            ];
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array{name: string, url: string}>  $breadcrumbItems
     * @return array<string, mixed>
     */
    private function applyTechProductSeo(array $context, Request $request, array &$breadcrumbItems): array
    {
        $product = $this->resolveTechProduct($request->route('product'));

        if (! $product) {
            return $context;
        }

        $productName = $this->cleanText($product->model) ?? 'Product';
        $context['seoTitle'] = sprintf('%s | Buy Online in Sri Lanka | DezeStore', $productName);
        $context['seoDescription'] = sprintf(
            'Buy %s online from DezeStore in Sri Lanka. View price, product details, and delivery options.',
            $productName
        );
        $context['canonicalUrl'] = $this->absoluteRoute('frontend.tech-products.show', [
            'product' => $this->routeIdentifier($product),
        ]);
        $context['ogTitle'] = $context['seoTitle'];
        $context['ogDescription'] = $context['seoDescription'];
        $context['ogType'] = 'product';
        $context['ogImage'] = $product->main_image_url ?: $product->hover_image_url ?: $this->defaultOgImage;
        $context['seoKeywords'] = $this->keywords(
            $productName,
            $product->brand?->name,
            $product->category?->name,
            'buy mobile phones Sri Lanka'
        );

        $breadcrumbItems = [
            ['name' => 'Home', 'url' => $this->canonicalRootUrl()],
            ['name' => 'Tech Products', 'url' => $this->absoluteRoute('frontend.tech-products.index')],
            ['name' => $productName, 'url' => $context['canonicalUrl']],
        ];

        $context['schemaBlocks'][] = $this->productSchemaForTechProduct($product, $context['canonicalUrl']);

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array{name: string, url: string}>  $breadcrumbItems
     * @return array<string, mixed>
     */
    private function applyShoeProductSeo(array $context, Request $request, array &$breadcrumbItems): array
    {
        $product = $this->resolveShoeProduct($request->route('product'));

        if (! $product) {
            return $context;
        }

        $productName = $this->cleanText($product->name) ?? 'Product';
        $context['seoTitle'] = sprintf('%s | Buy Online in Sri Lanka | DezeStore', $productName);
        $context['seoDescription'] = sprintf(
            'Buy %s online from DezeStore in Sri Lanka. View price, product details, and delivery options.',
            $productName
        );
        $context['canonicalUrl'] = $this->absoluteRoute('frontend.shoe-products.show', [
            'product' => $this->routeIdentifier($product),
        ]);
        $context['ogTitle'] = $context['seoTitle'];
        $context['ogDescription'] = $context['seoDescription'];
        $context['ogType'] = 'product';
        $context['ogImage'] = $product->thumbnail_url ?: $product->hover_image_url ?: $this->defaultOgImage;
        $context['seoKeywords'] = $this->keywords(
            $productName,
            $product->brand?->name,
            $product->category?->name,
            'shoes Sri Lanka'
        );

        $breadcrumbItems = [
            ['name' => 'Home', 'url' => $this->canonicalRootUrl()],
            ['name' => 'Shoe Products', 'url' => $this->absoluteRoute('frontend.shoe-products.index')],
            ['name' => $productName, 'url' => $context['canonicalUrl']],
        ];

        $context['schemaBlocks'][] = $this->productSchemaForShoeProduct($product, $context['canonicalUrl']);

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array{name: string, url: string}>  $breadcrumbItems
     * @return array<string, mixed>
     */
    private function applyCosmeticProductSeo(array $context, Request $request, array &$breadcrumbItems): array
    {
        $product = $this->resolveCosmeticProduct($request->route('product'));

        if (! $product) {
            return $context;
        }

        $productName = $this->cleanText($product->name) ?? 'Product';
        $context['seoTitle'] = sprintf('%s | Buy Online in Sri Lanka | DezeStore', $productName);
        $context['seoDescription'] = sprintf(
            'Buy %s online from DezeStore in Sri Lanka. View price, product details, and delivery options.',
            $productName
        );
        $context['canonicalUrl'] = $this->absoluteRoute('frontend.cosmetic-products.show', [
            'product' => $this->routeIdentifier($product),
        ]);
        $context['ogTitle'] = $context['seoTitle'];
        $context['ogDescription'] = $context['seoDescription'];
        $context['ogType'] = 'product';
        $context['ogImage'] = $product->main_image_url ?: $this->defaultOgImage;
        $context['seoKeywords'] = $this->keywords(
            $productName,
            $product->brand?->name,
            $product->category?->name,
            'cosmetics Sri Lanka'
        );

        $breadcrumbItems = [
            ['name' => 'Home', 'url' => $this->canonicalRootUrl()],
            ['name' => 'Cosmetics', 'url' => $this->absoluteRoute('frontend.cosmetic-products.index')],
            ['name' => $productName, 'url' => $context['canonicalUrl']],
        ];

        $context['schemaBlocks'][] = $this->productSchemaForCosmeticProduct($product, $context['canonicalUrl']);

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array{name: string, url: string}>  $breadcrumbItems
     * @return array<string, mixed>
     */
    private function applyModuleProductSeo(array $context, Request $request, array &$breadcrumbItems, string $section): array
    {
        $config = $this->moduleSeoConfig($section);
        $product = $this->resolveModuleProduct($request->route('product'), $config);

        if (! $product) {
            return $context;
        }

        $productName = $this->cleanText($product->{$config['name_column']} ?? null) ?? 'Product';
        $brandName = $this->moduleProductBrandName($product, $config);
        $categoryName = $this->cleanText($product->category?->name ?? null);
        $typeName = $this->moduleProductTypeName($product, $config);

        $context['seoTitle'] = sprintf('%s | Buy Online in Sri Lanka | DezeStore', $productName);
        $context['seoDescription'] = sprintf(
            'Buy %s online from DezeStore in Sri Lanka. View current price, stock status, product details, and delivery options.',
            $productName
        );
        $context['canonicalUrl'] = $this->absoluteRoute($config['show_route'], [
            'product' => $this->routeIdentifier($product),
        ]);
        $context['ogTitle'] = $context['seoTitle'];
        $context['ogDescription'] = $context['seoDescription'];
        $context['ogType'] = 'product';
        $context['ogImage'] = $product->main_image_url ?: $product->hover_image_url ?: $this->defaultOgImage;
        $context['seoKeywords'] = $this->keywords(
            $productName,
            $brandName,
            $categoryName,
            $typeName,
            ...$config['keywords']
        );

        $breadcrumbItems = [
            ['name' => 'Home', 'url' => $this->canonicalRootUrl()],
            ['name' => $config['breadcrumb'], 'url' => $this->absoluteRoute($config['index_route'])],
            ['name' => $productName, 'url' => $context['canonicalUrl']],
        ];

        $context['schemaBlocks'][] = $this->productSchemaForModuleProduct($product, $context['canonicalUrl'], $config);

        return $context;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function baseSchemaBlocks(): array
    {
        $sameAs = $this->socialLinks();

        return [
            $this->filterSchema([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $this->siteName,
                'url' => $this->canonicalRootUrl(),
                'logo' => $this->defaultLogoUrl,
                'sameAs' => $sameAs,
            ]),
            $this->filterSchema([
                '@context' => 'https://schema.org',
                '@type' => 'OnlineStore',
                'name' => $this->siteName,
                'url' => $this->canonicalRootUrl(),
                'logo' => $this->defaultLogoUrl,
                'image' => $this->defaultOgImage,
                'description' => 'DezeStore is an online shopping store in Sri Lanka selling mobile phones, gadgets, motorcycle products, shoes, cosmetics, fashion accessories, home needs, and lifestyle products.',
                'sameAs' => $sameAs,
            ]),
            $this->filterSchema([
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $this->siteName,
                'url' => $this->canonicalRootUrl(),
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $this->siteName,
                    'logo' => $this->defaultLogoUrl,
                ],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => $this->absoluteRoute('frontend.tech-products.index') . '?search={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ]),
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    private function breadcrumbSchema(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(
                fn (array $item, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ],
                $items,
                array_keys($items)
            )),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productSchemaForTechProduct(Product $product, string $canonicalUrl): array
    {
        $price = $this->techProductPrice($product);
        $images = array_values(array_filter(array_unique([
            $product->main_image_url,
            $product->hover_image_url,
            ...($product->gallery_urls ?? []),
        ])));

        return $this->filterSchema([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->model,
            'image' => $images ?: [$this->defaultOgImage],
            'description' => $this->productDescription($product->short_description, $product->long_description),
            'sku' => $product->sku,
            'brand' => $product->brand?->name ? [
                '@type' => 'Brand',
                'name' => $product->brand->name,
            ] : null,
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => 'LKR',
                'price' => $price !== null ? number_format($price, 2, '.', '') : null,
                'availability' => $this->techProductInStock($product)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
            'aggregateRating' => $this->aggregateRating($product),
            'dateModified' => optional($product->updated_at)->toIso8601String(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function productSchemaForShoeProduct(ShoeProduct $product, string $canonicalUrl): array
    {
        $price = $this->shoeProductPrice($product);
        $images = array_values(array_filter(array_unique([
            $product->thumbnail_url,
            $product->hover_image_url,
            ...($product->gallery_urls ?? []),
        ])));

        return $this->filterSchema([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => $images ?: [$this->defaultOgImage],
            'description' => $this->productDescription($product->short_description, $product->full_description),
            'sku' => $product->sku,
            'brand' => $product->brand?->name ? [
                '@type' => 'Brand',
                'name' => $product->brand->name,
            ] : null,
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => $product->currency ?: 'LKR',
                'price' => $price !== null ? number_format($price, 2, '.', '') : null,
                'availability' => $this->shoeProductInStock($product)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
            'aggregateRating' => $this->aggregateRating($product),
            'dateModified' => optional($product->updated_at)->toIso8601String(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function productSchemaForCosmeticProduct(CosmeticProduct $product, string $canonicalUrl): array
    {
        $price = $this->cosmeticProductPrice($product);
        $images = array_values(array_filter(array_unique([
            $product->main_image_url,
            ...($product->gallery_urls ?? []),
        ])));

        return $this->filterSchema([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => $images ?: [$this->defaultOgImage],
            'description' => $this->productDescription($product->short_description, $product->long_description),
            'sku' => $product->batch_number,
            'brand' => $product->brand?->name ? [
                '@type' => 'Brand',
                'name' => $product->brand->name,
            ] : null,
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => 'LKR',
                'price' => $price !== null ? number_format($price, 2, '.', '') : null,
                'availability' => $this->cosmeticProductInStock($product)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
            'aggregateRating' => $this->aggregateRating($product),
            'dateModified' => optional($product->updated_at)->toIso8601String(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function productSchemaForModuleProduct(object $product, string $canonicalUrl, array $config): array
    {
        $price = $this->moduleProductPrice($product, $config['price_column']);
        $images = array_values(array_filter(array_unique([
            $product->main_image_url ?? null,
            $product->hover_image_url ?? null,
            ...($product->gallery_urls ?? []),
        ])));
        $brandName = $this->moduleProductBrandName($product, $config);

        return $this->filterSchema([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->{$config['name_column']} ?? null,
            'image' => $images ?: [$this->defaultOgImage],
            'description' => $this->productDescription($product->short_description ?? null, $product->full_description ?? null),
            'sku' => $product->sku ?? null,
            'brand' => $brandName ? [
                '@type' => 'Brand',
                'name' => $brandName,
            ] : null,
            'category' => $product->category?->name ?? null,
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => 'LKR',
                'price' => $price !== null ? number_format($price, 2, '.', '') : null,
                'availability' => $this->moduleProductInStock($product)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
            'aggregateRating' => $this->aggregateRating($product),
            'dateModified' => optional($product->updated_at)->toIso8601String(),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function aggregateRating(object $product): ?array
    {
        $reviews = method_exists($product, 'reviews') ? $product->reviews()->count() : 0;
        $rating = method_exists($product, 'reviews') ? $product->reviews()->avg('rating') : null;

        if ($reviews < 1 || ! is_numeric($rating)) {
            return null;
        }

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => number_format((float) $rating, 1, '.', ''),
            'reviewCount' => (int) $reviews,
        ];
    }

    private function techProductPrice(Product $product): ?float
    {
        $price = is_numeric($product->price_lkr) ? (float) $product->price_lkr : null;

        if ($price === null || $price <= 0) {
            return null;
        }

        $discountValue = is_numeric($product->discount_value) ? (float) $product->discount_value : 0.0;

        if ($discountValue <= 0) {
            return $price;
        }

        if ($product->discount_type === 'percent') {
            return max(0, round($price - (($price * $discountValue) / 100), 2));
        }

        if ($product->discount_type === 'price') {
            return max(0, round($price - $discountValue, 2));
        }

        return $price;
    }

    private function shoeProductPrice(ShoeProduct $product): ?float
    {
        $regularPrice = is_numeric($product->regular_price) ? (float) $product->regular_price : null;
        $salePrice = is_numeric($product->sale_price) ? (float) $product->sale_price : null;
        $today = now()->startOfDay();

        $saleStarted = ! $product->sale_start_date || $product->sale_start_date->lte($today);
        $saleNotEnded = ! $product->sale_end_date || $product->sale_end_date->gte($today);

        if (
            $regularPrice !== null
            && $salePrice !== null
            && $salePrice > 0
            && $regularPrice > $salePrice
            && $saleStarted
            && $saleNotEnded
        ) {
            return $salePrice;
        }

        return $regularPrice;
    }

    private function cosmeticProductPrice(CosmeticProduct $product): ?float
    {
        $price = is_numeric($product->price) ? (float) $product->price : null;

        if ($price === null || $price <= 0) {
            return null;
        }

        $discountValue = is_numeric($product->discount_value) ? (float) $product->discount_value : 0.0;

        if ($discountValue <= 0) {
            return $price;
        }

        if ($product->discount_type === 'percentage') {
            return max(0, round($price - (($price * $discountValue) / 100), 2));
        }

        if ($product->discount_type === 'fixed') {
            return max(0, round($price - $discountValue, 2));
        }

        return $price;
    }

    private function moduleProductPrice(object $product, string $priceColumn): ?float
    {
        $regularPrice = is_numeric($product->{$priceColumn} ?? null) ? (float) $product->{$priceColumn} : null;

        if ($regularPrice === null || $regularPrice <= 0) {
            return null;
        }

        $salePrice = is_numeric($product->sale_price ?? null) ? (float) $product->sale_price : null;

        if ($salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice) {
            return $salePrice;
        }

        return $regularPrice;
    }

    private function techProductInStock(Product $product): bool
    {
        if (! $product->in_stock) {
            return false;
        }

        return $product->stock_count === null || (int) $product->stock_count > 0;
    }

    private function shoeProductInStock(ShoeProduct $product): bool
    {
        if ($product->stock_status === 'out_of_stock' || $product->status === 'out_of_stock') {
            return false;
        }

        return $product->stock_quantity === null || (int) $product->stock_quantity > 0;
    }

    private function cosmeticProductInStock(CosmeticProduct $product): bool
    {
        return $product->stock === null || (int) $product->stock > 0;
    }

    private function moduleProductInStock(object $product): bool
    {
        if (($product->stock_status ?? null) === 'out_of_stock') {
            return false;
        }

        return ($product->stock_quantity ?? null) === null || (int) $product->stock_quantity > 0;
    }

    private function productDescription(?string $short, ?string $long): ?string
    {
        $description = $this->cleanText($short) ?: $this->cleanText($long);

        if ($description === null) {
            return null;
        }

        return mb_substr($description, 0, 300);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function categoryCopy(string $name): array
    {
        $normalized = mb_strtolower(trim($name));

        return match ($normalized) {
            'mobile phones', 'mobile phone' => [
                'Mobile Phones in Sri Lanka | DezeStore',
                'Shop mobile phones online from DezeStore in Sri Lanka. Browse smartphones, prices, accessories, and delivery options.',
            ],
            'phone coolers', 'phone cooler' => [
                'Phone Coolers in Sri Lanka | DezeStore',
                'Shop phone coolers for gaming, streaming, and daily mobile use from DezeStore in Sri Lanka.',
            ],
            'mobile accessories', 'mobile accessory' => [
                'Mobile Accessories in Sri Lanka | DezeStore',
                'Shop mobile accessories, chargers, cables, cases, phone coolers, and gadget essentials from DezeStore in Sri Lanka.',
            ],
            'shoes', 'shoe' => [
                'Shoes in Sri Lanka | DezeStore',
                'Shop stylish shoes and everyday footwear from DezeStore in Sri Lanka.',
            ],
            'cosmetics', 'cosmetic' => [
                'Cosmetics in Sri Lanka | DezeStore',
                'Shop cosmetics, beauty products, and lifestyle essentials from DezeStore in Sri Lanka.',
            ],
            'motorcycle products', 'motorcycle helmets', 'helmets', 'bike accessories', 'motorcycle accessories' => [
                'Motorcycle Helmets & Bike Accessories in Sri Lanka | DezeStore',
                'Shop motorcycle helmets, riding accessories, bike accessories, and spare parts from DezeStore in Sri Lanka.',
            ],
            'fashion', 'fashion accessories', 'accessories', 'bags', 'sunglasses' => [
                'Fashion & Accessories in Sri Lanka | DezeStore',
                'Shop fashion accessories, bags, sunglasses, and style essentials from DezeStore in Sri Lanka.',
            ],
            'home needs', 'home products', 'kitchen', 'home accessories' => [
                'Home Needs in Sri Lanka | DezeStore',
                'Shop home, kitchen, storage, decor, and utility products from DezeStore in Sri Lanka.',
            ],
            default => [
                sprintf('%s in Sri Lanka | DezeStore', $name),
                sprintf(
                    'Shop %s online from DezeStore in Sri Lanka. Browse products, prices, and delivery options.',
                    $name
                ),
            ],
        };
    }

    private function hasNoindexTechFilters(Request $request): bool
    {
        return $this->hasNoindexFilters($request, [
            'search',
            'stock',
            'sale',
            'hot_deals',
            'featured',
            'best_seller',
            'top_rated',
            'sort',
            'min_price',
            'max_price',
        ]);
    }

    private function hasNoindexShoeFilters(Request $request): bool
    {
        return $this->hasNoindexFilters($request, [
            'search',
            'stock',
            'sale',
            'sort',
            'min_price',
            'max_price',
        ]);
    }

    private function hasNoindexCosmeticFilters(Request $request): bool
    {
        return $this->hasNoindexFilters($request, [
            'search',
            'stock',
            'sale',
            'sort',
            'min_price',
            'max_price',
        ]);
    }

    private function hasNoindexModuleFilters(Request $request): bool
    {
        return $this->hasNoindexFilters($request, [
            'search',
            'warranty',
            'stock',
            'sale',
            'featured',
            'today_best_deals',
            'hot_deals',
            'best_seller',
            'sort',
            'min_price',
            'max_price',
        ]);
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function hasNoindexFilters(Request $request, array $keys): bool
    {
        foreach ($keys as $key) {
            $value = $request->query($key);

            if (is_bool($value) && $value) {
                return true;
            }

            if (is_string($value) && trim($value) !== '') {
                return true;
            }

            if (is_numeric($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  mixed  ...$parts
     */
    private function keywords(...$parts): string
    {
        $keywords = $this->defaultKeywordList;

        foreach ($parts as $part) {
            if (! is_string($part) || trim($part) === '') {
                continue;
            }

            $keywords[] = trim($part);
        }

        return implode(', ', array_values(array_unique($keywords)));
    }

    private function cleanText(mixed $value): ?string
    {
        if (! is_scalar($value) || trim((string) $value) === '') {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)) ?? '');
    }

    /**
     * @param  array<string, scalar|null>  $params
     */
    private function buildCanonicalUrl(string $baseUrl, array $params = []): string
    {
        $filtered = [];

        foreach ($params as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            $filtered[$key] = $value;
        }

        if ($filtered === []) {
            return $baseUrl;
        }

        return $baseUrl . '?' . http_build_query($filtered, '', '&', PHP_QUERY_RFC3986);
    }

    private function pageQuery(Request $request): ?int
    {
        $page = (int) $request->query('page', 1);

        return $page > 1 ? $page : null;
    }

    private function absoluteRoute(string $name, array $params = []): string
    {
        $path = route($name, $params, false);

        return $this->siteUrl . ($path === '/' ? '/' : $path);
    }

    private function routeIdentifier(object $product): int|string
    {
        return filled($product->slug ?? null) ? $product->slug : $product->id;
    }

    private function resolveTechProduct(mixed $routeParameter): ?Product
    {
        if (! Schema::hasTable('products')) {
            return null;
        }

        if ($routeParameter instanceof Product) {
            $routeParameter->loadMissing(['brand', 'category']);

            return $routeParameter->status === 'active' ? $routeParameter : null;
        }

        return Product::query()
            ->with(['brand', 'category'])
            ->where(function ($query) use ($routeParameter) {
                if (is_numeric($routeParameter)) {
                    $query->whereKey((int) $routeParameter);
                }

                $query->orWhere('slug', (string) $routeParameter);
            })
            ->where('status', 'active')
            ->first();
    }

    private function resolveShoeProduct(mixed $routeParameter): ?ShoeProduct
    {
        if (! Schema::hasTable('shoe_products')) {
            return null;
        }

        if ($routeParameter instanceof ShoeProduct) {
            $routeParameter->loadMissing(['brand', 'category', 'subcategory']);

            return $routeParameter->status === 'published' ? $routeParameter : null;
        }

        $identifier = $this->cleanText($routeParameter);

        if ($identifier === null) {
            return null;
        }

        return ShoeProduct::query()
            ->with(['brand', 'category', 'subcategory'])
            ->where(function ($query) use ($identifier) {
                if (ctype_digit($identifier)) {
                    $query->whereKey((int) $identifier);
                }

                $query->orWhere('slug', $identifier);
            })
            ->where('status', 'published')
            ->first();
    }

    private function resolveCosmeticProduct(mixed $routeParameter): ?CosmeticProduct
    {
        if (! Schema::hasTable('cosmetic_products')) {
            return null;
        }

        if ($routeParameter instanceof CosmeticProduct) {
            $routeParameter->loadMissing(['brand', 'category']);

            return $routeParameter->status === 'active' ? $routeParameter : null;
        }

        $identifier = $this->cleanText($routeParameter);

        if ($identifier === null) {
            return null;
        }

        return CosmeticProduct::query()
            ->with(['brand', 'category'])
            ->where(function ($query) use ($identifier) {
                if (ctype_digit($identifier)) {
                    $query->whereKey((int) $identifier);
                }

                $query->orWhere('slug', $identifier);
            })
            ->where('status', 'active')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function resolveModuleProduct(mixed $routeParameter, array $config): ?object
    {
        $modelClass = $config['model'];
        $model = new $modelClass();

        if (! Schema::hasTable($model->getTable())) {
            return null;
        }

        if ($routeParameter instanceof $modelClass) {
            $routeParameter->loadMissing($config['relations']);

            return $routeParameter->status === 'active' ? $routeParameter : null;
        }

        $identifier = $this->cleanText($routeParameter);

        if ($identifier === null) {
            return null;
        }

        return $modelClass::query()
            ->with($config['relations'])
            ->where(function ($query) use ($identifier) {
                if (ctype_digit($identifier)) {
                    $query->whereKey((int) $identifier);
                }

                $query->orWhere('slug', $identifier);
            })
            ->where('status', 'active')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function moduleSeoConfig(string $section): array
    {
        return match ($section) {
            'motorcycle' => [
                'model' => MotorcycleProduct::class,
                'relations' => ['category', 'helmetBrand'],
                'name_column' => 'name',
                'price_column' => 'regular_price',
                'brand_relation' => 'helmetBrand',
                'brand_name_column' => null,
                'type_relation' => null,
                'type_column' => 'product_type',
                'index_route' => 'frontend.motorcycle-products.index',
                'show_route' => 'frontend.motorcycle-products.show',
                'breadcrumb' => 'Motorcycle Products',
                'short_label' => 'Motorcycle Products',
                'listing_title' => 'Motorcycle Helmets, Bike Accessories & Parts in Sri Lanka | DezeStore',
                'listing_description' => 'Shop motorcycle helmets, riding accessories, bike accessories, spare parts, and riding essentials from DezeStore in Sri Lanka.',
                'keywords' => ['motorcycle products Sri Lanka', 'motorcycle helmets Sri Lanka', 'bike accessories Sri Lanka'],
            ],
            'fashion' => [
                'model' => FashionProduct::class,
                'relations' => ['category', 'brand', 'productType'],
                'name_column' => 'name',
                'price_column' => 'price',
                'brand_relation' => 'brand',
                'brand_name_column' => 'brand_name',
                'type_relation' => 'productType',
                'type_column' => null,
                'index_route' => 'frontend.fashion.index',
                'show_route' => 'frontend.fashion.show',
                'breadcrumb' => 'Fashion & Accessories',
                'short_label' => 'Fashion Accessories',
                'listing_title' => 'Fashion & Accessories in Sri Lanka | DezeStore',
                'listing_description' => 'Shop necklaces, bags, fashion wear, sunglasses, accessories, and style essentials from DezeStore in Sri Lanka.',
                'keywords' => ['fashion accessories Sri Lanka', 'bags Sri Lanka', 'sunglasses Sri Lanka'],
            ],
            'home-needs' => [
                'model' => HomeNeedProduct::class,
                'relations' => ['category', 'brand'],
                'name_column' => 'name',
                'price_column' => 'price',
                'brand_relation' => 'brand',
                'brand_name_column' => 'brand_name',
                'type_relation' => null,
                'type_column' => null,
                'index_route' => 'frontend.home-needs.index',
                'show_route' => 'frontend.home-needs.show',
                'breadcrumb' => 'Home Needs',
                'short_label' => 'Home Needs',
                'listing_title' => 'Home Needs in Sri Lanka | DezeStore',
                'listing_description' => 'Shop everyday home, kitchen, storage, decor, and utility products from DezeStore in Sri Lanka.',
                'keywords' => ['home needs Sri Lanka', 'kitchen products Sri Lanka', 'home accessories Sri Lanka'],
            ],
            default => throw new \InvalidArgumentException('Unsupported SEO section.'),
        };
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function moduleProductBrandName(object $product, array $config): ?string
    {
        $relation = $config['brand_relation'];
        $brandNameColumn = $config['brand_name_column'];

        return $this->cleanText($relation ? ($product->{$relation}?->name ?? null) : null)
            ?: $this->cleanText($brandNameColumn ? ($product->{$brandNameColumn} ?? null) : null);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function moduleProductTypeName(object $product, array $config): ?string
    {
        if ($config['type_relation']) {
            return $this->cleanText($product->{$config['type_relation']}?->name ?? null);
        }

        if ($config['type_column']) {
            $value = $this->cleanText($product->{$config['type_column']} ?? null);

            return $value ? str_replace('_', ' ', $value) : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function filterSchema(array $schema): array
    {
        $filtered = [];

        foreach ($schema as $key => $value) {
            if (is_array($value)) {
                $value = array_is_list($value)
                    ? array_values(array_filter(array_map(function ($item) {
                        return is_array($item) ? $this->filterSchema($item) : $item;
                    }, $value)))
                    : $this->filterSchema($value);
            }

            if ($value === null) {
                continue;
            }

            if (is_array($value) && $value === []) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }
}
