<?php

// Web.php - Fixed subcategories route

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\CategoryStructureController;
use App\Http\Controllers\Admin\SubcategoryStructureController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Editor\CategoryController;
use App\Http\Controllers\Editor\SubcategoryController;
use App\Http\Controllers\Editor\ProductController;
use App\Http\Controllers\Editor\PromotionController;
use App\Http\Controllers\Editor\NewsController;
use App\Http\Controllers\Editor\BannerController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\SearchController;

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $products = Product::with(['subcategory.category'])
        ->whereIn('name', ['Biologische pompoen', 'Espresso Brownies', 'Red Velvet Muffins'])
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'short_description' => $product->short_description,
                'full_description' => $product->full_description,
                'price' => (float) $product->price,
                'image_path' => $product->image_path,
                'subcategory' => [
                    'id' => $product->subcategory->id,
                    'name' => $product->subcategory->name,
                    'category' => [
                        'id' => $product->subcategory->category->id,
                        'name' => $product->subcategory->category->name,
                        'banner_image' => $product->subcategory->category->banner_image ?? 'default-banner.jpg'
                    ]
                ]
            ];
        });

    return Inertia::render('HomePage', [
        'products' => $products,
        'subcategories' => Subcategory::with('category')->get()
    ]);
});

Route::get('/categories', function () {
    $categories = Category::all();
    return Inertia::render('CategoryPage', [
        'categories' => $categories
    ]);
})->name('AllCategories');

Route::get('/subcategories/{categoryId}', function ($categoryId) {
    $category = Category::with('subcategories.products')->findOrFail($categoryId);
    
    return Inertia::render('SubcategoryPage', [
        'categoryId' => (string) $categoryId,
        'categoryName' => $category->name,
        'bannerSrc' => $category->banner_path ?? 'default-banner.jpg',
        'subcategories' => $category->subcategories->map(function ($subcategory) {
            return [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'products' => $subcategory->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->short_description,
                        'price' => (float) $product->price, // Ensure it's a float
                        'imageSrc' => $product->image_path,
                    ];
                })
            ];
        })
    ]);
})->name('subcategories.show');

Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User management routes
    Route::resource('users', UsersController::class)->except(['show', 'create', 'edit']);
    Route::patch('users/{user}/update-role', [UsersController::class, 'updateRole'])->name('users.update-role');
    Route::patch('users/{user}/toggle-status', [UsersController::class, 'toggleStatus'])->name('users.toggle-status');

    // Category structure management
    Route::resource('categories', CategoryStructureController::class);
    Route::resource('subcategories', SubcategoryStructureController::class);

    // Search users API endpoint for Admin dashboard
    Route::get('/api/users/search', [UsersController::class, 'search'])->name('api.users.search')->middleware('auth');

    // Admin settings routes
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});

// Editor routes
Route::middleware(['auth', 'role:editor'])->prefix('editor')->name('editor.')->group(function () {
    Route::get('/', [EditorController::class, 'dashboard'])->name('dashboard');

    // Add more editor routes here
    // Categories
    Route::resource('categories', CategoryController::class);
    Route::resource('subcategories', SubcategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('promotions', PromotionController::class);
    Route::resource('news', NewsController::class);
    Route::resource('banners', BannerController::class)->only(['index', 'edit', 'update' ]);

    // XML API endpoints
    Route::prefix('api')->group(function() {
        Route::get('products.xml', [EditorController::class, 'productsXml'])->name('api.products');
        Route::get('categories.xml', [EditorController::class, 'categoriesXml'])->name('api.categories');
        Route::get('promotions.xml', [EditorController::class, 'promotionsXml'])->name('api.promotions');
    });

    // Editor settings routes
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->isSystemAdmin()) {
        return redirect()->route('admin.dashboard');
    } else if ($user->isEditor()) {
        return redirect()->route('editor.dashboard');
    } 
    // For customers, use the default dashboard with order data
    else if ($user->isCustomer()) {
        return Inertia::render('Dashboard', [
            'activeOrders' => $user->orders()
                ->with(['items.product', 'deliverySlot'])
                ->whereIn('status', ['pending', 'processing'])
                ->latest()
                ->get(),
            'orderHistory' => $user->orders()
                ->with(['items.product', 'deliverySlot'])
                ->where('status', 'completed')
                ->latest()
                ->paginate(10)
        ]);
    }
    abort(403, 'Je hebt geen toegang tot dit dashboard.');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/checkout', function () {
    $user = auth()->user();

    /*
    if (!$user->isCustomer()) {
        abort(403, 'Je hebt geen toegang tot deze pagina.');
    }
    */

    return Inertia::render('Checkout');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::post('/add', [CartController::class, 'add'])->name('cart.add');
        Route::delete('/{product}', [CartController::class, 'remove'])->name('cart.remove');
        Route::patch('/{product}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/clear', [CartController::class, 'clear'])->name('cart.clear');
    });
});

// Search routes
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');


require __DIR__.'/auth.php';