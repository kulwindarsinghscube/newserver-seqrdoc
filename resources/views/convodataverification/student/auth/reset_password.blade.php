@extends('admin.layout.layoutnonheader')

@section('style')
@stop
<style>
    body {
        position: relative;
        overflow: hidden;
        min-height: 100vh;
        background-color: #f8f9fa;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('{{ asset('backend/convodataverification/images/reset_image.png') }}');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        filter: blur(7px);
        z-index: -1;
    }

    .reset-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .reset-form {
        width: 100%;
        max-width: 400px;
        padding: 2rem;
        background-color: #ffffff;
        border-radius: .5rem;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .reset-form h1 {
        margin-bottom: 1rem;
    }

    .div {
        margin-bottom: 15px;
    }
</style>

@section('content')
    <div class="container">
        <div class="reset-container">
            <div class="reset-form">
                <h1 class="h3 mb-3 fw-normal text-center">Student Reset Password</h1>
                <form method="POST" action="#" id="resetForm" class="resetForm">
                    <div class="reset_response" style="display:none"></div>
                    @csrf
                    <div class="div mb-3 mt-2">
                        <label for="prn" class="form-label">PRN</label>
                        <input readonly type="text" id="prn" name="prn" class="form-control" value="{{ $student->prn }}">
                    </div>

                    <div class="div mb-3 mt-2">
                        <label for="password" class="form-label">New Password<span style="color:red">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your Password" >

                        <span id="password_errors" class="help-inline text-danger"><?=$errors->first('password')?></span>
                    </div>

                    <div class="div mb-3 mt-2">
                        <label for="password" class="form-label">Confirm Password<span style="color:red">*</span></label>
                        <input id="password-confirm" type="password" class="form-control" name="confirm_password" placeholder="Enter your Confirm Password" >
                        <span id="confirm_password_errors" class="help-inline text-danger"><?=$errors->first('confirm_password')?></span>
                    </div>

                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    
                    <div class="div text-right">
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script>
        $(document).ready(function() {
            $("form#resetForm").submit(function(e){
                e.preventDefault();
                var token = "{{ csrf_token() }}";
                if (!$('.resetForm').valid())
                {
                    return false;
                }else{
                    var formData = new FormData($(this)[0]);
                    $.ajax({
                        url: "{{route('convo_student.resetPasswordUpdate')}}",
                        type: 'POST',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType:'JSON',

                        success: function (data) {
                            if (data.errors) {
                                var errorString = '';
                                $.each(data.errors, function (key, value) {
                                    
                                    $('#'+key+'_errors').text(value)
                                    
                                });
                            } else {
                                toastr.success("Reset Password Successfully.");
                                window.location.href = "<?=route('convo_student.dashboard')?>";
                                $('#resetForm')[0].reset();
                            }
                            // if (data.success) {
                            //     toastr.success("Reset Password Successfully.");
                            //     $('#resetForm')[0].reset();
                            //     // window.location.href = "<?=route('convo_student.dashboard')?>";  // Redirect to dashboard or another page
                            // } else if (data.errors) {
                            //     var errorString = '';
                            //     $.each(data.errors, function (key, value) {
                                    
                            //         $('#'+key+'_errors').text(value)
                                    
                            //     });
                            // } else {
                                
                            //     $('.reset_response').html('<div class="alert alert-danger"><i class="fa fa-times-circle" style="margin-right: 6px;"></i>' + response.message + '</div>').show();
                            // }

                        }
                    });
                }
            });

            // $("#resetForm").validate({
            //     rules: {
            //         password: {
            //             required: true,
            //             minlength: 8,
            //             // alphanumeric: true
            //         }
            //     },
            //     messages: {
            //         password: {
            //             required: "Please provide your password",
            //             minlength: "Your password must be at least 8 characters long"
            //         }
            //     },
            //     errorClass: "text-danger",
            //     validClass: "text-success",
            //     errorElement: "div",
            //     highlight: function(element, errorClass) {
            //         $(element).addClass(errorClass);
            //     },
            //     unhighlight: function(element, errorClass) {
            //         $(element).removeClass(errorClass);
            //     },
            //     errorPlacement: function(error, element) {
            //         error.insertAfter(element);
            //     },
            //     submitHandler: function(form) {
            //         $('.reset_response').html('').hide();
            //         $.ajax({
            //             type: 'POST',
            //             url: "<?=route('convo_student.resetPasswordUpdate')?>",  // Update with your actual reset URL
            //             data: $(form).serialize(),
            //             success: function(response) {
            //                 console.log(response);
            //                 if (response.success) {
            //                     toastr.success("Reset Password Successfully.");
            //                     // window.location.href = "<?=route('convo_student.dashboard')?>";  // Redirect to dashboard or another page
            //                 } else {
                                
            //                     $('.reset_response').html('<div class="alert alert-danger"><i class="fa fa-times-circle" style="margin-right: 6px;"></i>' + response.message + '</div>').show();
            //                 }
            //             },
            //             error: function(xhr) {
            //                 $('.reset_response').html('<div class="alert alert-danger">An error occurred. Please try again.</div>').show();
            //             }
            //         });
            //     }
            // });
        });
    </script>
@stop
