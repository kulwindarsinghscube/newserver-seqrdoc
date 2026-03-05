@extends('admin.layout.layout')
@section('content')
    <div class="container">
        <div class="col-xs-12">
            <div class="clearfix">
                <div id="">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"><i class="fa fa-certificate"></i>Intifacc Mordern Technology
                                    <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"></ol>
                                </h1>
                            </div>
                        </div>
                             
                        <form action="{{ route('imt.store') }}" method="POST" enctype="multipart/form-data">
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
                            <div class="mb-3">
                                <label for="exampleFormControlInputName" class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" id="exampleFormControlInputName" placeholder="Enter Name" value="{{ old('name') }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputDOB" class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" id="exampleFormControlInputDOB" value="{{ old('dob') }}" >
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputPlaceofBirth" class="form-label">Place of Birth</label>
                                <input type="text" name="birth_place" class="form-control" id="exampleFormControlInputPlaceofBirth" placeholder="Enter Place of Birth" value="{{ old('birth_place') }}" >
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputIDCardNumber" class="form-label">ID Card Number</label>
                                <input type="text" name="card_number" class="form-control" id="exampleFormControlInputIDCardNumber" placeholder="Enter ID Card Number" value="{{ old('card_number') }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputDOB" class="form-label">Sex</label>
                                <input type="text" name="sex" class="form-control" id="exampleFormControlInputDOB" placeholder="Enter Sex" value="{{ old('sex') }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputMaritalStatus" class="form-label">Marital Status</label>
                                <input type="text" name="marital_status" class="form-control" id="exampleFormControlInputMaritalStatus" placeholder="Enter Marital Status" value="{{ old('marital_status') }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextareaPlaceofResidence" class="form-label">Place of Residence</label>
                                <textarea class="form-control" name="address" id="exampleFormControlTextareaPlaceofResidence" rows="3">{{ old('address') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputMotherName" class="form-label">Mother Name</label>
                                <input type="text" name="mother_name" class="form-control" id="exampleFormControlInputMotherName" placeholder="Enter Mother Name" value="{{ old('mother_name') }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputDOI" class="form-label">Date of Issue</label>
                                <input type="date" name="doi" class="form-control" id="exampleFormControlInputDOI" value="{{ old('doi') }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputOccupation" class="form-label">Occupation</label>
                                <input type="text" name="occupation" class="form-control" id="exampleFormControlInputOccupation" placeholder="Enter Occupation" value="{{ old('occupation') }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputImage" class="form-label">Image</label>
                                <input type="file" name="photo" class="form-control" id="exampleFormControlInputImage" value="{{ old('photo') }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputfingerprint" class="form-label">Finger Print </label>
                                <input type="file" name="fingerprint" class="form-control" id="exampleFormControlInputfingerprint" value="{{ old('fingerprint') }}">
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary" style="margin: 0.5%">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop