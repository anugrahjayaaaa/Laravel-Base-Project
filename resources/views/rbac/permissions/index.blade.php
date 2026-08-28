@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Permissions</h3>
    @can('permission.create')
    <a href="{{ route('permissions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New Permission</a>
    @endcan
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Guard</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($permissions as $perm)
                <tr>
                    <td class="text-muted">{{ $permissions->firstItem() + $loop->index }}</td>
                    <td>{{ $perm->name }}</td>
                    <td><span class="badge text-bg-secondary">{{ $perm->guard_name }}</span></td>
                    <td class="text-end">
                        <x-action-buttons
                            :edit="route('permissions.edit', $perm)"
                            :delete="route('permissions.destroy', $perm)" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No permissions.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@include('partials.pagination-info', ['items' => $permissions])
{{ $permissions->links() }}
@include('partials.delete-modal')
@endsection
