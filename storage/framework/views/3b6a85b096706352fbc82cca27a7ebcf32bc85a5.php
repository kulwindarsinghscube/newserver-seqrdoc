<?php $__env->startSection('style'); ?>
<?php $__env->stopSection(); ?>
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
        background-image: url('<?php echo e(asset('backend/convodataverification/images/reset_image.png')); ?>');
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

    .captcha {
        width: 100%;
        background: #f0f8ff;
        /* Light Alice Blue background */
        min-height: 63px;
        margin-bottom: 2%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #333;
        /* Dark Gray text */
        font-weight: bold;
        position: relative;
    }

    .captcha span {
        display: inline-block;
        margin: 0 5px;
        font-size: 30px;
        transition: transform 0.2s;
        color: #2c3e50;
        /* Dark Blue text color */
    }

</style>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="reset-container">
            <div class="reset-form">
                <h1 class="h3 mb-3 fw-normal text-center">Student Reset Password</h1>
                <form method="POST" action="#" id="resetForm" class="resetForm">
                    <div class="reset_response" style="display:none"></div>
                    <?php echo csrf_field(); ?>
                    <div class="div mb-3 mt-2">
                        <label for="prn" class="form-label">PRN <span style="color:red">*</span></label>
                        <input type="text" placeholder="Enter your prn" id="prn" name="prn" class="form-control">
                        <span id="prn_errors" class="help-inline error_msg_element text-danger"><?= $errors->first('prn') ?></span>

                    </div>


                    <div class="div mb-3 mt-2">
                        <label for="prn" class="form-label">DOB <span style="color:red">*</span></label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                        <span id="date_of_birth_errors"
                            class="help-inline text-danger error_msg_element"><?= $errors->first('date_of_birth') ?></span>

                    </div>

                    <div class="div mb-3 mt-2">
                        <label for="prn" class="form-label">Email ID <span style="color:red">*</span></label>
                        <input type="email" id="wpu_email_id" name="wpu_email_id"  placeholder="Enter your email id" class="form-control">
                        <span id="wpu_email_id_errors"
                            class="help-inline text-danger error_msg_element"><?= $errors->first('wpu_email_id') ?></span>

                    </div>


                    <div class="div mb-3 mt-2">
                        <label for="password" class="form-label">Captcha<span style="color:red">*</span>
                            <span id="refresh-captcha" class="refresh-button" onclick="">🔄</span></label>
                        <div id="captcha-container" class="captcha"></div>
                        <input type="text" id="captcha" name="captcha" class="form-control"
                            placeholder="Enter captcha" required>
                    </div>

                    


                    <div class="div text-right">
                        <button type="submit" class=" reset_btn btn btn-primary w-100">Send Reset Password Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        $(document).ready(function() {
            $("form#resetForm").submit(function(e) {
                e.preventDefault();
                var token = "<?php echo e(csrf_token()); ?>";
                $('.error_msg_element').text('');
                if (!$('.resetForm').valid()) {
                    return false;
                } else {
                    $('.reset_btn').prop('disabled', true);
                    var formData = new FormData($(this)[0]);
                    $.ajax({
                        url: "<?php echo e(route('convo_student.send_password_reset_request')); ?>",
                        type: 'POST',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: 'JSON',

                        success: function(data) {
                            if (data.errors) {
                                var errorString = '';
                                $.each(data.errors, function(key, value) {

                                    $('#' + key + '_errors').text(value)

                                });

                                $('.reset_btn').prop('disabled', false);
                            } else {
                                toastr.success("A reset password link has been sent to your email address.");
                                $('#resetForm')[0].reset();
                                $('.reset_btn').prop('disabled', false);
                                
                            }
                           

                        }
                    });
                }
            });

            $.validator.addMethod("pattern", function(value, element, pattern) {
                return this.optional(element) || pattern.test(value);
            }, "Invalid format.");

            $.validator.addMethod('captchaEqual', function(value, element) {
                const storedCaptcha = $(element).data('captcha');
                return value === storedCaptcha; // Compare input with stored CAPTCHA
            }, 'captcha is incorrect');

            $("#resetForm").validate({
                rules: {
                    prn: {
                        required: true,
                        minlength: 5 // Adjust this length based on your PRN requirements
                    },
                    date_of_birth: {
                        required: true,
                        dateISO: true // Ensures the date is in ISO format (YYYY-MM-DD)
                    },
                    wpu_email_id: {
                        required: true,
                        email: true
                    },
                    captcha: {
                        required: true,
                        minlength: 5,
                        captchaEqual: true
                    }
                },
                messages: {
                    prn: {
                        required: "Please enter your PRN",
                        minlength: "PRN must be at least 5 characters long"
                    },
                    date_of_birth: {
                        required: "Please enter your date of birth",
                        dateISO: "Please enter a valid date in the format YYYY-MM-DD"
                    },
                    wpu_email_id: {
                        required: "Please enter your email address",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please provide your password",
                        minlength: "Your password must be at least 8 characters long",
                        pattern: "Password must contain at least one uppercase letter, one lowercase letter, and one digit"
                    },
                    confirm_password: {
                        required: "Please confirm your password",
                        minlength: "Password confirmation must be at least 8 characters long",
                        equalTo: "Passwords do not match"
                    },
                    captcha: {
                        required: "Please enter the captcha",
                        minlength: "captcha must be 5 characters long",
                        equalTo: "captcha is incorrect"
                    }
                },
                errorClass: "text-danger",
                validClass: "text-success",
                errorElement: "div",
                highlight: function(element, errorClass) {
                    $(element).addClass(errorClass);
                },
                unhighlight: function(element, errorClass) {
                    $(element).removeClass(errorClass);
                },
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                }
            });
        });

        $(document).ready(function() {
            function generateRandomCaptcha(length) {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let captcha = '';
                for (let i = 0; i < length; i++) {
                    captcha += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return captcha;
            }

            function setCaptcha() {
                const $captchaContainer = $('#captcha-container');
                const captchaText = generateRandomCaptcha(5);
                $captchaContainer.empty(); // Clear previous CAPTCHA

                captchaText.split('').forEach((char) => {
                    const $span = $('<span></span>').text(char);
                    // Reduced rotation range: -5 to 5 degrees
                    const rotation = Math.floor(Math.random() * 10) - 5;
                    $span.css('transform', `rotate(${rotation}deg)`);
                    $captchaContainer.append($span);
                });
                $('#captcha').data('captcha', captchaText);
            }

            setCaptcha(); // Initialize CAPTCHA on page load

            // Optional: Refresh CAPTCHA on click
            $('#refresh-captcha').click(function() {
                setCaptcha();
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layoutnonheader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/student/auth/reset_password_all_details.blade.php ENDPATH**/ ?>