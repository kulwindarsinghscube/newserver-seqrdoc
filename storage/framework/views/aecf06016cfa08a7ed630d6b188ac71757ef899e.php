<body>
    <nav class="navbar navbar-default">
        <div class="container" style="width: 99%;">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo e(URL::route('admin.dashboard')); ?>"><i
                        class="fa fa-user-secret fa-fw"></i> Student Panel</a>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                

                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#"
                            style="display:block; padding-left:40px; position:relative;">
                            <img src="/backend/seqr_scan.png" alt="Icon"
                                style="height:30px; width:30px; position:absolute; top:5px; left:5px;">
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                             
                            <li><a href="<?= route('convo_student.logout') ?>"><span
                                        class="fa fa-fw fa-sign-out"></span> Logout</a></li>
                        </ul>
                    </li>
                </ul>

            </div>

        </div>
    </nav>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/student/pages/layout/header.blade.php ENDPATH**/ ?>