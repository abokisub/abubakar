<?php

use App\Http\Controllers\API\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Specific routes first (before catch-all)
Route::any('vdf_auto_fund_adex', [PaymentController::class, 'VDFWEBHOOK']);

Route::get('/cache', function () {
    return Cache::flush();
});

// Serve service-worker.js with correct MIME type
Route::get('/service-worker.js', function () {
    $swPath = public_path('service-worker.js');
    if (file_exists($swPath)) {
        return response()->file($swPath, ['Content-Type' => 'application/javascript']);
    }
    abort(404);
});

// Serve manifest.json
Route::get('/manifest.json', function () {
    $manifestPath = public_path('manifest.json');
    if (file_exists($manifestPath)) {
        return response()->file($manifestPath, ['Content-Type' => 'application/json']);
    }
    abort(404);
});

// Serve React SPA - all non-API routes should serve index.html
Route::get('/', function () {
    $indexPath = public_path('index.html');
    if (file_exists($indexPath)) {
        return response()->file($indexPath);
    }
    // Fallback to old view if React build not found
    $general = DB::table('general')->first();
    $mtn = DB::table('data_plan')
        ->where(['network' => 'MTN', 'plan_status' => 1])
        ->orderBy('id', 'asc')
        ->get();
    $glo = DB::table('data_plan')
        ->where(['network' => 'GLO', 'plan_status' => 1])
        ->get();
    $mobile = DB::table('data_plan')
        ->where(['network' => '9MOBILE', 'plan_status' => 1])
        ->get();
    $airtel = DB::table('data_plan')
        ->where(['network' => 'AIRTEL', 'plan_status' => 1])
        ->get();
    return view('index', [
        'general' => $general,
        'mtn' => $mtn,
        'airtel' => $airtel,
        'glo' => $glo,
        'mobile' => $mobile
    ]);
});

// Catch-all route for React Router - MUST be last
Route::get('/{any}', function ($any) {
    // Skip API routes and static files (these are handled by .htaccess and api.php)
    if (strpos($any, 'api/') === 0 || strpos($any, 'storage/') === 0 || strpos($any, 'static/') === 0) {
        abort(404);
    }
    
    $indexPath = public_path('index.html');
    if (file_exists($indexPath)) {
        return response()->file($indexPath);
    }
    abort(404);
})->where('any', '.*');
