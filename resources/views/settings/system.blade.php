@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ ui('system_settings') }}</h1>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header"><i class="bi bi-gear-fill me-2"></i> System Configuration</div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.system.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="locale_default" class="form-label">{{ __('messages.default_language') }}</label>
                    <p class="text-muted small">{{ __('messages.language_desc') }}</p>
                    <select name="locale_default" id="locale_default" class="form-select @error('locale_default') is-invalid @enderror">
                        @foreach ($availableLocales as $locale)
                            <option value="{{ $locale }}" {{ ($defaultLocale ?? config('app.locale')) === $locale ? 'selected' : '' }}>
                                {{ $locale === 'en' ? __('messages.english') : __('messages.indonesian') }}
                            </option>
                        @endforeach
                    </select>
                    @error('locale_default')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="registration_enabled"
                               name="registration_enabled" value="1" {{ $registrationEnabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="registration_enabled">{{ __('messages.registration_enabled') }}</label>
                    </div>
                    <p class="text-muted small">{{ __('messages.registration_desc') }}</p>
                    @error('registration_enabled')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">{{ ui('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
