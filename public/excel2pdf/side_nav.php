<div id="mySidenav" class="sidenav">    
        <span class="card loginname text-left"><?php echo $logged_in_user_name; ?></span>
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <img src="images/avtar.png" class="center-block" style="border:4px solid #fff;background:#fff;border-radius:50%;height:110px; width:110px;">
        <div class="py-1 hlink"></div>
<?php if(checkPermissions('view-dashboard')){ ?> 
        <a href="dashboard.php" class="hlink">Dashboard</a>
<?php } ?>  
<?php if(checkPermissions('view-template')){ ?> 
        <a href="templates.php" class="hlink">Templates</a>
<?php } ?>   
<?php if(checkPermissions('view-docs')){ ?> 
        <a href="individual_records.php" class="hlink">Documents</a>
<?php } ?>      
<?php if(checkPermissions('view-gh')){ ?> 
        <a href="generation_history.php" class="hlink">Generation History</a>
<?php } ?>   
<?php if(checkPermissions('view-verify')){ ?> 
        <a href="verification.php" class="hlink">Verification</a>
<?php } ?>    
<?php if(checkPermissions('view-user')){ ?> 
        <a href="users.php" class="hlink">Users</a>
<?php } ?>
<?php if(checkPermissions('view-role')){ ?>         
        <a href="roles.php" class="hlink">Roles</a>
<?php } ?>    
<?php if(checkPermissions('view-image')){ ?>         
        <a href="images.php" class="hlink">Images</a>
<?php } ?>   
<?php if(checkPermissions('view-dbg')){ ?>         
        <a href="document_bg.php" class="hlink">Document Backgrounds</a>
<?php } ?>    
        <!--<a href="permissions.php" class="hlink">Permissions</a>-->         
        <div class="py-1"></div>
        <a href="logout.php" class="btn btn-danger btn-sm logout">Logout</a>
</div>