<?php

use App\Models\Feature;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($this->user);
});

it('feature() helper returns enabled state and fails closed when missing', function () {
    expect(feature('users'))->toBeTrue();
    expect(feature('audit'))->toBeTrue();
    expect(feature('does-not-exist'))->toBeFalse();
});

it('lists feature flags', function () {
    $this->get(route('features.index'))->assertOk()->assertSee('Feature Flags');
});

it('toggles a feature off and back on', function () {
    $this->post(route('features.toggle', 'users'), ['enabled' => '0'])
        ->assertRedirect(route('features.index'));
    expect(feature('users'))->toBeFalse();

    $this->post(route('features.toggle', 'users'), ['enabled' => '1'])
        ->assertRedirect(route('features.index'));
    expect(feature('users'))->toBeTrue();
});

it('blocks a module route when its feature is off, even with permission', function () {
    // user holds user.view + super-admin; turn the feature off
    Feature::where('slug', 'users')->update(['enabled' => false]);

    $this->get(route('users.index'))->assertNotFound();
});

it('allows a module route when its feature is on', function () {
    Feature::where('slug', 'users')->update(['enabled' => true]);

    $this->get(route('users.index'))->assertOk();
});

it('hides a feature-off menu item from the sidebar', function () {
    Feature::where('slug', 'users')->update(['enabled' => false]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('data-menu-text="Users"');
});
