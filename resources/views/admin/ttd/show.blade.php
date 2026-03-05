@extends('admin.layout.layout')
@section('content')
    <div class="container">
        <div class="col-xs-12">
            <div class="clearfix">
                <div id="">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"><i class="fa fa-file-text-o"></i>Temporary Travel Document
                                    <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('ttdcertificate') }}</ol>
                                </h1>
                            </div>
                        </div>
                        <div>
                            Click <a href="{{ url($link)}}" target="_blank">Here</a> to view PDF
                            <ul class="nav nav-pills" id="pills-filter">
                                <li style="float: right;">
                                    <a class="btn btn-theme" href="{{route('ttd.create')}}" style="background-color: #0052cc;color:white;"><i class="fa fa-plus"></i> Create  New Certificate</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop