@extends('layouts.app')
@section('content')
<h3>{{ isset($role) ? 'Edit Role' : 'New Role' }}</h3>
<form method="POST" action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}">
    @csrf
    @if (isset($role)) @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $role->name ?? '') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Permissions</label>
                <div class="row">
                    @foreach ($permissions as $perm)
                    <div class="col-6 col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                id="perm_{{ $perm->id }}" {{ (isset($role) && $role->permissions->contains($perm->id)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_{{ $perm->id }}">{{ $perm->name }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('roles.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </div>
</form>
@endsection
