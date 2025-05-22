<?php

// Web.php

use App\Models\Category;
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

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('HomePage');
});

Route::get('/categories', function () {
    $categories = Category::all();
    return Inertia::render('CategoryPage', [
        'categories' => $categories
    ]);
})->name('AllCategories');

Route::get('/subcategories/{categoryId}', function ($categoryId) {
    // TEMPORARY: Use MockData for test, after test use database or CMS
    $mockDataPath = resource_path('js/Data/mockData.json');

    if (!file_exists($mockDataPath)) {
        abort(500, 'Mock data file not found');
    }

    $mockData = json_decode(file_get_contents($mockDataPath), true);

    $category = collect($mockData)->firstWhere('id', (int) $categoryId);

    if(!$category) {
        abort(404, 'Category not found');
    }

    return Inertia::render('SubcategoryPage', [
        'categoryId' => (string) $categoryId,
        'categoryName' => $category['name'],
        'bannerSrc' => $category['bannerSrc'],
        'subcategories' => $category['subcategories'] ?? [],
    ]);
})->name('subcategories.show');

Route::get('/product/{id}/{subcategoryName}/{categoryId}', function ($id, $subcategoryName, $categoryId) {
    $mockData = json_decode(file_get_contents(base_path('resources/js/Data/mockData.json')), true);
    
    $category = collect($mockData)->firstWhere('id', (int) $categoryId);
    
    if (!$category) {
        abort(404, 'Category not found');
    }
    
    $subcategory = collect($category['subcategories'])->firstWhere('name', $subcategoryName);
    
    if (!$subcategory) {
        abort(404, 'Subcategory not found');
    }
    
    $product = collect($subcategory['products'])->firstWhere('id', (int) $id);
    
    if (!$product) {
        abort(404, 'Product not found');
    }
    
    return Inertia::render('ProductPage', [
        'id' => (string) $id,
        'product' => $product,
        'bannerSrc' => $category['bannerSrc'],
        'categoryName' => $category['name'],
        'subcategoryName' => $subcategory['name'],
        'categoryId' => (string) $categoryId
    ]);
})->name('product.show');

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
    if (auth()->user()->isSystemAdmin()) {
        return redirect()->route('admin.dashboard');
    } else if (auth()->user()->isEditor()) {
        return redirect()->route('editor.dashboard');
    }

    // For customers, use the default dashboard with order data
    $user = auth()->user();
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
    
})->middleware(['auth', 'verified'])->name('dashboard');

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

require __DIR__.'/auth.php';
