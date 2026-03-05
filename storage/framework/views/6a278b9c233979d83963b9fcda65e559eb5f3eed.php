<!DOCTYPE html>
<html>

<head>
    <title>Blockchain Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h4 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr.success {
            background-color: #8ff8a8; /* Light green */
        }

        tr.failed {
            background-color: #fdb2b8; /* Light red */
        }

        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>

<body>
    <h4>Hello Team,</h4>
    <p>Please find below the blockchain documents report for your reference:</p>
    <table>
        <thead>
            <tr>
                <th>Instance Name</th>
                <th>Document ID</th>
                <th>Status</th>
                <th>Response</th>
                <th>Blockchain Url</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="<?php echo e(strtolower($value['status']) === 'success' ? 'success' : 'failed'); ?>">
                <td><?php echo e($value['instance']); ?></td>
                <td><?php echo e($value['document_id']); ?></td>
                <td><?php echo e($value['status']); ?></td>
                <td><?php echo e($value['message']); ?></td>
               <td>
                <a href="<?php echo e($value['url']); ?>" style="color: #007bff; text-decoration: none; padding: 8px 12px; background-color: #f0f0f0; border-radius: 4px; display: inline-block; font-size: 14px; font-family: Arial, sans-serif;">
                    Click here
                </a>
                </td>

                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
  
    <p><em>This is a system-generated email. Please do not reply. – SSSL Team</em></p>
</body>

</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/mail/send_blockchain_summary_report.blade.php ENDPATH**/ ?>