<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',[AuthController::class, 'login']);
Route::get('/register', [RegistrationController::class, 'create'])->name('register');
Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
Route::get('/registration-submitted', [RegistrationController::class, 'success'])->name('register.success');

Route::middleware('throttle:5,1')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'update'])->name('password.update');
});

// Route::get('/about-us', [WebController::class, 'aboutUs'])->name('website.about_us');
// Route::get('/contact-us', [WebController::class, 'contactUs'])->name('website.contact_us');
// Route::get('/services', [WebController::class, 'services'])->name('website.services');
// Route::get('/blog', [WebController::class, 'OurBlog'])->name('website.our_blog');
// Route::get('/privacy-policy', [WebController::class, 'privacy_policy'])->name('website.privacy_policy');
// Route::get('/term-and-condition', [WebController::class, 'term_and_condition'])->name('website.term_and_condition');

// Route::get('/image-gallery', [WebController::class, 'imageGallery'])->name('website.image_gallery');
// Route::get('/video-gallery', [WebController::class, 'videoGallery'])->name('website.video_gallery');
