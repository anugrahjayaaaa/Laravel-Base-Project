<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;

beforeEach(fn () => $this->seed());

it('logout link renders in sidebar and logs out', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $html = $this->get(route('dashboard'))->assertOk()->getContent();
    expect(str_contains($html, 'Logout'))->toBeTrue();
    expect(str_contains($html, (string) route('logout')))->toBeTrue();

    $this->post(route('logout'))->assertRedirect();
    expect(Auth::check())->toBeFalse();
});
