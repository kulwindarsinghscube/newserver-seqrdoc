@extends('admin.layout.layout')
@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <?php
    $domain = request()->getHost();
    $subdomain = explode('.', $domain);
    $baseDirectoryPath = $subdomain[0] . '/' . config('constant.backend') . '/students/';
    ?>

    <style>
        .form-group {
            height: 7rem;
        }

        .form-row {
            margin-bottom: 15px;
        }

        .dataTables_wrapper {
            border-top: 1px solid #eee;
        }

        .request_div {
            margin: 10px 0;
            border: 1px solid #cfcfcf;
            padding: 10px;
        }

        .centered-card {
            margin-bottom: 20px;
        }

        #divLoading {
            position: fixed;
            right: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(102, 102, 102, 0.8);
            z-index: 30001;
            display: none;
        }

        #divLoading p {
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .campus-class {
    border: 2px solid #007bff; /* Customize the border color and width */
    border-radius: 5px; /* Optional: for rounded corners */
    padding: 10px; /* Space between the border and content */
    text-align: center; /* Center text horizontally */
    background-color: #f8f9fa; /* Optional: light background color */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Optional: subtle shadow */
    text-transform: capitalize; /* Capitalize the first letter of each word */
    margin-bottom: 2%;

}

    </style>

    <div class="container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><i class="fa fa-user-circle"></i> Edit Student</h1>
                </div>
            </div>

            <div class="row centered-card">
                <div class="col-md-12 col-lg-12">
                    <div class="form-row row">
                        <div class="col-md-12 campus-class">
                            <h4>Status : {{ $student->status }}</h4>
                        </div>
                    </div>
                    
                    <form id="student-form" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" id="student-id" value="{{ $student->id }}">

                        <div class="form-row row">
                            <div class="form-group col-md-3">
                                <label for="prn">PRN:<small style="color:red">*</small></label>
                                <input type="text" class="form-control" id="prn" name="prn"
                                    value="{{ old('prn', $student->prn) }}" required>
                            </div>


                            <div class="form-group col-md-3">
                                <label for="wpu_email_id">Email ID:<small style="color:red">*</small></label>
                                <input type="email" class="form-control" id="wpu_email_id" name="wpu_email_id"
                                    value="{{ old('wpu_email_id', $student->wpu_email_id) }}" required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="secondary_email_id">Secondary Email ID:<small style="color:red">*</small></label>
                                <input type="email" class="form-control" id="secondary_email_id" name="secondary_email_id"
                                    value="{{ old('secondary_email_id', $student->secondary_email_id) }}" required>
                            </div>
                            
                            <div class="form-group col-md-3">
                                <label for="gender">Gender:</label>
                                <select id="gender" name="gender" class="form-control"
                                    >
                                    <option value="" {{ @$student->gender === '' ? 'selected' : '' }}>Select
                                        gender</option>
                                    <option value="M" {{ @$student->gender === 'M' ? 'selected' : '' }}>Male
                                    </option>
                                    <option value="F" {{ @$student->gender === 'F' ? 'selected' : '' }}>
                                        Female</option>
                                    <option value="O" {{ @$student->gender === 'O' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="date_of_birth">Date of Birth:<small style="color:red">*</small></label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                    value="{{ old('date_of_birth', $student->date_of_birth) }}" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="cgpa">CGPA:<small style="color:red">*</small>
                                    @if ($student->studentAckLogs)
                                        {!! @$student->studentAckLogs->cgpa_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : '' !!}
                                        {!! @$student->studentAckLogs->cgpa_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : '' !!}
                                    @endif
                                </label>
                                <input type="text" class="form-control" id="cgpa" name="cgpa"
                                    value="{{ old('cgpa', $student->cgpa) }}" required>
                            </div>
                        </div>


                        {{-- <div class="form-row row">
                                
                            </div> --}}

                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="full_name">Full Name:<small style="color:red">*</small>
                                    @if ($student->studentAckLogs)
                                        {!! @$student->studentAckLogs->fn_en_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : '' !!}
                                        {!! @$student->studentAckLogs->fn_en_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : '' !!}
                                    @endif
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                    value="{{ old('full_name', $student->full_name) }}"
                                    onchange="english_text_change(this,'#full_name_hindi','#full_name_krutidev')">
                            </div>

                            <div class="form-group col-md-4" style="margin-bottom: 11rem;">
                                <label for="full_name_hindi">Full Name (Hindi):<small style="color:red">*</small>
                                    @if ($student->studentAckLogs)
                                        {!! @$student->studentAckLogs->fn_hi_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : '' !!}
                                        {!! @$student->studentAckLogs->fn_hi_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : '' !!}
                                    @endif
                                </label>
                                <input type="text" class="form-control" style="margin-bottom: 2%;" id="full_name_hindi"
                                    name="full_name_hindi" onchange="hindi_text_change(this,'#full_name_krutidev')"
                                    value="{{ old('full_name_hindi', $student->full_name_hindi) }}" required>

                                <div class="col-md-12 mt-2  hindi_keyboard" style="">

                                    @include('convodataverification.admin.pages.hindi_keyboard')

                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="full_name_krutidev">Full Name (Krutidev):<small style="color:red">*</small>

                                </label>
                                <input type="text" class="form-control" id="full_name_krutidev" name="full_name_krutidev"
                                    value="{{ old('full_name_krutidev', $student->full_name_krutidev) }}" required>
                            </div>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="mother_name">Mother's Name:<small style="color:red">*</small>
                                    @if ($student->studentAckLogs)
                                    {!! @$student->studentAckLogs->mn_en_status == 0
                                        ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                        : '' !!}
                                    {!! @$student->studentAckLogs->mn_en_status == 1
                                        ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                        : '' !!}
                                @endif
                                </label>
                                <input type="text" class="form-control" id="mother_name" name="mother_name"
                                    onchange="english_text_change(this,'#mother_name_hindi','#mother_name_krutidev')"
                                    value="{{ old('mother_name', $student->mother_name) }}">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="mother_name_hindi">Mother's Name (Hindi):
                                    @if ($student->studentAckLogs)
                                    {!! @$student->studentAckLogs->mn_hi_status == 0
                                        ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                        : '' !!}
                                    {!! @$student->studentAckLogs->mn_hi_status == 1
                                        ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                        : '' !!}
                                @endif

                                </label>
                                <input type="text" class="form-control" id="mother_name_hindi" name="mother_name_hindi"
                                    readonly value="{{ old('mother_name_hindi', $student->mother_name_hindi) }}">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="mother_name_krutidev">Mother's Name (Krutidev):</label>
                                <input type="text" class="form-control" id="mother_name_krutidev"
                                    name="mother_name_krutidev" readonly
                                    value="{{ old('mother_name_krutidev', $student->mother_name_krutidev) }}">
                            </div>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="father_name">Father's Name:<small style="color:red">*</small>
                                    @if ($student->studentAckLogs)
                                    {!! @$student->studentAckLogs->ftn_en_status == 0
                                        ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                        : '' !!}
                                    {!! @$student->studentAckLogs->ftn_en_status == 1
                                        ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                        : '' !!}
                                @endif
                                </label>
                                <input type="text" class="form-control" id="father_name" name="father_name"
                                    onchange="english_text_change(this,'#father_name_hindi','#father_name_krutidev')"
                                    value="{{ old('father_name', $student->father_name) }}">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="father_name_hindi">Father's Name (Hindi):
                                    @if ($student->studentAckLogs)
                                    {!! @$student->studentAckLogs->ftn_hi_status == 0
                                        ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                        : '' !!}
                                    {!! @$student->studentAckLogs->ftn_hi_status == 1
                                        ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                        : '' !!}
                                @endif

                                </label>
                                <input type="text" class="form-control" id="father_name_hindi"
                                    name="father_name_hindi" readonly
                                    value="{{ old('father_name_hindi', $student->father_name_hindi) }}">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="father_name_krutidev">Father's Name (Krutidev):</label>
                                <input type="text" class="form-control" id="father_name_krutidev"
                                    name="father_name_krutidev" readonly
                                    value="{{ old('father_name_krutidev', $student->father_name_krutidev) }}">
                            </div>
                        </div>
                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="course_name">Course Name:<small style="color:red">*</small>
                                    @if ($student->studentAckLogs)
                                        {!! @$student->studentAckLogs->cs_en_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : '' !!}
                                        {!! @$student->studentAckLogs->cs_en_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : '' !!}
                                    @endif
                                </label>
                                <input type="text" class="form-control" id="course_name" name="course_name"
                                    onchange="english_text_change(this,'#course_name_hindi','#course_name_krutidev')"
                                    value="{{ old('course_name', $student->course_name) }}" required>
                            </div>

                            <div class="form-group col-md-4" style="margin-bottom: 11rem;">
                                <label for="course_name_hindi">Course Name (Hindi):<small style="color:red">*</small>
                                    @if ($student->studentAckLogs)
                                        {!! @$student->studentAckLogs->cs_hi_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : '' !!}
                                        {!! @$student->studentAckLogs->cs_hi_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : '' !!}
                                    @endif
                                </label>
                                <input type="text" class="form-control" style="margin-bottom: 2%;"
                                    id="course_name_hindi" name="course_name_hindi"
                                    onchange="hindi_text_change(this,'#course_name_krutidev')"
                                    value="{{ old('course_name_hindi', $student->course_name_hindi) }}" required>

                                <div class="col-md-12 mt-2  hindi_keyboard" style="">

                                    {{-- @include('convodataverification.admin.pages.hindi_course_keyboard') --}}
                                    @include('convodataverification.admin.pages.hindi_keyboard_course')

                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="course_name_krutidev">Course Name (Krutidev):<small
                                        style="color:red">*</small></label>
                                <input type="text" class="form-control" id="course_name_krutidev"
                                    name="course_name_krutidev"
                                    value="{{ old('course_name_krutidev', $student->course_name_krutidev) }}" required>
                            </div>
                        </div>




                        <div class="form-row row">
                            <div class="col-md-12">
                                <label for="photograph">Student Photograph:<small style="color:red">*</small></label>
                                <br>
                                @if ($student->student_photo)
                                    <a target="_blank" href="{{ url($baseDirectoryPath . $student->student_photo) }}">
                                        <img class="photograph"
                                            src="{{ url($baseDirectoryPath . $student->student_photo) }}"
                                            style="width:20%" alt="">
                                    </a><br>
                                @endif
                                <input type="file" id="photograph" style="width: 30%;" name="photograph"
                                    class="form-control" accept="image/*"
                                    @if ($student->student_photo) data-photo-exists="true" @else data-photo-exists="false" @endif>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom: 20px">
                            
                            @if(@$student->collection_mode)
                            <hr>
                                <div class=" col-md-4">
                                    <h4> Collection Mode </h4>
                                    @if(@$student->collection_mode == 'By Post')
                                        <p>Receiving Degree Certificate by Post (Rs. 500/-)</p>
                                        <p><b>Delivery Address: </b>{{ @$student->delivery_address }}</p>
                                        <p><b>Pincode: </b>{{ @$student->delivery_pincode }}</p>
                                        <p><b>Country: </b>{{ @$student->delivery_country }}</p>
                                    @elseif(@$student->collection_mode == 'Attending Convocation')
                                        <p><b>Attending Convocation In Person (Rs. 3000/-)</b></p>
                                        <p><b>Attire Size: </b>{{ @$student->attire_size }}</p>
                                      
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="form-row row">
                        <div class="form-group col-md-6">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a class="btn btn-danger" href="javascript:history.back()">Back</a>
                        </div>
                    </div>
                    </form>

                </div>

            </div>
            @php
                $logs = $student->studentAckLogs;
            @endphp
            @if (
                !empty($logs) &&
                    ($logs->cgpa_status == 0 ||
                        $logs->fn_en_status == 0 ||
                        $logs->fn_hi_status == 0 ||
                        $logs->cs_en_status == 0 ||
                        $logs->cs_hi_status == 0 ||
                        $logs->mn_en_status == 0 ||
                        $logs->mn_hi_status == 0 ||
                        $logs->ftn_en_status == 0 ||
                        $logs->ftn_hi_status == 0))

                <div class="row centered-card student_request_class"
                    style="border-top: 1px solid rgb(209, 209, 214); padding-top: 1%;">

                    <div class="col-md-12 col-lg-12">
                        <h3><strong>Student Request for Correction</strong></h3>



                        @if ($logs->fn_en_status == 0)
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Full Name</strong></h4>
                                @if ($logs->fn_en_remark)
                                    <p style="margin-top:1%"><b>Remark: </b>{{ $logs->fn_en_remark }}</p>
                                @endif
                                @if ($logs->fn_en_image)
                                    <b>Image: </b><br><a href="{{ url($baseDirectoryPath . $logs->fn_en_image) }}"
                                        target="_blank"><img src="{{ url($baseDirectoryPath . $logs->fn_en_image) }}"
                                            alt="Fn En Image" style="width: 9%;"></a>
                                @endif
                            </div>
                        @endif

                        @if ($logs->fn_hi_status == 0)
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Full Name (Hindi)</strong></h4>
                                @if ($logs->fn_hi_remark)
                                    <p style="margin-top:1%"><b>Remark: </b>{{ $logs->fn_hi_remark }}</p>
                                @endif
                                @if ($logs->fn_hi_image)
                                    <b>Image: </b><br><a href="{{ url($baseDirectoryPath . $logs->fn_hi_image) }}"
                                        target="_blank"><img src="{{ url($baseDirectoryPath . $logs->fn_hi_image) }}"
                                            alt="Fn Hi Image" style="width: 9%;"></a>
                                @endif
                            </div>
                        @endif

                        @if ($logs->cs_en_status == 0)
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Course Name</strong></h4>
                                @if ($logs->cs_en_remark)
                                    <p style="margin-top:1%"><b>Remark: </b>{{ $logs->cs_en_remark }}</p>
                                @endif
                                @if ($logs->cs_en_image)
                                    <b>Image: </b><br><a href="{{ url($baseDirectoryPath . $logs->cs_en_image) }}"
                                        target="_blank"><img src="{{ url($baseDirectoryPath . $logs->cs_en_image) }}"
                                            alt="Cs En Image" style="width: 9%;"></a>
                                @endif
                            </div>
                        @endif

                        @if ($logs->cs_hi_status == 0)
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Course Name (Hindi)</strong></h4>
                                @if ($logs->cs_hi_remark)
                                    <p style="margin-top:1%"><b>Remark: </b>{{ $logs->cs_hi_remark }}</p>
                                @endif
                                @if ($logs->cs_hi_image)
                                    <b>Image: </b><br><a href="{{ url($baseDirectoryPath . $logs->cs_hi_image) }}"
                                        target="_blank"><img src="{{ url($baseDirectoryPath . $logs->cs_hi_image) }}"
                                            alt="Cs Hi Image" style="width: 9%;"></a>
                                @endif
                            </div>
                        @endif

                        @if ($logs->cgpa_status == 0)
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>CGPA</strong></h4>
                                @if ($logs->cgpa_remark)
                                    <p style="margin-top:1%"><b>Remark: </b>{{ $logs->cgpa_remark }}</p>
                                @endif
                                @if ($logs->cgpa_image)
                                    <b>Image: </b><br><a href="{{ url($baseDirectoryPath . $logs->cgpa_image) }}"
                                        target="_blank"><img src="{{ url($baseDirectoryPath . $logs->cgpa_image) }}"
                                            alt="Cgpa Image" style="width: 9%;"></a>
                                @endif
                            </div>
                        @endif

                        @if ($logs->mn_en_status == 0)
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Mother's Name (English)</strong></h4>
                                @if ($logs->mn_en_remark)
                                    <p style="margin-top:1%"><b>Remark: </b>{{ $logs->mn_en_remark }}</p>
                                @endif
                                @if ($logs->mn_en_image)
                                    <b>Image: </b><br>
                                    <a href="{{ url($baseDirectoryPath . $logs->mn_en_image) }}"
                                        target="_blank">
                                        <img src="{{ url($baseDirectoryPath . $logs->mn_en_image) }}"
                                            alt="Mother Name En Image" style="width: 9%;">
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if ($logs->mn_hi_status == 0)
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Mother's Name (Hindi)</strong></h4>
                                @if ($logs->mn_hi_remark)
                                    <p style="margin-top:1%"><b>Remark: </b>{{ $logs->mn_hi_remark }}</p>
                                @endif
                                @if ($logs->mn_hi_image)
                                    <b>Image: </b><br>
                                    <a href="{{ url($baseDirectoryPath . $logs->mn_hi_image) }}"
                                        target="_blank">
                                        <img src="{{ url($baseDirectoryPath . $logs->mn_hi_image) }}"
                                            alt="Mother Name Hi Image" style="width: 9%;">
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if ($logs->ftn_en_status == 0)
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Father's Name (English)</strong></h4>
                                @if ($logs->ftn_en_remark)
                                    <p style="margin-top:1%"><b>Remark: </b>{{ $logs->ftn_en_remark }}</p>
                                @endif
                                @if ($logs->ftn_en_image)
                                    <b>Image: </b><br>
                                    <a href="{{ url($baseDirectoryPath . $logs->ftn_en_image) }}"
                                        target="_blank">
                                        <img src="{{ url($baseDirectoryPath . $logs->ftn_en_image) }}"
                                            alt="Father Name En Image" style="width: 9%;">
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if ($logs->ftn_hi_status == 0)
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Father's Name (Hindi)</strong></h4>
                                @if ($logs->ftn_hi_remark)
                                    <p style="margin-top:1%"><b>Remark: </b>{{ $logs->ftn_hi_remark }}</p>
                                @endif
                                @if ($logs->ftn_hi_image)
                                    <b>Image: </b><br>
                                    <a href="{{ url($baseDirectoryPath . $logs->ftn_hi_image) }}"
                                        target="_blank">
                                        <img src="{{ url($baseDirectoryPath . $logs->ftn_hi_image) }}"
                                            alt="Father Name Hi Image" style="width: 9%;">
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                    <hr>
                </div>
            @endif

            @if (!$payments->isEmpty())
                <div class="row">
                    <h2><i class="fa fa-credit-card" style="font-size: 30px;    "></i>
                        Payment Details</h2> <!-- Add this line for the heading -->
                    <hr class="my-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment Mode</th>
                                <th>Gateway</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $index => $payment)
                                <tr>
                                    <td>{{ $index + 1 }}</td> <!-- This will show a serial number starting from 1 -->
                                    <td>{{ $payment->txn_id }}</td>
                                    <td>{{ $payment->txn_amount }}</td>
                                    <td>{{ $payment->txn_date->format('Y-m-d h:i A') }}</td> <!-- Format the date here -->
                                    <td>{{ $payment->status == 'TXN_SUCCESS' ? 'SUCCESS' : 'FAILED' }}</td>
                                    <td>{{ $payment->payment_mode }}</td>
                                    <td>{{ $payment->gateway_name }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>

                </div>
            @endif
            <div id="divLoading">
                <p>Processing... Please wait ..... <img src="/backend/images/loading.gif"></p>
            </div>
        </div>
    </div>

@stop

@section('script')

    <script>
        var token = $('meta[name="csrf-token"]').attr('content');

        // Set the token in jQuery AJAX headers
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': token
            }
        });

        $(document).ready(function() {
            $("#student-form").validate({
                rules: {
                    prn: "required",
                    cgpa: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 10
                    },
                    date_of_birth: "required",
                    wpu_email_id: {
                        required: true,
                        email: true
                    },
                    full_name: "required",
                    full_name_hindi: "required",
                    full_name_krutidev: "required",
                    course_name: "required",
                    course_name_hindi: "required",
                    course_name_krutidev: "required",
                    mother_name: "required",
                    // mother_name_hindi: "required",
                    // mother_name_krutidev: "required",
                    father_name: "required",
                    secondary_email_id : "required",
                    gender : "required",
                    // father_name_hindi: "required",
                    // father_name_krutidev: "required",

                    photograph: {
                        required: function() {
                            return $('input[name="photograph"]').data('photo-exists') == false;
                        },
                        extension: "jpg|jpeg|png|gif"
                    }
                },
                messages: {
                    prn: "Please enter PRN",
                    cgpa: {
                        required: "Please enter CGPA",
                        number: "CGPA must be a number",
                        min: "CGPA must be at least 0",
                        max: "CGPA cannot be more than 10"
                    },
                    date_of_birth: "Please select Date of Birth",
                    gender: "Please select gender",
                    wpu_email_id: "Please enter a valid email address",
                    secondary_email_id: "Please enter a valid email address",
                    full_name: "Please enter Full Name",
                    full_name_hindi: "Please enter Full Name in Hindi",
                    full_name_krutidev: "Please enter Full Name in Krutidev",
                    course_name: "Please enter Course Name",
                    course_name_hindi: "Please enter Course Name in Hindi",
                    course_name_krutidev: "Please enter Course Name in Krutidev",
                    mother_name: "Please enter the mother's name",
                    mother_name_hindi: "Please enter the mother's name in Hindi",
                    mother_name_krutidev: "Please enter the mother's name in Krutidev",
                    father_name: "Please enter the father's name",
                    father_name_hindi: "Please enter the father's name in Hindi",
                    father_name_krutidev: "Please enter the father's name in Krutidev",
                    photograph: {
                        required: "Please upload a photograph",
                        extension: "Please upload a valid image file (jpg, jpeg, png, gif)"
                    }
                },
                submitHandler: function(form) {
                    // Show loading overlay
                    $("#divLoading").show();

                    var formData = new FormData($(form)[0]);

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('convo_student.update', $student->id) }}",
                        data: formData,
                        contentType: false,
                        processData: false,
                        datatype: "JSON",
                        success: function(response) {
                            $("#divLoading").hide();

                            // $('.student_request_class').hide();
                            toastr.success('Student updated successfully!')
                            // setTimeout(() => {
                            //     window.location.reload();
                            // }, 2000);
                            // Optionally redirect or update UI
                            // window.location.reload(); // Uncomment to reload the page
                        },
                        error: function(xhr, status, error) {
                            $("#divLoading").hide();
                            toastr.error('Student updated successfully!')
                        }
                    });
                }
            });
        });


        function english_text_change(from_element, to_hindi_element, to_krutidev_element) {
            // $('.loader').removeClass('hidden');
            // e.preventDefault();
            var text = $(from_element).val();
            var convertTo = 'hindi'
            $.ajax({
                url: "<?= route('text-translator.save') ?>",
                type: 'POST',
                data: {
                    text: text,
                    convertTo: convertTo
                },
                dataType: 'json',
                success: function(response) {
                    $(to_hindi_element).val(response.value);
                    hindi_text_change(to_hindi_element, to_krutidev_element);
                    // $('.loader').addClass('hidden');
                },
                error: function() {
                    // alert('An error occurred. Please try again.');
                    // $('.loader').addClass('hidden');
                }
            });
        }


        function hindi_text_change(from_element, to_element) {
            var text = $(from_element).val();
            // $('.loader').removeClass('hidden');
            var convertTo = 'kritidev'
            $.ajax({
                url: "<?= route('text-translator.save') ?>",
                type: 'POST',
                data: {
                    text: text,
                    convertTo: convertTo
                },
                dataType: 'json',
                success: function(response) {
                    $(to_element).val(response.value);
                    // $('.loader').addClass('hidden');

                },
                error: function() {
                    // $('.loader').addClass('hidden');
                    // alert('An error occurred. Please try again.');
                }
            });
        };
    </script>
@stop
