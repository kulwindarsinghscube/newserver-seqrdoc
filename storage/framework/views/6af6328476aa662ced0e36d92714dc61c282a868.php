<?php $__env->startSection('style'); ?>
<?php $__env->stopSection(); ?>
<style>
    body {
        position: relative;
        overflow: hidden;
        min-height: 100vh;
        background-color: #f8f9fa;
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

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo e(asset('backend/convodataverification/images/background_image.png')); ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        filter: blur(7px);
        z-index: -1;
    }

    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .login-form {
        width: 401px;
        max-width: 800px;
        padding: 2rem;
        background-color: #ffffff;
        border-radius: .5rem;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .login-form h1 {
        margin-bottom: 1rem;
    }

    .div {
        margin-bottom: 15px;
    }
</style>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="login-container">
            <div class="login-form">

                
                    

                <h1 class="h3 mb-3 fw-normal text-center">Student Login</h1>
                <form id="loginForm">
                    <div class="login_response" style="display:none"></div>
                    <?php echo csrf_field(); ?>
                    <div class="div mb-3 mt-2">
                        <label for="prn" class="form-label">PRN<span style="color:red">*</span></label>
                        <input type="text" id="prn" name="prn" class="form-control"
                            placeholder="Enter your prn" required>
                    </div>

                    <div class="div mb-3 mt-2">
                        <label for="date_of_birth" class="form-label">Date of Birth<span style="color:red">*</span></label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" required>
                    </div>

                    <div class="div mb-3 mt-2">
                        <label for="wpu_email_id" class="form-label">Email ID<span style="color:red">*</span></label>
                        <input type="email" id="wpu_email_id" name="wpu_email_id" class="form-control"
                            placeholder="Enter your email id" required>
                    </div>

                    <div class="div mb-3 mt-2">
                        <label for="password" class="form-label">Password<span style="color:red">*</span></label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Enter your password" required>
                    </div>
                    <div class="div mb-3 mt-2">
                        <label for="password" class="form-label">Captcha<span style="color:red">*</span>
                            <span id="refresh-captcha" class="refresh-button" onclick="">🔄</span></label>
                        <div id="captcha-container" class="captcha"></div>
                        <input type="text" id="captcha" name="captcha" class="form-control"
                            placeholder="Enter captcha" required>
                    </div>
                    <div class="div text-left">
                        <a href="https://demo.seqrdoc.com/demo/SSL STUDENT SUPPORT MANUAL - V1.0_10-09-2024.pdf" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> APPLICATION GUIDE</a>
                    </div>
                    <div class="div text-right">
                        <button type="submit" class="btn btn-primary w-100">Sign In</button>
                    </div>

                    <div class="div text-center">
                        <a href="<?php echo e(route('convo_student.reset_password_all_details')); ?>" style="cursor: pointer">Forgotten password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>
    <script>
        $(document).ready(function() {
            $.validator.addMethod('captchaEqual', function(value, element) {
                const storedCaptcha = $(element).data('captcha');
                return value === storedCaptcha; // Compare input with stored CAPTCHA
            }, 'captcha is incorrect');


            $("#loginForm").validate({
                rules: {
                    prn: {
                        required: true,
                        // minlength: 3
                    },
                    date_of_birth: {
                        required: true,
                        date: true
                    },
                    wpu_email_id: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        // minlength: 6
                    },
                    captcha: {
                        required: true,
                        minlength: 5,
                        captchaEqual: true
                    }
                },
                messages: {
                    prn: {
                        required: "Please enter your prn",
                        // minlength: "prn must be at least 3 characters long"
                    },
                    date_of_birth: {
                        required: "Please enter your date of birth",
                        date: "Please enter a valid date"
                    },
                    wpu_email_id: {
                        required: "Please enter your email id",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please provide your password",
                        minlength: "Your password must be at least 6 characters long"
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
                },
                submitHandler: function(form) {
                    $('.login_response').html('').hide();
                    $.ajax({
                        type: 'POST',
                        url: "<?= route('convo_student.login') ?>", // Update with your actual login URL
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.success) {
                                toastr.success("Login Successfully");
                                window.location.href =
                                    "<?= route('convo_student.dashboard') ?>"; // Redirect to dashboard or another page
                            } else {
                                if (Array.isArray(response.message)) {
                                        // Join array elements with a line break
                                        var messageHtml = response.message.join('<br><i class="fa fa-times-circle" style="margin-right: 6px;"></i>');
                                    } else {
                                        // If it's not an array, treat it as a single message
                                        var messageHtml = response.message;
                                    }

                                    // Display the messages
                                    $('.login_response').html(
                                        '<div class="alert alert-danger"><i class="fa fa-times-circle" style="margin-right: 6px;"></i>' +
                                       messageHtml + '</div>'
                                    ).show();
                            }
                        },
                        error: function(xhr) {
                            $('.login_response').html(
                                '<div class="alert alert-danger">An error occurred. Please try again.</div>'
                                ).show();
                        }
                    });
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

<?php echo $__env->make('admin.layout.layoutnonheader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/student/auth/login.blade.php ENDPATH**/ ?>