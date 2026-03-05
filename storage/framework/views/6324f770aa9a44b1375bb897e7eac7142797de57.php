<?php $__env->startSection('content'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php
    $domain = request()->getHost();
    $subdomain = explode('.', $domain);
    $baseDirectoryPath = $subdomain[0] . '/' . config('constant.backend') . '/students/';
    if($subdomain[0] == "convocation"){
        $baseDirectoryPath = "mitwpu" . '/' . config('constant.backend') . '/students/';
                 $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.edu.in/'; 
    }
    $isPdfApproved = in_array($student->status, ['student re-acknowledged new data as correct, Payment is completed and preview pdf is approved', 'student acknowledge all data as correct, Payment is completed and preview pdf is approved']);

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
            border: 2px solid #007bff;
            /* Customize the border color and width */
            border-radius: 5px;
            /* Optional: for rounded corners */
            padding: 10px;
            /* Space between the border and content */
            text-align: center;
            /* Center text horizontally */
            background-color: #f8f9fa;
            /* Optional: light background color */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Optional: subtle shadow */
            text-transform: capitalize;
            /* Capitalize the first letter of each word */
            margin-bottom: 2%;

        }
    </style>
    <style>
        .containerPdf {
            position: relative;
            width: 100%;
            overflow: hidden;
            /* padding-top: 56.25%; 16:9 Aspect Ratio */
            min-height: 1140px;
        }

        body {
            background-image: none !important;
        }

        .responsive-iframe {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 100%;

            border: none;
        }

        @media  only screen and (max-width: 600px) {
            .containerPdf {
                min-height: 1140px;
            }
        }

        @media  only screen and (max-width: 420px) {
            .containerPdf {
                margin-top: 20px;
                min-height: 550px;
            }

            .responsive-iframe {
                width: 420;
            }
        }

        /* tetsing */

        #show-pdf-button {
            width: 150px;
            display: block;
            margin: 20px auto;
        }

        #file-to-upload {
            display: none;
        }

        #pdf-main-container {
            /* width: 400px; */
            /* margin: 20px auto; */
        }

        #pdf-loader {
            display: none;
            text-align: center;
            color: #999999;
            font-size: 13px;
            line-height: 100px;
            height: 100px;
        }

        #pdf-contents {
            display: none;
        }

        #pdf-meta {
            overflow: hidden;
            margin: 0 0 20px 0;
        }

        #pdf-buttons {
            float: left;
        }

        #page-count-container {
            float: right;
        }

        #pdf-current-page {
            display: inline;
        }

        #pdf-total-pages {
            display: inline;
        }

        #pdf-canvas {
            border: 1px solid rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
        }

        #page-loader {
            height: 100px;
            line-height: 100px;
            text-align: center;
            display: none;
            color: #999999;
            font-size: 13px;
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
                            <h4>Status : <?php echo e($student->status); ?></h4>

                            <?php if(@$is_transaction_pending): ?>
                            <div class="row"
                                    style="text-align: center;  margin-bottom: 12px; padding-bottom: 8px !important;">
                                    <h4 style="color:#FF9800;font-size: 14px;"> We are currently awaiting the payment status. Once it is confirmed, Student will be able to view the preview PDF.</h4>
                                </div>
                            <?php elseif(@$is_transaction_failed): ?>
                                <div class="row"
                                style="text-align: center;  margin-bottom: 12px; padding-bottom: 8px !important;">
                                <h4 style="color:#ee390c;font-size: 14px;">The student’s  most recent payment transaction was unsuccessful. Kindly ask student to attempt the payment once more and confirm the preview PDF to finalize your registration.</h4>
                            </div>

                            <?php endif; ?>
                        </div>
                    </div>

                    <form id="student-form" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <input type="hidden" id="student-id" value="<?php echo e($student->id); ?>">

                        <div class="form-row row">
                            <div class="form-group col-md-3">
                                <label for="prn">PRN:<small style="color:red">*</small></label>
                                <input type="text" class="form-control" id="prn" name="prn"
                                    value="<?php echo e(old('prn', $student->prn)); ?>" required>
                            </div>


                            <div class="form-group col-md-3">
                                <label for="wpu_email_id">Student Email ID:<small style="color:red">*</small></label>
                                <input type="email" class="form-control" id="wpu_email_id" name="wpu_email_id"
                                    value="<?php echo e(old('wpu_email_id', $student->wpu_email_id)); ?>" required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="secondary_email_id">Secondary Email ID:<small
                                        style="color:red">*</small>
                                        <?php if($student->studentAckLogs): ?>
                                        <?php echo @$student->studentAckLogs->se_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : ''; ?>

                                        <?php echo @$student->studentAckLogs->se_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : ''; ?>

                                    <?php endif; ?>
                                    </label>
                                <input type="email" class="form-control" id="secondary_email_id" name="secondary_email_id"
                                    value="<?php echo e(old('secondary_email_id', $student->secondary_email_id)); ?>" required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="gender">Gender:</label>
                                <select id="gender" name="gender" class="form-control">
                                    <option value="" <?php echo e(@$student->gender === '' ? 'selected' : ''); ?>>Select
                                        gender</option>
                                    <option value="M" <?php echo e(@$student->gender === 'M' ? 'selected' : ''); ?>>Male
                                    </option>
                                    <option value="F" <?php echo e(@$student->gender === 'F' ? 'selected' : ''); ?>>
                                        Female</option>
                                    <option value="O" <?php echo e(@$student->gender === 'O' ? 'selected' : ''); ?>>Other
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="date_of_birth">DOB:<small style="color:red">*</small></label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                    value="<?php echo e(old('date_of_birth', $student->date_of_birth)); ?>" required>
                            </div>
                            <?php if($student->student_type == 0): ?>
                            <div class="form-group col-md-3">
                                <label for="cgpa">CGPA:<small style="color:red">*</small>
                                   
                                </label>
                                <input type="text" class="form-control" id="cgpa" name="cgpa"
                                    value="<?php echo e(old('cgpa', $student->cgpa)); ?>" required>
                            </div>
                            <?php else: ?>
                            <div class="form-group col-md-3">
                                <label for="topic">Topic:<small style="color:red">*</small>
                                   
                                </label>
                                <input type="text" class="form-control" id="topic" name="topic"
                                    value="<?php echo e(old('topic', $student->topic)); ?>" required>
                            </div>
                            <?php endif; ?>
                        </div>


                        

                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="full_name">Name As per Tc:<small style="color:red">*</small>
                                    <?php if($student->studentAckLogs): ?>
                                        <?php echo @$student->studentAckLogs->fn_en_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : ''; ?>

                                        <?php echo @$student->studentAckLogs->fn_en_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : ''; ?>

                                    <?php endif; ?>
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                    value="<?php echo e(old('full_name', $student->full_name)); ?>"
                                    onchange="english_text_change(this,'#full_name_hindi','#full_name_krutidev')">
                            </div>

                            <div class="form-group col-md-4" style="margin-bottom: 11rem;">
                                <label for="full_name_hindi">Name As per Tc (Hindi):<small style="color:red">*</small>
                                    <?php if($student->studentAckLogs): ?>
                                        <?php echo @$student->studentAckLogs->fn_hi_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : ''; ?>

                                        <?php echo @$student->studentAckLogs->fn_hi_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : ''; ?>

                                    <?php endif; ?>
                                </label>
                                <input type="text" class="form-control" style="margin-bottom: 2%;" id="full_name_hindi"
                                    name="full_name_hindi" onchange="hindi_text_change(this,'#full_name_krutidev')"
                                    value="<?php echo e(old('full_name_hindi', $student->full_name_hindi)); ?>" required>

                                <div class="col-md-12 mt-2  hindi_keyboard" style="">

                                    <?php echo $__env->make('convodataverification.admin.pages.hindi_keyboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="full_name_krutidev">Name As per Tc (Krutidev):<small style="color:red">*</small>

                                </label>
                                <input type="text" class="form-control" id="full_name_krutidev" name="full_name_krutidev"
                                    value="<?php echo e(old('full_name_krutidev', $student->full_name_krutidev)); ?>" required>
                            </div>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="mother_name">Mother's First Name:<small style="color:red">*</small>
                                    <?php if($student->studentAckLogs): ?>
                                        <?php echo @$student->studentAckLogs->mn_en_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : ''; ?>

                                        <?php echo @$student->studentAckLogs->mn_en_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : ''; ?>

                                    <?php endif; ?>
                                </label>
                                <input type="text" class="form-control" id="mother_name" name="mother_name"
                                    onchange="english_text_change(this,'#mother_name_hindi','#mother_name_krutidev')"  onpaste="english_text_change(this,'#mother_name_hindi','#mother_name_krutidev')"
                                    value="<?php echo e(old('mother_name', $student->mother_name)); ?>">
                            </div>

                            <div class="form-group col-md-4" style="margin-bottom: 11rem;">
                                <label for="mother_name_hindi">Mother's First Name (Hindi):
                                    <?php if($student->studentAckLogs): ?>
                                        <?php echo @$student->studentAckLogs->mn_hi_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : ''; ?>

                                        <?php echo @$student->studentAckLogs->mn_hi_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : ''; ?>

                                    <?php endif; ?>

                                </label>
                                <input type="text" class="form-control" id="mother_name_hindi"
                                    name="mother_name_hindi"
                                    value="<?php echo e(old('mother_name_hindi', $student->mother_name_hindi)); ?>"
                                    onchange="hindi_text_change(this,'#mother_name_krutidev')" onpaste="hindi_text_change(this,'#mother_name_krutidev')" style="margin-bottom: 2%;">
                                <div class="col-md-12 mt-2  hindi_keyboard" style="">
                                    <?php echo $__env->make('convodataverification.admin.pages.hindi_keyboard_mother', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="mother_name_krutidev">Mother's First Name (Krutidev):</label>
                                <input type="text" class="form-control" id="mother_name_krutidev"
                                    name="mother_name_krutidev"
                                    value="<?php echo e(old('mother_name_krutidev', $student->mother_name_krutidev)); ?>">


                            </div>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="father_name">Father's First Name:<small style="color:red">*</small>
                                    <?php if($student->studentAckLogs): ?>
                                        <?php echo @$student->studentAckLogs->ftn_en_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : ''; ?>

                                        <?php echo @$student->studentAckLogs->ftn_en_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : ''; ?>

                                    <?php endif; ?>
                                </label>
                                <input type="text" class="form-control" id="father_name" name="father_name"
                                    onchange="english_text_change(this,'#father_name_hindi','#father_name_krutidev')" onpaste="english_text_change(this,'#father_name_hindi','#father_name_krutidev')"
                                    value="<?php echo e(old('father_name', $student->father_name)); ?>">

                            </div>

                            <div class="form-group col-md-4" style="margin-bottom: 11rem;">
                                <label for="father_name_hindi">Father's First Name (Hindi):
                                    <?php if($student->studentAckLogs): ?>
                                        <?php echo @$student->studentAckLogs->ftn_hi_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : ''; ?>

                                        <?php echo @$student->studentAckLogs->ftn_hi_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : ''; ?>

                                    <?php endif; ?>

                                </label>
                                <input type="text" class="form-control" id="father_name_hindi"
                                    name="father_name_hindi" style="margin-bottom: 2%;"
                                    onchange="hindi_text_change(this,'#father_name_krutidev')" onpaste="hindi_text_change(this,'#father_name_krutidev')"
                                    value="<?php echo e(old('father_name_hindi', $student->father_name_hindi)); ?>">
                                <div class="col-md-12 mt-2  hindi_keyboard" style="">
                                    <?php echo $__env->make('convodataverification.admin.pages.hindi_keyboard_father', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="father_name_krutidev">Father's First Name (Krutidev):</label>
                                <input type="text" class="form-control" id="father_name_krutidev"
                                    name="father_name_krutidev"
                                    value="<?php echo e(old('father_name_krutidev', $student->father_name_krutidev)); ?>">
                            </div>
                        </div>
                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="course_name">Competency Level:<small style="color:red">*</small>
                                    <?php if($student->studentAckLogs): ?>
                                        <?php echo @$student->studentAckLogs->cs_en_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : ''; ?>

                                        <?php echo @$student->studentAckLogs->cs_en_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : ''; ?>

                                    <?php endif; ?>
                                </label>
                                <input type="text" class="form-control" id="course_name" name="course_name"
                                    onchange="english_text_change(this,'#course_name_hindi','#course_name_krutidev')" onpaste="english_text_change(this,'#course_name_hindi','#course_name_krutidev')"
                                    value="<?php echo e(old('course_name', $student->course_name)); ?>" required>
                            </div>

                            <div class="form-group col-md-4" style="margin-bottom: 11rem;">
                                <label for="course_name_hindi">Competency Level (Hindi):<small style="color:red">*</small>
                                    <?php if($student->studentAckLogs): ?>
                                        <?php echo @$student->studentAckLogs->cs_hi_status == 0
                                            ? '<i class="fa fa-times-circle" style="font-size: 14px;color:red"></i>'
                                            : ''; ?>

                                        <?php echo @$student->studentAckLogs->cs_hi_status == 1
                                            ? '<i class="fa fa-check-circle" style="font-size: 14px;color:rgb(15, 235, 26)"></i>'
                                            : ''; ?>

                                    <?php endif; ?>
                                </label>
                                <input type="text" class="form-control" style="margin-bottom: 2%;"
                                    id="course_name_hindi" name="course_name_hindi"
                                    onchange="hindi_text_change(this,'#course_name_krutidev')" onpaste="hindi_text_change(this,'#course_name_krutidev')"
                                    value="<?php echo e(old('course_name_hindi', $student->course_name_hindi)); ?>" required>

                                <div class="col-md-12 mt-2  hindi_keyboard" style="">

                                    
                                    <?php echo $__env->make('convodataverification.admin.pages.hindi_keyboard_course', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="course_name_krutidev">Competency Level (Krutidev):<small
                                        style="color:red">*</small></label>
                                <input type="text" class="form-control" id="course_name_krutidev"
                                    name="course_name_krutidev"
                                    value="<?php echo e(old('course_name_krutidev', $student->course_name_krutidev)); ?>" required>
                            </div>
                        </div>


                        <hr>
                        <div class="form-row row">
                            <div class="form-group col-md-3">
                                <label for="first_name">First Name:</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    value="<?php echo e(old('first_name', $student->first_name)); ?>" placeholder="Enter First Name">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="middle_name">Middle Name:</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name"
                                    value="<?php echo e(old('middle_name', $student->middle_name)); ?>"
                                    placeholder="Enter Middle Name">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="last_name">Last Name:</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    value="<?php echo e(old('last_name', $student->last_name)); ?>" placeholder="Enter Last Name">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="student_mobile_no">Student Mobile No:</label>
                                <input type="text" class="form-control" id="student_mobile_no"
                                    name="student_mobile_no"
                                    value="<?php echo e(old('student_mobile_no', $student->student_mobile_no)); ?>"
                                    placeholder="Enter Mobile Number">
                            </div>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-6">
                                <label for="permanent_address">Permanent Address:</label>
                                <textarea class="form-control" id="permanent_address" name="permanent_address"
                                    placeholder="Enter Permanent Address"><?php echo e(old('permanent_address', $student->permanent_address)); ?></textarea>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="local_address">Local Address:</label>
                                <textarea class="form-control" id="local_address" name="local_address" placeholder="Enter Local Address"><?php echo e(old('local_address', $student->local_address)); ?></textarea>
                            </div>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="certificateid">Certificate ID:</label>
                                <input type="number" class="form-control" id="certificateid" name="certificateid"
                                    value="<?php echo e(old('certificateid', $student->certificateid)); ?>"
                                    placeholder="Enter Certificate ID">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="cohort_id">Cohort ID:</label>
                                <input type="number" class="form-control" id="cohort_id" name="cohort_id"
                                    value="<?php echo e(old('cohort_id', $student->cohort_id)); ?>" placeholder="Enter Cohort ID">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="cohort_name">Cohort Name:</label>
                                <input type="text" class="form-control" id="cohort_name" name="cohort_name"
                                    value="<?php echo e(old('cohort_name', $student->cohort_name)); ?>"
                                    placeholder="Enter Cohort Name">
                            </div>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="faculty_name">Faculty Name:</label>
                                <input type="text" class="form-control" id="faculty_name" name="faculty_name"
                                    value="<?php echo e(old('faculty_name', $student->faculty_name)); ?>"
                                    placeholder="Enter Faculty Name">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="specialization">Specialization:</label>
                                <input type="text" class="form-control" id="specialization" name="specialization"
                                    value="<?php echo e(old('specialization', $student->specialization)); ?>"
                                    placeholder="Enter Specialization">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="rank">Rank:</label>
                                <input type="text" class="form-control" id="rank" name="rank"
                                    value="<?php echo e(old('rank', $student->rank)); ?>" placeholder="Enter Rank">
                            </div>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-4">
                                <label for="medal_type">Medal Type:</label>
                                <input type="text" class="form-control" id="medal_type" name="medal_type"
                                    value="<?php echo e(old('medal_type', $student->medal_type)); ?>" placeholder="Enter Medal Type">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="completion_date">Completion Date:</label>
                                <input type="date" class="form-control" id="completion_date" name="completion_date"
                                    value="<?php echo e(old('completion_date', $student->completion_date)); ?>">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="issue_date">Issue Date:</label>
                                <input type="date" class="form-control" id="issue_date" name="issue_date"
                                    value="<?php echo e(old('issue_date', $student->issue_date)); ?>">
                            </div>
                        </div>


                        <div class="form-row row">
                            <div class="col-md-12">
                                <label for="photograph">Student Photograph:<small style="color:red">*</small></label>
                                <br>
                                <img id="photo-display" class="photograph" style="height: 25mm; width: 25mm;  display: none;"
                                    alt="Student Photograph">
                                    <div class="row" style="text-align: center;">
                                        <div class="col-md-12">
                                            <button id="cropButton" type="button" class="btn btn-success"
                                                style="display: none; margin-bottom: 10px;margin-top: 10px;"><i
                                                    class="fa fa-crop" style="font-size:16px;margin-right:2%"></i>
                                                Crop</button>
                                            <button id="cropButton_close" type="button" class="btn btn-danger"
                                                style="display: none; margin-bottom: 10px;margin-top: 10px;">Close</button>
    
                                        </div>
    
                                    </div>
                                <input type="file" id="photograph" style="width: 30%;" name="photograph"
                                    class="form-control" accept="image/*"
                                    <?php if($student->student_photo): ?> data-photo-exists="true" <?php else: ?> data-photo-exists="false" <?php endif; ?>>
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
                        <div class="row" style="margin-bottom: 20px">

                            <?php if(@$student->collection_mode): ?>
                                <hr>
                                <div class=" col-md-4">
                                    <h4> Collection Mode </h4>
                                    <?php if(@$student->collection_mode == 'By Post'): ?>
                                        <p>Receiving Degree Certificate by Post
                                            (Rs. 3000/-) 
                                            <?php if($student->delivery_country == "India"): ?>
                                            
                                            <?php else: ?>
                                            
                                            <?php endif; ?>
                                        </p>
                                        <p><b>Delivery Address: </b><?php echo e(@$student->delivery_address); ?></p>
                                        <p><b>Pincode: </b><?php echo e(@$student->delivery_pincode); ?></p>
                                        <p><b>Country: </b><?php echo e(@$student->delivery_country); ?></p>
                                    <?php elseif(@$student->collection_mode == 'Attending Convocation'): ?>
                                        <p><b>Attending Convocation In Person (Rs. 3000/-)</b></p>
                                        <p><b>Attire Size: </b><?php echo e(@$student->attire_size); ?></p>
                                      
                                        <div class="form-group border p-3" style="    margin-top: 8%;">
                                            <label for="no_of_people_accompanied">No. Of People Accompanied With You:</label>
                                            <input 
                                              oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                            class="form-control" id="no_of_people_accompanied" name="no_of_people_accompanied" 
                                               min="0" type="number" value="<?php echo e(@$student->no_of_people_accompanied); ?>">
        
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            
                        </div>
                        <?php if(!$isPdfApproved): ?>
                        <div class="form-row row">
                            <div class=" col-md-6">
                                <div class="">
                                <label for="correction_message">Message To Student:</label>
                                <textarea class="form-control" id="correction_message" name="correction_message"  placeholder="Enter Message"></textarea>
                            </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="form-row row">
                         
                            <div class="form-group col-md-6">
                              

                                  <button type="submit" style=" " class=" btn btn-primary">Update</button>    
                                <a class="btn btn-danger" href="javascript:history.back()">Back</a>
                            </div>
                        </div>
                    </form>


                </div>

            </div>
            <?php
            $domain = \Request::getHost();
            $subdomain = explode('.', $domain);
            $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
            $path = $protocol . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/';
            $certificate_pdf = $path . $subdomain[0] . '/' . 'backend' . '/' . 'convocation' . '/' . 'certificate' . '/' . $student->certificate_pdf;
            if($subdomain[0] == "convocation"){
                 $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.edu.in/';
                 $certificate_pdf = $path . 'mitwpu' . '/' . 'backend' . '/' . 'convocation' . '/' . 'certificate' . '/' . $student->certificate_pdf;

                }
            ?>
            <?php if(!empty($student->certificate_pdf)): ?>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <a class="btn btn-primary" onclick="$('.certificate_div').toggle()">View Pdf</a>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 certificate_div" style="margin-top: 1%;display:none">
                        <embed src="<?php echo e($certificate_pdf); ?>?t=<?php echo e(time()); ?>#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf"
                            width="600" height="880">

                    </div>

                    
                </div>
            <?php endif; ?>
            <?php
                $logs = $student->studentAckLogs;
            ?>
            <?php if(
                !empty($logs) &&
                    ( 
                        $logs->fn_en_status == 0 ||
                        $logs->fn_hi_status == 0 ||
                        $logs->cs_en_status == 0 ||
                        $logs->cs_hi_status == 0 ||
                        $logs->mn_en_status == 0 ||
                        $logs->mn_hi_status == 0 ||
                        $logs->ftn_en_status == 0 ||
                        $logs->ftn_hi_status == 0)): ?>

                <div class="row centered-card student_request_class"
                    style="border-top: 1px solid rgb(209, 209, 214); padding-top: 1%;">

                    <div class="col-md-12 col-lg-12">
                        <h3><strong>Student Request for Correction</strong></h3>



                        <?php if($logs->fn_en_status == 0): ?>
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Name As per Tc</strong></h4>
                                <?php if($logs->fn_en_remark): ?>
                                    <p style="margin-top:1%"><b>Remark: </b><?php echo e($logs->fn_en_remark); ?></p>
                                <?php endif; ?>
                                <?php if($logs->fn_en_image): ?>
                                    <b>Image: </b><br><a href="<?php echo e(url($baseDirectoryPath . $logs->fn_en_image)); ?>"
                                        target="_blank"><img src="<?php echo e(url($baseDirectoryPath . $logs->fn_en_image)); ?>"
                                            alt="Fn En Image" style="width: 9%;"></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($logs->fn_hi_status == 0): ?>
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Full Name (Hindi)</strong></h4>
                                <?php if($logs->fn_hi_remark): ?>
                                    <p style="margin-top:1%"><b>Remark: </b><?php echo e($logs->fn_hi_remark); ?></p>
                                <?php endif; ?>
                                <?php if($logs->fn_hi_image): ?>
                                    <b>Image: </b><br><a href="<?php echo e(url($baseDirectoryPath . $logs->fn_hi_image)); ?>"
                                        target="_blank"><img src="<?php echo e(url($baseDirectoryPath . $logs->fn_hi_image)); ?>"
                                            alt="Fn Hi Image" style="width: 9%;"></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($logs->cs_en_status == 0): ?>
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Competency Level</strong></h4>
                                <?php if($logs->cs_en_remark): ?>
                                    <p style="margin-top:1%"><b>Remark: </b><?php echo e($logs->cs_en_remark); ?></p>
                                <?php endif; ?>
                                <?php if($logs->cs_en_image): ?>
                                    <b>Image: </b><br><a href="<?php echo e(url($baseDirectoryPath . $logs->cs_en_image)); ?>"
                                        target="_blank"><img src="<?php echo e(url($baseDirectoryPath . $logs->cs_en_image)); ?>"
                                            alt="Cs En Image" style="width: 9%;"></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($logs->cs_hi_status == 0): ?>
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Competency Level (Hindi)</strong></h4>
                                <?php if($logs->cs_hi_remark): ?>
                                    <p style="margin-top:1%"><b>Remark: </b><?php echo e($logs->cs_hi_remark); ?></p>
                                <?php endif; ?>
                                <?php if($logs->cs_hi_image): ?>
                                    <b>Image: </b><br><a href="<?php echo e(url($baseDirectoryPath . $logs->cs_hi_image)); ?>"
                                        target="_blank"><img src="<?php echo e(url($baseDirectoryPath . $logs->cs_hi_image)); ?>"
                                            alt="Cs Hi Image" style="width: 9%;"></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                      
                        <?php if($logs->mn_en_status == 0): ?>
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Mother's First Name (English)</strong></h4>
                                <?php if($logs->mn_en_remark): ?>
                                    <p style="margin-top:1%"><b>Remark: </b><?php echo e($logs->mn_en_remark); ?></p>
                                <?php endif; ?>
                                <?php if($logs->mn_en_image): ?>
                                    <b>Image: </b><br>
                                    <a href="<?php echo e(url($baseDirectoryPath . $logs->mn_en_image)); ?>" target="_blank">
                                        <img src="<?php echo e(url($baseDirectoryPath . $logs->mn_en_image)); ?>"
                                            alt="Mother Name En Image" style="width: 9%;">
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($logs->mn_hi_status == 0): ?>
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Mother's First Name (Hindi)</strong></h4>
                                <?php if($logs->mn_hi_remark): ?>
                                    <p style="margin-top:1%"><b>Remark: </b><?php echo e($logs->mn_hi_remark); ?></p>
                                <?php endif; ?>
                                <?php if($logs->mn_hi_image): ?>
                                    <b>Image: </b><br>
                                    <a href="<?php echo e(url($baseDirectoryPath . $logs->mn_hi_image)); ?>" target="_blank">
                                        <img src="<?php echo e(url($baseDirectoryPath . $logs->mn_hi_image)); ?>"
                                            alt="Mother Name Hi Image" style="width: 9%;">
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($logs->ftn_en_status == 0): ?>
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Father's First Name (English)</strong></h4>
                                <?php if($logs->ftn_en_remark): ?>
                                    <p style="margin-top:1%"><b>Remark: </b><?php echo e($logs->ftn_en_remark); ?></p>
                                <?php endif; ?>
                                <?php if($logs->ftn_en_image): ?>
                                    <b>Image: </b><br>
                                    <a href="<?php echo e(url($baseDirectoryPath . $logs->ftn_en_image)); ?>" target="_blank">
                                        <img src="<?php echo e(url($baseDirectoryPath . $logs->ftn_en_image)); ?>"
                                            alt="Father Name En Image" style="width: 9%;">
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($logs->ftn_hi_status == 0): ?>
                            <div class="col-md-12 col-lg-12 request_div">
                                <h4><strong>Father's First Name (Hindi)</strong></h4>
                                <?php if($logs->ftn_hi_remark): ?>
                                    <p style="margin-top:1%"><b>Remark: </b><?php echo e($logs->ftn_hi_remark); ?></p>
                                <?php endif; ?>
                                <?php if($logs->ftn_hi_image): ?>
                                    <b>Image: </b><br>
                                    <a href="<?php echo e(url($baseDirectoryPath . $logs->ftn_hi_image)); ?>" target="_blank">
                                        <img src="<?php echo e(url($baseDirectoryPath . $logs->ftn_hi_image)); ?>"
                                            alt="Father Name Hi Image" style="width: 9%;">
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <hr>
                </div>
            <?php endif; ?>


            <?php if(!$student_ack_logs->isEmpty()): ?>
                <div class="row">
                    <h2><i class="fa fa-history" style="font-size: 30px;"></i>
                        Student Ack Log</h2> <!-- Add this line for the heading -->
                    <hr class="my-4">
                    <table class="table table-bordered" id="logs">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name As per Tc Remark</th>
                                <th>Name As per Tc Hindi Remark</th>
                                <th>Mother's First Name Remark</th>
                                <th>Mother's First Name Hindi Remark</th>
                                <th>Father's First Name Remark</th>
                                <th>Father's First Name Hindi Remark</th>
                                <th>Competency Level Remark</th>
                                <th>Competency Level Hindi Remark</th>
                               
                                
                                <th>Secondary Email Remark</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $student_ack_logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student_ack): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td> <!-- This will show a serial number starting from 1 -->
                                    <td>
                                        <?php
                                        if($subdomain[0] =='convocation') {
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
                                        <?php echo e($html); ?>

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
                                        <?php echo e($html); ?>

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
                                        <?php echo e($html); ?>

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
                                        <?php echo e($html); ?>

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
                                        <?php echo e($html); ?>

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
                                        <?php echo e($html); ?>

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
                                        <?php echo e($html); ?>

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
                                        <?php echo e($html); ?>

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
                                        <?php echo e($html); ?>

                                        <?php
                                        if ($filePath != '') {
                                            echo '<a href="' . $filePath . '" target="_blank" >Click Here to image</a> ';
                                        }
                                        ?>

                                    </td>
                                    
                                    <td><?php echo e($student_ack->created_at->format('d-m-y h:i A')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </tbody>
                    </table>

                </div>
            <?php endif; ?>

            <hr>
            <?php if(!$convo_student_logs->isEmpty()): ?>
                <div class="row">
                    <h2><i class="fa fa-history" style="font-size: 30px;    "></i>
                        Admin Ack Log</h2> <!-- Add this line for the heading -->
                    <hr class="my-4">
                    <div style="overflow-x:auto;">
                        <table id="logs_admin"  class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student Email ID</th>
                                    <th>Secondary Email ID</th>
                                    <th>Gender</th>
                                    <th>DOB</th>
                                    
                                    <th>Name As per Tc</th>
                                    <th>Name As per Tc (Hindi)</th>
                                    <th>Name As per Tc (Krutidev)</th>
                                    <th>Mother's First Name</th>
                                    <th>Mother's First Name (Hindi)</th>
                                    <th>Mother's First Name (Krutidev)</th>
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
                                <?php $__currentLoopData = $convo_student_logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $convo_student_log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>

                                        <td><?php echo e($index + 1); ?></td>
                                        <!-- This will show a serial number starting from 1 -->
                                        <td><?php echo e($convo_student_log->wpu_email_id ? $convo_student_log->wpu_email_id : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->secondary_email_id ? $convo_student_log->secondary_email_id : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->gender ? $convo_student_log->gender : '-'); ?></td>
                                        <td><?php echo e($convo_student_log->date_of_birth != '0000-00-00' ? $convo_student_log->date_of_birth : '-'); ?>

                                        </td>
                                        
                                        <td><?php echo e($convo_student_log->full_name ? $convo_student_log->full_name : '-'); ?></td>
                                        <td><?php echo e($convo_student_log->full_name_hindi ? $convo_student_log->full_name_hindi : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->full_name_krutidev ? $convo_student_log->full_name_krutidev : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->mother_name ? $convo_student_log->mother_name : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->mother_name_hindi ? $convo_student_log->mother_name_hindi : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->mother_name_krutidev ? $convo_student_log->mother_name_krutidev : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->father_name ? $convo_student_log->father_name : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->father_name_hindi ? $convo_student_log->father_name_hindi : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->father_name_krutidev ? $convo_student_log->father_name_krutidev : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->course_name ? $convo_student_log->course_name : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->course_name_hindi ? $convo_student_log->course_name_hindi : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->course_name_krutidev ? $convo_student_log->course_name_krutidev : '-'); ?>

                                        </td>


                                        <td><?php echo e($convo_student_log->first_name ? $convo_student_log->first_name : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->middle_name ? $convo_student_log->middle_name : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->last_name ? $convo_student_log->last_name : '-'); ?></td>
                                        <td><?php echo e($convo_student_log->student_mobile_no ? $convo_student_log->student_mobile_no : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->permanent_address ? $convo_student_log->permanent_address : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->local_address ? $convo_student_log->local_address : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->certificateid ? $convo_student_log->certificateid : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->cohort_id ? $convo_student_log->cohort_id : '-'); ?></td>
                                        <td><?php echo e($convo_student_log->cohort_name ? $convo_student_log->cohort_name : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->faculty_name ? $convo_student_log->faculty_name : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->specialization ? $convo_student_log->specialization : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->rank ? $convo_student_log->rank : '-'); ?></td>
                                        <td><?php echo e($convo_student_log->medal_type ? $convo_student_log->medal_type : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->completion_date != '0000-00-00' ? $convo_student_log->completion_date : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->issue_date != '0000-00-00' ? $convo_student_log->issue_date : '-'); ?>

                                        </td>
                                        <td><?php echo e($convo_student_log->correction_message); ?></td> 
                                        <td><?php echo e(date('d-m-y h:i A', strtotime($convo_student_log->log_date))); ?></td>

                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </tbody>
                        </table>
                    </div>

                </div>
            <?php endif; ?>

            <?php if(!$payments->isEmpty()): ?>
                <div class="row">
                    <h2><i class="fa fa-credit-card" style="font-size: 30px;    "></i>
                        Payment Details</h2> <!-- Add this line for the heading -->
                    <hr class="my-4">
                    <table class="table table-bordered" id="payments">
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
                            <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td> <!-- This will show a serial number starting from 1 -->
                                    <td><?php echo e($payment->txn_id); ?></td>
                                    <td><?php echo e($payment->txn_amount); ?></td>
                                    <td><?php echo e($payment->txn_date->format('Y-m-d h:i A')); ?></td> <!-- Format the date here -->
                                    <td>
                                        <?php switch($payment->status):
                                            case ('TXN_SUCCESS'): ?>
                                                SUCCESS
                                                <?php break; ?>
                                            <?php case ('TXN_FAILURE'): ?>
                                                FAILED
                                                <?php break; ?>
                                            <?php case ('PENDING'): ?>
                                                PENDING
                                                <?php break; ?>
                                            <?php default: ?>
                                                UNKNOWN STATUS
                                        <?php endswitch; ?>
                                    </td>
                                    <td><?php echo e($payment->payment_mode); ?></td>
                                    <td><?php echo e($payment->gateway_name); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </tbody>
                    </table>

                </div>
            <?php endif; ?>
            <div id="divLoading">
                <p>Processing... Please wait ..... <img src="/backend/images/loading.gif"></p>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>

    <script>
        var token = $('meta[name="csrf-token"]').attr('content');

        // Set the token in jQuery AJAX headers
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': token
            }
        });

        $(document).ready(function() {
            $.validator.addMethod("fileSize", function(value, element, param) {
                console.log(element.files[0]);
                var fileSize = element.files[0] ? element.files[0].size : 0;
                return this.optional(element) || (fileSize >= param[0] && fileSize <= param[1]);
            }, "File size must be between 5 KB and 500 KB.");


            $.validator.addMethod("isCropInProgress", function(value, element) {
                // Check if the cropping process is in progress by verifying the visibility of the crop button

                return !$('#cropButton').is(':visible');
            }, "Please complete a cropping process.");

            $("#student-form").validate({
                rules: {
                    prn: "required",
                    cgpa: {
                        required: function(element) {
                            return $(element); // Only require if it has a value
                        },
                        number: true,
                        min: 0,
                        max: 10
                    },
                    topic: {
                        required: true, 
                        required: function(element) {
                            return $(element); // Only require if it has a value
                        }
                    },
                    date_of_birth: "required",
                    wpu_email_id: {
                        required: true,
                        email: true
                    },
                    correction_message:{
                        maxlength: 500
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
                    secondary_email_id: "required",
                    gender: "required",
                    // father_name_hindi: "required",
                    // father_name_krutidev: "required",

                    photograph: {
                        required: function() {
                            return $('input[name="photograph"]').data('photo-exists') == false;
                        },

                        extension: "jpg|jpeg|png|gif",
                        fileSize: [5 * 1024, 500 * 1024],
                        isCropInProgress: true
                    },
                    no_of_people_accompanied: {
                        required: true,  // This will be ignored if the element is not present
                        max:5
                    }
                },
                messages: {
                    prn: "Please enter PRN",
                    topic: "Please enter topic",
                    cgpa: {
                        required: "Please enter CGPA",
                        number: "CGPA must be a number",
                        min: "CGPA must be at least 0",
                        max: "CGPA cannot be more than 10"
                    },
                    correction_message:{
                         maxlength: "Your input exceeds 500 characters. Please limit it to 500 characters."
                    },
                    date_of_birth: "Please select DOB",
                    gender: "Please select gender",
                    wpu_email_id: "Please enter a valid email address",
                    secondary_email_id: "Please enter a valid email address",
                    full_name: "Please enter Name As per Tc",
                    full_name_hindi: "Please enter Name As per Tc in Hindi",
                    full_name_krutidev: "Please enter Name As per Tc in Krutidev",
                    course_name: "Please enter Competency Level",
                    course_name_hindi: "Please enter Competency Level in Hindi",
                    course_name_krutidev: "Please enter Competency Level in Krutidev",
                    mother_name: "Please enter the mother's first name",
                    mother_name_hindi: "Please enter the mother's first name in Hindi",
                    mother_name_krutidev: "Please enter the mother's first name in Krutidev",
                    father_name: "Please enter the father's first name",
                    father_name_hindi: "Please enter the father's first name in Hindi",
                    father_name_krutidev: "Please enter the father's first name in Krutidev",
                    photograph: {
                        required: "Please upload a photograph",
                        extension: "Please upload a valid image file (jpg, jpeg, png, gif)"
                    },
                    no_of_people_accompanied:{
                        required: "Please specify the number of people accompanying the student.",
                        max:"Maximum 5 people allowed"
                    }                
                },
                    submitHandler: function(form) {
                    // Show loading overlay
                    $("#divLoading").show();

                    var formData = new FormData($(form)[0]);

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo e(route('convo_student.update', $student->id)); ?>",
                        data: formData,
                        contentType: false,
                        processData: false,
                        datatype: "JSON",
                        success: function(response) {
                            $("#divLoading").hide();

                            // $('.student_request_class').hide();
                            toastr.success('Student details updated successfully!')
                            $('#correction_message').val("");
                             setTimeout(() => {
                                window.location.reload();
                             }, 2000);
                            // Optionally redirect or update UI
                            // window.location.reload(); // Uncomment to reload the page
                        },
                        error: function(xhr, status, error) {
                            $("#divLoading").hide();
                            toastr.error('Something went wrong!')
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

        $('#logs').DataTable();
        $('#payments').DataTable();
        $('#logs_admin').DataTable();

        $(document).ready(function() {
            var photoDisplay = $('#photo-display');
            var photoExists = $('#photograph').data('photo-exists');
           
            // If an existing photo exists, show it
            if (photoExists) {
                var currentPhotoUrl = "<?php echo e(url($baseDirectoryPath . $student->student_photo)); ?>";
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
    <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.min.css">
    <script src="https://unpkg.com/cropperjs/dist/cropper.min.js"></script>

    <script>
        $(document).ready(function() {
            let cropper;

            $('#photograph').on('change', function(e) {
                const files = e.target.files;

                if (files && files.length > 0) {
                    const reader = new FileReader();

                    reader.onload = function(event) {
                        $('#photo-display').attr('src', event.target.result).show();
                        $('#cropButton').show(); // Show the crop button
                        $('#cropButton_close').show();
                        if (cropper) {
                            cropper.destroy();
                        }

                        // Initialize the cropper
                        cropper = new Cropper($('#photo-display')[0], {
                            aspectRatio: 1, // Fixed aspect ratio of 2:1 (1:0.5) 
                            responsive: true,
                        });
                        // $('#photograph')[0].files = '';
                    };

                    reader.readAsDataURL(files[0]);
                    $(this).val('');
                }
            });

            $('#cropButton_close').on('click', function() {
                $(this).hide();
                $('#cropButton').hide();
                cropper.destroy();
                // $('#photograph')[0].files =  old_image;
                var photoDisplay = $('#photo-display');
                var photoExists = $('#photograph').data('photo-exists');

                // If an existing photo exists, show it
                if (photoExists) {
                    var currentPhotoUrl = "<?php echo e(url($baseDirectoryPath . $student->student_photo)); ?>";
                    photoDisplay.attr('src', currentPhotoUrl).show();
                } else {
                    photoDisplay.attr('src', '').hide();
                }
                $("#student-form").validate().element($('#photograph'));

            });
            $('#cropButton').on('click', function() {
                const canvas = cropper.getCroppedCanvas({
                    width: 95,
                    height: 95,
                });
                const randomNum = Math.floor(Math.random() * 10000); // Random number between 0 and 9999
                const timestamp = Date.now(); // Current timestamp in milliseconds
                const fileName = `cropped_image_${timestamp}_${randomNum}.png`; // Create filename
                // Convert the canvas to a Blob
                canvas.toBlob((blob) => {
                    const newFile = new File([blob], fileName, {
                        type: 'image/png'
                    });
                    const dataTransfer = new DataTransfer(); // Create a new DataTransfer object
                    dataTransfer.items.add(newFile); // Add the new file
                    $('#photograph')[0].files = dataTransfer.files; // Update the file input

                    const croppedImage = canvas.toDataURL();
                    $('#photo-display').attr('src', croppedImage).show(); // Show the cropped image
                    $("#student-form").validate().element($('#photograph'));

                    // Hide the crop button and clean up
                    $(this).hide();
                    $('#cropButton_close').hide();
                    cropper.destroy(); // Clean up cropper instance
                }, 'image/png');
            });
        });
    </script>
 
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/admin/pages/edit.blade.php ENDPATH**/ ?>