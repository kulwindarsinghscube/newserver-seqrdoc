<!DOCTYPE html>
<html>

<head>
    <title>Registration Completed</title>
</head>

<body>
    <h1>Dear Student,</h1>
    <p>This is to inform the students of all Programs of MIT WPU passing out in the Academic Year 2023-24 that MIT WPU
        is organizing the 6th Convocation ceremony. Following are the details:</p>
    <p><strong>Day and Date:</strong> Saturday 19 October 2024</p>
    <p><strong>Venue:</strong> World Peace Dome, Vishwarajbaug, Loni Kalbhor, Pune-412201, India</p>


    <p>Please
        <a href="https://convocation.mitwpu.edu.in/convo_student/login">log in</a>
        to verify your details, make the payment and approve the pdf.
    </p>

    <h2>Your Login Details are as below : </h2>
    <p><strong>Email ID:</strong> <?php echo e(@$student['wpu_email_id']); ?></p>
    <p><strong>PRN Number:</strong> <?php echo e(@$student['prn']); ?></p>
    <p><strong>Date of Birth:</strong> <?php echo e(\Carbon\Carbon::parse(@$student['date_of_birth'])->format('d-m-Y')); ?></p>
    <p><strong>Password:</strong> MITWPUPASS </p>

    <p>You can log in using the following URL: <a
            href="https://convocation.mitwpu.edu.in/convo_student/login">https://convocation.mitwpu.edu.in/convo_student/login</a>
    </p>
    
    <p>The user manual has been included for your reference.</p>

    <br>

    <p>Thank you!</p>


    <p>
        <strong>Controller of Examinations</strong> <br>
        Dr. Vishwanath Karad<br>
        MIT World Peace University,<br>
        Pune-411038 (India)
    </p>



    <?php echo $__env->make('convodataverification.emails.email_footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>

</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/emails/registration.blade.php ENDPATH**/ ?>