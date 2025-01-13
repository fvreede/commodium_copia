<?php

use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('HomePage');
});

Route::get('/categories', function () {
    return Inertia::render('CategoryPage');
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

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Add more admin routes here
    Route::resource('users', UsersController::class)->except(['show', 'create', 'edit']);
    Route::patch('users/{user}/update-role', [UsersController::class, 'updateRole'])->name('users.update-role');
    Route::patch('users/{user}/toggle-status', [UsersController::class, 'toggleStatus'])->name('users.toggle-status');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
