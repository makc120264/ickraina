@extends('layouts.app')

@section('content')
    <div class="text-center py-5">
        <h1 class="display-4 text-primary">Welcome to {{ config('app.name', 'Laravel') }}!</h1>
        <p class="lead">Your Laravel environment is configured successfully 🎉</p>
        <a href="{{ url('/') }}" class="btn btn-outline-primary mt-3">Home</a>
    </div>
@endsection
