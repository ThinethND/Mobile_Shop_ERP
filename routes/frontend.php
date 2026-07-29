<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerGalleryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\AiChatController;
use App\Http\Controllers\Frontend\CartPageController;
use App\Http\Controllers\Frontend\ContactUsController;
use App\Http\Controllers\Frontend\CheckoutPageController;
use App\Http\Controllers\Frontend\HomeSectionController;
use App\Http\Controllers\Frontend\ModuleProductViewController;
use App\Http\Controllers\Frontend\ProductSuggestionController;
use App\Http\Controllers\Frontend\WishlistPageController;
use App\Http\Controllers\Frontend\WebProductCollectionController;
use App\Http\Controllers\Frontend\WebShoeProductController;
use App\Http\Controllers\Frontend\WebTechProductController;
use App\Http\Controllers\Frontend\TechProductViewController;
use App\Http\Controllers\Frontend\ShoeProductViewController;
use App\Http\Controllers\Frontend\WebCosmeticProductController;
use App\Http\Controllers\Frontend\CosmeticProductViewController;

Route::get('/', [HomeController::class, 'index'])->name('frontend.root');

Route::get('/home', [HomeController::class, 'index'])->name('frontend.home');

Route::get('/home/customer-gallery', [CustomerGalleryController::class, 'publicImages'])
    ->name('frontend.home.customer-gallery');

Route::get('/home/customer-gallery/images/{customerGalleryImage}', [CustomerGalleryController::class, 'image'])
    ->whereNumber('customerGalleryImage')
    ->name('frontend.home.customer-gallery.image');

Route::get('/motorcycle-products', [WebProductCollectionController::class, 'motorcycleIndex'])
    ->name('frontend.motorcycle-products.index');

Route::get('/motorcycle-products/products', [WebProductCollectionController::class, 'motorcycleProducts'])
    ->name('frontend.motorcycle-products.products');

Route::get('/motorcycle-products/{product}', [ModuleProductViewController::class, 'motorcycleIndex'])
    ->name('frontend.motorcycle-products.show');

Route::get('/motorcycle-products/{product}/data', [ModuleProductViewController::class, 'motorcycleData'])
    ->name('frontend.motorcycle-products.show.data');

Route::get('/motorcycle-products/{product}/reviews', [ModuleProductViewController::class, 'motorcycleReviews'])
    ->name('frontend.motorcycle-products.show.reviews');

Route::post('/motorcycle-products/{product}/reviews', [ModuleProductViewController::class, 'storeMotorcycleReview'])
    ->name('frontend.motorcycle-products.show.reviews.store');

Route::get('/fashion', [WebProductCollectionController::class, 'fashionIndex'])
    ->name('frontend.fashion.index');

Route::get('/fashion/products', [WebProductCollectionController::class, 'fashionProducts'])
    ->name('frontend.fashion.products');

Route::get('/fashion/{product}', [ModuleProductViewController::class, 'fashionIndex'])
    ->name('frontend.fashion.show');

Route::get('/fashion/{product}/data', [ModuleProductViewController::class, 'fashionData'])
    ->name('frontend.fashion.show.data');

Route::get('/home-needs', [WebProductCollectionController::class, 'homeNeedsIndex'])
    ->name('frontend.home-needs.index');

Route::get('/home-needs/products', [WebProductCollectionController::class, 'homeNeedsProducts'])
    ->name('frontend.home-needs.products');

Route::get('/home-needs/{product}', [ModuleProductViewController::class, 'homeNeedsIndex'])
    ->name('frontend.home-needs.show');

Route::get('/home-needs/{product}/data', [ModuleProductViewController::class, 'homeNeedsData'])
    ->name('frontend.home-needs.show.data');

Route::get('/contact-us', [ContactUsController::class, 'index'])
    ->name('frontend.contact-us.index');

Route::get('/cart', [CartPageController::class, 'index'])
    ->name('frontend.cart.index');

Route::get('/wishlist', [WishlistPageController::class, 'index'])
    ->name('frontend.wishlist.index');

Route::get('/checkout', [CheckoutPageController::class, 'index'])
    ->name('frontend.checkout.index');

Route::post('/checkout/place-order', [CheckoutPageController::class, 'store'])
    ->name('frontend.checkout.store');

Route::get('/home/categories', [HomeController::class, 'categories'])
    ->name('frontend.home.categories');

Route::get('/home/all-categories', [HomeController::class, 'allCategories'])
    ->name('frontend.home.all-categories');

Route::get('/home/sections/today-deals', [HomeSectionController::class, 'todayDeals'])
    ->name('frontend.home.sections.today-deals');

Route::get('/home/sections/best-sellers', [HomeSectionController::class, 'bestSellers'])
    ->name('frontend.home.sections.best-sellers');

Route::get('/home/sections/{section}', [HomeSectionController::class, 'categorySection'])
    ->whereIn('section', ['motorcycle', 'electronics', 'cosmetics', 'fashion', 'home-needs'])
    ->name('frontend.home.sections.category');

Route::get('/home/shoe-categories', [HomeController::class, 'shoeCategories'])
    ->name('frontend.home.shoe-categories');

    Route::get('/featured-products', [HomeController::class, 'featuredProducts'])
    ->name('home.featured-products');

Route::get('/home/featured-shoes', [HomeController::class, 'featuredShoes'])
    ->name('frontend.home.featured-shoes');

Route::get('/home/featured-cosmetics', [HomeController::class, 'featuredCosmetics'])
    ->name('frontend.home.featured-cosmetics');

Route::get('/home/products', [HomeController::class, 'products'])
    ->name('frontend.home.products');

Route::get('/home/cosmetic-brands', [HomeController::class, 'cosmeticBrands'])
    ->name('frontend.home.cosmetic-brands');

Route::get('/home/cosmetic-products', [HomeController::class, 'cosmeticProducts'])
    ->name('frontend.home.cosmetic-products');

Route::get('/tech-products', [WebProductCollectionController::class, 'electronicsIndex'])
    ->name('frontend.tech-products.index');

Route::get('/tech-products/products', [WebProductCollectionController::class, 'electronicsProducts'])
    ->name('frontend.tech-products.products');

Route::get('/tech-products/related/cart', [WebTechProductController::class, 'cartRelated'])
    ->name('frontend.tech-products.cart-related');

Route::get('/tech-products/{product}', [TechProductViewController::class, 'index'])
    ->name('frontend.tech-products.show');

Route::get('/tech-products/{product}/data', [TechProductViewController::class, 'data'])
    ->name('frontend.tech-products.show.data');

Route::get('/tech-products/{product}/reviews', [TechProductViewController::class, 'reviews'])
    ->name('frontend.tech-products.show.reviews');

Route::post('/tech-products/{product}/reviews', [TechProductViewController::class, 'storeReview'])
    ->name('frontend.tech-products.show.reviews.store');

Route::get('/shoe-products', [WebShoeProductController::class, 'index'])
    ->name('frontend.shoe-products.index');

Route::get('/shoe-products/products', [WebShoeProductController::class, 'products'])
    ->name('frontend.shoe-products.products');

Route::get('/shoe-products/{product}', [ShoeProductViewController::class, 'index'])
    ->name('frontend.shoe-products.show');

Route::get('/shoe-products/{product}/data', [ShoeProductViewController::class, 'data'])
    ->name('frontend.shoe-products.show.data');

Route::get('/shoe-products/{product}/reviews', [ShoeProductViewController::class, 'reviews'])
    ->name('frontend.shoe-products.show.reviews');

Route::post('/shoe-products/{product}/reviews', [ShoeProductViewController::class, 'storeReview'])
    ->name('frontend.shoe-products.show.reviews.store');

Route::get('/cosmetics', [WebProductCollectionController::class, 'cosmeticsIndex'])
    ->name('frontend.cosmetic-products.index');

Route::get('/cosmetics/products', [WebProductCollectionController::class, 'cosmeticsProducts'])
    ->name('frontend.cosmetic-products.products');

Route::get('/cosmetics/{product}', [CosmeticProductViewController::class, 'index'])
    ->name('frontend.cosmetic-products.show');

Route::get('/cosmetics/{product}/data', [CosmeticProductViewController::class, 'data'])
    ->name('frontend.cosmetic-products.show.data');

Route::get('/cosmetics/{product}/reviews', [CosmeticProductViewController::class, 'reviews'])
    ->name('frontend.cosmetic-products.show.reviews');

Route::post('/cosmetics/{product}/reviews', [CosmeticProductViewController::class, 'storeReview'])
    ->name('frontend.cosmetic-products.show.reviews.store');

Route::permanentRedirect('/cosmetic-products', '/cosmetics');
Route::permanentRedirect('/cosmetic-products/products', '/cosmetics/products');
Route::permanentRedirect('/cosmetic-products/{product}', '/cosmetics/{product}');
Route::permanentRedirect('/cosmetic-products/{product}/data', '/cosmetics/{product}/data');
Route::permanentRedirect('/cosmetic-products/{product}/reviews', '/cosmetics/{product}/reviews');
Route::post('/cosmetic-products/{product}/reviews', [CosmeticProductViewController::class, 'storeReview']);

Route::get('/search/product-suggestions', [ProductSuggestionController::class, 'suggestions'])
    ->name('frontend.products.suggestions');

Route::post('/ai/chat', AiChatController::class)
    ->middleware('throttle:20,1')
    ->name('frontend.ai.chat');
