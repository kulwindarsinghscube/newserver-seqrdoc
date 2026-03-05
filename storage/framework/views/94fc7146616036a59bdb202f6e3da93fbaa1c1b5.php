<!DOCTYPE html>
<html>

<head>
    <title>Registration Completed</title>
</head>

<body>
    <h1>Dear Student,</h1>
    <p>This is to inform the students of all Programs of SSSL passing out in the Academic Year 2023-24 that SSSL
        is organizing the 6th Convocation ceremony. Following are the details:</p>
    <p><strong>Day and Date:</strong> <?php
        // Create a DateTime object for today
        $today = new DateTime('today');
        
        // Add 10 days
        $today->modify('+10 days');
        
        // Print the date in the desired format
        echo $today->format('l d F Y');
        ?></p>
    <p><strong>Venue:</strong> 16, Samrat Mill Compound, LBS Road, Vikhroli West, Mumbai, Maharashtra 400079</p>


    <p>Please
        <a href="https://kmtc.seqrdoc.com/convo_student/login">log in</a>
        to verify your details, make the payment and approve the pdf.
    </p>

    <h2>Your Login Details are as below : </h2>
    <p><strong>Email ID:</strong> <?php echo e(@$student['wpu_email_id']); ?></p>
    <p><strong>PRN Number:</strong> <?php echo e(@$student['prn']); ?></p>
    <p><strong>Date of Birth:</strong> <?php echo e(\Carbon\Carbon::parse(@$student['date_of_birth'])->format('d-m-Y')); ?></p>
    <p><strong>Password:</strong> SSSLPASS </p>

    <p>You can log in using the following URL: <a
            href="https://kmtc.seqrdoc.com/convo_student/login">https://kmtc.seqrdoc.com/convo_student/login</a>
    </p>
    
    <p>The user manual has been included for your reference.</p>

    <br>

    <p>Thank you!</p>
    <strong>Controller of Examinations</strong> <br>

    
    



    <!-- <?php echo $__env->make('convodataverification.emails.email_footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> -->
</body>

</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/emails/registration_kmtc.blade.php ENDPATH**/ ?>