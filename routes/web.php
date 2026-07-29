<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\StorageOptionController;
use App\Http\Controllers\RamOptionController;
use App\Http\Controllers\WarrantyOptionController;
use App\Http\Controllers\ColorOptionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\HomeBannerController;
use App\Http\Controllers\KokoPaySettingController;
use App\Http\Controllers\DeliveryChargeSettingController;
use App\Http\Controllers\Admin\Shoes\ShoeBrandController;
use App\Http\Controllers\Admin\Shoes\ShoeTypeController;
use App\Http\Controllers\Admin\Shoes\ShoeCategoryController;
use App\Http\Controllers\Admin\Shoes\ShoeSubcategoryController;
use App\Http\Controllers\Admin\Shoes\ShoeSizeTypeController;
use App\Http\Controllers\Admin\Shoes\ShoeColorController;
use App\Http\Controllers\Admin\Shoes\ShoeMaterialController;
use App\Http\Controllers\Admin\Shoes\ShoeProductController;
use App\Http\Controllers\Admin\Shoes\ShoeProductReviewController;
use App\Http\Controllers\Admin\Cosmetics\CosmeticBrandController;
use App\Http\Controllers\Admin\Cosmetics\CosmeticCategoryController;
use App\Http\Controllers\Admin\Cosmetics\CosmeticCountryOfOriginController;
use App\Http\Controllers\Admin\Cosmetics\CosmeticProductController;
use App\Http\Controllers\Admin\Cosmetics\CosmeticProductTypeController;
use App\Http\Controllers\Admin\Cosmetics\CosmeticProductReviewController;
use App\Http\Controllers\Admin\Cosmetics\CosmeticSizeVolumeController;
use App\Http\Controllers\Admin\Motorcycles\MotorcycleBikeModelController;
use App\Http\Controllers\Admin\Motorcycles\MotorcycleHelmetBrandController;
use App\Http\Controllers\Admin\Motorcycles\MotorcycleProductCategoryController;
use App\Http\Controllers\Admin\Motorcycles\MotorcycleProductController;
use App\Http\Controllers\Admin\Motorcycles\MotorcycleProductOptionController;
use App\Http\Controllers\Admin\Motorcycles\MotorcycleProductReviewController;
use App\Http\Controllers\Admin\HomeNeeds\HomeNeedCategoryController;
use App\Http\Controllers\Admin\HomeNeeds\HomeNeedBrandController;
use App\Http\Controllers\Admin\HomeNeeds\HomeNeedProductController;
use App\Http\Controllers\Admin\Fashion\FashionBrandController;
use App\Http\Controllers\Admin\Fashion\FashionCategoryController;
use App\Http\Controllers\Admin\Fashion\FashionProductController;
use App\Http\Controllers\Admin\Fashion\FashionProductTypeController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\CustomerGalleryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SitemapController;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

require __DIR__ . '/frontend.php';

Route::redirect('/dashboard', '/admin/dashboard');

Route::redirect('/go-home', '/home')->name('home');
Route::get('/login', function () {
    return Inertia::render('auth/Login', [
        'status' => session('status'),
        'canRegister' => Features::enabled(Features::registration()),
        'canResetPassword' => Features::enabled(Features::resetPasswords()),
    ]);
})->middleware('guest')->name('login');
Route::post('/login', [\Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::prefix('admin')->middleware('web')->group(function () {

    Route::get('/login', function () {
        return Inertia::render('auth/Login', [
            'status' => session('status'),
            'canRegister' => Features::enabled(Features::registration()),
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
        ]);
    })->middleware('guest')->name('admin.login');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.profile.update-password');
        Route::get('/koko-pay', [KokoPaySettingController::class, 'edit'])->name('koko-pay.edit');
        Route::put('/koko-pay', [KokoPaySettingController::class, 'update'])->name('koko-pay.update');
        Route::get('/delivery-charges', [DeliveryChargeSettingController::class, 'edit'])->name('delivery-charges.edit');
        Route::put('/delivery-charges', [DeliveryChargeSettingController::class, 'update'])->name('delivery-charges.update');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/data', [CategoryController::class, 'data'])->name('categories.data');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/data', [BrandController::class, 'data'])->name('brands.data');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

        Route::get('/storage-options', [StorageOptionController::class, 'index'])->name('storage.index');
        Route::get('/storage-options/data', [StorageOptionController::class, 'data'])->name('storage.data');
        Route::get('/storage-options/create', [StorageOptionController::class, 'create'])->name('storage.create');
        Route::post('/storage-options', [StorageOptionController::class, 'store'])->name('storage.store');
        Route::get('/storage-options/{storageOption}/edit', [StorageOptionController::class, 'edit'])->name('storage.edit');
        Route::put('/storage-options/{storageOption}', [StorageOptionController::class, 'update'])->name('storage.update');
        Route::delete('/storage-options/{storageOption}', [StorageOptionController::class, 'destroy'])->name('storage.destroy');

        Route::get('/ram-options', [RamOptionController::class, 'index'])->name('ram.index');
        Route::get('/ram-options/data', [RamOptionController::class, 'data'])->name('ram.data');
        Route::get('/ram-options/create', [RamOptionController::class, 'create'])->name('ram.create');
        Route::post('/ram-options', [RamOptionController::class, 'store'])->name('ram.store');
        Route::get('/ram-options/{ramOption}/edit', [RamOptionController::class, 'edit'])->name('ram.edit');
        Route::put('/ram-options/{ramOption}', [RamOptionController::class, 'update'])->name('ram.update');
        Route::delete('/ram-options/{ramOption}', [RamOptionController::class, 'destroy'])->name('ram.destroy');

        Route::get('/warranty-options', [WarrantyOptionController::class, 'index'])->name('warranty.index');
        Route::get('/warranty-options/data', [WarrantyOptionController::class, 'data'])->name('warranty.data');
        Route::get('/warranty-options/create', [WarrantyOptionController::class, 'create'])->name('warranty.create');
        Route::post('/warranty-options', [WarrantyOptionController::class, 'store'])->name('warranty.store');
        Route::get('/warranty-options/{warrantyOption}/edit', [WarrantyOptionController::class, 'edit'])->name('warranty.edit');
        Route::put('/warranty-options/{warrantyOption}', [WarrantyOptionController::class, 'update'])->name('warranty.update');
        Route::delete('/warranty-options/{warrantyOption}', [WarrantyOptionController::class, 'destroy'])->name('warranty.destroy');

        Route::get('/color-options', [ColorOptionController::class, 'index'])->name('colors.index');
        Route::get('/color-options/data', [ColorOptionController::class, 'data'])->name('colors.data');
        Route::get('/color-options/create', [ColorOptionController::class, 'create'])->name('colors.create');
        Route::post('/color-options', [ColorOptionController::class, 'store'])->name('colors.store');
        Route::get('/color-options/{colorOption}/image', [ColorOptionController::class, 'image'])->name('colors.image');
        Route::get('/color-options/{colorOption}/edit', [ColorOptionController::class, 'edit'])->name('colors.edit');
        Route::put('/color-options/{colorOption}', [ColorOptionController::class, 'update'])->name('colors.update');
        Route::delete('/color-options/{colorOption}', [ColorOptionController::class, 'destroy'])->name('colors.destroy');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/data', [ProductController::class, 'data'])->name('products.data');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::prefix('product-reviews')
            ->name('product-reviews.')
            ->controller(ProductReviewController::class)
            ->scopeBindings()
            ->group(function () {
                Route::get('/', 'products')->name('index');
                Route::get('/data', 'productsData')->name('data');

                Route::prefix('{product}/reviews')->name('reviews.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/data', 'reviewsData')->name('data');
                    Route::get('/create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('/{review}/edit', 'edit')->name('edit');
                    Route::match(['put', 'patch'], '/{review}', 'update')->name('update');
                    Route::delete('/{review}', 'destroy')->name('destroy');
                });
            });

        Route::prefix('shoes')->name('admin.shoes.')->group(function () {
            Route::prefix('brands')->name('brands.')->controller(ShoeBrandController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{brand}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{brand}', 'update')->name('update');
                Route::delete('/{brand}', 'destroy')->name('destroy');
            });

            Route::prefix('types')->name('types.')->controller(ShoeTypeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{type}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{type}', 'update')->name('update');
                Route::delete('/{type}', 'destroy')->name('destroy');
            });

            Route::prefix('categories')->name('categories.')->controller(ShoeCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
            });

            Route::prefix('subcategories')->name('subcategories.')->controller(ShoeSubcategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{subcategory}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{subcategory}', 'update')->name('update');
                Route::delete('/{subcategory}', 'destroy')->name('destroy');
            });

            Route::prefix('size-types')->name('size-types.')->controller(ShoeSizeTypeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{sizeType}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{sizeType}', 'update')->name('update');
                Route::delete('/{sizeType}', 'destroy')->name('destroy');
            });

            Route::prefix('colors')->name('colors.')->controller(ShoeColorController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{color}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{color}', 'update')->name('update');
                Route::delete('/{color}', 'destroy')->name('destroy');
            });

            Route::prefix('materials')->name('materials.')->controller(ShoeMaterialController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{material}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{material}', 'update')->name('update');
                Route::delete('/{material}', 'destroy')->name('destroy');
            });

            Route::prefix('products')->name('products.')->controller(ShoeProductController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::get('/generate-sku', 'generateSku')->name('generate-sku');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{product}', 'update')->name('update');
                Route::patch('/{product}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::delete('/{product}', 'destroy')->name('destroy');
            });

            Route::prefix('product-reviews')
                ->name('product-reviews.')
                ->controller(ShoeProductReviewController::class)
                ->scopeBindings()
                ->group(function () {
                    Route::get('/', 'products')->name('index');
                    Route::get('/data', 'productsData')->name('data');

                    Route::prefix('{product}/reviews')->name('reviews.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/data', 'reviewsData')->name('data');
                        Route::get('/create', 'create')->name('create');
                        Route::post('/', 'store')->name('store');
                        Route::get('/{review}/edit', 'edit')->name('edit');
                        Route::match(['put', 'patch'], '/{review}', 'update')->name('update');
                        Route::delete('/{review}', 'destroy')->name('destroy');
                    });
                });
        });

        Route::prefix('cosmetics')->name('admin.cosmetics.')->group(function () {
            Route::prefix('brands')->name('brands.')->controller(CosmeticBrandController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/generate-slug', 'generateSlug')->name('generate-slug');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{brand}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{brand}', 'update')->name('update');
                Route::delete('/{brand}', 'destroy')->name('destroy');
            });

            Route::prefix('categories')->name('categories.')->controller(CosmeticCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/generate-slug', 'generateSlug')->name('generate-slug');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
            });

            Route::prefix('product-types')->name('product-types.')->controller(CosmeticProductTypeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{productType}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{productType}', 'update')->name('update');
                Route::delete('/{productType}', 'destroy')->name('destroy');
            });

            Route::prefix('sizes-volume')->name('sizes-volume.')->controller(CosmeticSizeVolumeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{sizeVolume}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{sizeVolume}', 'update')->name('update');
                Route::delete('/{sizeVolume}', 'destroy')->name('destroy');
            });

            Route::prefix('countries-origin')->name('countries-origin.')->controller(CosmeticCountryOfOriginController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{country}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{country}', 'update')->name('update');
                Route::delete('/{country}', 'destroy')->name('destroy');
            });

            Route::prefix('products')->name('products.')->controller(CosmeticProductController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{product}', 'update')->name('update');
                Route::patch('/{product}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::delete('/{product}', 'destroy')->name('destroy');
            });

            Route::prefix('product-reviews')
                ->name('product-reviews.')
                ->controller(CosmeticProductReviewController::class)
                ->scopeBindings()
                ->group(function () {
                    Route::get('/', 'products')->name('index');
                    Route::get('/data', 'productsData')->name('data');

                    Route::prefix('{product}/reviews')->name('reviews.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/data', 'reviewsData')->name('data');
                        Route::get('/create', 'create')->name('create');
                        Route::post('/', 'store')->name('store');
                        Route::get('/{review}/edit', 'edit')->name('edit');
                        Route::match(['put', 'patch'], '/{review}', 'update')->name('update');
                        Route::delete('/{review}', 'destroy')->name('destroy');
                    });
                });
        });

        Route::prefix('motorcycles')->name('admin.motorcycles.')->group(function () {
            Route::prefix('products')->name('products.')->controller(MotorcycleProductController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{product}', 'update')->name('update');
                Route::patch('/{product}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::delete('/{product}', 'destroy')->name('destroy');
            });

            Route::prefix('categories')->name('categories.')->controller(MotorcycleProductCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
            });

            Route::prefix('helmet-brands')->name('helmet-brands.')->controller(MotorcycleHelmetBrandController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{brand}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{brand}', 'update')->name('update');
                Route::delete('/{brand}', 'destroy')->name('destroy');
            });

            Route::prefix('bike-models')->name('bike-models.')->controller(MotorcycleBikeModelController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{bikeModel}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{bikeModel}', 'update')->name('update');
                Route::delete('/{bikeModel}', 'destroy')->name('destroy');
            });

            Route::prefix('options')->name('options.')->controller(MotorcycleProductOptionController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/list', 'options')->name('list');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{option}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{option}', 'update')->name('update');
                Route::delete('/{option}', 'destroy')->name('destroy');
            });

            Route::prefix('product-reviews')->name('product-reviews.')->controller(MotorcycleProductReviewController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{review}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{review}', 'update')->name('update');
                Route::delete('/{review}', 'destroy')->name('destroy');
            });
        });

        Route::prefix('home-needs')->name('admin.home-needs.')->group(function () {
            Route::prefix('products')->name('products.')->controller(HomeNeedProductController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{product}', 'update')->name('update');
                Route::patch('/{product}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::delete('/{product}', 'destroy')->name('destroy');
            });

            Route::prefix('brands')->name('brands.')->controller(HomeNeedBrandController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{brand}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{brand}', 'update')->name('update');
                Route::delete('/{brand}', 'destroy')->name('destroy');
            });

            Route::prefix('categories')->name('categories.')->controller(HomeNeedCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
            });
        });

        Route::prefix('fashion')->name('admin.fashion.')->group(function () {
            Route::prefix('products')->name('products.')->controller(FashionProductController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{product}', 'update')->name('update');
                Route::patch('/{product}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::delete('/{product}', 'destroy')->name('destroy');
            });

            Route::prefix('brands')->name('brands.')->controller(FashionBrandController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{brand}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{brand}', 'update')->name('update');
                Route::delete('/{brand}', 'destroy')->name('destroy');
            });

            Route::prefix('categories')->name('categories.')->controller(FashionCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
            });

            Route::prefix('product-types')->name('product-types.')->controller(FashionProductTypeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{productType}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{productType}', 'update')->name('update');
                Route::delete('/{productType}', 'destroy')->name('destroy');
            });
        });

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/data', [InvoiceController::class, 'data'])->name('invoices.data');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::post('/invoices/order-status', [InvoiceController::class, 'updateorderstatus'])->name('invoices.order-status');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

        Route::prefix('other-cms')->group(function () {
            Route::get('/homebanners', [HomeBannerController::class, 'index'])->name('homebanners.index');
            Route::get('/homebanners/create', [HomeBannerController::class, 'create'])->name('homebanners.create');
            Route::post('/homebanners', [HomeBannerController::class, 'store'])->name('homebanners.store');
            Route::get('/homebanners/{homeBanner}/edit', [HomeBannerController::class, 'edit'])->name('homebanners.edit');
            Route::put('/homebanners/{homeBanner}', [HomeBannerController::class, 'update'])->name('homebanners.update');
            Route::delete('/homebanners/{homeBanner}', [HomeBannerController::class, 'destroy'])->name('homebanners.destroy');
            Route::get('/homebanners/data', [HomeBannerController::class, 'data'])->name('homebanners.data');

            Route::get('/our-customers', [CustomerGalleryController::class, 'index'])->name('customer-gallery.index');
            Route::put('/our-customers', [CustomerGalleryController::class, 'update'])->name('customer-gallery.update');
        });
    });
});
