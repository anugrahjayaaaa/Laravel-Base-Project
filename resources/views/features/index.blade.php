@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>Feature Flags</h3>
<p class="text-muted">A disabled feature is inaccessible to everyone — except users who hold the <code>feature.manage</code> permission, who stay in so they can operate modules while off.</p>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Feature</th><th>Slug</th><th>Status</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
                @forelse ($features as $feature)
                <tr>
                    <td>{{ $feature->label }}</td>
                    <td><span class="text-muted small">{{ $feature->slug }}</span></td>
                    <td>
                        @if($feature->enabled)
                            <span class="badge text-bg-success">Enabled</span>
                        @else
                            <span class="badge text-bg-secondary">Disabled</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="form-check form-switch d-inline-flex align-items-center justify-content-end mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="feat-{{ $feature->slug }}" {{ $feature->enabled ? 'checked' : '' }}
                                   aria-label="Toggle {{ $feature->label }}"
                                   data-bs-toggle="modal" data-bs-target="#featureToggleModal"
                                   data-action="{{ route('features.toggle', $feature->slug) }}"
                                   data-enabled="{{ $feature->enabled ? '0' : '1' }}">
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No features.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@include('partials.feature-toggle-modal')
@endsection
