<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
//Route::get('/callback', function (Request $request){
//    return
//});

Route::get('/auth/callback', function (Request $request) {
    $state = $request->session()->pull('state');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class,
        'Invalid state value.'
    );

    $response = Http::asForm()->post('http://localhost:8080/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => '9b29833f-1ef0-43b2-8ecd-ba5d4b204e00',
        'client_secret' => '9uITfUONHLTh1bidPBusjKlrJcgufp1brR5N9znC',
        'redirect_uri' => 'http://127.0.0.1:8000/auth/callback',
        'code' => $request->code,
    ]);

    return $response->json();
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/redirect', function (Request $request) {
    $request->session()->put('state', $state = Str::random(40));

    $query = http_build_query([
        'client_id' => '9b29833f-1ef0-43b2-8ecd-ba5d4b204e00',
        'redirect_uri' => 'http://127.0.0.1:8000/auth/callback',
        'response_type' => 'code',
        'scope' => '',
        'state' => $state,
         'prompt' => 'login', // "none", "consent", or "login"
    ]);

    return redirect('http://localhost:8080/oauth/authorize?'.$query);
});
