<?php

use App\Http\Middleware\SetLocale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('persists chosen locale in session', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($u)
        ->post(route('locale.update'), ['locale' => 'id'])
        ->assertRedirect();

    expect(session('locale'))->toBe('id');
});

it('SetLocale middleware applies session locale to the app', function () {
    Session::put('locale', 'id');
    $request = Request::create('/dashboard');
    $called = false;
    (new SetLocale())->handle($request, function ($req) use (&$called) {
        $called = true;
        expect(App::getLocale())->toBe('id');
        expect(__('messages.users'))->toBe('Pengguna');
    });
    expect($called)->toBeTrue();
});

it('rejects unsupported locale', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($u)
        ->post(route('locale.update'), ['locale' => 'fr'])
        ->assertStatus(422);
});

it('reads translations from database (language_lines)', function () {
    // seeded by DatabaseSeeder -> LanguageLineSeeder
    $line = \Spatie\TranslationLoader\LanguageLine::where('group', 'messages')->where('key', 'users')->first();
    expect($line)->not->toBeNull();
    expect($line->text)->toBe(['en' => 'Users', 'id' => 'Pengguna']);

    // DB overrides the file: __() returns DB value
    Session::put('locale', 'id');
    $request = Request::create('/dashboard');
    (new \App\Http\Middleware\SetLocale())->handle($request, function ($req) {
        expect(__('messages.users'))->toBe('Pengguna');
    });
});
