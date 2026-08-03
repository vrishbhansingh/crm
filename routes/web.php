<?php

use App\Http\Controllers\Admin\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Website\WebController;
use Illuminate\Support\Facades\Http;
use App\Models\Partner;
use App\Models\SupportService;
use App\Models\Banner;
use App\Models\Contact;
use Auth as at;

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

// Route::get('/about-us', [WebController::class, 'aboutUs'])->name('website.about_us');
// Route::get('/contact-us', [WebController::class, 'contactUs'])->name('website.contact_us');
// Route::get('/services', [WebController::class, 'services'])->name('website.services');
// Route::get('/blog', [WebController::class, 'OurBlog'])->name('website.our_blog');
// Route::get('/privacy-policy', [WebController::class, 'privacy_policy'])->name('website.privacy_policy');
// Route::get('/term-and-condition', [WebController::class, 'term_and_condition'])->name('website.term_and_condition');

// Route::get('/image-gallery', [WebController::class, 'imageGallery'])->name('website.image_gallery');
// Route::get('/video-gallery', [WebController::class, 'videoGallery'])->name('website.video_gallery');