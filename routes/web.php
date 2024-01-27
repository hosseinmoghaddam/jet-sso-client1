<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
Route::get('/', function (Request $request){
    return view('welcome');
});

Route::get('/auth/callback', function (Request $request) {
    $state = $request->session()->pull('state');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class,
        'Invalid state value.'
    );
    $response = Http::asForm()->post('http://localhost:8000/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => '9b2abe4a-17c2-471d-9e42-0a90065c849e',
        'client_secret' => 'eYDxKSIRJ4UbeUp6DzvuaduOvTbWtLBKLrsan8nt',
        'redirect_uri' => 'http://127.0.0.1:8080/auth/callback',
        'code' => $request->code,
    ]);



    $response2 = Http::withHeaders([
        'Authorization' => 'Bearer '. $response->json('access_token')
    ])->get('http://localhost:8000/api/user');



    $user = User::updateOrCreate([
        'id' => $response2->json('id'),
    ], [
        'name' =>  $response2->json('name'),
        'email' => $response2->json('email'),
    ]);

    Auth::login($user);

    return redirect('/dashboard');
});

Route::middleware([
    'auth',
  /*  config('jetstream.auth_session'),
    'verified',*/
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/redirect', function (Request $request) {
    $request->session()->put('state', $state = Str::random(40));

    $query = http_build_query([
        'client_id' => '9b2abe4a-17c2-471d-9e42-0a90065c849e',
        'redirect_uri' => 'http://127.0.0.1:8080/auth/callback',
        'response_type' => 'code',
        'scope' => '',
        'state' => $state,
//         'prompt' => 'login', // "none", "consent", or "login"
    ]);

    return redirect('http://localhost:8000/oauth/authorize?'.$query);
})->name('redirect-to-user-base');
