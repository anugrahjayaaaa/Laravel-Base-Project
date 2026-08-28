@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>Feature Flags</h3>
<p class="text-muted">A disabled feature is inaccessible to everyone — even users who hold the matching permission. Super-admin is also blocked while a feature is off.</p>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Feature</th><th>Slug</th><th class="text-end">Status</th></tr>
            </thead>
            <tbody>
                @forelse ($features as $feature)
                <tr>
                    <td>{{ $feature->label }}</td>
                    <td><span class="text-muted small">{{ $feature->slug }}</span></td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('features.toggle', $feature->slug) }}">
                            @csrf
                            <input type="hidden" name="enabled" value="{{ $feature->enabled ? '0' : '1' }}">
                            <button type="submit" class="btn btn-sm {{ $feature->enabled ? 'btn-success' : 'btn-outline-secondary' }}">
                                {{ $feature->enabled ? 'Enabled' : 'Disabled' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No features.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
