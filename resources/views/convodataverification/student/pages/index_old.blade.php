<?php

// dd(@$student->studentAckLogs->fn_hi_status == 1 || empty(@$student->studentAckLogs));
?>
@extends('convodataverification.student.pages.layout.layout')
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')

    <?php
    $domain = request()->getHost();
    $subdomain = explode('.', $domain);
    
    // Define the path to save the file locally
    // $baseDirectoryPath = $domain . '/'.$subdomain[0].'/'. config('constant.backend') . "/students/";
    
    $baseDirectoryPath = $subdomain[0] . '/' . config('constant.backend') . '/students/';
    $baseDirectoryPath2 =  asset('convodataverification/images/');
    
    $status = $student->status;
    // $status_array = [
    // 'have completed 1st time sign up',
    // 'student acknowledge all data as correct, preview pdf is approved but payment is pending',
    // 'student acknowledge all data as correct, preview pdf is approved and payment completed',
    // 'admin performed correction but student’s re-acknowledgement pending'
    // 'student marked few data as incorrect and admin’s action pending',
    // 'student re-acknowledged new data as correct, preview pdf is approved and payment completed',
    // 'student re-acknowledged new data as correct, preview pdf is approved but payment is pending',
    // 'student re-acknowledged new data as correct, preview pdf is approved and payment completed'
    // ];
    $status_array = ['have completed 1st time sign up', 'have not yet signed up', 'admin performed correction but student’s re-acknowledgement pending'];
    
    $isPdfEligibleForApproval = in_array($status, ['student acknowledge all data as correct but preview pdf approval is pending', 'student re-acknowledged new data as correct but preview pdf approval is pending']);
    $isReadonly = !empty($student->studentAckLogs) && !in_array($status, $status_array);
    // dd($status,$isReadonly);
    $is_admin_action_pending = @$student->status == 'student marked few data as incorrect and admin’s action pending';
    $is_payment_completed = @$student->status == 'student re-acknowledged new data as correct, preview pdf is approved and payment completed' || @$student->status == 'student acknowledge all data as correct, preview pdf is approved and payment completed';
    // dd(in_array($status, $status_array),$status);
    $is_payment_pending = @$student->status == 'student acknowledge all data as correct, preview pdf is approved but payment is pending' || @$student->status == 'student re-acknowledged new data as correct, preview pdf is approved but payment is pending';
    ?>
    @if ($isReadonly)
        <style>
            
            #verificationForm input,
            #verificationForm textarea,
            #verificationForm select
            {
                background-color: #f0f0f0 !important;
                /* Light gray background */
                cursor: not-allowed !important;
                /* Show a not-allowed cursor */
                pointer-events: none !important;
                /* Disable pointer events */
            }

            #verificationForm label {
                cursor: not-allowed !important;
                /* Show a not-allowed cursor */
                pointer-events: none !important;
            }
        </style>
    @endif

    @if ($is_payment_completed || $isPdfEligibleForApproval)
        <style>
            .on_complete {
                display: none !important;

            }
        </style>
    @endif
    <style>
        .conditional-fields {
                display: none;
            }

        .hidden_1 {
            display: none;
        }

        .fields_div {
            border: 1px solid #d3d3d3;
            /* Light gray color */
            margin-bottom: 10px;
            padding-top: 1%;
        }

        .guidelines {
            border: 1px solid #d3d3d3;
            /* Light gray color */
            margin-bottom: 10px;
            padding: 15px;
        }
    </style>

    <div class="container mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><i class="fa fa-user"></i> Verify Details</h1>
                </div>
                <div class="col-lg-12">
                    <form id="verificationForm" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if (!empty(@$student->studentAckLogs))
                            @if ($is_admin_action_pending)
                                <div class="row"
                                    style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                    <h2 style="color:red">Admin’s action pending</h2>
                                </div>
                            @endif
                            @if (@$student->status == 'admin performed correction but student’s re-acknowledgement pending')
                                <div class="row"
                                    style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                    <h2 style="color:#e57a62">Admin performed correction but student’s re-acknowledgement
                                        pending</h2>
                                </div>
                            @endif

                            <div class="row is_payment_pending {{ !$is_payment_pending ? 'hidden_1' : '' }}"
                                style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                <h2 style="color:#e57a62"> Payment is Pending</h2>
                            </div>



                            <div class="row is_payment_completed {{ !$is_payment_completed ? 'hidden_1' : '' }}"
                                style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                <h2 style="color:#62e562"> Payment is Completed</h2>
                            </div>


                            <div class="row isPdfEligibleForApproval {{ !$isPdfEligibleForApproval ? 'hidden_1' : '' }}"
                                style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                <h2 style="color:#e57a62"> Please approve pdf preview</h2>
                            </div>



                        @endif
                        <!-- PRN Section -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="border p-3" style="font-size: 20px">
                                    <label for="prn">PRN: {{ @$student->prn }}</p></label>
                                </div>
                            </div>
                        </div>

                        <!-- Full Name Section (English) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="full_name">Full Name (English):</label>
                                    <p id="full_name" class="form-control-plaintext">{{ @$student->full_name }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="full_name_correct" name="full_name_correct" value="1"
                                        {{ @$student->studentAckLogs->fn_en_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="full_name_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="full_name_incorrect" name="full_name_correct" value="0"
                                        {{ @$student->studentAckLogs->fn_en_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="full_name_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="full_name_correction_remarks">Correction Remarks:</label>
                                    <textarea id="full_name_correction_remarks" name="full_name_correction_remarks" class="form-control">{{ @$student->studentAckLogs->fn_en_remark }}</textarea>
                                    <input type="file" name="full_name_correction_file" class="form-control "
                                        style="margin-top: 1%" accept="image/*">
                                    @if (@$student->studentAckLogs->fn_en_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->fn_en_image) }}">
                                            {{ @$student->studentAckLogs->fn_en_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Full Name Section (Hindi) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="full_name_hindi">नाम(Hindi):</label>
                                    <p id="full_name_hindi" class="form-control-plaintext">{{ @$student->full_name_hindi }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="full_name_hindi_correct" name="full_name_hindi_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->fn_hi_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="full_name_hindi_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="full_name_hindi_incorrect" name="full_name_hindi_correct"
                                        value="0" {{ @$student->studentAckLogs->fn_hi_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="full_name_hindi_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="full_name_hindi_correction_remarks">Correction Remarks:</label>
                                    <textarea id="full_name_hindi_correction_remarks" name="full_name_hindi_correction_remarks" class="form-control">{{ @$student->studentAckLogs->fn_hi_remark }}</textarea>
                                    <input type="file" name="full_name_hindi_correction_file" class="form-control mt-2"
                                        style="margin-top: 1%" accept="image/*">

                                    @if (@$student->studentAckLogs->fn_hi_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->fn_hi_image) }}">
                                            {{ @$student->studentAckLogs->fn_hi_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Mother Name Section (English) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="mother_name">Mother's Name (English):</label>
                                    <input onchange="english_text_change(this,'#mother_name_hindi')" class="form-control"
                                        id="mother_name" name="mother_name" type="text"
                                        value="{{ @$student->mother_name }}">
                                    {{-- <p id="mother_name" class="form-control-plaintext">{{ @$student->mother_name }}</p> --}}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="mother_name_correct" name="mother_name_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->mn_en_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="mother_name_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="mother_name_incorrect" name="mother_name_correct"
                                        value="0"
                                        {{ @$student->studentAckLogs->mn_en_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="mother_name_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="mother_name_correction_remarks">Correction Remarks:</label>
                                    <textarea id="mother_name_correction_remarks" name="mother_name_correction_remarks" class="form-control">{{ @$student->studentAckLogs->mn_en_remark }}</textarea>
                                    <input type="file" name="mother_name_correction_file" class="form-control mt-2"
                                        style="margin-top: 1%" accept="image/*">
                                    @if (@$student->studentAckLogs->mn_en_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->mn_en_image) }}">
                                            {{ @$student->studentAckLogs->mn_en_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Mother Name Section (Hindi) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="mother_name_hindi">माँ का नाम (Hindi):</label>
                                    <input class="form-control" id="mother_name_hindi" name="mother_name_hindi" readonly
                                        type="text" value="{{ @$student->mother_name_hindi }}">

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="mother_name_hindi_correct" name="mother_name_hindi_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->mn_hi_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="mother_name_hindi_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="mother_name_hindi_incorrect"
                                        name="mother_name_hindi_correct" value="0"
                                        {{ @$student->studentAckLogs->mn_hi_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="mother_name_hindi_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="mother_name_hindi_correction_remarks">Correction Remarks:</label>
                                    <textarea id="mother_name_hindi_correction_remarks" name="mother_name_hindi_correction_remarks" class="form-control">{{ @$student->studentAckLogs->mn_hi_remark }}</textarea>
                                    <input type="file" name="mother_name_hindi_correction_file"
                                        class="form-control mt-2" style="margin-top: 1%" accept="image/*">
                                    @if (@$student->studentAckLogs->mn_hi_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->mn_hi_image) }}">
                                            {{ @$student->studentAckLogs->mn_hi_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Father Name Section (English) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="father_name">Father's Name (English):</label>
                                    <p id="father_name" class="form-control-plaintext">{{ @$student->father_name }}</p>
                                    <input class="form-control" onchange="english_text_change(this,'#father_name_hindi')"
                                        id="father_name" name="father_name" type="text"
                                        value="{{ @$student->father_name }}">

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="father_name_correct" name="father_name_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->ftn_en_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="father_name_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="father_name_incorrect" name="father_name_correct"
                                        value="0"
                                        {{ @$student->studentAckLogs->ftn_en_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="father_name_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="father_name_correction_remarks">Correction Remarks:</label>
                                    <textarea id="father_name_correction_remarks" name="father_name_correction_remarks" class="form-control">{{ @$student->studentAckLogs->ftn_en_remark }}</textarea>
                                    <input type="file" name="father_name_correction_file" class="form-control mt-2"
                                        style="margin-top: 1%" accept="image/*">
                                    @if (@$student->studentAckLogs->ftn_en_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->ftn_en_image) }}">
                                            {{ @$student->studentAckLogs->ftn_en_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Father Name Section (Hindi) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="father_name_hindi">पिता का नाम (Hindi):</label>
                                    <input class="form-control" id="father_name_hindi" name="father_name_hindi" readonly
                                        type="text" value="{{ @$student->father_name_hindi }}">

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="father_name_hindi_correct" name="father_name_hindi_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->ftn_hi_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="father_name_hindi_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="father_name_hindi_incorrect"
                                        name="father_name_hindi_correct" value="0"
                                        {{ @$student->studentAckLogs->ftn_hi_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="father_name_hindi_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="father_name_hindi_correction_remarks">Correction Remarks:</label>
                                    <textarea id="father_name_hindi_correction_remarks" name="father_name_hindi_correction_remarks" class="form-control">{{ @$student->studentAckLogs->ftn_hi_remark }}</textarea>
                                    <input type="file" name="father_name_hindi_correction_file"
                                        class="form-control mt-2" style="margin-top: 1%" accept="image/*">
                                    @if (@$student->studentAckLogs->ftn_hi_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->ftn_hi_image) }}">
                                            {{ @$student->studentAckLogs->ftn_hi_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Program Section (English) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="program_english">Program (English):</label>
                                    <p id="program_english" class="form-control-plaintext">{{ @$student->course_name }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="program_english_correct" name="program_english_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->cs_en_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="program_english_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="program_english_incorrect" name="program_english_correct"
                                        value="0"
                                        {{ @$student->studentAckLogs->cs_en_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="program_english_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="program_english_correction_remarks">Correction Remarks:</label>
                                    <textarea id="program_english_correction_remarks" name="program_english_correction_remarks" class="form-control">{{ @$student->studentAckLogs->cs_en_remark }}</textarea>
                                    <input type="file" name="program_english_correction_file"
                                        class="form-control mt-2" style="margin-top: 1%" accept="image/*">

                                    @if (@$student->studentAckLogs->cs_en_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->cs_en_image) }}">
                                            {{ @$student->studentAckLogs->cs_en_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Program Section (Hindi) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="program_hindi">प्रोग्राम (Hindi):</label>
                                    <p id="program_hindi" class="form-control-plaintext">
                                        {{ @$student->course_name_hindi }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="program_hindi_correct" name="program_hindi_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->cs_hi_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="program_hindi_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="program_hindi_incorrect" name="program_hindi_correct"
                                        value="0"
                                        {{ @$student->studentAckLogs->cs_hi_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="program_hindi_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="program_hindi_correction_remarks">Correction Remarks:</label>
                                    <textarea id="program_hindi_correction_remarks" name="program_hindi_correction_remarks" class="form-control">{{ @$student->studentAckLogs->cs_hi_remark }}</textarea>
                                    <input type="file" name="program_hindi_correction_file" class="form-control mt-2"
                                        style="margin-top: 1%" accept="image/*">

                                    @if (@$student->studentAckLogs->cs_hi_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->cs_hi_image) }}">
                                            {{ @$student->studentAckLogs->cs_hi_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- CGPA Section -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="cgpa">CGPA:</label>
                                    <p id="cgpa" class="form-control-plaintext">{{ @$student->cgpa }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="cgpa_correct" name="cgpa_correct" value="1"
                                        {{ @$student->studentAckLogs->cgpa_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="cgpa_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="cgpa_incorrect" name="cgpa_correct" value="0"
                                        {{ @$student->studentAckLogs->cgpa_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="cgpa_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="cgpa_correction_remarks">Correction Remarks:</label>
                                    <textarea id="cgpa_correction_remarks" name="cgpa_correction_remarks" class="form-control">{{ @$student->studentAckLogs->cgpa_remark }}</textarea>
                                    <input type="file" name="cgpa_correction_file" class="form-control mt-2"
                                        style="margin-top: 1%" accept="image/*">
                                    @if (@$student->studentAckLogs->cgpa_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->cgpa_image) }}">
                                            {{ @$student->studentAckLogs->cgpa_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="secondary_email">Secondary Email ID:</label>
                                    <input type="email" class="form-control" id="secondary_email_id"
                                        name="secondary_email_id"
                                        value="{{ old('secondary_email_id', $student->secondary_email_id) }}" required>

                                    <p id="secondary_email" class="form-control-plaintext">
                                        {{ @$student->secondary_email }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" id="email_correct" name="email_correct" value="1"
                                        {{ @$student->studentAckLogs->se_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="email_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" id="email_incorrect" name="email_correct" value="0"
                                        {{ @$student->studentAckLogs->se_status === 0 ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="email_incorrect">Incorrect</label>
                                </div>
                            </div>
                            <div class="col-md-6 remark-upload-section">
                                <div class="form-group border p-3">
                                    <label for="secondary_email_correction_remarks">Correction Remarks:</label>
                                    <textarea id="secondary_email_correction_remarks" name="secondary_email_correction_remarks" class="form-control">{{ @$student->studentAckLogs->se_remark }}</textarea>
                                    <input type="file" name="secondary_email_correction_file"
                                        class="form-control mt-2" style="margin-top: 1%" accept="image/*">
                                    @if (@$student->studentAckLogs->se_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->se_image) }}">
                                            {{ @$student->studentAckLogs->se_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="gender">Gender:</label>
                                    <div class="form-group">
                                        @if (@$student->gender == 'M')
                                            MALE
                                        @endif
                                        @if (@$student->gender == 'F')
                                            FEMALE
                                        @endif
                                        @if (@$student->gender == 'O')
                                            OTHER
                                        @endif
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="row mb-4 fields_div">
                            <div class="col-md-12">
                                <div class="form-group border p-3">
                                    <label for="mode">Mode of Collection of Degree Certificate:</label>
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="collection_mode"
                                                id="in_person" value="Attending Convocation" @if($student->collection_mode == 'Attending Convocation') checked @endif>
                                            <label class="form-check-label" for="in_person">
                                                Attending Convocation In Person (Rs.3000/-)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="collection_mode"
                                                id="by_post" value="By Post" @if($student->collection_mode == 'By Post') checked @endif>
                                            <label class="form-check-label" for="by_post">
                                                Receiving Degree Certificate by Post (Rs.500/-)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div id="in_person_details" class=" row mb-4 fields_div conditional-fields"  style="@if($student->collection_mode == 'Attending Convocation') display:block !important;  @endif">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="attire_size">Choose Attire Size:</label>
                                    @if(@$student->gender == "M")
                                    <select class="form-control" id="attire_size" name="attire_size">
                                        <option value="">Select Size</option>
                                        <option value="X-Small" {{ @$student->attire_size == 'X-Small' ? 'selected' : '' }}>X-Small</option>
                                        <option value="Small" {{ @$student->attire_size == 'Small' ? 'selected' : '' }}>Small</option>
                                        <option value="Medium" {{ @$student->attire_size == 'Medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="Large" {{ @$student->attire_size == 'Large' ? 'selected' : '' }}>Large</option>
                                        <option value="2X-Large" {{ @$student->attire_size == '2X-Large' ? 'selected' : '' }}>2X-Large</option>
                                        <option value="3X-Large" {{ @$student->attire_size == '3X-Large' ? 'selected' : '' }}>3X-Large</option>
                                        <option value="4X-Large" {{ @$student->attire_size == '4X-Large' ? 'selected' : '' }}>4X-Large</option>
                                    </select>
                                    <br>
                                    <a    href="{{ url($baseDirectoryPath2 . '/size_chart_women.png') }}" target="_blank">Click here to view the size chart</a>

                                    @elseif (@$student->gender == "M")
                                    <select class="form-control" id="attire_size" name="attire_size">
                                        <option value="">Select Size</option>
                                        <option value="S" {{ @$student->attire_size == 'S' ? 'selected' : '' }}>S</option>
                                        <option value="M" {{ @$student->attire_size == 'M' ? 'selected' : '' }}>M</option>
                                        <option value="L" {{ @$student->attire_size == 'L' ? 'selected' : '' }}>L</option>
                                        <option value="XL" {{ @$student->attire_size == 'XL' ? 'selected' : '' }}>XL</option>
                                        <option value="2XL" {{ @$student->attire_size == '2XL' ? 'selected' : '' }}>2XL</option>
                                        <option value="3XL" {{ @$student->attire_size == '3XL' ? 'selected' : '' }}>3XL</option>
                                    </select>
                                    
                                    <br>
                                    <a   href="{{ url($baseDirectoryPath2 . '/size_chart_men.png') }}" target="_blank">Click here to view the size chart</a>

                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Conditional Fields for Delivery Address -->

                        <div id="by_post_details" class="row mb-4 fields_div conditional-fields" style="@if($student->collection_mode == 'By Post') display:block !important;  @endif">
                            <div class="col-md-6">
                                <div class="form-group border p-3">
                                    <label for="delivery_address">Enter Delivery Address:</label>
                                    <textarea class="form-control" name="delivery_address" id="delivery_address" cols="50" rows="4" placeholder="Street Address, City, State/Province">{{@$student->delivery_address}}</textarea>
                                </div> 
                            </div> 
                                    <div class="col-md-3">
                                        <label for="postal_code">Pincode:</label>
                                        <input type="text" value="{{@$student->delivery_pincode}}" class="form-control" id="delivery_pincode" name="delivery_pincode" placeholder="Pincode">
                                    </div>
                        
                                    <div class="col-md-3">
                                        <label for="delivery_country">Country:</label>
                                        <select class="form-control" id="delivery_country" name="delivery_country" value="{{@$student->delivery_country}}" >
                                            <option value="">Select Country</option>
                                            @foreach($countries as  $value)
                                            <option value="{{ $value->name }}" {{  @$student->delivery_country ==  $value->name ? 'selected' : '' }}>
                                                {{$value->name}}
                                            </option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                        <div class="row mb-4 guidelines">
                            <div class="col-md-12">
                                <div class="form-group border p-3">


                                    <label for="photograph">Photograph:</label>
                                    <br>

                                    @if (@$student->student_photo)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->student_photo) }}">
                                            <img src="{{ url($baseDirectoryPath . $student->student_photo) }}"
                                                style="width:20%" alt="">
                                        </a><br>
                                    @endif
                                    <input type="file" id="photograph" style="width: 30%;" name="photograph"
                                        @if ($student->student_photo) data-photo-exists="true" @else data-photo-exists="false" @endif
                                        class="form-control on_complete" accept="image/*">
                                    <br>
                                    <small class="form-text text-muted mt-2 on_complete">
                                        <strong>Guidelines:</strong><br>
                                        - File size should not exceed 500 KB.<br>
                                        - Photograph should cover at least 80% of your face.<br>
                                        - Use a formal photograph with a plain background.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-primary submit_button mt-4 {{ !(!$isReadonly && !$is_payment_pending) ? 'hidden_1' : '' }}">Submit</button>



                        <a href="{{ route('convo_student.payment') }}"
                            class="btn btn-primary mt-4 make_payment_button {{ !$is_payment_pending ? 'hidden_1' : '' }}">Make
                            Payment</a>


                    </form>
                    <div class="row isPdfEligibleForApproval {{ !$isPdfEligibleForApproval ? 'hidden_1' : '' }}"
                        style="margin-bottom:2%; padding:2%; border: 1px solid #d3d3d3; ">
                        <div class="col-md-12 ">
                            <a style="margin-right:2%;" href="{{ route('mitwpu.pdf-view', ['prnNo' => $student->prn]) }}"
                                target="_blank" class="">
                                <i class="fa fa-file-pdf-o"></i> Click here to view PDF
                            </a>


                            <a class="btn btn-primary" id="approve_pdf">
                                Approve pdf
                            </a>
                        </div>


                    </div>
                </div>
            </div>
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
        </div>
    </div>



    <!-- Confirmation Modal -->
    <!-- Bootstrap 4 Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog"
        aria-labelledby="confirmationModalLabel" aria-hidden_1="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Submission</h5>
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden_1="true">&times;</span>
                    </button> --}}
                </div>
                <div class="modal-body">
                    Your request will be forwarded to the administrator. You will not be able to make any changes until it
                    is reviewed. Do you want to proceed?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Submit</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal HTML -->
    <div class="modal fade" id="confirmationApprovePdf" tabindex="-1" role="dialog"
        aria-labelledby="updateModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Approve Pdf Preview</h5>
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
           </button> --}}
                </div>
                <div class="modal-body">
                    Are you sure you want to approve the pdf?
                </div>
                <div class="modal-footer">
                    <button id="pdfApproveButton" type="button" class="btn btn-primary">Yes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


@stop

@section('script')
    <script>
        $(document).ready(function() {

            var token = $('meta[name="csrf-token"]').attr('content');

            // Set the token in jQuery AJAX headers
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
            $('#verificationForm').validate({
                rules: {
                    'full_name_correct': {
                        required: true
                    },
                    'full_name_correction_remarks': {
                        required: function(element) {
                            return $('input[name="full_name_correct"]:checked').val() == 0 && !$(
                                'input[name="full_name_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'full_name_correction_file': {
                        required: function(element) {
                            return $('input[name="full_name_correct"]:checked').val() == 0 && !$(
                                '#full_name_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },
                    'full_name_hindi_correct': {
                        required: true
                    },
                    'full_name_hindi_correction_remarks': {
                        required: function(element) {
                            return $('input[name="full_name_hindi_correct"]:checked').val() == 0 && !$(
                                'input[name="full_name_hindi_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'full_name_hindi_correction_file': {
                        required: function(element) {
                            return $('input[name="full_name_hindi_correct"]:checked').val() == 0 && !$(
                                '#full_name_hindi_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },
                    'program_english_correct': {
                        required: true
                    },
                    'program_english_correction_remarks': {
                        required: function(element) {
                            return $('input[name="program_english_correct"]:checked').val() == 0 && !$(
                                'input[name="program_english_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'program_english_correction_file': {
                        required: function(element) {
                            return $('input[name="program_english_correct"]:checked').val() == 0 && !$(
                                '#program_english_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },
                    'program_hindi_correct': {
                        required: true
                    },
                    'program_hindi_correction_remarks': {
                        required: function(element) {
                            return $('input[name="program_hindi_correct"]:checked').val() == 0 && !$(
                                'input[name="program_hindi_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'program_hindi_correction_file': {
                        required: function(element) {
                            return $('input[name="program_hindi_correct"]:checked').val() == 0 && !$(
                                '#program_hindi_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },
                    'cgpa_correct': {
                        required: true
                    },
                    'cgpa_correction_remarks': {
                        required: function(element) {
                            return $('input[name="cgpa_correct"]:checked').val() == 0 && !$(
                                'input[name="cgpa_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'cgpa_correction_file': {
                        required: function(element) {
                            return $('input[name="cgpa_correct"]:checked').val() == 0 && !$(
                                '#cgpa_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },
                    'email_correct': {
                        required: true
                    },
                    'secondary_email_correction_remarks': {
                        required: function(element) {
                            // Remarks are required if the email is marked as incorrect and no file is uploaded
                            return $('input[name="email_correct"]:checked').val() == 0 && !$(
                                'input[name="secondary_email_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'secondary_email_correction_file': {
                        required: function(element) {
                            // File is required if the email is marked as incorrect and no remarks are provided
                            return $('input[name="email_correct"]:checked').val() == 0 && !$(
                                '#secondary_email_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },
                    'photograph': {
                        required: function() {
                            return $('input[name="photograph"]').data('photo-exists') == false;
                        },
                        extension: "jpg|jpeg|png"
                    },
                    // Mother Name Validation (English)
                    'mother_name_correct': {
                        required: true
                    },
                    'mother_name_correction_remarks': {
                        required: function(element) {
                            return $('input[name="mother_name_correct"]:checked').val() == 0 && !$(
                                'input[name="mother_name_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'mother_name_correction_file': {
                        required: function(element) {
                            return $('input[name="mother_name_correct"]:checked').val() == 0 && !$(
                                '#mother_name_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },

                    // Mother Name Validation (Hindi)
                    'mother_name_hindi_correct': {
                        required: true
                    },
                    'mother_name_hindi_correction_remarks': {
                        required: function(element) {
                            return $('input[name="mother_name_hindi_correct"]:checked').val() == 0 && !
                                $(
                                    'input[name="mother_name_hindi_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'mother_name_hindi_correction_file': {
                        required: function(element) {
                            return $('input[name="mother_name_hindi_correct"]:checked').val() == 0 && !
                                $(
                                    '#mother_name_hindi_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },

                    // Father Name Validation (English)
                    'father_name_correct': {
                        required: true
                    },
                    'father_name_correction_remarks': {
                        required: function(element) {
                            return $('input[name="father_name_correct"]:checked').val() == 0 && !$(
                                'input[name="father_name_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'father_name_correction_file': {
                        required: function(element) {
                            return $('input[name="father_name_correct"]:checked').val() == 0 && !$(
                                '#father_name_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },

                    // Father Name Validation (Hindi)
                    'father_name_hindi_correct': {
                        required: true
                    },
                    'father_name_hindi_correction_remarks': {
                        required: function(element) {
                            return $('input[name="father_name_hindi_correct"]:checked').val() == 0 && !
                                $(
                                    'input[name="father_name_hindi_correction_file"]').val();
                        },
                        minlength: 1
                    },
                    'father_name_hindi_correction_file': {
                        required: function(element) {
                            return $('input[name="father_name_hindi_correct"]:checked').val() == 0 && !
                                $(
                                    '#father_name_hindi_correction_remarks').val();
                        },
                        extension: "jpg|jpeg|png|pdf"
                    },
                    'father_name': {
                        required: true
                    },
                    'mother_name': {
                        required: true
                    },
                    collection_mode: {
                        required: true
                    },
                    attire_size: {
                        required: {
                            depends: function(element) {
                                return $('input[name="collection_mode"]:checked').val() === 'Attending Convocation';
                            }
                        }
                    },
                    delivery_address: {
                        required: {
                            depends: function(element) {
                                return $('input[name="collection_mode"]:checked').val() === 'By Post';
                            }
                        }
                    },
                    delivery_pincode: {
                        required: {
                            depends: function(element) {
                                return $('input[name="collection_mode"]:checked').val() === 'By Post';
                            }
                        }
                    },
                    delivery_country: {
                        required: {
                            depends: function(element) {
                                return $('input[name="collection_mode"]:checked').val() === 'By Post';
                            }
                        }
                    }
                    // 'gender': {
                    //     required: true
                    // },
                },
                messages: {
                    'full_name_correct': "Please specify if the full name is correct.",
                    'full_name_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'full_name_correction_file': {
                        required: "Please provide a image file if the full name is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'full_name_hindi_correct': "Please specify if the full name in Hindi is correct.",
                    'full_name_hindi_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'full_name_hindi_correction_file': {
                        required: "Please provide a image file if the full name in Hindi is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'program_english_correct': "Please specify if the program in English is correct.",
                    'program_english_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'program_english_correction_file': {
                        required: "Please provide a image file if the program in English is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'program_hindi_correct': "Please specify if the program in Hindi is correct.",
                    'program_hindi_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'program_hindi_correction_file': {
                        required: "Please provide a image file if the program in Hindi is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'cgpa_correct': "Please specify if the CGPA is correct.",
                    'cgpa_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'cgpa_correction_file': {
                        required: "Please provide a image file if the CGPA is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'email_correct': "Please specify if the secondary email is correct.",
                    'secondary_email_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'secondary_email_correction_file': {
                        required: "Please provide an image file if the secondary email is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'photograph': {
                        required: "Please upload a photograph.",
                        extension: "Only jpg, jpeg, and png files are allowed."
                    },
                    'mother_name_correct': "Please specify if the mother's name in English is correct.",
                    'mother_name_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'mother_name_correction_file': {
                        required: "Please provide an image file if the mother's name in English is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'mother_name_hindi_correct': "Please specify if the mother's name in Hindi is correct.",
                    'mother_name_hindi_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'mother_name_hindi_correction_file': {
                        required: "Please provide an image file if the mother's name in Hindi is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'father_name_correct': "Please specify if the father's name in English is correct.",
                    'father_name_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'father_name_correction_file': {
                        required: "Please provide an image file if the father's name in English is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'father_name_hindi_correct': "Please specify if the father's name in Hindi is correct.",
                    'father_name_hindi_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'father_name_hindi_correction_file': {
                        required: "Please provide an image file if the father's name in Hindi is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'mother_name': {
                        required: "Please enter the mother's name",
                    },
                    'father_name': {
                        required: "Please enter the father's name",
                    },
                    'gender': {
                        required: "Please select gender",
                    },
                    'collection_mode': "Please select a mode of collection.",
                    'attire_size': "Please select your attire size.",
                    'delivery_address': "Please enter your delivery address.",
                    'delivery_pincode': "Please enter your pincode.",
                    'delivery_country': "Please select your country."
                },
                errorPlacement: function(error, element) {
                    if (element.is(":radio") || element.is(":checkbox")) {
                        // Place the error message after the last element in the group
                            error.insertAfter(element.closest('.form-group').last());
                        } else {
                            // Default placement for other elements
                            error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {
                    // Show confirmation modal
                    $('#confirmationModal').modal('show');

                    // Handle confirmation
                    $('#confirmSubmit').on('click', function() {
                        // Disable form elements to prevent further changes
                        // $(form).find(':input').prop('disabled', true);

                        // Create a FormData object to handle file uploads
                        var formData = new FormData(form);

                        // Perform the AJAX request
                        $.ajax({
                            url: "<?= route('convo_student.verify_details') ?>", // URL where the form will be submitted
                            type: 'POST', // Method type
                            data: formData, // Data to be sent to the server
                            processData: false, // Prevent jQuery from automatically transforming the data into a query string
                            contentType: false, // Prevent setting content type header
                            success: function(response) {
                                // Handle the response from the server 
                                toastr.success("Form submitted successfully!");
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            },
                            error: function(xhr, status, error) {
                                // Handle errors here
                                toastr.error(
                                    "An error occurred while submitting the form."
                                );
                                console.log(xhr.responseText);
                            }
                        });

                        // Hide the confirmation modal
                        $('#confirmationModal').modal('hide');
                    });

                    // Prevent the form from submitting until confirmation is given
                    return false;
                }
            });




        });

        function hideRemarkUpload(element) {
            // alert(1);
            // Get the value of the selected radio button
            const value = element.value;

            // Find the nearest '.fields_div' container that is a parent of the clicked radio button
            const fieldsDiv = element.closest('.fields_div');


            // Find the '.remark-upload-section' within this container
            const remarkUploadSection = fieldsDiv.querySelector('.remark-upload-section');

            // Hide or show the section based on the selected radio button value
            if (value === '1') {
                remarkUploadSection.style.display = 'none';
            } else {
                remarkUploadSection.style.display = 'block';
            }
        }

        // Initial call to set the correct visibility on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Iterate over each radio button group
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                // If a radio button is checked, adjust visibility accordingly
                if (radio.checked) {
                    hideRemarkUpload(radio);
                }
            });
        });

        function english_text_change(from_element, to_hindi_element) {
            // $('.loader').removeClass('hidden_1');
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
                    // $('.loader').addClass('hidden_1');
                },
                error: function() {
                    // alert('An error occurred. Please try again.');
                    // $('.loader').addClass('hidden_1');
                }
            });
        }

        $('#approve_pdf').on('click', function() {
            // Show the modal
            $('#confirmationApprovePdf').modal('show');
        });

        // Handle the confirmation click
        $('#pdfApproveButton').on('click', function() {
            // Close the modal
            $('#confirmationApprovePdf').modal('hide');

            // Perform the AJAX call
            $.ajax({
                url: '{{ route('convo_student.approve_pdf_preview') }}', // Replace with your server endpoint
                method: 'GET', // or 'GET', depending on your needs
                data: {
                    student_id: "{{ @$student->id }}"
                },
                success: function(response) {
                    if (response.success) {
                        // On success, hide and show elements
                        $('.isPdfEligibleForApproval').hide();
                        $('.is_payment_pending').show();
                        $('.make_payment_button').show();
                        toastr.success("Pdf Preview Approved successfully!");
                    } else {
                        // Handle any error messages
                        alert('An error occurred: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle errors here
                    console.error('An error occurred:', error);
                }
            });
        });

        $('input[name="collection_mode"]').change(function() {
            if ($(this).val() === 'Attending Convocation') {
                $('#in_person_details').show();
                $('#by_post_details').hide();
            } else if ($(this).val() === 'By Post') {
                $('#in_person_details').hide();
                $('#by_post_details').show();
            }
        });
    </script>
@stop
