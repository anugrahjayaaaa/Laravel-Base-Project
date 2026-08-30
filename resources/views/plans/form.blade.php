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
                    <label class="form-label">{{ ui('name') }}</label>
                    <input type="text" name="name" id="plan-name" class="form-control"
                           value="{{ old('name', $plan->name ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('slug') }}</label>
                    <input type="text" name="slug" id="plan-slug" class="form-control"
                           value="{{ old('slug', $plan->slug ?? '') }}"
                           placeholder="{{ ui('slug_auto') }}" {{ $plan && $plan->slug === 'free' ? 'readonly' : '' }}>
                    <div class="form-text">{{ ui('slug_help') }}</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ ui('price_monthly') }}</label>
                    <input type="number" step="0.01" min="0" name="price_monthly" class="form-control"
                           value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('billing_period') }}</label>
                    <select name="billing_period" class="form-select">
                        <option value="monthly" @selected(old('billing_period', $plan->billing_period ?? 'monthly') === 'monthly')>{{ ui('period_monthly') }}</option>
                        <option value="lifetime" @selected(old('billing_period', $plan->billing_period ?? 'monthly') === 'lifetime')>{{ ui('period_lifetime') }}</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ ui('limit_max_members') }}</label>
                    <input type="number" min="0" name="max_members" class="form-control"
                           value="{{ old('max_members', $plan?->limit('max_members') ?? 0) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ ui('limit_max_projects') }}</label>
                    <input type="number" min="0" name="max_projects" class="form-control"
                           value="{{ old('max_projects', $plan?->limit('max_projects') ?? 0) }}">
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active"
                               {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">{{ ui('active') }}</label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">{{ ui('features') }}</label>
                    <div class="row row-cols-2 row-cols-md-3 g-2">
                        @foreach(config('pennant.features') as $slug => $cfg)
                            @php($checked = in_array($slug, old('features', $plan->features ?? [])))
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="features[]"
                                           value="{{ $slug }}" id="feat-{{ $slug }}" @checked($checked)>
                                    <label class="form-check-label" for="feat-{{ $slug }}">{{ $cfg['label'] ?? $slug }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
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

@push('scripts')
<script>
    // Slug auto-generates from name; user can override until they type in slug.
    (() => {
        const name = document.getElementById('plan-name');
        const slug = document.getElementById('plan-slug');
        if (!name || !slug || slug.readOnly) return;
        let touched = slug.value.trim() !== '';
        slug.addEventListener('input', () => { touched = true; });
        name.addEventListener('input', () => {
            if (!touched) slug.value = name.value.toLowerCase().trim()
                .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        });
    })();
</script>
@endpush
@endsection
