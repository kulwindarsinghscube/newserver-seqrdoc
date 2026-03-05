<?php
use Carbon\Carbon;

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
    if ($subdomain[0] == 'convocation') {
        $subdomain[0] = 'mitwpu';
    }
    $baseDirectoryPath = $subdomain[0] . '/' . config('constant.backend') . '/students/';
    $baseDirectoryPath2 = asset('backend/convodataverification/images/');
    
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
    
    $isPdfEligibleForApproval = in_array($status, ['student acknowledge all data as correct, Payment is completed but preview pdf approval is pending', 'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending']);
    $isReadonly = !empty($student->studentAckLogs) && !in_array($status, $status_array);
    // dd($status,$isReadonly);
    $is_admin_action_pending = @$student->status == 'student marked few data as incorrect and admin’s action pending';
    $is_payment_completed = @$student->status == 'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending' || @$student->status == 'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending';
    // dd(in_array($status, $status_array),$status);
    $is_payment_pending = @$student->status == 'student acknowledge all data as correct but payment & preview pdf approval is pending' || @$student->status == 'student re-acknowledged new data as correct but payment & preview pdf approval is pending';
 
    $isPdfApproved = in_array($status, ['student re-acknowledged new data as correct, Payment is completed and preview pdf is approved', 'student acknowledge all data as correct, Payment is completed and preview pdf is approved']);
    ?>
    @if ($isReadonly)
        <style>
            #verificationForm input,
            #verificationForm textarea,
            #verificationForm select {
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

    @if ($is_payment_completed || $isPdfEligibleForApproval || $isPdfApproved)
        <style>
            .on_complete {
                display: none !important;

            }
        </style>
    @endif
    <style>
        @media (max-width: 576px) {

            .radio_button_lable {
                font-size: 13px;
                /* Adjust button font size */
            }

            .attire_size_image{
                width: 100%;
            }
        }

        embed {
            width: 100%;
            height:700px;
        }

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

                        {{-- @if (!empty(@$student->studentAckLogs)) --}}
                            @if ($is_admin_action_pending)
                                <div class="row"
                                    style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                    <h4 style="color:red">Status : Admin’s action pending</h4>
                                </div>
                            @endif
                            @if (@$student->status == 'admin performed correction but student’s re-acknowledgement pending')
                                <div class="row"
                                    style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                    <h4 style="color:#e57a62">Status : Admin performed correction but student’s
                                        re-acknowledgement
                                        pending</h4>
                                </div>
                            @endif

                            <div class="row is_payment_pending {{ !$is_payment_pending ? 'hidden_1' : '' }}"
                                style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                <h4 style="color:#e57a62"> Status : Payment is Pending</h4>

                                @if(@$is_transaction_pending)
                                <div class="row"
                                        style="text-align: center;  margin-bottom: 12px; padding-bottom: 8px !important;">
                                        <h4 style="color:#FF9800;font-size: 14px;">We are currently awaiting the payment status. Once it is confirmed, you will be able to view the preview PDF.</h4>
                                    </div>
                                @elseif (@$is_transaction_failed)
                                    <div class="row"
                                    style="text-align: center;  margin-bottom: 12px; padding-bottom: 8px !important;">
                                    <h4 style="color:#ee390c;font-size: 14px;">Your most recent payment transaction was unsuccessful. Kindly attempt the payment once more and confirm the preview PDF to finalize your registration.</h4>
                                </div>
    
                            @endif
                            </div>



                            <div class="row is_payment_completed {{ !$is_payment_completed ? 'hidden_1' : '' }}"
                                style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                <h4 style="color:#eea959">Status : Payment is completed, but PDF approval is still pending.
                                </h4>
                            </div>

                            <div class="row isPdfApproved {{ !$isPdfApproved ? 'hidden_1' : '' }}"
                                style="text-align: center; border: 1px solid #d3d3d3; margin-bottom: 12px; padding-bottom: 10px !important;">
                                <h4 style="color:#55c047"> Status : {{ $status }}</h4>
                            </div>


                        {{-- @endif --}}

                       
                        @if(!empty($student->correction_message))
                            <div class="row mb-4 fields_div on_complete">
                                <div class="col-md-12"> 
                                    <div class="row" style="text-align: center; margin-bottom: 12px; padding-bottom: 0px !important; color:red">
                                        <p style="color: #FF9800; font-size: 18px; text-align: justify; line-height: 1.5; padding: 2%;">
                                                <i class="fa fa-warning" style="margin-right: 2px;" ></i> Message from Admin:
                                                <i style="color: #32404D;    font-size: 14px;">{{  $student->correction_message  }}</i>
                                            </p>  
                                    </div> 
                                </div>
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
                                    <label for="full_name">Name As per Tc (English):</label>
                                    <p id="full_name" class="form-control-plaintext">{{ @$student->full_name }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" class="radio_option" id="full_name_correct" name="full_name_correct" value="1"
                                        {{ @$student->studentAckLogs->fn_en_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="full_name_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="full_name_incorrect" name="full_name_correct" value="0"
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
                                         <p style="color:orange;">Grade Card or Aadhaar Card is the preferred option.</p>
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
                                    <label for="full_name_hindi">Name As per Tc(Hindi):</label>
                                    <p id="full_name_hindi" class="form-control-plaintext">{{ @$student->full_name_hindi }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" class="radio_option" id="full_name_hindi_correct" name="full_name_hindi_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->fn_hi_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="full_name_hindi_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="full_name_hindi_incorrect" name="full_name_hindi_correct"
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
                                         <p style="color:orange;">Grade Card or Aadhaar Card is the preferred option.</p>

                                    @if (@$student->studentAckLogs->fn_hi_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->fn_hi_image) }}">
                                            {{ @$student->studentAckLogs->fn_hi_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- Father Name Section (English) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="father_name">Father's First Name (English):</label>
                                    <p  class="form-control-plaintext">{{ @$student->father_name }}</p>
                                    <input class="form-control" onchange="english_text_change(this,'#father_name_hindi')"
                                        id="father_name" name="father_name" type="text"
                                        value="{{ @$student->father_name }}">

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" class="radio_option" id="father_name_correct" name="father_name_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->ftn_en_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="father_name_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="father_name_incorrect" name="father_name_correct"
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
                                         <p style="color:orange;">Grade Card or Aadhaar Card is the preferred option.</p>
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
                                    <label for="father_name_hindi">Father's First Name (Hindi):</label>
                                    <input class="form-control" id="father_name_hindi" name="father_name_hindi" readonly
                                        type="text" value="{{ @$student->father_name_hindi }}">

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" class="radio_option" id="father_name_hindi_correct" name="father_name_hindi_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->ftn_hi_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="father_name_hindi_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="father_name_hindi_incorrect"
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
                                         <p style="color:orange;">Grade Card or Aadhaar Card is the preferred option.</p>
                                    @if (@$student->studentAckLogs->ftn_hi_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->ftn_hi_image) }}">
                                            {{ @$student->studentAckLogs->ftn_hi_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- Mother Name Section (English) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="mother_name">Mother's First Name  (English):</label>
                                    <input onchange="english_text_change(this,'#mother_name_hindi')" class="form-control"
                                        id="mother_name" name="mother_name" type="text"
                                        value="{{ @$student->mother_name }}">
                                    {{-- <p id="mother_name" class="form-control-plaintext">{{ @$student->mother_name }}</p> --}}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" class="radio_option" id="mother_name_correct" name="mother_name_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->mn_en_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="mother_name_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="mother_name_incorrect" name="mother_name_correct"
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
                                         <p style="color:orange;">Grade Card or Aadhaar Card is the preferred option.</p>
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
                                    <label for="mother_name_hindi">Mother's First Name  (Hindi):</label>
                                    <input class="form-control" id="mother_name_hindi" name="mother_name_hindi" readonly
                                        type="text" value="{{ @$student->mother_name_hindi }}">

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" class="radio_option" id="mother_name_hindi_correct" name="mother_name_hindi_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->mn_hi_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="mother_name_hindi_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="mother_name_hindi_incorrect"
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
                                         <p style="color:orange;">Grade Card or Aadhaar Card is the preferred option.</p>
                                    @if (@$student->studentAckLogs->mn_hi_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->mn_hi_image) }}">
                                            {{ @$student->studentAckLogs->mn_hi_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>



                        <!-- Competency Level Section (English) -->
                        <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="program_english">Competency Level (English):</label>
                                    <p id="program_english" class="form-control-plaintext">{{ @$student->course_name }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" class="radio_option" id="program_english_correct" name="program_english_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->cs_en_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="program_english_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="program_english_incorrect" name="program_english_correct"
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
                                         <p style="color:orange;">Grade Card or Aadhaar Card is the preferred option.</p>
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
                                    <label for="program_hindi">Competency Level (Hindi):</label>
                                    <p id="program_hindi" class="form-control-plaintext">
                                        {{ @$student->course_name_hindi }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" class="radio_option" id="program_hindi_correct" name="program_hindi_correct"
                                        value="1"
                                        {{ @$student->studentAckLogs->cs_hi_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="program_hindi_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="program_hindi_incorrect" name="program_hindi_correct"
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
                                         <p style="color:orange;">Grade Card or Aadhaar Card is the preferred option.</p>
                                    @if (@$student->studentAckLogs->cs_hi_image)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->studentAckLogs->cs_hi_image) }}">
                                            {{ @$student->studentAckLogs->cs_hi_image }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- CGPA Section -->
                        {{-- <div class="row mb-4 fields_div">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="cgpa">CGPA:</label>
                                    <p id="cgpa" class="form-control-plaintext">{{ @$student->cgpa }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="radio" class="radio_option" id="cgpa_correct" name="cgpa_correct" value="1"
                                        {{ @$student->studentAckLogs->cgpa_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="cgpa_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="cgpa_incorrect" name="cgpa_correct" value="0"
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
                        </div> --}}

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
                                    <input type="radio" class="radio_option" id="email_correct" name="email_correct" value="1"
                                        {{ @$student->studentAckLogs->se_status == 1 || empty(@$student->studentAckLogs) ? 'checked' : '' }}
                                        onchange="hideRemarkUpload(this)">
                                    <label class="form-check-label" for="email_correct">Correct</label>
                                </div>
                                <div class="form-check on_complete">
                                    <input type="radio" class="incorrect_option radio_option" id="email_incorrect" name="email_correct" value="0"
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
                                                id="in_person" value="Attending Convocation"
                                                @if ($student->collection_mode == 'Attending Convocation') checked @endif>
                                            <label class="form-check-label radio_button_lable" for="in_person">
                                                Attending Convocation In Person (Rs.3000/-)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="collection_mode"
                                                id="by_post" value="By Post"
                                                @if ($student->collection_mode == 'By Post') checked @endif>
                                            <label class="form-check-label radio_button_lable" for="by_post">
                                                Receiving Certificate by Post

                                            </label>
                                            <br>
                                            <p class="radio_button_lable"> &nbsp;&nbsp; &nbsp;(Within India Rs.750/-)
                                                (Outside India Rs.1500/-)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div id="in_person_details"
                            style="background-color: #e6fbf1;@if ($student->collection_mode == 'Attending Convocation') display:block !important; @endif"
                            class=" row mb-4 fields_div conditional-fields  ">
                            <div class="col-md-3">
                                <div class="form-group border p-3">
                                    <label for="attire_size">Choose Attire Size:</label>
                                     
                                    <select class="form-control" id="attire_size" name="attire_size">
                                        <option value="">Select Size</option>
                                        <option value="SXX" {{ @$student->attire_size == 'SXX' ? 'selected' : '' }}>SXX</option>

                                        <option value="XS" {{ @$student->attire_size == 'XS' ? 'selected' : '' }}>XS</option>
                                        <option value="S" {{ @$student->attire_size == 'S' ? 'selected' : '' }}>S</option>
                                        <option value="M" {{ @$student->attire_size == 'M' ? 'selected' : '' }}>M</option>
                                        <option value="L" {{ @$student->attire_size == 'L' ? 'selected' : '' }}>L</option>
                                        <option value="XL" {{ @$student->attire_size == 'XL' ? 'selected' : '' }}>XL</option>
                                        <option value="XLL" {{ @$student->attire_size == 'XLL' ? 'selected' : '' }}>XLL</option>
                                        <option value="2XL" {{ @$student->attire_size == '2XL' ? 'selected' : '' }}>2XL</option>
                                        <option value="3XL" {{ @$student->attire_size == '3XL' ? 'selected' : '' }}>3XL</option>
                                        <option value="4XL" {{ @$student->attire_size == '4XL' ? 'selected' : '' }}>4XL</option>
                                        <option value="5XL" {{ @$student->attire_size == '5XL' ? 'selected' : '' }}>5XL</option>
                                    </select>
                                    

                                        <br>
                                         
                                        <a onclick="$('.attire_size_div').toggle()"
                                            data-href="{{ url($baseDirectoryPath2 . '/attrie_size.png') }}"  style="cursor: pointer;"
                                            target="_blank">Click here to view the size chart</a>
                                        <div class="attire_size_div" style="display: none">
                                            <img src="{{ url($baseDirectoryPath2 . '/attrie_size.png') }}"
                                            class="attire_size_image">
                                        </div>
                                        <br>
                                        <div class="form-group border p-3" style="    margin-top: 8%;">
                                            <label for="no_of_people_accompanied">No. Of People Accompanied With You:</label>
                                            <input class="form-control" id="no_of_people_accompanied" name="no_of_people_accompanied" 
                                                type="number" value="{{ @$student->no_of_people_accompanied }}">
        
                                        </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conditional Fields for Delivery Address -->

                        <div id="by_post_details"
                            style="@if ($student->collection_mode == 'By Post') display:block !important; @endif background-color: #e6fbf1;"
                            class="row mb-4 fields_div conditional-fields ">
                            <div class="col-md-6">
                                <div class="form-group border p-3">
                                    <label for="delivery_address">Enter Delivery Address:</label>
                                    <textarea class="form-control" name="delivery_address" id="delivery_address" cols="50" rows="4"
                                        placeholder="Street Address, City, State/Province">{{ @$student->delivery_address }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="postal_code">Pincode:</label>
                                <input type="text" value="{{ @$student->delivery_pincode }}" class="form-control"
                                    id="delivery_pincode" name="delivery_pincode" placeholder="Pincode">
                            </div>

                            <div class="col-md-3">
                                <label for="delivery_country">Country:</label>
                                <select class="form-control" id="delivery_country" name="delivery_country"
                                    value="{{ @$student->delivery_country }}">
                                    <option value="">Select Country</option>
                                    @foreach ($countries as $value)
                                        <option value="{{ $value->name }}"
                                            {{ @$student->delivery_country == $value->name ? 'selected' : '' }}>
                                            {{ $value->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        <div class="row mb-4 guidelines">
                            <div class="col-md-5 col-5">
                                <div class="form-group border p-3">


                                    <label for="photograph">Photograph:</label>
                                    <br>

                                    {{-- @if (@$student->student_photo)
                                        <a target="_blank"
                                            href="{{ url($baseDirectoryPath . $student->student_photo) }}">
                                            <img src="{{ url($baseDirectoryPath . $student->student_photo) }}"
                                                style="width:20%" alt="">
                                        </a><br>
                                    @endif --}}
                                    <img id="photo-display" class="photograph"
                                        style="height: 25mm; width: 25mm;  display: none;" alt="Student Photograph">
                                    <input type="file" id="photograph" style="width: 100%;" name="photograph"
                                        @if ($student->student_photo) data-photo-exists="true" @else data-photo-exists="false" @endif
                                        class="form-control on_complete" accept="image/*">
                                    <br>
                                    <small class="form-text text-muted mt-2 on_complete">
                                        <strong>Guidelines:</strong><br>
                                        - Height : 95 px and Width : 95 px.<br>
                                        - File size should not exceed 500 KB.<br>
                                        - Photograph should cover at least 80% of your face.<br>
                                        - Use a formal photograph with a plain background.
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4 guidelines">
                            <div class="col-md-12">
                                <div class="form-group border p-3">
                                    <label>
                                        <input type="checkbox" name="student_declaration" value="1"
                                            @if (@$student->student_declaration == 1) checked @endif required>
                                        I agree to the terms and conditions. <a href="#"
                                            style="cursor: pointer !important; pointer-events: auto !important;"
                                            data-toggle="modal" data-target="#termsConditionsModal">click here to view
                                            terms and conditions</a> 
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" 
                            class="btn btn-primary submit_button mt-4 {{ !(!$isReadonly && !$is_payment_pending) ? 'hidden_1' : '' }}">Save & Make Payment</button>



                        <a href="{{ route('convo_student.payment') }}"
                            class="btn btn-primary mt-4 make_payment_button {{ !$is_payment_pending ? 'hidden_1' : '' }}">Make
                            Payment</a>


                    </form>
                    <?php
                    $domain = \Request::getHost();
                    $subdomain = explode('.', $domain);
                    $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
                    $path = $protocol . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/';
                    $certificate_pdf = $path . $subdomain[0] . '/' . 'backend' . '/' . 'convocation' . '/' . 'certificate' . '/' . $student->certificate_pdf;
                    
                    if ($subdomain[0] == 'convocation') {
                        $path = $protocol . '://' . $subdomain[0] . '.' . $subdomain[1] . '.edu.in/';
                        $certificate_pdf = $path . 'mitwpu' . '/' . 'backend' . '/' . 'convocation' . '/' . 'certificate' . '/' . $student->certificate_pdf;
                    }
                    
                    // $excel_url=$path.$subdomain[0]."/backend/sample_excel/Galgotias Sample Excel.xlsx";*/
                    
                    ?>
                    @if ($isPdfApproved)
                        <div class="row " style="margin-bottom:2%; padding:2%; border: 1px solid #d3d3d3; ">
                            <div class="col-md-12 ">
                                {{-- <a style="margin-right:2%;" href="{{ route('mitwpu.pdf-view', ['prnNo' => $student->prn]) }}"
                        target="_blank" class="">
                        <i class="fa fa-file-pdf-o"></i> Click here to view PDF
                    </a> --}}


                                <a class="btn btn-primary" onclick="$('.pdf_view').toggle()" id="">
                                    View Pdf
                                </a>
                                @if (!empty($student->certificate_pdf))
                                    <div class="row pdf_view" style="display:none;margin-top:2%">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <embed src="{{ $certificate_pdf }}?t={{ time() }}#toolbar=0&navpanes=0&scrollbar=0"
                                                type="application/pdf">

                                        </div>


                                    </div>
                                @endif


                                
                            </div>


                          


                        </div>

                        @if ($student->collection_mode == "By Post")
                        <div class="row " style="margin-bottom:2%; padding:2%; border: 1px solid #d3d3d3; ">
                            <div class="col-md-12 ">
                        <div class="row " >
                            <div class="col-lg-12 col-md-12 col-sm-12">
                              Would you be interested in attending the convocation in person? If so, please  <a  onClick="confirmationChangeCollection()">click here.</a>

                            </div>

                        </div>
                        </div>
                    @endif
                    @endif
                    <div class="row isPdfEligibleForApproval {{ !$isPdfEligibleForApproval ? 'hidden_1' : '' }}"
                        style="margin-bottom:2%; padding:2%; border: 1px solid #d3d3d3; ">
                        <div class="col-md-12 ">
                            {{-- <a style="margin-right:2%;" href="{{ route('mitwpu.pdf-view', ['prnNo' => $student->prn]) }}"
                                target="_blank" class="">
                                <i class="fa fa-file-pdf-o"></i> Click here to view PDF
                            </a> --}}


                            <a class="btn btn-primary" id="approve_pdf">
                                Approve pdf
                            </a>
                            @if (!empty($student->certificate_pdf))
                                <div class="row" style="margin-top:2%">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <embed src="{{ $certificate_pdf }}?t={{ time() }}#toolbar=0&navpanes=0&scrollbar=0"
                                            type="application/pdf" width="600" height="880">

                                    </div>

                                    {{-- <div class="col-lg-6 col-md-6 col-sm-12">
                                      <div id="pdfDiv" class="fade-in-right containerPdf" style="text-align: center; left: 100;">
                                    
                                        <div id="pdf-main-container">
                                            <div id="pdf-loader">Loading document ...</div>
                                            <div id="pdf-contents">
                                                
                                                <canvas style="width: 100%;" id="pdf-canvas" width="794px" height="1122px"></canvas>
                                            </div>
                                          
                                        </div>
                            
                                    
                                    </div>
                                </div> --}}
                                </div>
                            @endif
                        </div>


                    </div>
                </div>
            </div>


            @if (!$student_ack_logs->isEmpty())
                <div class="row">
                    <div class="col-md-12">


                        <h2><i class="fa fa-history" style="font-size: 30px;"></i>
                            Student Ack Log</h2> <!-- Add this line for the heading -->
                        <hr class="my-4">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="logs">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name As per Tc Remark</th>
                                        <th>Name As per Tc Hindi Remark</th>
                                        <th>Mother's First Name  Remark</th>
                                        <th>Mother's First Name  Hindi Remark</th>
                                        <th>Father's First Name Remark</th>
                                        <th>Father's First Name Hindi Remark</th>
                                        <th>Competency Level Remark</th>
                                        <th>Competency Level Hindi Remark</th>
                                        {{-- <th>CGPA Remark</th> --}}
                                        <th>Secondary Email Remark</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($student_ack_logs as $index => $student_ack)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <!-- This will show a serial number starting from 1 -->
                                            <td>
                                                <?php
                                                if ($subdomain[0] == 'convocation') {
                                                    $subdomain[0] = 'mitwpu';
                                                }
                                                $html = '';
                                                $filePath = '';
                                                if ($student_ack->fn_en_status == 0) {
                                                    $html .= $student_ack->fn_en_remark;
                                                
                                                    if (!empty($student_ack->fn_en_image)) {
                                                        $filePath = $path . $subdomain[0] . '/' . config('constant.backend') . '/students/' . $student_ack->fn_en_image;
                                                    }
                                                } else {
                                                    $html .= 'Correct';
                                                }
                                                ?>
                                                {{ $html }}
                                                <?php
                                                if ($filePath != '') {
                                                    echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $html = '';
                                                $filePath = '';
                                                if ($student_ack->fn_hi_status == 0) {
                                                    $html .= $student_ack->fn_hi_remark;
                                                    if (!empty($student_ack->fn_hi_image)) {
                                                        $filePath = $path . $subdomain[0] . '/' . config('constant.backend') . '/students/' . $student_ack->fn_hi_image;
                                                    }
                                                } else {
                                                    $html .= 'Correct';
                                                }
                                                ?>
                                                {{ $html }}
                                                <?php
                                                if ($filePath != '') {
                                                    echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                                }
                                                ?>

                                            </td>
                                            <td>
                                                <?php
                                                $html = '';
                                                $filePath = '';
                                                if ($student_ack->mn_en_status == 0) {
                                                    $html .= $student_ack->mn_en_remark;
                                                    if (!empty($student_ack->mn_en_image)) {
                                                        $filePath = $path . $subdomain[0] . '/' . config('constant.backend') . '/students/' . $student_ack->mn_en_image;
                                                    }
                                                } else {
                                                    $html .= 'Correct';
                                                }
                                                ?>
                                                {{ $html }}
                                                <?php
                                                if ($filePath != '') {
                                                    echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                                }
                                                ?>

                                            </td>
                                            <td>

                                                <?php
                                                $html = '';
                                                $filePath = '';
                                                if ($student_ack->mn_hi_status == 0) {
                                                    $html .= $student_ack->mn_hi_remark;
                                                    if (!empty($student_ack->mn_hi_image)) {
                                                        $filePath = $path . $subdomain[0] . '/' . config('constant.backend') . '/students/' . $student_ack->mn_hi_image;
                                                    }
                                                } else {
                                                    $html .= 'Correct';
                                                }
                                                ?>
                                                {{ $html }}
                                                <?php
                                                if ($filePath != '') {
                                                    echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $html = '';
                                                $filePath = '';
                                                if ($student_ack->ftn_en_status == 0) {
                                                    $html .= $student_ack->ftn_en_remark;
                                                    if (!empty($student_ack->ftn_en_image)) {
                                                        $filePath = $path . $subdomain[0] . '/' . config('constant.backend') . '/students/' . $student_ack->ftn_en_image;
                                                    }
                                                } else {
                                                    $html .= 'Correct';
                                                }
                                                ?>
                                                {{ $html }}
                                                <?php
                                                if ($filePath != '') {
                                                    echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $html = '';
                                                $filePath = '';
                                                if ($student_ack->ftn_hi_status == 0) {
                                                    $html .= $student_ack->ftn_hi_remark;
                                                    if (!empty($student_ack->ftn_hi_image)) {
                                                        $filePath = $path . $subdomain[0] . '/' . config('constant.backend') . '/students/' . $student_ack->ftn_hi_image;
                                                    }
                                                } else {
                                                    $html .= 'Correct';
                                                }
                                                ?>
                                                {{ $html }}
                                                <?php
                                                if ($filePath != '') {
                                                    echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                                }
                                                ?>

                                            </td>
                                            <td>
                                                <?php
                                                $html = '';
                                                $filePath = '';
                                                if ($student_ack->cs_en_status == 0) {
                                                    $html .= $student_ack->cs_en_remark;
                                                    if (!empty($student_ack->cs_en_image)) {
                                                        $filePath = $path . $subdomain[0] . '/' . config('constant.backend') . '/students/' . $student_ack->cs_en_image;
                                                    }
                                                } else {
                                                    $html .= 'Correct';
                                                }
                                                ?>
                                                {{ $html }}
                                                <?php
                                                if ($filePath != '') {
                                                    echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $html = '';
                                                $filePath = '';
                                                if ($student_ack->cs_hi_status == 0) {
                                                    $html .= $student_ack->cs_hi_remark;
                                                    if (!empty($student_ack->cs_hi_image)) {
                                                        $filePath = $path . $subdomain[0] . '/' . config('constant.backend') . '/students/' . $student_ack->cs_hi_image;
                                                    }
                                                } else {
                                                    $html .= 'Correct';
                                                }
                                                ?>
                                                {{ $html }}
                                                <?php
                                                if ($filePath != '') {
                                                    echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                                }
                                                ?>
                                            </td>
                                           
                                            <td>
                                                <?php
                                                $html = '';
                                                $filePath = '';
                                                if ($student_ack->se_status == 0) {
                                                    $html .= $student_ack->se_remark;
                                                    if (!empty($student_ack->se_image)) {
                                                        $filePath = $path . $subdomain[0] . '/' . config('constant.backend') . '/students/' . $student_ack->se_image;
                                                    }
                                                } else {
                                                    $html .= 'Correct';
                                                }
                                                ?>
                                                {{ $html }}
                                                <?php
                                                if ($filePath != '') {
                                                    echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                                }
                                                ?>

                                            </td>
                                            {{-- <td>{{ $student_ack->se_status ==0  ? $student_ack->se_remark : 'Correct' }}</td> --}}
                                            <td>{{ $student_ack->created_at->format('d-m-y h:i A') }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            <hr>
            @if (!$convo_student_logs->isEmpty())
                <div class="row">
                    <h2><i class="fa fa-history" style="font-size: 30px;    "></i>
                        Admin Ack Log</h2> <!-- Add this line for the heading -->
                    <hr class="my-4">
                    <div style="overflow-x:auto;">
                        <div class="table-responsive">
                            <table id="logs_admin"  class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Student Email ID</th>
                                        <th>Secondary Email ID</th>
                                        <th>Gender</th>
                                        <th>DOB</th>
                                        {{-- <th>CGPA</th> --}}
                                        <th>Name As per Tc</th>
                                        <th>Name As per Tc (Hindi)</th>
                                        <th>Name As per Tc (Krutidev)</th>
                                        <th>Mother's First Name </th>
                                        <th>Mother's First Name  (Hindi)</th>
                                        <th>Mother's First Name  (Krutidev)</th>
                                        <th>Father's First Name</th>
                                        <th>Father's First Name (Hindi)</th>
                                        <th>Father's First Name (Krutidev)</th>
                                        <th>Competency Level</th>
                                        <th>Competency Level (Hindi)</th>
                                        <th>Competency Level (Krutidev)</th>
                                        <th>First Name</th>
                                        <th>Middle Name</th>
                                        <th>Last Name</th>
                                        <th>Student Mobile No</th>
                                        <th>Permanent Address</th>
                                        <th>Local Address</th>
                                        <th>Certificate ID</th>
                                        <th>Cohort ID</th>
                                        <th>Cohort Name</th>
                                        <th>Faculty Name</th>
                                        <th>Specialization</th>
                                        <th>Rank</th>
                                        <th>Medal Type</th>
                                        <th>Completion Date</th>
                                        <th>Issue Date</th>
                                        <th>Message To Student </th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($convo_student_logs as $index => $convo_student_log)
                                        <tr>
                                            <?php
                                            
                                            $logDate = Carbon::parse($convo_student_log->log_date);
                                            $logDate = $logDate->format('d-m-y h:i A');
                                            ?>
                                            <td>{{ $index + 1 }}</td>
                                            <!-- This will show a serial number starting from 1 -->
                                            <td>{{ $convo_student_log->wpu_email_id ? $convo_student_log->wpu_email_id : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->secondary_email_id ? $convo_student_log->secondary_email_id : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->gender ? $convo_student_log->gender : '-' }}</td>
                                            <td>{{ $convo_student_log->date_of_birth != '0000-00-00' ? $convo_student_log->date_of_birth : '-' }}
                                            </td>
                                            {{-- <td>{{ $convo_student_log->cgpa != '0' ? $convo_student_log->cgpa : '-' }}
                                            </td> --}}
                                            <td>{{ $convo_student_log->full_name ? $convo_student_log->full_name : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->full_name_hindi ? $convo_student_log->full_name_hindi : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->full_name_krutidev ? $convo_student_log->full_name_krutidev : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->mother_name ? $convo_student_log->mother_name : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->mother_name_hindi ? $convo_student_log->mother_name_hindi : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->mother_name_krutidev ? $convo_student_log->mother_name_krutidev : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->father_name ? $convo_student_log->father_name : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->father_name_hindi ? $convo_student_log->father_name_hindi : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->father_name_krutidev ? $convo_student_log->father_name_krutidev : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->course_name ? $convo_student_log->course_name : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->course_name_hindi ? $convo_student_log->course_name_hindi : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->course_name_krutidev ? $convo_student_log->course_name_krutidev : '-' }}
                                            </td>


                                            <td>{{ $convo_student_log->first_name ? $convo_student_log->first_name : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->middle_name ? $convo_student_log->middle_name : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->last_name ? $convo_student_log->last_name : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->student_mobile_no ? $convo_student_log->student_mobile_no : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->permanent_address ? $convo_student_log->permanent_address : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->local_address ? $convo_student_log->local_address : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->certificateid ? $convo_student_log->certificateid : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->cohort_id ? $convo_student_log->cohort_id : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->cohort_name ? $convo_student_log->cohort_name : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->faculty_name ? $convo_student_log->faculty_name : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->specialization ? $convo_student_log->specialization : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->rank ? $convo_student_log->rank : '-' }}</td>
                                            <td>{{ $convo_student_log->medal_type ? $convo_student_log->medal_type : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->completion_date != '0000-00-00' ? $convo_student_log->completion_date : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->issue_date != '0000-00-00' ? $convo_student_log->issue_date : '-' }}
                                            </td>
                                            <td>{{ $convo_student_log->correction_message }}</td> 
                                            <td>{{ $logDate }}</td>

                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            @endif

            @if (!$payments->isEmpty())
                <div class="row">
                    <h2><i class="fa fa-credit-card" style="font-size: 30px;    "></i>
                        Payment Details</h2> <!-- Add this line for the heading -->
                    <hr class="my-4">
                    <div class="table-responsive">
                        <table id="payments" class="table table-bordered">
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
                                        <td>{{ $index + 1 }}</td>
                                        <!-- This will show a serial number starting from 1 -->
                                        <td>{{ $payment->txn_id }}</td>
                                        <td>{{ $payment->txn_amount }}</td>
                                        <td>{{ $payment->txn_date->format('Y-m-d h:i A') }}</td>
                                        <!-- Format the date here -->
                                        <td>
                                            @switch($payment->status)
                                                @case('TXN_SUCCESS')
                                                    SUCCESS
                                                    @break
                                                @case('TXN_FAILURE')
                                                    FAILED
                                                    @break
                                                @case('PENDING')
                                                    PENDING
                                                    @break
                                                @default
                                                    UNKNOWN STATUS
                                            @endswitch
                                        </td>
                                        <td>{{ $payment->payment_mode }}</td>
                                        <td>{{ $payment->gateway_name }}</td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
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


    <div class="modal fade" id="confirmationChangeCollection" tabindex="-1" role="dialog"
        aria-labelledby="updateModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Change Collection Mode</h5>
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
           </button> --}}
                </div>
                <div class="modal-body">
                    Are you certain that you will be attending the convocation in person?
                    <br> <br>
                    The fee for attending the in-person convocation is RS.3000/-.
                    <br> <br>
                    You have already paid RS.{{ $paid_amount }}/-.
                    <br><br> Please remit the remaining balance of RS. {{  (3000-$paid_amount) }}/-.

 
                 <br>

                    <div class="row" style="margin-top:3%">
                        <div class="col-md-4">
                            <div class="form-group border p-3">
                                <label for="attire_size">Choose Attire Size:</label>
                                <select class="form-control" id="attire_size_collection_mode" name="attire_size_collection_mode">
                                    <option value="">Select Size</option>
                                    <option value="SXX">SXX</option>
                                    <option value="XS">XS</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XLL">XLL</option>
                                    <option value="2XL">2XL</option>
                                    <option value="3XL">3XL</option>
                                    <option value="4XL">4XL</option>
                                    <option value="5XL">5XL</option>
                                </select>
                        </div>
                        </div>
                    </div>
                   
                    
                </div>
                <div class="modal-footer">
                    <button id="change_collection_mode_btn" type="button" class="btn btn-primary">   Proceed to Pay</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="termsConditionsModal" tabindex="-1" role="dialog"
        aria-labelledby="termsConditionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsConditionsModalLabel">Terms and Conditions</h5>
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> --}}
                </div>
                <div class="modal-body">
                    <p><b>I hereby declare that the following information provided in the convocation data is true and
                            accurate to the best of my knowledge:</b></p>

                    <p><b>Academic Records:</b></p>
                    <ul>
                        <li>My academic Grade Card reflect my grades and courses completed.</li>
                        <li>There are no discrepancies or errors in my academic records.</li>
                        <li>I have fulfilled all the academic requirements for my degree.</li>
                    </ul>

                    <p><b>Personal Information:</b></p>
                    <ul>
                        <li>My personal details, including name, date of birth, and contact information, are correct.</li>
                        <li>There are no discrepancies or errors in my personal information.</li>
                    </ul>

                    <p><b>Convocation Participation:</b></p>
                    <ul>
                        <li>If I have chosen to participate in person, I understand that my participation is subject to the
                            university's convocation guidelines and policies.</li>
                        <li>If I have chosen to receive my degree certificate by post, I understand that the university will
                            mail the certificate to the address provided in my application.</li>
                    </ul>

                    <p><b>I understand that any false or misleading information provided in this declaration may result in
                            disciplinary action.</b></p>
                </div>
                <div class="modal-footer">
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

            $.validator.addMethod('filesize', function(value, element, param) {
                var fileInput = $(element).get(0);
                if (fileInput.files.length > 0) {
                    return fileInput.files[0].size <= param;
                }
                return true; // If no file is selected, validation passes
            }, 'File size must be less than or equal to 500 kb.');
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
                    // 'cgpa_correct': {
                    //     required: true
                    // },
                    // 'cgpa_correction_remarks': {
                    //     required: function(element) {
                    //         return $('input[name="cgpa_correct"]:checked').val() == 0 && !$(
                    //             'input[name="cgpa_correction_file"]').val();
                    //     },
                    //     minlength: 1
                    // },
                    // 'cgpa_correction_file': {
                    //     required: function(element) {
                    //         return $('input[name="cgpa_correct"]:checked').val() == 0 && !$(
                    //             '#cgpa_correction_remarks').val();
                    //     },
                    //     extension: "jpg|jpeg|png|pdf"
                    // },
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
                        extension: "jpg|jpeg|png",
                        filesize: 500 * 1024 // 500 KB in bytes

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
                                return $('input[name="collection_mode"]:checked').val() ===
                                    'Attending Convocation';
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
                    },
                    // 'gender': {
                    //     required: true
                    // },
                    student_declaration: {
                        required: true
                    },
                    no_of_people_accompanied : {
                        required: {
                            depends: function(element) {
                                return $('input[name="collection_mode"]:checked').val() === 'Attending Convocation';
                            },
                           
                        }
                    }
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
                    'program_english_correct': "Please specify if the Competency Level in English is correct.",
                    'program_english_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'program_english_correction_file': {
                        required: "Please provide a image file if the Competency Level in English is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'program_hindi_correct': "Please specify if the Competency Level in Hindi is correct.",
                    'program_hindi_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'program_hindi_correction_file': {
                        required: "Please provide a image file if the Competency Level in Hindi is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    // 'cgpa_correct': "Please specify if the CGPA is correct.",
                    // 'cgpa_correction_remarks': {
                    //     required: "Please provide correction remarks or upload a file.",
                    //     minlength: "Remarks cannot be empty."
                    // },
                    // 'cgpa_correction_file': {
                    //     required: "Please provide a image file if the CGPA is not correct.",
                    //     extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    // },
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
                    'mother_name_correct': "Please specify if the mother's first name in English is correct.",
                    'mother_name_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'mother_name_correction_file': {
                        required: "Please provide an image file if the mother's first name in English is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'mother_name_hindi_correct': "Please specify if the mother's first name in Hindi is correct.",
                    'mother_name_hindi_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'mother_name_hindi_correction_file': {
                        required: "Please provide an image file if the mother's first name in Hindi is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'father_name_correct': "Please specify if the father's first name in English is correct.",
                    'father_name_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'father_name_correction_file': {
                        required: "Please provide an image file if the father's first name in English is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'father_name_hindi_correct': "Please specify if the father's first name in Hindi is correct.",
                    'father_name_hindi_correction_remarks': {
                        required: "Please provide correction remarks or upload a file.",
                        minlength: "Remarks cannot be empty."
                    },
                    'father_name_hindi_correction_file': {
                        required: "Please provide an image file if the father's first name in Hindi is not correct.",
                        extension: "Only jpg, jpeg, png, and pdf files are allowed."
                    },
                    'mother_name': {
                        required: "Please enter the mother's first name",
                    },
                    'father_name': {
                        required: "Please enter the father's first name",
                    },
                    'gender': {
                        required: "Please select gender",
                    },
                    'collection_mode': "Please select a mode of collection.",
                    'attire_size': "Please select your attire size.",
                    'delivery_address': "Please enter your delivery address.",
                    'delivery_pincode': "Please enter your pincode.",
                    'delivery_country': "Please select your country.",
                    'student_declaration': "You must agree to the terms and conditions.",
                    'no_of_people_accompanied':"Please specify the number of people accompanying you."
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
                    // $('#confirmSubmit').off('click').on('click', function() {
                    //     // Disable form elements to prevent further changes
                    //     // $(form).find(':input').prop('disabled', true);

                    //     // Create a FormData object to handle file uploads
                    //     var formData = new FormData(form);

                    //     // Perform the AJAX request
                    //     $.ajax({
                    //         url: "<?= route('convo_student.verify_details') ?>", // URL where the form will be submitted
                    //         type: 'POST', // Method type
                    //         data: formData, // Data to be sent to the server
                    //         processData: false, // Prevent jQuery from automatically transforming the data into a query string
                    //         contentType: false, // Prevent setting content type header
                    //         success: function(response) {
                    //             // Handle the response from the server 
                    //             toastr.success("Application saved successfully");
                    //             setTimeout(function() {
                    //                 window.location.reload();
                    //             }, 2000);
                    //         },
                    //         error: function(xhr, status, error) {
                    //             // Handle errors here
                    //             toastr.error(
                    //                 "An error occurred while submitting the form."
                    //             );
                    //             console.log(xhr.responseText);
                    //         }
                    //     });

                    //     // Hide the confirmation modal
                    //     $('#confirmationModal').modal('hide');
                    // });
                    $('#confirmSubmit').off('click').on('click', function() {
                        var father_name_hindi = $('#father_name_hindi').val();
                        var mother_name_hindi = $('#mother_name_hindi').val();
                        var promises = [];
                        $('#submitBtn').prop('disabled', true);
                        $(this).prop('disabled', true);
                        if (!father_name_hindi || father_name_hindi.trim() === '') {
                            promises.push(english_text_change('#father_name', '#father_name_hindi'));
                        }
                        if (!mother_name_hindi || mother_name_hindi.trim() === '') {
                            promises.push(english_text_change('#mother_name', '#mother_name_hindi'));
                        }
                        Promise.all(promises)
                        .then(() => { 
                            var formData = new FormData(form);
                            $.ajax({
                                url: "<?= route('convo_student.verify_details') ?>", // URL where the form will be submitted
                                type: 'POST', // Method type
                                data: formData, // Data to be sent to the server
                                processData: false, // Prevent jQuery from automatically transforming the data into a query string
                                contentType: false, // Prevent setting content type header
                                success: function(response) {
                                    // Handle the response from the server 
                                    toastr.success("Application saved successfully");
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 2000);
                                },
                                error: function(xhr, status, error) {
                                    // Handle errors here
                                    toastr.error(
                                        "An error occurred while submitting the form."
                                    );
                                    $('#submitBtn').prop('disabled', false);
                                    $(this).prop('disabled', false);
                                    console.log(xhr.responseText);
                                }
                            });

                        })

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
                if (remarkUploadSection) {
                    remarkUploadSection.style.display = 'none';

                }
            } else {
                if (remarkUploadSection) {
                    remarkUploadSection.style.display = 'block';
                }
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

        // function english_text_change(from_element, to_hindi_element) {
        //     // $('.loader').removeClass('hidden_1');
        //     // e.preventDefault();
        //     var text = $(from_element).val();
        //     var convertTo = 'hindi'
        //     $.ajax({
        //         url: "<?= route('text-translator.save') ?>",
        //         type: 'POST',
        //         data: {
        //             text: text,
        //             convertTo: convertTo
        //         },
        //         dataType: 'json',
        //         success: function(response) {
        //             $(to_hindi_element).val(response.value);
        //             // $('.loader').addClass('hidden_1');
        //         },
        //         error: function() {
        //             // alert('An error occurred. Please try again.');
        //             // $('.loader').addClass('hidden_1');
        //         }
        //     });
        // }

        function english_text_change(from_element, to_hindi_element) {
            return new Promise((resolve, reject) => {
                var text = $(from_element).val();
                var convertTo = 'hindi';

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
                        resolve(); // Resolve the promise when AJAX is successful
                    },
                    error: function() {
                        reject(new Error('An error occurred during the AJAX request')); // Reject the promise on error
                    }
                });
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
                        $('.is_payment_completed').hide();
                        $('.is_payment_pending').hide();
                        $('.isPdfApproved').show();
                        toastr.success("Pdf Preview Approved successfully!");
                        setTimeout(function() {
                                    window.location.reload();
                        }, 2000);

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
                $('#in_person_details').fadeIn(300);
                $('#by_post_details').hide();
            } else if ($(this).val() === 'By Post') {
                $('#in_person_details').hide();
                $('#by_post_details').fadeIn(300);
            }
        });

        $('#logs').DataTable();
        $('#payments').DataTable();
        $('#logs_admin').DataTable();
        
        $(document).ready(function() {
            var photoDisplay = $('#photo-display');
            var photoExists = $('#photograph').data('photo-exists');

            // If an existing photo exists, show it
            if (photoExists) {
                var currentPhotoUrl = "{{ url($baseDirectoryPath . $student->student_photo) }}";
                photoDisplay.attr('src', currentPhotoUrl).show();
                // console.log(photoExists);
            }

            // Handle file input change
            $('#photograph').on('change', function(event) {
                var file = event.target.files[0];
                if (file) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        photoDisplay.attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(file);
                } else {
                    photoDisplay.hide(); // Hide image if no file is selected
                }
            });

            // Optionally, hide the preview if the file input is cleared
            $('#photograph').on('input', function() {
                if (!this.files.length) {

                    photoDisplay.hide();

                }
            });
        });
        $('#father_name, #mother_name').on('input', function() {
                // Replace all spaces with an empty string
                $(this).val($(this).val().replace(/\s+/g, ''));
            });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.2.228/pdf.min.js"></script>

    <script>
        // console.log(pdfUrlStr);
        // showPDF(pdfUrlStr);

        //     var pdfUrlStr = "<?php echo $certificate_pdf; ?>";
        //     console.log(pdfUrlStr);
        //     showPDF(pdfUrlStr);

        // var _PDF_DOC,
        //     _CURRENT_PAGE,
        //     _TOTAL_PAGES,
        //     _PAGE_RENDERING_IN_PROGRESS = 0,
        //     _CANVAS = document.querySelector('#pdf-canvas');

        // // initialize and load the PDF
        // async function showPDF(pdf_url) {
        //     document.querySelector("#pdf-loader").style.display = 'block';

        //     // get handle of pdf document
        //     try {
        //         _PDF_DOC = await pdfjsLib.getDocument({ url: pdf_url });
        //     }
        //     catch(error) {
        //         alert(error.message);
        //     }

        //     // total pages in pdf
        //     _TOTAL_PAGES = _PDF_DOC.numPages;

        //     // Hide the pdf loader and show pdf container
        //     document.querySelector("#pdf-loader").style.display = 'none';
        //     document.querySelector("#pdf-contents").style.display = 'block';
        //     // document.querySelector("#pdf-total-pages").innerHTML = _TOTAL_PAGES;

        //     // show the first page
        //     showPage(1);
        // }

        // // load and render specific page of the PDF
        // async function showPage(page_no) {
        //     _PAGE_RENDERING_IN_PROGRESS = 1;
        //     _CURRENT_PAGE = page_no;

        //     // disable Previous & Next buttons while page is being loaded
        //     // document.querySelector("#pdf-next").disabled = true;
        //     // document.querySelector("#pdf-prev").disabled = true;

        //     // while page is being rendered hide the canvas and show a loading message
        //     document.querySelector("#pdf-canvas").style.display = 'none';
        //     // document.querySelector("#page-loader").style.display = 'block';

        //     // update current page
        //     // document.querySelector("#pdf-current-page").innerHTML = page_no;

        //     // get handle of page
        //     try {
        //         var page = await _PDF_DOC.getPage(page_no);
        //     }
        //     catch(error) {
        //         alert(error.message);
        //     }

        //     // original width of the pdf page at scale 1
        //     var pdf_original_width = page.getViewport(1).width;

        //     // as the canvas is of a fixed width we need to adjust the scale of the viewport where page is rendered
        //     var scale_required = _CANVAS.width / pdf_original_width;

        //     // get viewport to render the page at required scale
        //     var viewport = page.getViewport(scale_required);

        //     // set canvas height same as viewport height
        //     _CANVAS.height = viewport.height;

        //     // setting page loader height for smooth experience
        //     // document.querySelector("#page-loader").style.height =  _CANVAS.height + 'px';
        //     // document.querySelector("#page-loader").style.lineHeight = _CANVAS.height + 'px';

        //     // page is rendered on <canvas> element
        //     var render_context = {
        //         canvasContext: _CANVAS.getContext('2d'),
        //         viewport: viewport
        //     };

        //     // render the page contents in the canvas
        //     try {
        //         await page.render(render_context);
        //     }
        //     catch(error) {
        //         alert(error.message);
        //     }

        //     _PAGE_RENDERING_IN_PROGRESS = 0;

        //     // re-enable Previous & Next buttons
        //     // document.querySelector("#pdf-next").disabled = true;
        //     // document.querySelector("#pdf-prev").disabled = true;

        //     // show the canvas and hide the page loader
        //     document.querySelector("#pdf-canvas").style.display = 'block';
        //     // document.querySelector("#page-loader").style.display = 'none';
        //     // document.querySelector("#pdf-buttons").style.display = 'none';






        // }


        // Event handlers for pagination (if you want to add navigation)
        // document.querySelector("#pdf-next").addEventListener("click", () => {
        //     if (_CURRENT_PAGE < _TOTAL_PAGES) {
        //         showPage(_CURRENT_PAGE + 1);
        //     }
        // });

        // document.querySelector("#pdf-prev").addEventListener("click", () => {
        //     if (_CURRENT_PAGE > 1) {
        //         showPage(_CURRENT_PAGE - 1);
        //     }
        // });

        // function clearCanvas() {
        //     const context = _CANVAS.getContext('2d');
        //     context.clearRect(0, 0, _CANVAS.width, _CANVAS.height);
        // }


        $(document).ready(function() {
            // Replace 'yourClassName' with the actual class of your checkbox
            $('.radio_option').click(function() {
               var isChecked = $('.incorrect_option:checked').length > 0;

                    if (isChecked) {
                        $('.confirmationModalBody').html('Your request will be forwarded to the administrator. You will not be able to make any changes until it is reviewed. Do you want to proceed?') 

                         $('#submitBtn').html('Send For Approval');
                    } else {
                        $('.confirmationModalBody').html('After confirming that all details are correct, you will be unable to modify your application form.') 

                       // console.log('No radio button is checked.');
                        $('#submitBtn').html('Save & Make Payment');
                    }
            });

             var isChecked = $('.incorrect_option:checked').length > 0;

                    if (isChecked) {
                        $('.confirmationModalBody').html('Your request will be forwarded to the administrator. You will not be able to make any changes until it is reviewed. Do you want to proceed?') 

                         $('#submitBtn').html('Send For Approval');
                    } else {
                        $('.confirmationModalBody').html('After confirming that all details are correct, you will be unable to modify your application form.') 

                       // console.log('No radio button is checked.');
                        $('#submitBtn').html('Save & Make Payment');
                    }
        });
        function confirmationChangeCollection(){
            $('#confirmationChangeCollection').modal('show'); // Show the modal


        }

        $('#change_collection_mode_btn').prop('disabled', true);

        $('#attire_size_collection_mode').on('change', function(e) {
            // Check if the selected value is empty
            if ($(this).val() === "") {
                // Disable the button if no selection is made
                $('#change_collection_mode_btn').prop('disabled', true);
            } else {
                // Enable the button if a valid selection is made
                $('#change_collection_mode_btn').prop('disabled', false);
            }
        });

        $('#change_collection_mode_btn').on('click', function(e) {
            e.preventDefault(); // Prevent default button behavior

            // Get the value from the select element or input field
            var size = $('#attire_size_collection_mode').val();

            // Construct the URL using Laravel's route and the size value
            var url = "{{ route('convo_student.change_collection_mode_payment', ':size') }}".replace(':size', size);

            // Redirect to the constructed URL
            window.location.href = url;
        });

    </script>
@stop
