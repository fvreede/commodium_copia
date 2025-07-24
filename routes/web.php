<?php

/**
 * Bestandsnaam: web.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.26
 * Datum: 2025-07-24
 * Tijd: 21:40
 * Doel: Hoofdroute definitie bestand voor de webshop applicatie. Bevat alle web routes voor publieke content, admin/editor dashboards, e-commerce functionaliteit, checkout proces, order management en API endpoints.
 */

// web.php - Bijgewerkt met Order Management Routes
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
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SessionExpiredController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ================================================================================
 * PUBLIEKE ROUTES - Toegankelijk voor alle bezoekers (gasten en ingelogde gebruikers)
 * ================================================================================
 */

/**
 * HOMEPAGE ROUTE
 * Toont featured producten en categorieën op de startpagina
 */
Route::get('/', function () {
    // Haal specifieke featured producten op voor homepage weergave
    $products = Product::with(['subcategory.category'])
        ->whereIn('name', ['Biologische pompoen', 'Espresso Brownies', 'Red Velvet Muffins'])
        ->get()
        ->map(function ($product) {
            // Transformeer product data voor frontend weergave
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

/**
 * CATEGORIEËN OVERZICHT ROUTE
 * Toont alle hoofdcategorieën voor product browsing
 */
Route::get('/categories', function () {
    $categories = Category::all();
    
    return Inertia::render('CategoryPage', [
        'categories' => $categories
    ]);
})->name('AllCategories');

/**
 * SUBCATEGORIEËN BINNEN CATEGORIE ROUTE
 * Toont alle subcategorieën en producten binnen een specifieke hoofdcategorie
 */
Route::get('/subcategories/{categoryId}', function (int $categoryId) {
    // Haal categorie op met alle subcategorieën en bijbehorende producten
    /** @var \App\Models\Category $category */
    $category = Category::with('subcategories.products')->findOrFail($categoryId);
    
    return Inertia::render('SubcategoryPage', [
        'categoryId' => (string) $categoryId,
        'categoryName' => $category->name,
        'bannerSrc' => $category->banner_path ?? 'default-banner.jpg',
        'subcategories' => $category->subcategories->map(function ($subcategory) {
            /** @var \App\Models\Subcategory $subcategory */
            return [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'products' => $subcategory->products->map(function ($product) {
                    /** @var \App\Models\Product $product */
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

/**
 * PRODUCT DETAIL ROUTE
 * Toont gedetailleerde productpagina met volledige informatie
 */
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

/**
 * ================================================================================
 * ADMIN ROUTES - Alleen toegankelijk voor gebruikers met admin rol
 * ================================================================================
 */
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Admin dashboard - statistieken en overzichten
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    /**
     * GEBRUIKERSBEHEER ROUTES
     * CRUD operaties voor gebruikersaccounts en rol management
     */
    Route::resource('users', UsersController::class)->except(['show', 'create', 'edit']);
    Route::patch('users/{user}/update-role', [UsersController::class, 'updateRole'])->name('users.update-role');
    Route::patch('users/{user}/toggle-status', [UsersController::class, 'toggleStatus'])->name('users.toggle-status');
    
    /**
     * CATEGORIESTRUCTUUR BEHEER ROUTES
     * Admin niveau category en subcategory management
     */
    Route::resource('categories', CategoryStructureController::class);
    Route::resource('subcategories', SubcategoryStructureController::class);
    
    /**
     * API ENDPOINTS VOOR ADMIN FUNCTIONALITEIT
     */
    // Zoek gebruikers API endpoint voor Admin dashboard
    Route::get('/api/users/search', [UsersController::class, 'search'])
        ->name('api.users.search')
        ->middleware('auth');
    
    /**
     * ADMIN INSTELLINGEN ROUTES
     */
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});

/**
 * ================================================================================
 * EDITOR ROUTES - Alleen toegankelijk voor gebruikers met editor rol
 * ================================================================================
 */
Route::middleware(['auth', 'role:editor'])->prefix('editor')->name('editor.')->group(function () {
    
    // Editor dashboard - content management overzicht
    Route::get('/', [EditorController::class, 'dashboard'])->name('dashboard');
    
    /**
     * CONTENT MANAGEMENT ROUTES
     * CRUD operaties voor alle webshop content
     */
    Route::resource('categories', CategoryController::class);      // Categorieën beheer
    Route::resource('subcategories', SubcategoryController::class); // Subcategorieën beheer
    Route::resource('products', ProductController::class);         // Producten beheer
    Route::resource('promotions', PromotionController::class);     // Promoties beheer
    Route::resource('news', NewsController::class);               // Nieuws artikelen beheer
    Route::resource('banners', BannerController::class)->only(['index', 'edit', 'update']); // Banner beheer (beperkt)
    
    /**
     * XML API ENDPOINTS VOOR EXTERNE INTEGRATIES
     * Export functionaliteit voor externe systemen
     */
    Route::prefix('api')->group(function() {
        Route::get('products.xml', [EditorController::class, 'productsXml'])->name('api.products');
        Route::get('categories.xml', [EditorController::class, 'categoriesXml'])->name('api.categories');
        Route::get('promotions.xml', [EditorController::class, 'promotionsXml'])->name('api.promotions');
    });
    
    /**
     * EDITOR INSTELLINGEN ROUTES
     */
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});

/**
 * ================================================================================
 * DASHBOARD ROUTE - Rol-gebaseerde dashboard redirect
 * ================================================================================
 */
Route::get('/dashboard', function () {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    
    // Controleer of gebruiker ingelogd is
    if (!$user) {
        return redirect()->route('login');
    }
    
    // Redirect naar juiste dashboard op basis van gebruikersrol
    if ($user->isSystemAdmin()) {
        return redirect()->route('admin.dashboard');
    } else if ($user->isEditor()) {
        return redirect()->route('editor.dashboard');
    }
    // Voor klanten: toon standaard dashboard met order data
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

/**
 * ================================================================================
 * CHECKOUT ROUTES - 3-staps checkout proces voor geauthenticeerde gebruikers
 * ================================================================================
 */
Route::middleware(['auth'])->group(function () {
    
    /**
     * CHECKOUT STAPPEN - Gestructureerd checkout proces
     */
    // Stap 1: Bezorgslot selectie
    Route::get('/checkout/delivery', [CheckoutController::class, 'delivery'])->name('checkout.delivery');
    
    // Legacy checkout redirect naar stap 1
    Route::get('/checkout', function() {
        return redirect()->route('checkout.delivery');
    })->name('checkout.index');
    
    // Stap 2: Bestelling overzicht en controle
    Route::get('/checkout/review', [CheckoutController::class, 'review'])->name('checkout.review');
    
    // Stap 3: Finale bevestiging en bestelling plaatsen
    Route::get('/checkout/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
    
    // Bestelling succes pagina - NIEUW
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    
    /**
     * CHECKOUT API ENDPOINTS
     * AJAX endpoints voor checkout functionaliteit
     */
    Route::post('/checkout/select-slot', [CheckoutController::class, 'selectDeliverySlot'])->name('checkout.select-slot');
    Route::post('/checkout/store-selected-slot', [CheckoutController::class, 'storeSelectedSlot'])->name('checkout.store-slot');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    
    /**
     * WINKELWAGEN EN SESSIE API ENDPOINTS
     */
    Route::get('/api/checkout/cart-data', [CheckoutController::class, 'getCartData'])->name('checkout.cart-data');
    Route::get('/api/session-check', [CheckoutController::class, 'checkSession'])->name('session.check');
    
    /**
     * ADRESBEHEER API ENDPOINTS
     * CRUD operaties voor gebruiker bezorgadressen
     */
    Route::post('/api/user/address', [ProfileController::class, 'storeAddress'])->name('api.address.store');
    Route::put('/api/user/address', [ProfileController::class, 'updateAddress'])->name('api.address.update');
    Route::get('/api/user/address', [ProfileController::class, 'getAddress'])->name('api.address.show');
    Route::delete('/api/user/address', [ProfileController::class, 'deleteAddress'])->name('api.address.delete');
    
    /**
     * ================================================================================
     * ORDER MANAGEMENT ROUTES - NIEUW - Bestelling beheer voor klanten
     * ================================================================================
     */
    Route::prefix('orders')->name('orders.')->group(function () {
        // Bestelling geschiedenis en overzicht
        Route::get('/', [OrderController::class, 'index'])->name('index');
        
        // Specifieke bestelling bekijken
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        
        // Bestelling tracking/volgen
        Route::get('/{order}/track', [OrderController::class, 'track'])->name('track');
        
        // Bestelling annuleren
        Route::patch('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        
        // Bevestigingsmail opnieuw versturen
        Route::post('/{order}/send-confirmation', [OrderController::class, 'sendConfirmation'])->name('send-confirmation');
    });
});

/**
 * ================================================================================
 * SESSIE MANAGEMENT ROUTES - Sessie verloop afhandeling
 * ================================================================================
 */

/**
 * SESSIE VERLOPEN ROUTES
 * Afhandeling van verlopen sessies en gebruiker opties
 */
Route::get('/session-expired', [SessionExpiredController::class, 'show'])->name('session.expired');
Route::post('/session-expired', [SessionExpiredController::class, 'handle'])->name('session.expiry.handle');

/**
 * AANVULLENDE SESSIE MANAGEMENT ROUTES
 * API endpoints voor sessie status en vernieuwing (toegankelijk voor iedereen)
 */
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

/**
 * ================================================================================
 * WINKELWAGEN ROUTES - Toegankelijk voor zowel gasten als geauthenticeerde gebruikers
 * ================================================================================
 */
Route::prefix('cart')->name('cart.')->group(function () {
    
    // Winkelwagen pagina weergave (Inertia view)
    Route::get('/', function() {
        return Inertia::render('CartPage');
    })->name('index');
    
    /**
     * WINKELWAGEN API ENDPOINTS (retourneren JSON responses)
     */
    Route::post('/add', [CartController::class, 'add'])->name('add');                    // Product toevoegen
    Route::patch('/{product}', [CartController::class, 'update'])->name('update');       // Hoeveelheid bijwerken
    Route::delete('/{product}', [CartController::class, 'remove'])->name('remove');      // Product verwijderen
    Route::delete('/', [CartController::class, 'clear'])->name('clear');                 // Winkelwagen legen
});

/**
 * AANVULLENDE WINKELWAGEN API ROUTE
 * Voor het ophalen van winkelwagen data (retourneert JSON)
 */
Route::get('/cart', [CartController::class, 'index']);

/**
 * ================================================================================
 * PROFIEL MANAGEMENT ROUTES - Voor geauthenticeerde gebruikers
 * ================================================================================
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * ================================================================================
 * ZOEK ROUTES - Product zoeken en suggesties
 * ================================================================================
 */
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

/**
 * ================================================================================
 * AUTHENTICATIE ROUTES IMPORT
 * ================================================================================
 */
require __DIR__.'/auth.php';

/**
 * ROUTE ORGANISATIE UITLEG:
 * 
 * Dit bestand is georganiseerd in logische secties:
 * 1. Publieke routes (homepage, categorieën, producten)
 * 2. Admin routes (gebruikersbeheer, systeem configuratie)
 * 3. Editor routes (content management, XML exports)
 * 4. Dashboard route (rol-gebaseerde redirect)
 * 5. Checkout routes (3-staps proces + API endpoints)
 * 6. Order management routes (klant order tracking)
 * 7. Sessie management (verloop afhandeling)
 * 8. Winkelwagen routes (gasten + geauthenticeerd)
 * 9. Profiel management (account instellingen)
 * 10. Zoek functionaliteit
 * 
 * MIDDLEWARE STRATEGIE:
 * - 'auth': Vereist ingelogde gebruiker
 * - 'role:admin/editor': Spatie Permission rol controle
 * - 'verified': Email verificatie vereist
 * - Geen middleware: Publiek toegankelijk
 * 
 * API vs WEB ROUTES:
 * - /api/ prefix routes retourneren JSON voor AJAX calls
 * - Normale routes retourneren Inertia.js views
 * - Duidelijke scheiding voor frontend/backend communicatie
 */