@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">Two-Factor Challenge</div>
                <div class="card-body">
                    <form method="POST" action="{{ url('/two-factor-challenge') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="code" class="form-label">Authentication Code</label>
                            <input type="text" id="code" name="code" class="form-control" placeholder="123456" autofocus>
                        </div>
                        <p class="small text-muted">Alternatively, you may enter one of your recovery codes.</p>
                        <div class="mb-3">
                            <label for="recovery_code" class="form-label">Recovery Code</label>
                            <input type="text" id="recovery_code" name="recovery_code" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Authenticate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection