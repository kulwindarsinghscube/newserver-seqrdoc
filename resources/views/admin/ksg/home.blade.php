@extends('admin.layout.layout')
@section('content')
<div class="container">
	<div class="container-fluid">
        {{-- Check for an error message --}}
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- Your other content --}}
    </div>
</div>
@stop
@section('script')
@stop
