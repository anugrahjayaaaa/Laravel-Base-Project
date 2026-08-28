@extends('layouts.app')
@section('content')
<h3>{{ isset($permission) ? 'Edit Permission' : 'New Permission' }}</h3>
<form method="POST" action="{{ isset($permission) ? route('permissions.update', $permission) : route('permissions.store') }}">
    @csrf
    @if (isset($permission)) @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $permission->name ?? '') }}" placeholder="e.g. user.edit" required>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('permissions.index') }}" class="btn btn-link">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </div>
</form>
@endsection
