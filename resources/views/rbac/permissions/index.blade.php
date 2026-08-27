@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Permissions</h3>
    <a href="{{ route('permissions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Permission</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Name</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($permissions as $perm)
                <tr>
                    <td>{{ $permissions->firstItem() + $loop->index }}</td>
                    <td>{{ $perm->name }}</td>
                    <td class="text-end">
                        <a href="{{ route('permissions.edit', $perm) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-action="{{ route('permissions.destroy', $perm) }}"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted">No permissions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination-info', ['items' => $permissions])
{{ $permissions->links() }}
@include('partials.delete-modal')
@endsection
