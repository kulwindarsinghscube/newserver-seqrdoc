@extends('admin.layout.layout')
@section('content')
<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            <div id="">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header"><i class="fa fa-print"></i>Print Serial Number
                                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"></ol>
                            </h1>
                        </div>
                    </div>
                         
                    <form action="{{ route('vgujaipur.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <style>
                            .flex-row{
                                display: flex;
                                flex-wrap: wrap;
                            }
                        </style>
                        <div class="row flex-row">
                            <div class="col-3" style="margin-right: 1%; margin-left: 5%;">
                                <label for="exampleFormControlInputStartNumber" class="form-label">Starting Serial Number</label>
                                <input type="number" min="1" name="start" class="form-control" id="exampleFormControlInputStartNumber" placeholder="Enter Starting Number" value="{{ old('start') }}">
                            </div>
                            <div class="col-3"> 
                                <label for="exampleFormControlInputQty" class="form-label">Quantity</label>
                                <input type="number" min="1" name="qty" class="form-control" id="exampleFormControlInputQty" placeholder="Enter Quantity" value="{{ old('qty') }}" >
                            </div>
                            <div class="col-3">
                                <button type="submit" class="btn btn-primary" style="margin:30%">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop