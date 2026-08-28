<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\TranslationLoader\LanguageLine;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('lists translations for admin', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($u)
        ->get(route('translations.index'))
        ->assertOk()
        ->assertSee('messages') // group column
        ->assertSee('users');    // key column (rendered in <code>)
});

it('updates a translation value and reflects in __()', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $line = LanguageLine::where('group', 'messages')->where('key', 'users')->first();

    $this->actingAs($u)
        ->put(route('translations.update', $line), ['en' => 'Users', 'id' => 'Pengguna Edit'])
        ->assertRedirect(route('translations.index'));

    $line->refresh();
    expect($line->text['id'])->toBe('Pengguna Edit');

    session(['locale' => 'id']);
    $request = request()->create('/dashboard');
    app(\App\Http\Middleware\SetLocale::class)->handle($request, function ($req) {
        expect(__('messages.users'))->toBe('Pengguna Edit');
    });
});

it('denies translations to unauthorized user', function () {
    $user = User::factory()->create(['username' => 'unauth'.time()]);
    $this->actingAs($user)
        ->get(route('translations.index'))
        ->assertForbidden();
});
