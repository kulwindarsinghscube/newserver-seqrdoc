<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <title>User Manual :: SeQR Doc</title>
    <!-- Styles -->	
    <link href="{{asset('manual/css/docs.css')}}" rel="stylesheet">
    <link href="{{asset('manual/css/custom.css')}}" rel="stylesheet">
    <link rel="icon" href="{{asset('manual/img/report.ico')}}">
	<link href='http://fonts.googleapis.com/css?family=Raleway:100,300,400,500%7CLato:300,400' rel='stylesheet' type='text/css'>
  </head>
  <body>
    <header class="site-header navbar-fullwidth navbar-transparent" id="header"></header>
    <main class="container">
      <div class="row">
        <!-- Sidebar -->
        <aside class="col-md-1 col-sm-1 sidebar">
          <ul class="sidenav dropable sticky" id="sidebar" style="display:none"></ul>
        </aside>
        <!-- END Sidebar -->		
        <!-- Main content -->
        <article class="col-md-11 col-sm-11 main-content" role="main">		
          <header>
            <h1>SeQR Doc User Manual</h1>    
            <p class="text-justify">
            <strong>SeQR Doc</strong> is the next generation document <strong>Security Tool</strong>. It is an <strong>Innovative Product</strong>.
            It came into existence to <strong>stop the forgery and counterfeiting</strong> of valuable and important documents.
            It provides <strong>7 layered security approach</strong>, which is meant to stop all types of document frauds.
            It is a complete and proven <strong>solution for document protection</strong> in soft as well as printed form.            
            </p>       
            <ol class="toc">
              <li><a href="#module1">Logging into the SeQR Admin</a></li>
              <li><a href="#module2">Document Setup</a></li>
              <li><a href="#module3">Payment Setup</a></li>
			  <li><a href="#module4">Document Management</a></li>
			  <li><a href="#module5">System Config</a></li>
			  <li><a href="#module6">Reports</a></li>
            </ol>
          </header>
          <!-- Logging into the RCP -->
		  <section>
            <h2 id="module1">Logging into the SeQR Admin</h2>
			<p>Use the provided url for login page.<p>
			1. In the login box, enter the valid User Name and Password. Then press login button.  
			<a href="javascript:void(0);" file="{{asset('manual/docs/user_mgt/login.jpg')}}" id="1" class="manual text-info">View</a>
			</p> 
			<p>2. Upon successfully logging into the SeQR Doc, a menu bar will appear the top side of your browser.&nbsp;
			<a href="javascript:void(0);" file="{{asset('manual/docs/user_mgt/dashboard.jpg')}}" id="3" class="manual text-info">View</a></p>			
			<p>A dashboard that displays all information regarding Students, Active and Inactive Users, Successful, Inactive and Regular Scan History of the document and the last 3 scans.</p>
            <p class="text-danger"><small>Note: This menu bar will remain in place the entire time you are logged into the SeQR Doc. The functionality of each menu bar item is covered later in this manual.</small></p>
          </section>
		  <!-- Document Setup -->
          <section>
            <h2 id="module2">Document Setup</h2>
            <p>
			<a href="javascript:toggleDiv('master1');"><h6 id="module2-1" data-id="master1">Font Master</h6></a>
			<ol id="master1" class="hide-object">
              <li>You can see the list of fonts by clicking on "Font Master".&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/font_list.jpg')}}" id="4" class="manual text-info">View</a>              
			  </li>
              <li>Click on <img src="{{asset('manual/docs/add_font.jpg')}}" /> button to add Font. Form will be opened in a pop-up window.
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/font_add.jpg')}}" id="46" class="manual text-info">View</a>
			  <p>Enter the following information:</p>
			  <ul>
			  <li>Enter Font Name</li>
			  <li>Enter Upload Font Normal</li>
			  <li>Enter Upload Font Bold</li>
			  <li>Enter Upload Font Italic</li> 
			  <li>Enter Upload Font Bold Italic</li>
			  <li>Select Status</li>		  			  
			  </ul>			  
			  and press the <img src="{{asset('manual/docs/save.jpg')}}" /> button to store the record. 
              <p class="text-danger"><small>If the font package consists of only one file, choose the same for all the fields else upload the files in their respective fields.</small></p>
			  </li>
			  <li>
			  Every record have a button called Edit. If you click on edit sign <img src="{{asset('manual/docs/edit.jpg')}}" /> it opens detailed edit page of respected record.<br />
			  Modify the record and press the <img src="{{asset('manual/docs/update.jpg')}}" /> button to save the record.
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/font_edit.jpg')}}" id="6" class="manual text-info">View</a>
			  </li>
			  <li>
			  Click the delete sign <img src="{{asset('manual/docs/delete.jpg')}}" /> beside a record to delete the related record.&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/font_list.jpg')}}" id="bm_de" class="manual text-info">View</a>
			  </li>			  
            </ol>		
			</p>  
			
			<p>
			<a href="javascript:toggleDiv('master2');"><h6 id="module2-2" data-id="master2">Upload QR image</h6></a>
			<ol id="master2" class="hide-object">              
              <li>Form will be opened by clicking on "Upload QR image".&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/qrcode.jpg')}}" id="10" class="manual text-info">View</a>
              <ul> 
              	<li>Select/Choose the image from the system that needs to be set upon the QR code.</li>
              	<li>After an image has been selected, click on the <img src="{{asset('manual/docs/upload_image.jpg')}}" /> button to upload the desired file.</li>
              </ul>
			  </li>			 
            </ol>			
			</p>

			<p>
			<a href="javascript:toggleDiv('master3');"><h6 id="module2-3" data-id="master3">Background Template Management</h6></a>
			<ol id="master3" class="hide-object">
              <li>You can see the list of background templates by clicking on "Background Template Management".&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/background_templates_list.jpg')}}" id="15" class="manual text-info">View</a>
			  </li>
              <li>Click on <img src="{{asset('manual/docs/add_background_template.jpg')}}" /> button to add background template. Form will be opened.
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/background_template_add.jpg')}}" id="16" class="manual text-info">View</a>
			  <p>Enter the following information:
			  </p>
			  <ul>
               <li>Enter the Background Template Name</li>
               <li>Set the Width and Height of the background template as per your requirement</li>
               <li>Select Status as Active or Inactive</li>
               <li>Select the background template to be added from the system</li>
               </ul> 
               and press the <img src="{{asset('manual/docs/save_green.jpg')}}" /> button to store the record. &nbsp;
			  </li>
			  <li>
			  Every record have a button called Edit. If you click on edit sign <img src="{{asset('manual/docs/edit.jpg')}}" /> it opens detailed edit page of respected record.<br />
			  Modify the record and press the <img src="{{asset('manual/docs/save_green.jpg')}}" /> button to save the record.
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/background_template_edit.jpg')}}" id="17" class="manual text-info">View</a>
			  </li>
			   <li>
			  Click the eye sign <img src="{{asset('manual/docs/eye.jpg')}}" /> beside a record to see the related background template preview.&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/background_template_preview.jpg')}}" id="cm_ac" class="manual text-info">View</a>
			  </li>
            </ol>			
			</p>

			<p>
			<a href="javascript:toggleDiv('master4');"><h6 id="module2-4" data-id="master4">Template Management</h6></a>
			<ol id="master4" class="hide-object">
              <li>You can see the list of templates by clicking on "Template Management".&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/templates_list.jpg')}}" id="20" class="manual text-info">View</a>
			  </li>
              <li>Click on <img src="{{asset('manual/docs/add_template.jpg')}}" /> button to create new template.
              	<a href="javascript:void(0);" file="{{asset('manual/docs/masters/template_add.jpg')}}" id="21" class="manual text-info">View</a>
              <ul>
              <li>Enter Template Name</li> 
              <li>Enter Template Description</li> 
              <li>Select Background Template from dropdown list</li>
              <li>Set Print With Background option</li>
              <li>If Background Template is selected a Blank Template then Width and Height text boxes are available.</li>
              <li>Select Template status from dropdown list</li>
              <li>In Template Window you can add elements by using the availbale <strong>Features</strong> like <strong class="text-purple">Text Security, Dynamic Image, Micro Text, Ghost Image, Security Line, QR Code, Barcode and many more</strong>.</li>               
              </ul>
               and press the <img src="{{asset('manual/docs/save_green.jpg')}}" /> button to store the record. &nbsp;
			  </li>
			  <li>
			  Every record have a button called Edit. If you click on edit sign <img src="{{asset('manual/docs/edit.jpg')}}" /> it opens detailed edit page of respected record.<br />
			  Modify the record and press the <img src="{{asset('manual/docs/save_green.jpg')}}" /> button to save the record.
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/template_edit.jpg')}}" id="22" class="manual text-info">View</a>
			  </li>
			  <li>Click the copy sign <img src="{{asset('manual/docs/copy.jpg')}}" /> beside a record to make a duplicate the related record.</a></li>
              <li>
			  Click the map sign <img src="{{asset('manual/docs/map.jpg')}}" /> beside a record to map this template with Excel Sheet OR Database.&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/template-map.jpg')}}" id="cmm_ac" class="manual text-info">View</a>
              <p>
              Click on <img src="{{asset('manual/docs/map-from-file.jpg')}}" /> button to upload <strong>Excel Sheet</strong>.&nbsp;
              <a href="javascript:void(0);" file="{{asset('manual/docs/masters/map-fields.jpg')}}" id="map_fields" class="manual text-info">View</a>
              </p>
              <p>Excel Format&nbsp;
              <a href="javascript:void(0);" file="{{asset('manual/docs/masters/excel-format.jpg')}}" id="excel_format" class="manual text-info">View</a>
              <p class="text-danger"><small>Note: You have to use "Dynamic Image Managemant" module to upload student images.</small></p>
              </p>
			  </li>
			  <li>
			  Click the pdf sign <img src="{{asset('manual/docs/pdf.jpg')}}" /> beside a record to generate PDF file.&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/pdf-generate.jpg')}}" id="cmm_de" class="manual text-info">View</a>
              <p>Once again you will have to upload the <strong>Excel Sheet</strong> that was mapped with the template.</p>
			  </li>			  			  
            </ol>			
			</p>

			<p>
			<a href="javascript:toggleDiv('master5');"><h6 id="module2-5" data-id="master5">Dynamic Image Managemant</h6></a>
			<ol id="master5" class="hide-object">
              <li>You can see the list of Template Names on left side by clicking on "Dynamic Image Managemant".&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/dynamic_image_page.jpg')}}" id="24" class="manual text-info">View</a>
			  </li>
			  <li>Click the any template name. Previously uploaded images will be displayed.&nbsp;
			  <a href="javascript:void(0);" file="{{asset('manual/docs/masters/dynamic_image.jpg')}}" id="cma_ac" class="manual text-info">View</a>
			  </li>
			  <li>Select/Choose the images from the system and press "Submit" button to upload.</li>			  
            </ol>			
			</p>			
          </section>  
          <!-- Payment Setup --> 
          <section>
          	<h2 id="module3">Payment Setup</h2>
          	<p>
          		<a href="javascript:toggleDiv('Transaction1');"><h6 id="module3-1" data-id="Transaction1">Payment Gateway</h6></a>
			<ol id="Transaction1" class="hide-object">	
				<li>You can see the names of Payment Gateway by clicking on "Payment Gateway".&nbsp;
				  <a href="javascript:void(0);" file="{{asset('manual/docs/pg/pg.jpg')}}" id="cdt-1" class="manual text-info">View</a>	
				</li>
				<li>
				  	Click the <img src="{{asset('manual/docs/add-pg.jpg')}}" /> button to add name of Payment Gateway. Form will be opened in a pop-up window.&nbsp;
					  <a href="javascript:void(0);" file="{{asset('manual/docs/pg/pg-add.jpg')}}" id="cdt-2" class="manual text-info">View</a>
                      <p>Enter the following information:
					  </p>
					  <ul>
		               <li>Enter the Payment Gateway Name</li>
                       <li>Select Status from drop down list</li>
		               </ul> 
		               and press the <img src="{{asset('manual/docs/save.jpg')}}" /> button to store the record. &nbsp;
				 </li>
                 <li>
                  Every record have a button called Edit. If you click on edit sign <img src="{{asset('manual/docs/edit.jpg')}}" /> it opens detailed edit page of respected record.<br />
                  Modify the record and press the <img src="{{asset('manual/docs/update.jpg')}}" /> button to save the record.
                  <a href="javascript:void(0);" file="{{asset('manual/docs/pg/pg-update.jpg')}}" id="cdt-3" class="manual text-info">View</a>
                 </li>
			</ol>		
          	</p>

          	<p>
          		<a href="javascript:toggleDiv('Transaction2');"><h6 id="module3-2" data-id="Transaction2">PG Configuration</h6></a>
				<ol id="Transaction2" class="hide-object">	
		            <li>Form will be opened by clicking on "PG Configuration".&nbsp;
					  <a href="javascript:void(0);" file="{{asset('manual/docs/pg/pg-config.jpg')}}" id="bpvr-1" class="manual text-info">View</a>	
		              <p>Enter the following information:
					  </p>
					  <ul>
		               <li>Select Payment Gateway Name from drop down list</li>
		               <li>Select Status from drop down list</li>
		               <li>Enter the Amount to charge</li>
		               <li>Select Credentials from drop down list</li>
		               </ul> 
		               and press the <img src="{{asset('manual/docs/update.jpg')}}" /> button to store the record. &nbsp;
                    </li>
				</ol>		
          	</p>          	
          </section>
          <!-- Document Management -->
          <section>
          		<h2 id="module4">Document Management</h2>
          		<p>
          			<a href="javascript:toggleDiv('Print1');"><h6 id="module4-1" data-id="Print1">Certificate Management</h6></a>
					<ol id="Print1" class="hide-object">	
					<li>You can find the active and inactive certificates in this module.&nbsp;
				  	<a href="javascript:void(0);" file="{{asset('manual/docs/document_mgt/certificate-mgt.jpg')}}" id="pq-1" class="manual text-info">View</a>	
					</li>
					<li>You can also enable or disable a certificate if necessary.</li>
					</ol>		
          		</p>

          		<p>
          			<a href="javascript:toggleDiv('Print2');"><h6 id="module4-2" data-id="Print2">Printing Details</h6></a>
					<ol id="Print2" class="hide-object">	
					<li>Here we can find the details of the documents printed.&nbsp;
				  	<a href="javascript:void(0);" file="{{asset('manual/docs/document_mgt/printing-details.jpg')}}" id="pqh-1" class="manual text-info">View</a>	
					</li>
					<li>Click the info sign <img src="{{asset('manual/docs/info.jpg')}}" /> beside a record to find the printing details.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/document_mgt/print-info.jpg')}}" id="pqh-2" class="manual text-info">View</a>
					</li>
					</ol>		
          		</p>

          </section>

          <!--System Config-->
          <section>
          	<h2 id="module5">System Config</h2>
          		<p>
          			<a href="javascript:toggleDiv('Utilities1');"><h6 id="module5-1" data-id="Utilities1">Institute Management</h6></a>
					<ol id="Utilities1" class="hide-object">	
					<li>You can see the institute users by clicking on "Institute Management".&nbsp;
				  	<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/institute-list.jpg')}}" id="sm-1" class="manual text-info">View</a>	
                    <p>Every record have Edit and Delete buttons.</p>
					</li>
                    <li>Click on <img src="{{asset('manual/docs/create-insttitute-user.jpg')}}" /> button to add new user. Form will be opened in a pop-up window.
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/institute-add.jpg')}}" id="sm-2" class="manual text-info">View</a>			  		
			  		<ul>
			  		<li> Enter the username</li>
			  		<li> Enter the Full Name</li>
			  		<li> Enter the Password</li>
			  		<li> Select Status from drop down list</li>
			  		and press the <img src="{{asset('manual/docs/save.jpg')}}" /> button to store the record.
			  		</li>
			  		</ul>	
			  		</li> 
			  		<li>
                    If you click on edit sign <img src="{{asset('manual/docs/edit.jpg')}}" /> it opens edit form of respected record in a pop-up window.
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/institute-edit.jpg')}}" id="93" class="manual text-info">View</a>
			  		</li>
			  		<li>
			  		Click the delete sign <img src="{{asset('manual/docs/delete.jpg')}}" /> beside a record to delete the related record.&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/institute-list.jpg')}}" id="94" class="manual text-info">View</a>
			  		</li>
					</ol>		
          		</p>

          		<p>
          			<a href="javascript:toggleDiv('Utilities2');"><h6 id="module5-2" data-id="Utilities2">User Management</h6></a>
					<ol id="Utilities2" class="hide-object">	
					<li>You can see the List of users in the system by clicking on "User Management".&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/user_management_list.jpg')}}" id="um-1" class="manual text-info">View</a>
			  		</li>
			  		<li>Click on <img src="{{asset('manual/docs/create-user.jpg')}}" /> button to add new user account setup. Form will be opened in a pop-up window.
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/user_management_add.jpg')}}" id="um-2" class="manual text-info">View</a>			  		
			  		<ul>
			  		<li> Enter the Username</li>
                    <li> Enter the Password</li>
			  		<li> Enter the Full Name</li>
			  		<li> Enter the Email</li>
                    <li> Enter the Mobile number</li>
                    <li> Select Status from drop down list</li>
			  		<li> Select User Role from drop down list</li>
			  		and press the <img src="{{asset('manual/docs/save.jpg')}}" /> button to store the record.
			  		</li>
			  		</ul>	
			  		</li> 
			  		<li>Click the info sign <img src="{{asset('manual/docs/info.jpg')}}" /> beside a record to find the user details.&nbsp;
                    <a href="javascript:void(0);" file="{{asset('manual/docs/system_config/user-info.jpg')}}" id="um-5" class="manual text-info">View</a>
					</li>
                    <li>
                    If you click on edit sign <img src="{{asset('manual/docs/edit.jpg')}}" /> it opens edit form of respected record in a pop-up window.
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/user_management_edit.jpg')}}" id="um-4" class="manual text-info">View</a>
			  		</li>
			  		<li>Click the delete sign <img src="{{asset('manual/docs/delete.jpg')}}" /> beside a record to delete the related record.&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/user_management_list.jpg')}}" id="um-5" class="manual text-info">View</a>
                    </li>
                    <li>Click this sign <img src="{{asset('manual/docs/disable.jpg')}}" /> beside a record to disable the related record.</li>		  
					</ol>		
          		</p>
          		<p>
          			<a href="javascript:toggleDiv('Utilities4');"><h6 id="module5-4" data-id="Utilities4">Admin Management</h6></a>
					<ol id="Utilities4" class="hide-object">	
					<li>You can see the List of users in the system by clicking on "User Management".&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/admin_management_list.jpg')}}" id="am-1" class="manual text-info">View</a>
			  		</li>
			  		<li>Click on <img src="{{asset('manual/docs/add-admin.jpg')}}" /> button to add new user account setup. Form will be opened in a pop-up window.
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/admin_management_add.jpg')}}" id="am-2" class="manual text-info">View</a>			  		
			  		<ul>
			  		<li> Enter the Full Name</li>
                    <li> Enter the Username</li>
                    <li> Enter the Password</li>			  		
			  		<li> Enter the Email</li>
                    <li> Enter the Mobile number</li>
                    <li> Select Status from drop down list</li>
			  		<li> Select User Role from drop down list</li>
			  		and press the <img src="{{asset('manual/docs/save.jpg')}}" /> button to store the record.
			  		</li>
			  		</ul>	
			  		</li> 
			  		<li>
                    If you click on edit sign <img src="{{asset('manual/docs/edit.jpg')}}" /> it opens edit form of respected record in a pop-up window.
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/admin_management_edit.jpg')}}" id="am-3" class="manual text-info">View</a>
			  		</li>
			  		<li>Click the delete sign <img src="{{asset('manual/docs/delete.jpg')}}" /> beside a record to delete the related record.&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/admin_management_list.jpg')}}" id="am-4" class="manual text-info">View</a></li>						  
					</ol>		
          		</p>

          		<p>
          			<a href="javascript:toggleDiv('Utilities3');"><h6 id="module5-3" data-id="Utilities3">Roles Management</h6></a>
					<ol id="Utilities3" class="hide-object">	
					<li>You can see the List of user roles by clicking on "Roles Management".&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/roles_management.jpg')}}" id="rm-1" class="manual text-info">View</a>
			  		</li>
              		<li>Click on <img src="{{asset('manual/docs/add_role.jpg')}}" /> button to add new role. Form will be opened in a pop-up window.<br />
			  		Enter the role name in text box. Select a Status and press the <img src="{{asset('manual/docs/save.jpg')}}" /> button to store the record.&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/roles_management_add.jpg')}}" id="rm-2" class="manual text-info">View</a>
			  		</li>
			  		<li>
			  		Every record have a button called Edit. If you click on edit sign <img src="{{asset('manual/docs/edit.jpg')}}" /> it opens detailed edit page of respected record.<br />
			  		Modify the record and press the <img src="{{asset('manual/docs/update.jpg')}}" /> button to save the record.
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/roles_management_edit.jpg')}}" id="rm-3" class="manual text-info">View</a>
			  		</li>
			  		<li>Click the delete sign <img src="{{asset('manual/docs/delete.jpg')}}" /> beside a record to delete the related record.&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/roles_management.jpg')}}" id="rm-4" class="manual text-info">View</a>
                    </li>
			  		</ol>		
          		</p>
                
                <p>
          			<a href="javascript:toggleDiv('Utilities5');"><h6 id="module5-4" data-id="Utilities5">Permission Management</h6></a>
					<ol id="Utilities5" class="hide-object">	
					<li>To assign the permissions as per role, click the "Permission Management".&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/permission_management.jpg')}}" id="pm-1" class="manual text-info">View</a>
			  		<p>First you have to select a Role form the dropdown list to assign permissions for it.<br />You can use the checkboxes to select or unselect certain modules and press the <img src="{{asset('manual/docs/check.jpg')}}" /> button to store the permissions.</p>
			  		</li>					
					</ol>		
          		</p>
                <p>
          			<a href="javascript:toggleDiv('Utilities6');"><h6 id="module5-6" data-id="Utilities6">Settings</h6></a>
					<ol id="Utilities6" class="hide-object">	
					<li>Setting form will be opened by clicking on "Settings".&nbsp;
				  	<a href="javascript:void(0);" file="{{asset('manual/docs/system_config/settings.jpg')}}" id="s1" class="manual text-info">View</a>	
                    <ul>
			  		<li> Enter the Printer Name</li>
                    <li> Enter the Time Zone</li>
                    <li> Select Print Color</li>
                    <li> Enter Auto Logout Duration</li>			  		
			  		<li> Enter the SMTP address</li>
                    <li> Enter the Port number</li>
                    <li> Enter the Sender Email Id</li>
			  		<li> Enter the Password</li>
			  		and press the <img src="{{asset('manual/docs/save_green.jpg')}}" /> button to store the record.
			  		</li>
			  		</ul>
					</li>
					</ol>		
          		</p>
          </section>

          <!--Reports-->
          <section>
          	<h2 id="module6">Reports</h2>
          		<p>
          			<a href="javascript:toggleDiv('Report1');"><h6 id="module6-1" data-id="Report1">Template Data</h6></a>
					<ol id="Report1" class="hide-object">	
					<li>You can see the Template report by clicking on "Template Data".&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/reports/template-data.jpg')}}" id="td-1" class="manual text-info">View</a>
			  		</li>
              		</ol>		
          		</p>

          		<p>
          			<a href="javascript:toggleDiv('Report2');"><h6 id="module6-2" data-id="Report2">Printing Report</h6></a>
					<ol id="Report2" class="hide-object">	
					<li>Click the "Printing Report" to view the printer report of various documents.&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/reports/printer-report.jpg')}}" id="pr-2" class="manual text-info">View</a>
			  		</li> 
					</ol>		
          		</p>

          		<p>
          			<a href="javascript:toggleDiv('Report3');"><h6 id="module6-3" data-id="Report3">Scan History</h6></a>
					<ol id="Report3" class="hide-object">	
					<li>Click the "Scan History" to view the scan history of various documents.&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/reports/scan-history.jpg')}}" id="sh-1" class="manual text-info">View</a>
			  		</li>			  				
					</ol>		
          		</p>
                
                <p>
          			<a href="javascript:toggleDiv('Report4');"><h6 id="module6-3" data-id="Report4">Payment Transactions</h6></a>
					<ol id="Report4" class="hide-object">	
					<li>Click the "Payment Transactions" to view the status of transaction occurred.&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/reports/payment-transaction.jpg')}}" id="pt-1" class="manual text-info">View</a>
			  		</li>			  				
					</ol>		
          		</p>
                
                <p>
          			<a href="javascript:toggleDiv('Report5');"><h6 id="module6-3" data-id="Report5">User Session Manager</h6></a>
					<ol id="Report5" class="hide-object">	
					<li>Here you can manage sessions of people logged in.&nbsp;
			  		<a href="javascript:void(0);" file="{{asset('manual/docs/reports/session-manager.jpg')}}" id="usm-1" class="manual text-info">View</a>
			  		</li>
                    <li>To find location of the person logged in, click on the navigation icon <img src="{{asset('manual/docs/navigation.jpg')}}" />. If location is disabled, a red circle <img src="{{asset('manual/docs/round-red-cross.jpg')}}" /> can be viewed around it.</li>
                    <li>To terminate the session of a person who has logged in, click on the black cross icon <img src="{{asset('manual/docs/round-cross.jpg')}}" />.</li>			  				
					</ol>		
          		</p>
          </section>

          
        </article>
        <!-- END Main content -->
      </div>
    </main>
    <!-- Footer -->
    <footer class="site-footer">
      <div class="container">
        <a id="scroll-up" href="#"><i class="fa fa-angle-up"></i></a>
        <div class="row" id="footer"></div>
      </div>	
	</footer>
    <!-- END Footer -->
    <!-- Scripts -->
    <script src="{{asset('manual/js/docs.js')}}"></script>
    <script src="{{asset('manual/js/custom.js')}}"></script>
	<script>
$(document).ready(function(){
	if(window.location.hash) {
		var hash = window.location.hash.substring(1);
		var id = $('#'+hash).attr('data-id');		
		toggleDiv(id)
	}
});	
	</script>
  </body>
</html>
 