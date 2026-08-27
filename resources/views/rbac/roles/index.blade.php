@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Roles</h3>
    @can('role.create')
    <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New Role</a>
    @endcan
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Permissions</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                <tr>
                    <td class="text-muted">{{ $roles->firstItem() + $loop->index }}</td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $role->name }}</span>
                    </td>
                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $role->permissions->count() }}</span></td>
                    <td class="text-end">
                        <x-action-buttons
                            :edit="route('roles.edit', $role)"
                            :delete="$role->name !== 'super-admin' ? route('roles.destroy', $role) : null" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No roles.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@include('partials.pagination-info', ['items' => $roles])
{{ $roles->links() }}
@include('partials.delete-modal')
@endsection
