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
                        <form method="POST" action="{{ route('features.toggle', $feature->slug) }}" class="d-inline features-toggle-form">
                            @csrf
                            <input type="hidden" name="enabled" value="{{ $feature->enabled ? '0' : '1' }}">
                            <div class="form-check form-switch d-inline-flex align-items-center justify-content-end mb-0">
                                <input class="form-check-input feature-toggle" type="checkbox" role="switch"
                                       id="feat-{{ $feature->slug }}" {{ $feature->enabled ? 'checked' : '' }}
                                       aria-label="Toggle {{ $feature->label }}">
                            </div>
                        </form>
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
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.feature-toggle').forEach(function (el) {
        el.addEventListener('change', function () {
            var form = el.closest('.features-toggle-form');
            var next = el.checked ? 'enable' : 'disable';
            if (! confirm('Are you sure you want to ' + next + ' this feature?')) {
                el.checked = ! el.checked; // revert the switch
                return;
            }
            form.querySelector('input[name="enabled"]').value = el.checked ? '1' : '0';
            form.submit();
        });
    });
</script>
@endpush
