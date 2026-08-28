<?php
use App\Models\User;

beforeEach(fn () => $this->seed());

it('header user dropdown renders as button trigger with logout form', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $html = $this->get(route('dashboard'))->assertOk()->getContent();
    // trigger is now a <button> with data-bs-toggle=dropdown
    expect(preg_match('/<button[^>]*data-bs-toggle="dropdown"[^>]*>/', $html))->toBe(1);
    expect(str_contains($html, (string) route('logout')))->toBeTrue();
    expect(str_contains($html, 'Logout'))->toBeTrue();
});
