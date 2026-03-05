@extends('admin.layout.layout')
@section('content')
<style>
    .mb-5 {
        margin-bottom:5px;
    }
</style>

    <div class="container">
        <div class="col-xs-12">
            <div class="clearfix">
                <div id="">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"><i class="fa fa-file-text-o"></i>Temporary Travel Document
                                    <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"></ol>
                                </h1>
                            </div>
                        </div>
                             
                        <form action="{{ route('ttd.store') }}" method="POST" enctype="multipart/form-data">
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
                            
                            <div class="mb-5">
                                <label for="exampleFormControlTDNo" class="form-label">TD Number</label>
                                <input type="text" name="td_no" class="form-control" id="exampleFormControlTDNo" placeholder="Enter TD Number" value="{{ old('td_no') }}">
                            </div>

                            <div class="mb-5">
                                <label for="exampleFormControlDate" class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" id="exampleFormControlDate" placeholder="Enter Date" value="{{ old('date') }}">
                            </div>

                            <div class="mb-5">
                                <label for="exampleFormControlInputName" class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" id="exampleFormControlInputName" placeholder="Enter Full Name" value="{{ old('full_name') }}">
                            </div>
                            
                            <div class="mb-5">
                                <label for="exampleFormControlInputMotherName" class="form-label">Mother Name</label>
                                <input type="text" name="mother_name" class="form-control" id="exampleFormControlInputMotherName" placeholder="Enter Mother Name" value="{{ old('mother_name') }}">
                            </div>

                            <div class="mb-5">
                                <label for="exampleFormControlInputSex" class="form-label">Sex</label>
                                <select class="form-control" name="sex">
                                    <option value="">Select Gender</option>
                                    <option {{ old('sex') == 'male' ? 'selected' : '' }} value="male">Male</option>
                                    <option {{ old('sex') == 'female' ? 'selected' : '' }} value="female">Female</option>
                                    <option {{ old('sex') == 'other' ? 'selected' : '' }} value="other">Other</option>
                                </select>
                            </div>

                            <div class="mb-5">
                                <label for="exampleFormControlInputDateOfBirth" class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" id="exampleFormControlInputDateOfBirth" placeholder="Enter Date of Birth" value="{{ old('dob') }}">
                            </div>
                            
                            <div class="mb-5">
                                <label for="exampleFormControlInputPlaceOfBirth" class="form-label">Place Of Birth</label>
                                <input type="text" name="place_of_birth" class="form-control" id="exampleFormControlInputPlaceOfBirth" placeholder="Enter Place Of Birth" value="{{ old('place_of_birth') }}">
                            </div>

                            <div class="mb-5">
                                <label for="exampleFormControlInputNationality" class="form-label">Nationality</label>
                                <input type="text" name="nationality" class="form-control" id="exampleFormControlInputNationality" placeholder="Enter Nationality" value="{{ old('nationality') }}">
                            </div>

                            <div class="mb-5">
                                <label for="exampleFormControlInputFaceMarks" class="form-label">Face Marks</label>
                                <input type="text" name="face_marks" class="form-control" id="exampleFormControlInputFaceMarks" placeholder="Enter Face Marks" value="{{ old('face_marks') }}">
                            </div>


                            <div class="mb-5">
                                <label for="exampleFormControlTextareaAddressCountryReseidence" class="form-label">Address of Country Residence</label>
                                <textarea class="form-control" name="address_country_residance" id="exampleFormControlTextareaAddressCountryReseidence" rows="3" placeholder="Enter Address of Country Residence">{{ old('address_country_residance') }}</textarea>
                            </div>
                            
                            <div class="mb-5">
                                <label for="exampleFormControlInputDOI" class="form-label">Date of Issue</label>
                                <input type="date" name="date_of_issue" class="form-control" id="exampleFormControlInputDOI" value="{{ old('date_of_issue') }}">
                            </div>
                            
                            <div class="mb-5">
                                <label for="exampleFormControlInputIssusingAuthority" class="form-label">Issuing Authority</label>
                                <input type="text" name="issuing_authority" class="form-control" id="exampleFormControlInputIssusingAuthority" placeholder="Enter Issuing Authority" value="{{ old('issuing_authority') }}">
                            </div>


                            <div class="mb-5">
                                <label for="exampleFormControlInpuValidUnit" class="form-label">Valid Unit</label>
                                <input type="text" name="valid_unit" class="form-control" id="exampleFormControlInpuValidUnit" placeholder="Enter Valid Unit" value="{{ old('valid_unit') }}">
                            </div>


                            <div class="mb-5">
                                <label for="exampleFormControlInputImage" class="form-label">Attached Photo</label>
                                <input type="file" name="photo" class="form-control" id="exampleFormControlInputImage" value="{{ old('photo') }}">
                            </div>
                            <!-- <div class="mb-5">
                                <label for="exampleFormControlInputfingerprint" class="form-label">Finger Print </label>
                                <input type="file" name="fingerprint" class="form-control" id="exampleFormControlInputfingerprint" value="{{ old('fingerprint') }}">
                            </div> -->
                            <div class="mb-5">
                                <button type="submit" class="btn btn-primary" style="margin: 0.5%">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop