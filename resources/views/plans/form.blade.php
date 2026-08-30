@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>{{ $plan ? ui('edit_plan') : ui('new_plan') }}</h3>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ $plan ? route('plans.update', $plan) : route('plans.store') }}">
            @csrf
            @if($plan) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ ui('slug') }}</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $plan->slug ?? '') }}" {{ $plan && $plan->slug === 'free' ? 'readonly' : '' }} required>
                    <div class="form-text">{{ ui('slug_help') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('name') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('price_monthly') }}</label>
                    <input type="number" step="0.01" min="0" name="price_monthly" class="form-control" value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}" required>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">{{ ui('active') }}</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('limits') }}</label>
                    <textarea name="limits" class="form-control" rows="4">{{ old('limits', $plan ? collect($plan->limits ?? [])->map(fn($v,$k) => "$k: $v")->join("\n") : '') }}</textarea>
                    <div class="form-text">{{ ui('limits_help') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('features') }}</label>
                    <textarea name="features" class="form-control" rows="4">{{ old('features', $plan ? collect($plan->features ?? [])->join("\n") : '') }}</textarea>
                    <div class="form-text">{{ ui('features_help') }}</div>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">{{ ui('save') }}</button>
                <a href="{{ route('plans.index') }}" class="btn btn-light border">{{ ui('cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
