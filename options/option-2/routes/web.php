<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CapturedPageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PropertyController::class, 'home'])->name('home');
Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property}', [PropertyController::class, 'show'])->whereNumber('property')->name('properties.show');
Route::get('/media/photos/{photo}', [MediaController::class, 'photo'])->name('media.photo');
Route::get('/media/presentation/{property}/{kind}/{index?}', [MediaController::class, 'presentation'])->where('kind', 'tour|floor')->whereNumber('index')->name('media.presentation');
Route::get('/media/avatars/{user}', [MediaController::class, 'avatar'])->name('media.avatar');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::post('/newsletter', [ContactController::class, 'subscribe'])->middleware('throttle:5,1')->name('newsletter.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/forgot-password', [AuthController::class, 'forgotForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/unread', [DashboardController::class, 'unread'])->name('dashboard.unread');
    Route::get('/dashboard/properties', [ListingController::class, 'index'])->name('listings.index');
    Route::get('/dashboard/properties/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/dashboard/properties', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/dashboard/properties/{property}/edit', [ListingController::class, 'edit'])->name('listings.edit');
    Route::put('/dashboard/properties/{property}', [ListingController::class, 'update'])->name('listings.update');
    Route::delete('/dashboard/properties/{property}', [ListingController::class, 'destroy'])->name('listings.destroy');
    Route::post('/dashboard/properties/{id}/restore', [ListingController::class, 'restore'])->name('listings.restore');
    Route::get('/dashboard/properties/{property}/preview', [PropertyController::class, 'preview'])->name('listings.preview');
    Route::patch('/dashboard/properties/{property}/moderation', [ListingController::class, 'moderate'])->name('listings.moderate');
    Route::get('/dashboard/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/properties/{property}/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/properties/{property}/favorites', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::post('/properties/{property}/inquiries', [ConversationController::class, 'store'])->middleware('throttle:15,1')->name('inquiries.store');
    Route::get('/dashboard/messages', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/dashboard/messages/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/dashboard/messages/{conversation}', [ConversationController::class, 'reply'])->middleware('throttle:30,1')->name('conversations.reply');
    Route::get('/dashboard/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/properties/{property}/reviews', [ReviewController::class, 'store'])->middleware('throttle:10,1')->name('reviews.store');
    Route::delete('/dashboard/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::patch('/dashboard/reviews/{review}', [ReviewController::class, 'moderate'])->name('reviews.moderate');
    Route::get('/dashboard/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/dashboard/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/dashboard/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/dashboard/users', [AdminController::class, 'users'])->name('admin.users');
    Route::patch('/dashboard/users/{user}', [AdminController::class, 'role'])->name('admin.role');
    Route::get('/dashboard/inbox', [AdminController::class, 'inbox'])->name('admin.inbox');
    Route::patch('/dashboard/inbox/{contact}', [AdminController::class, 'handled'])->name('admin.handled');
});
foreach ([
    'index.html' => '/', 'index-2.html' => '/', 'index-3.html' => '/',
    'signin.html' => '/login', 'signup.html' => '/register', 'forgot-password.html' => '/forgot-password',
    'add-property-buy.html' => '/dashboard/properties/create', 'add-property-rent.html' => '/dashboard/properties/create?purpose=rent',
    'wishlist.html' => '/dashboard/favorites', 'notifications.html' => '/dashboard/messages',
    'contact-us.html' => '/contact', 'dashboard.html' => '/dashboard',
] as $old => $new) {
    Route::redirect('/'.$old, $new);
}
foreach (['buy' => 'sale', 'rent' => 'rent'] as $prefix => $purpose) {
    foreach (['property-grid', 'property-list', 'property-grid-sidebar', 'property-list-sidebar', 'grid-map', 'list-map'] as $variant) {
        Route::redirect('/'.$prefix.'-'.$variant.'.html', '/properties?purpose='.$purpose.'&view='.(str_contains($variant, 'list') ? 'list' : 'grid'));
    }
    Route::redirect('/'.$prefix.'-details.html', '/properties?purpose='.$purpose);
}
foreach (['cart', 'checkout', 'rental-booking', 'rental-order-confirmation', 'rental-order-details', 'invoice-details'] as $inactive) {
    Route::redirect('/'.$inactive.'.html', '/contact');
}
Route::get('/property-details-v1/{id}.html', [PropertyController::class, 'legacy']);
Route::get('/{page}', [CapturedPageController::class, 'show'])->where('page', '.*')->name('captured-page.show');
