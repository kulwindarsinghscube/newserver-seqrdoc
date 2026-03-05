<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <link rel="icon" type="image/png" href="{{asset('backend/images/fav.png')}}">
        <!--  Css files  -->
        @include('admin.layout.css')
        @yield('style')
    </head>
    
    <!-- @yield('start_form') -->
        <!-- Header file -->
        @include('admin.layout.header')

        <!-- Side-Bar-->
        
        <!-- Content Start here -->
       
            @yield('content')
        <!-- Content End here -->
        <!-- footer contant -->
        <!-- @yield('end_form') -->
    <!-- Js file -->
        @include('admin.layout.footer')
        @include('admin.layout.script')
        @yield('script')
    
</html>