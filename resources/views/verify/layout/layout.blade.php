<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SeQR WebApp</title>
	<link rel="icon" type="image/png" href="assets/images/fav.png">
	@include('verify.layout.css')
	@yield('style')

	@include('verify.layout.header')
    @yield('content')
   
    @include('verify.layout.footer')
    @include('verify.layout.script')
    @yield('script')
</head>