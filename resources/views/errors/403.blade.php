@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-lock display-1 text-muted"></i>
                <h1 class="display-4 fw-bold my-3">403</h1>
                <h3>{{ ui('access_denied') }}</h3>
                <p class="text-muted">{{ ui('access_denied_body') }}</p>
                <a href="{{ url('/') }}" class="btn btn-primary">{{ ui('go_back_to_dashboard') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
