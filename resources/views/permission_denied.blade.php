@extends('admin.layout.layout')
@section('style')
    <style type="text/css">
        .box__dragndrop,
        .box__uploading,
        .box__success,
        .box__error {
            display: none;
        }
    </style>
@stop
@section('top_fixed_content')
<nav class="navbar navbar-static-top">
    <div class="title">
        <h4><i class="fa fa-user"></i>Users</h4>
    </div>
    <div class="top_filter"></div>
    
</nav>
@stop
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-title-w-btn">
                <h4 class="title">Unauthrorized Access!</h4>
            </div>
            <hr>
            <div class="card-body">
                <div class="login-box">
                    <section class="error-wrapper">
                        <!-- <div style="text-align: center; ">
                            <img src="{{ asset('backend/images/access_denied.jpg')}}"/>
                        </div> -->
                        <h2 class="f_bold text-danger">Access Denied</h2>
                        <h5 class="f_bold text-danger">Your Request for this page has been denied because of access control<h5> 
                        
                            <a href="<?= route('admin.dashboard') ?>"><span class="text-primary"><b><u>Return to Dashboard</u></b></span></a>
                        
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('script')
@stop