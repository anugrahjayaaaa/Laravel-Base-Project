@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Roles</h3>
    <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Role</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>#</th><th>Name</th><th>Permissions</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                <tr>
                    <td>{{ $roles->firstItem() + $loop->index }}</td>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->permissions->count() }}</td>
                    <td class="text-end">
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        @if ($role->name !== 'super-admin')
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-action="{{ route('roles.destroy', $role) }}"><i class="bi bi-trash"></i></button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No roles.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination-info', ['items' => $roles])
{{ $roles->links() }}
@include('partials.delete-modal')
@endsection
