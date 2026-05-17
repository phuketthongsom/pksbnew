<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\PostsController;
use App\Http\Controllers\Admin\TimetablesController;
use App\Http\Controllers\Admin\PassesController;
use App\Http\Controllers\Admin\UsersController;

/*
 * Pream's note for the team:
 * Locale lives in the URL path for SEO — /th/blog, /zh/about, /ru/contact.
 * English stays at the bare root (no /en/ prefix), so /about is the canonical
 * English page and /th/about is the Thai variant of the same content.
 *
 * Why two registrations?
 *   - Laravel route names must be unique. We give the bare-root routes the
 *     names (home, about, blog, …) so route('home') and lurl('home') work.
 *   - The locale-prefixed group reuses the same closures but is unnamed; it
 *     exists only to make /th/blog, /zh/about etc. resolve. The SetLocale
 *     middleware reads the {locale} param off the matched route and pins
 *     App::getLocale() before the view renders.
 *   - lurl() (see app/helpers.php) wraps route() to inject the active locale
 *     prefix when generating URLs, so internal links keep the user's locale.
 */
$publicRoutes = function (): void {
    Route::view('/', 'pages.home')->name('home');
    Route::view('/about', 'pages.about')->name('about');
    Route::view('/contact', 'pages.contact')->name('contact');
    Route::view('/timetable', 'pages.timetable')->name('timetable');
    Route::view('/tracking', 'pages.tracking')->name('tracking');
    Route::view('/payment', 'pages.payment')->name('payment');
    Route::view('/pass', 'pages.pass')->name('pass');

    Route::get('/blog', [BlogController::class, 'index'])->name('blog');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
};

// English at root (carries the route names)
$publicRoutes();

// Locale-prefixed mirror (unnamed) for /th/*, /zh/*, /ru/*
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'th|zh|ru'],
    'as' => 'i18n.',
], $publicRoutes);

// Sitemap with hreflang alternates for every page × every locale
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

/* ----------------------------- Admin ----------------------------- */

Route::prefix('admin')->name('admin.')->group(function () {

    // Login / logout
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:admin-login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Authenticated admin routes
    Route::middleware('admin')->group(function () {

        Route::get('/', fn () => redirect()->route('admin.posts.index'))->name('home');

        // Posts (Destinations)
        Route::get('/posts', [PostsController::class, 'index'])->name('posts.index');
        Route::get('/posts/create', [PostsController::class, 'create'])->name('posts.create')->middleware('admin.can:posts.manage');
        Route::post('/posts', [PostsController::class, 'store'])->name('posts.store')->middleware('admin.can:posts.manage');
        Route::get('/posts/{slug}/edit', [PostsController::class, 'edit'])->name('posts.edit');
        Route::match(['put', 'post'], '/posts/{slug}', [PostsController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{slug}', [PostsController::class, 'destroy'])->name('posts.destroy')->middleware('admin.can:posts.manage');
        Route::delete('/posts/{slug}/photos', [PostsController::class, 'destroyPhoto'])->name('posts.photos.destroy')->middleware('admin.can:posts.manage');
        Route::post('/posts/{slug}/cover', [PostsController::class, 'setCover'])->name('posts.cover')->middleware('admin.can:posts.manage');

        // Timetables (per-route, multi-image with per-image captions)
        Route::middleware('admin.can:timetables.manage')->group(function () {
            Route::get('/timetables', [TimetablesController::class, 'index'])->name('timetables.index');
            Route::post('/timetables/{key}/images', [TimetablesController::class, 'storeImages'])->name('timetables.images.store');
            Route::post('/timetables/{key}/images/{imageId}/caption', [TimetablesController::class, 'updateCaption'])->name('timetables.images.caption');
            Route::post('/timetables/{key}/images/{imageId}/reorder', [TimetablesController::class, 'reorderImage'])->name('timetables.images.reorder');
            Route::delete('/timetables/{key}/images/{imageId}', [TimetablesController::class, 'destroyImage'])->name('timetables.images.destroy');
        });

        // Passes (day-pass catalog)
        Route::middleware('admin.can:passes.manage')->group(function () {
            Route::get('/passes', [PassesController::class, 'index'])->name('passes.index');
            Route::post('/passes', [PassesController::class, 'store'])->name('passes.store');
            Route::match(['put', 'post'], '/passes/{id}', [PassesController::class, 'update'])->name('passes.update');
            Route::post('/passes/{id}/reorder', [PassesController::class, 'reorder'])->name('passes.reorder');
            Route::delete('/passes/{id}', [PassesController::class, 'destroy'])->name('passes.destroy');
        });

        // Users (owner-only)
        Route::middleware('admin.can:users.manage')->group(function () {
            Route::get('/users', [UsersController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
            Route::post('/users', [UsersController::class, 'store'])->name('users.store');
            Route::get('/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
            Route::match(['put', 'post'], '/users/{id}', [UsersController::class, 'update'])->name('users.update');
            Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
        });
    });
});
