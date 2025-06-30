<?php

// web.php - Updated with Order Management Routes

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
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController; // NEW
use App\Http\Controllers\SessionExpiredController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                        'price' => (float) $product->price,
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

// 3-Step Checkout routes - accessible only to authenticated users
Route::middleware(['auth'])->group(function () {
    // Step 1: Delivery slot selection
    Route::get('/checkout/delivery', [CheckoutController::class, 'delivery'])->name('checkout.delivery');
    Route::get('/checkout', function() {
        return redirect()->route('checkout.delivery');
    })->name('checkout.index'); // Redirect old checkout to step 1
    
    // Step 2: Review order
    Route::get('/checkout/review', [CheckoutController::class, 'review'])->name('checkout.review');
    
    // Step 3: Confirm and place order
    Route::get('/checkout/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
    
    // Order success page - NEW
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    
    // API endpoints for checkout functionality
    Route::post('/checkout/select-slot', [CheckoutController::class, 'selectDeliverySlot'])->name('checkout.select-slot');
    Route::post('/checkout/store-selected-slot', [CheckoutController::class, 'storeSelectedSlot'])->name('checkout.store-slot');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    
    // Cart and session API endpoints
    Route::get('/api/checkout/cart-data', [CheckoutController::class, 'getCartData'])->name('checkout.cart-data');
    Route::get('/api/session-check', [CheckoutController::class, 'checkSession'])->name('session.check');

    // Address Management API endpoints
    Route::post('/api/user/address', [ProfileController::class, 'storeAddress'])->name('api.address.store');
    Route::put('/api/user/address', [ProfileController::class, 'updateAddress'])->name('api.address.update');
    Route::get('/api/user/address', [ProfileController::class, 'getAddress'])->name('api.address.show');
    
    // ORDER MANAGEMENT ROUTES - NEW
    Route::prefix('orders')->name('orders.')->group(function () {
        // Order history and listing
        Route::get('/', [OrderController::class, 'index'])->name('index');
        
        // View specific order
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        
        // Track order
        Route::get('/{order}/track', [OrderController::class, 'track'])->name('track');
        
        // Cancel order
        Route::patch('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        
        // Send confirmation email
        Route::post('/{order}/send-confirmation', [OrderController::class, 'sendConfirmation'])->name('send-confirmation');
    });
});

// Session expired routes
Route::get('/session-expired', [SessionExpiredController::class, 'show'])->name('session.expired');
Route::post('/session-expired', [SessionExpiredController::class, 'handle'])->name('session.expiry.handle');

// Additional session management routes (accessible to all)
Route::get('/api/session-check', function(Request $request) {
    return response()->json([
        'authenticated' => Auth::check(),
        'expires_at' => session()->has('login.web') ? session('login.web') : null,
        'time_remaining' => Auth::check() ? (config('session.lifetime') * 60) - (time() - session()->get('_token_time', time())) : 0
    ]);
})->name('api.session.check');

Route::post('/refresh-session', function(Request $request) {
    if (Auth::check()) {
        $request->session()->regenerate();
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false], 401);
})->middleware(['auth'])->name('session.refresh');
 
// Cart routes - accessible to both guests and authenticated users
// These routes return JSON responses for API calls
Route::prefix('cart')->name('cart.')->group(function () {
    // For displaying cart page (returns Inertia view)
    Route::get('/', function() {
        return Inertia::render('CartPage');
    })->name('index');
    
    // API endpoints (return JSON)
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/{product}', [CartController::class, 'update'])->name('update');
    Route::delete('/{product}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

// Additional cart API route for getting cart data (returns JSON)
Route::get('/cart', [CartController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Search routes
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

require __DIR__.'/auth.php';