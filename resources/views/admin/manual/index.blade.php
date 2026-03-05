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
                            <li><a href="#module7">Customs</a></li>
                            <li><a href="#module8">Galgotias Degree Certificate</a></li>
                            <li><a href="#module9">Masters</a></li>
                            <li><a href="#module10">Document Verification</a></li>
                            <li><a href="#module11">Stock</a></li>
                            <li><a href="#module12">Sandboxing</a></li>
                        </ol>
                    </header>
                    <!-- Logging into the RCP -->
                    <section>
                        <h2 id="module1">Logging into the SeQR Admin</h2>
                        <p>Use the provided url for login page.
                        <p>
                            1. In the login box, enter the valid User Name and Password. Then press login button.  
                            <a href="javascript:void(0);" file="{{asset('manual/docs/user_mgt/login.jpg')}}" id="1" class="manual text-info">View</a>
                        </p>
                        <p>
                            2. Upon successfully logging into the SeQR Doc, a menu bar will appear the top side of your browser.&nbsp;
                            <a href="javascript:void(0);" file="{{asset('manual/docs/user_mgt/dashboard.jpg')}}" id="3" class="manual text-info">
                                View
                            </a>
                        </p>
                        <p>
                            A dashboard that displays all information regarding Students, Active and Inactive Users, Successful, Inactive and Regular Scan History of the document and the last 3 scans.
                        </p>
                        <p class="text-danger">
                            <small>Note: This menu bar will remain in place the entire time you are logged into the SeQR Doc. The functionality of each menu bar item is covered later in this manual.</small>
                        </p>
                    </section>
                    <!-- Document Setup -->
                    <section>
                        <h2 id="module2">Document Setup</h2>
                        <p>
                            <a href="javascript:toggleDiv('master1');">
                                <h6 id="module2-1" data-id="master1">
                                    Font Master
                                </h6>
                            </a>
                            <ol id="master1" class="hide-object">
                                <li>You can see the list of fonts by clicking on "Font Master".&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/masters/font_list.jpg')}}" id="4" class="manual text-info">View</a>              
                                </li>
                                <li>
                                    Click on <img src="{{asset('manual/docs/add_font.jpg')}}" /> button to add Font. Form will be opened in a pop-up window.
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
                            <a href="javascript:toggleDiv('master2');">
                                <h6 id="module2-2" data-id="master2">
                                    Upload QR image
                                </h6>
                            </a>
                            <ol id="master2" class="hide-object">
                                <li>
                                    Form will be opened by clicking on "Upload QR image".&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/masters/qrcode.jpg')}}" id="10" class="manual text-info">View</a>
                                    <ul>
                                        <li>Select/Choose the image from the system that needs to be set upon the QR code.</li>
                                        <li>After an image has been selected, click on the <img src="{{asset('manual/docs/upload_image.jpg')}}" /> button to upload the desired file.</li>
                                    </ul>
                                </li>
                            </ol>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('master3');">
                                <h6 id="module2-3" data-id="master3">
                                    Background Template Management
                                </h6>
                            </a>
                            <ol id="master3" class="hide-object">
                                <li>You can see the list of background templates by clicking on "Background Template Management".&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/masters/background_templates_list.jpg')}}" id="15" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Click on <img src="{{asset('manual/docs/add_background_template.jpg')}}" /> button to add background template. Form will be opened.
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
                            <a href="javascript:toggleDiv('master4');">
                                <h6 id="module2-4" data-id="master4">
                                    Template Management
                                </h6>
                            </a>
                            <ol id="master4" class="hide-object">
                                <li>You can see the list of templates by clicking on "Template Management".&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/masters/templates_list.jpg')}}" id="20" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Click on <img src="{{asset('manual/docs/add_template.jpg')}}" /> button to create new template.
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
                            <a href="javascript:toggleDiv('master5');">
                                <h6 id="module2-5" data-id="master5">
                                    Dynamic Image Managemant
                                </h6>
                            </a>
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

                        <p>
                            <a href="javascript:toggleDiv('master6');">
                                <h6 id="module2-6" data-id="master6">
                                    Process Excel
                                </h6>
                            </a>
                            <ol id="master6" class="hide-object">
                                <li>You can see the Process Excel by clicking on “Document Setup” module.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/Document Setup/Document Setup 1.png')}}" id="documentSetup1" class="manual text-info">View</a>              
                                </li>
                                <li>You can download Sample Excel.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/Document Setup/Document Setup 2.png')}}" id="documentSetup2" class="manual text-info">View</a>              
                                </li>
                                <li>You will see a list of “Process Excel”.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/Document Setup/Document Setup 3.png')}}" id="documentSetup3" class="manual text-info">View</a>              
                                </li>
                                <li>Click on the “Choose File” tab and select your excel file from your device, press the “Submit” button to upload the excel file.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/Document Setup/Document Setup 4.png')}}" id="documentSetup4" class="manual text-info">View</a>              
                                </li>
                                <li>You can download Excel file of “Raw" and “Processed”.             
                                </li>
                            </ol>
                        </p>

                        <p>
                            <a href="javascript:toggleDiv('master7');">
                                <h6 id="module2-7" data-id="master7">
                                    Generate ID Cards
                                </h6>
                            </a>
                            <ol id="master7" class="hide-object">
                                <li>You can see the list of ID Cards by clicking on “Generate ID Cards”.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/Document Setup/Generate ID Card/id-1.JPG')}}" id="generateIdCard1" class="manual text-info">View</a>              
                                </li>

                                <li>
                                    <p>Enter the following information:</p>
                                    <ul>
                                        <li>Click on the pdf sign, beside a record to generate PDF file.</li>
                                        <li>You have to upload the excel sheet and click on upload.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Document Setup/Generate ID Card/id-2.JPG')}}" id="generateIdCard2" class="manual text-info">View</a></li>
                                    </ul>
                                </li>

                                <li>
                                    <p>For Softcopy ID Card:</p>
                                    <ul>
                                        <li>Click on the pdf sign, beside a record to generate PDF file.</li>
                                        <li>You have to upload the excel sheet and click on upload.</li>
                                        <li>After successful generation, click on "Click Here Link" to download the PDF file.</li>
                                    </ul>
                                </li>
                            </ol>
                        </p>


                        <p>
                            <a href="javascript:toggleDiv('master8');">
                                <h6 id="module2-8" data-id="master8">
                                    ID cards Status
                                </h6>
                            </a>
                            <ol id="master8" class="hide-object">
                               
                                <li>
                                    <p><b>Pending:</b></p>
                                    <ul>
                                        <li>You can see the list of ID Card Status.</li>
                                        <li>You will see the loader icon, beside a record, Click on that icon to Processed PDF file.</li>
                                        <li>Click on the cross sign to “Revoke” the record.</li>
                                        <li>Click on eye button, to see</li>
                                        <li>Click on download icon, you will see the uploaded images and excel file.</li>
                                        <li>To change the record status complete to acknowledge, Click on checkbox.</li>
                                    </ul>
                                </li>

                                <li>
                                    <p><b>Acknowledged:</b></p>
                                    <ul>
                                        <li>You can see the list of ID Card Status.</li>
                                        <li>You will see the eye button, to see</li>
                                    </ul>
                                </li>
                            </ol>
                        </p>		
                    </section>
                    <!-- Payment Setup --> 
                    <section>
                        <h2 id="module3">Payment Setup</h2>
                        <p>
                            <a href="javascript:toggleDiv('Transaction1');">
                                <h6 id="module3-1" data-id="Transaction1">
                                    Payment Gateway
                                </h6>
                            </a>
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
                            <a href="javascript:toggleDiv('Transaction2');">
                                <h6 id="module3-2" data-id="Transaction2">
                                    PG Configuration
                                </h6>
                            </a>
                            <ol id="Transaction2" class="hide-object">
                                <li>
                                    Form will be opened by clicking on "PG Configuration".&nbsp;
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
                        <p>
                            <a href="javascript:toggleDiv('Transaction3');">
                                <h6 id="module3-3" data-id="Transaction3">
                                    Document Rate Master
                                </h6>
                            </a>
                            <div id="Transaction3" class="hide-object">
                                <p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the “PGC for old certificate” and “PGC for QR Certificate” by clicking on “Document rate Master”.</p>
                                <ol>
                                    <li>
                                        PGC for old certificate &nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/Payment Setup/Payment Setup 1.png')}}" id="generateIdCard1" class="manual text-info">View</a> 
                                        <ol type="a">
                                            <li>You can see the list of old documents</li>
                                            <li>You can add/update the rate as per universities requirement.and then click on update button to save the entries.</li>
                                        </ol>
                                    </li>
                                    <li>
                                        PGC for QR certificate &nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/Payment Setup/Payment Setup 2.png')}}" id="generateIdCard1" class="manual text-info">View</a> 
                                        <ol type="a">
                                            <li>You can see the list of templates, here you can add/update the scanning rate as per universities requirement.and then click on update button to save the entries.</li>
                                        </ol>
                                    </li>
                                </ol>
                            </div>
                        </p>          	
                    </section>
                    <!-- Document Management -->
                    <section>
                        <h2 id="module4">Document Management</h2>
                        <p>
                            <a href="javascript:toggleDiv('Print1');">
                                <h6 id="module4-1" data-id="Print1">
                                    Certificate Management
                                </h6>
                            </a>
                            <ol id="Print1" class="hide-object">
                                <li>You can find the active and inactive certificates in this module.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/document_mgt/certificate-mgt.jpg')}}" id="pq-1" class="manual text-info">View</a>	
                                </li>
                                <li>You can also enable or disable a certificate if necessary.</li>
                            </ol>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('Print2');">
                                <h6 id="module4-2" data-id="Print2">
                                    Printing Details
                                </h6>
                            </a>
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
                            <a href="javascript:toggleDiv('Utilities1');">
                                <h6 id="module5-1" data-id="Utilities1">
                                    Institute Management
                                </h6>
                            </a>
                            <ol id="Utilities1" class="hide-object">
                                <li>
                                    You can see the institute users by clicking on "Institute Management".&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/system_config/institute-list.jpg')}}" id="sm-1" class="manual text-info">View</a>	
                                    <p>Every record have Edit and Delete buttons.</p>
                                </li>
                                <li>
                                    Click on <img src="{{asset('manual/docs/create-insttitute-user.jpg')}}" /> button to add new user. Form will be opened in a pop-up window.
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
                            <a href="javascript:toggleDiv('Utilities2');">
                                <h6 id="module5-2" data-id="Utilities2">
                                    User Management
                                </h6>
                            </a>
                            <ol id="Utilities2" class="hide-object">
                                <li>You can see the List of users in the system by clicking on "User Management".&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/system_config/user_management_list.jpg')}}" id="um-1" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Click on <img src="{{asset('manual/docs/create-user.jpg')}}" /> button to add new user account setup. Form will be opened in a pop-up window.
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
                            <a href="javascript:toggleDiv('Utilities4');">
                                <h6 id="module5-4" data-id="Utilities4">
                                    Admin Management
                                </h6>
                            </a>
                            <ol id="Utilities4" class="hide-object">
                                <li>You can see the List of users in the system by clicking on "User Management".&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/system_config/admin_management_list.jpg')}}" id="am-1" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Click on <img src="{{asset('manual/docs/add-admin.jpg')}}" /> button to add new user account setup. Form will be opened in a pop-up window.
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
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/system_config/admin_management_list.jpg')}}" id="am-4" class="manual text-info">View</a>
                                </li>
                            </ol>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('Utilities3');">
                                <h6 id="module5-3" data-id="Utilities3">
                                    Roles Management
                                </h6>
                            </a>
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
                            <a href="javascript:toggleDiv('Utilities5');">
                                <h6 id="module5-4" data-id="Utilities5">
                                    Permission Management
                                </h6>
                            </a>
                            <ol id="Utilities5" class="hide-object">
                                <li>
                                    To assign the permissions as per role, click the "Permission Management".&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/system_config/permission_management.jpg')}}" id="pm-1" class="manual text-info">View</a>
                                    <p>First you have to select a Role form the dropdown list to assign permissions for it.<br />You can use the checkboxes to select or unselect certain modules and press the <img src="{{asset('manual/docs/check.jpg')}}" /> button to store the permissions.</p>
                                </li>
                            </ol>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('Utilities6');">
                                <h6 id="module5-6" data-id="Utilities6">
                                    Settings
                                </h6>
                            </a>
                            <ol id="Utilities6" class="hide-object">
                                <li>
                                    Setting form will be opened by clicking on "Settings".&nbsp;
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
                        <p>
                            <a href="javascript:toggleDiv('systemConfig1');">
                                <h6 id="module5-7" data-id="systemConfig1">
                                    Student Management
                                </h6>
                            </a>
                            <ol id="systemConfig1" class="hide-object">
                                <li>You can see the Student Management by clicking on “System Config”.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/System Config/System Config 1.png')}}" id="systemConfig1" class="manual text-info">View</a></li>
                                <li>You will see a list of Student Management.</li>
                                <li>
                                    Click on the “Import” tab to add student Data.<br>Form will be opened&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/System Config/System Config 2.png')}}" id="systemConfig2" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Click on the “Choose File” tab and select your excel file from your device, press the “Upload” button to upload the excel file.
                                </li>
                            </ol>
                        </p>
                    </section>
                    <!--Reports-->
                    <section>
                        <h2 id="module6">Reports</h2>
                        <p>
                            <a href="javascript:toggleDiv('Report1');">
                                <h6 id="module6-1" data-id="Report1">
                                    Template Data
                                </h6>
                            </a>
                            <ol id="Report1" class="hide-object">
                                <li>You can see the Template report by clicking on "Template Data".&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/reports/template-data.jpg')}}" id="td-1" class="manual text-info">View</a>
                                </li>
                            </ol>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('Report2');">
                                <h6 id="module6-2" data-id="Report2">
                                    Printing Report
                                </h6>
                            </a>
                            <ol id="Report2" class="hide-object">
                                <li>Click the "Printing Report" to view the printer report of various documents.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/reports/printer-report.jpg')}}" id="pr-2" class="manual text-info">View</a>
                                </li>
                            </ol>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('Report3');">
                                <h6 id="module6-3" data-id="Report3">
                                    Scan History
                                </h6>
                            </a>
                            <ol id="Report3" class="hide-object">
                                <li>Click the "Scan History" to view the scan history of various documents.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/reports/scan-history.jpg')}}" id="sh-1" class="manual text-info">View</a>
                                </li>
                            </ol>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('Report4');">
                                <h6 id="module6-3" data-id="Report4">
                                    Payment Transactions
                                </h6>
                            </a>
                            <ol id="Report4" class="hide-object">
                                <li>Click the "Payment Transactions" to view the status of transaction occurred.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/reports/payment-transaction.jpg')}}" id="pt-1" class="manual text-info">View</a>
                                </li>
                            </ol>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('Report5');">
                                <h6 id="module6-3" data-id="Report5">
                                    User Session Manager
                                </h6>
                            </a>
                            <ol id="Report5" class="hide-object">
                                <li>Here you can manage sessions of people logged in.&nbsp;
                                    <a href="javascript:void(0);" file="{{asset('manual/docs/reports/session-manager.jpg')}}" id="usm-1" class="manual text-info">View</a>
                                </li>
                                <li>To find location of the person logged in, click on the navigation icon <img src="{{asset('manual/docs/navigation.jpg')}}" />. If location is disabled, a red circle <img src="{{asset('manual/docs/round-red-cross.jpg')}}" /> can be viewed around it.</li>
                                <li>To terminate the session of a person who has logged in, click on the black cross icon <img src="{{asset('manual/docs/round-cross.jpg')}}" />.</li>
                            </ol>
                        </p>
                    </section>
                    <!-- Customs -->
                    <section>
                        <h2 id="module7">Customs</h2>
                        <p>
                            <a href="javascript:toggleDiv('Customs1');">
                                <h6 id="module8-1" data-id="Customs1">
                                    Pdf2Pdf Templates
                                </h6>
                            </a>
                            <div id="Customs1" class="hide-object">
                            	<p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the “Active Template” and “Inactive Template” by clicking on “Pdf2Pdf Template”.</p>
	                        <ol>
	                            <li>
	                            		<b>Active Template</b>
	                            		<ol type="a">
	                                    <li>You can see the list of “Active Template” by clicking on the “Active Template” tab.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Custom/Customs 1.png')}}" id="customImage1" class="manual text-info">View</a></li>
	                                    <li>Click on the “Add Template” tab to add Template.</li>
	                                    <li>
	                                    	Every record has a button called “Edit Template”, “Copy Template”, “Generate PDF”, “Inactive Template”, “Edit Template Title”, “Assign Background” and “Generate Invisible PDF”. Modify the record and press the update button to save the record.
	                                    	<ul>
	                                    		<li><b>Edit Template:</b> In “Edit Template” you can edit template design and records of your record.</li>
	                                    		<li><b>Copy Template:</b> In “Copy Template” you can create a duplicate template of your recorded template.</li>
	                                    		<li><b>Generate PDF:</b> Click on the “Generate PDF” sign and select your PDF file from your device, press the “Upload” button to upload the PDF file.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Custom/Customs 3.png')}}" id="customImage3" class="manual text-info">View</a></li>
	                                    		<li><b>Inactive Template:</b> In “Inactive Template” you can inactive template of your record.</li>
	                                    		<li><b>Edit Template Title:</b> Click on the “Edit Template Title” sign and edit your template title, press the “Update” button to change the template title.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Custom/Customs 4.png')}}" id="customImage4" class="manual text-info">View</a></li>
	                                    		<li>
	                                    			<b>Assign Background:</b> Click on the “Assign Background” sign to assign background.<br>
	                                    			Form will be opened.<br>
	                                    			Select updated background from “Print Background” and “Verification Background” press the “Save” button to store the record.<br>
	                                    			Both records have “Use as Background” options. You can see Template with and without background by clicking on use as background option.<br>&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Custom/Customs 5.png')}}" id="customImage5" class="manual text-info">View</a>
	                                    		</li>
	                                    		<li>
	                                    			<b>Generate Invisible PDF:</b> Click on the “Generate Invisible PDF” tab to add PDF file.<br>
	                                    			Form will be opened.<br>
	                                    			Click on the “Choose File” tab and select your PDF file from your device, press the “Upload” button to upload the PDF file.<br>
	                                    		</li>
	                                    	</ul>
	                                    </li>
	                                </ol>
	                            </li>
	                            <li>
	                            	<b>Inactive Template</b>
	                            	<p>&nbsp;&nbsp;&nbsp;In “Inactive Template” you can see inactive temples records.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Custom/Customs 7.png')}}" id="customImage7" class="manual text-info">View</a></p>
    	                            </li>
    	                        </ol>
	                        </div>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('Customs2');">
                                <h6 id="module8-2" data-id="Customs2">
                                    Customs Templates
                                </h6>
                            </a>
                            <div id="Customs2" class="hide-object">
	                           <p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the list of custom templates by clicking on “Custom – Instance Name”.</p>
    	                        <ol>
    	                            <li>
    	                                For Preview
    	                                <ul>
    	                                    <li>Enable the toggle button and select template from dropdown.</li>
    	                                    <li>You can download sample excel from the right side.</li>
    	                                    <li>Choose excel and click on submit.</li>
    	                                    <li>After successful generation, click on "Click Here Link" to download the PDF file.</li>
    	                                </ul>
    	                            </li>

    	                            <li>
    	                                For Live
    	                                <ul>
    	                                    <li>Disable the toggle button and select template from dropdown</li>
    	                                    <li>Choose excel and click on submit</li>
    	                                    <li>After successful generation, click on "Click Here Link" to download the PDF file.</li>
    	                                </ul>
    	                            </li>
    	                        </ol>
	                        </div>
                        </p>          	
                    </section>
                    <!-- Galoties Degree Certificate -->
                    <section>
                        <h2 id="module8">Galgotias Degree Certificate</h2>
                        <p>
                            1. You can see the list of templates by clicking on instance name – degree certificate  
                            &nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Galgotias Degree Certificate/Galgotias Degree Certificate 1.png')}}" id="galgotiasDegreeCertificate1" class="manual text-info">View</a> 
                        </p>
                        <p>2. Click on the PDF sign, beside a record to generate PDF file.
                        </p>
                        <p>3. You have to upload the excel sheet and click on upload. &nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Galgotias Degree Certificate/Galgotias Degree Certificate 2.png')}}" id="galgotiasDegreeCertificate2" class="manual text-info">View</a> </p>
                        <p>4. After successful generation, click on "Click Here Link" to download the PDF file.</p>
                    </section>
                    <!-- Masters -->
                    <section>
                        <h2 id="module9">Masters</h2>
                        <p>
                            <a href="javascript:toggleDiv('Masters1');">
                                <h6 id="module9-1" data-id="Masters1">
                                    Session
                                </h6>
                            </a>
                            <ol id="Masters1" class="hide-object">
                                <li>You can see the Session by clicking on “Masters”.</li>
                                <li>You will see a list of Session Name.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Masters/Master 1.png')}}" id="masterImage1" class="manual text-info">View</a></li>
                                <li>
                                    Click on the “Add Session” tab to add Session Name.<br>Form will be opened.<br>Enter Session Name and press the “Save” button to store the record.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Masters/Master 2.png')}}" id="masterImage2" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Every record has a button called edit. If you click on the edit sign it opens a detailed edit page of record.<br>Modify the record and press the update button to save the record.
                                </li>
                            </ol>
                        </p>

                        <p>
                            <a href="javascript:toggleDiv('Masters2');">
                                <h6 id="module9-2" data-id="Masters2">
                                    Degree
                                </h6>
                            </a>
                            <ol id="Masters2" class="hide-object">
                                <li>You can see the Degree by clicking on “Masters”.</li>
                                <li>You will see a list of “Degree Name”.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Masters/Master 3.png')}}" id="masterImage3" class="manual text-info">View</a></li>
                                <li>
                                    Click on the “Add Degree” tab to add “Degree Name”.<br>Form will be opened.<br>Enter Degree Name and press the “Save” button to store the record.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Masters/Master 4.png')}}" id="masterImage4" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Every record has a button called edit. If you click on the edit sign it opens a detailed edit page of record.<br>Modify the record and press the update button to save the record.
                                </li>
                            </ol>
                        </p>

                        <p>
                            <a href="javascript:toggleDiv('Masters3');">
                                <h6 id="module9-3" data-id="Masters3">
                                    Branch
                                </h6>
                            </a>
                            <ol id="Masters3" class="hide-object">
                                <li>You can see the Branch by clicking on “Masters”.</li>
                                <li>You will see a list of “Branch Name”.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Masters/Master 5.png')}}" id="masterImage5" class="manual text-info">View</a></li>
                                <li>
                                    Click on the “Add Branch” tab to add Branch Name.<br>Form will be opened.<br>Select “Branch Name”, enter “Branch Full Name” and “Branch Short Name” press the “Save” button to store the record.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Masters/Master 6.png')}}" id="masterImage6" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Every record has a button called edit. If you click on the edit sign it opens a detailed edit page of record.<br>Modify the record and press the update button to save the record.
                                </li>
                            </ol>
                        </p>

                        <p>
                            <a href="javascript:toggleDiv('Master4');">
                                <h6 id="module9-4" data-id="Master4">
                                    Semester
                                </h6>
                            </a>
                            <ol id="Master4" class="hide-object">
                                <li>You can see the Semester by clicking on “Masters”.</li>
                                <li>You will see a list of Semester Name.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Masters/Master 7.png')}}" id="masterImage7" class="manual text-info">View</a></li>
                                <li>
                                    Click on the “Add Semester” tab to add Semester Name.<br>Form will be opened.<br>Enter “Semester Name” and Semester Full Name” press the “Save” button to store the record.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Masters/Master 8.png')}}" id="masterImage8" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Every record has a button called edit. If you click on the edit sign it opens a detailed edit page of record.<br>Modify the record and press the update button to save the record.
                                </li>
                            </ol>
                        </p>
                    </section>
                    <!-- Document Varification -->
                    <section>
                        <h2 id="module10">Document Verification</h2>
                        <p>
                            <a href="javascript:toggleDiv('documentVerification1');">
                                <h6 id="module10-1" data-id="documentVerification1">
                                    Old Documents
                                </h6>
                            </a>
                            <div id="documentVerification1" class="hide-object">
                                <p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the “Pending” and “Complete” tabs on the left side by clicking on “Old Documents”.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Document Verification/Document Verification 1.png')}}" id="documentVerificationImage1" class="manual text-info">View</a></p>
                                <ol>
                                    <li>
                                        <b>Pending</b>
                                        <ol type="a">
                                            <li>You can see the list of “Student Name” by clicking on the “Pending” tab.</li>
                                            <li>Every record has a “Info” button. If you click on the info sign it opens a detailed page of record.</li>
                                            <li>
                                                If you want to “Update” the record click on Update sign it opens a detailed page of record.<br>Modify the record and press the “Submit” button to save the record.
                                                <ul>
                                                    <li><b>Generate Non-QR Report</b> Click on Generate “Non-QR Report” to generate the report.</li>
                                                    <li><b>Generate Report Summury</b> Click on Generate Report Summury to generate the report.</li>
                                                </ul>
                                            </li>
                                        </ol>
                                    </li>
                                    <li>
                                        <b>Completed</b>
                                        <ol type="a">
                                            <li>You can see the list of “Student Name” by clicking on the “Completed” tab.</li>
                                            <li>Every record has a “Info” button. If you click on the info sign it opens a detailed page of record.
                                                <ul>
                                                    <li><b>Generate Non-QR Report</b> Click on Generate “Non-QR” Report to generate the report.</li>
                                                    <li><b>Generate Report Summury</b> Click on Generate Report Summury to generate the report.</li>
                                                </ul>
                                            </li>
                                        </ol>
                                    </li>
                                </ol>
                            </div>
                        </p>
                        <p>
                            <a href="javascript:toggleDiv('documentVerification2');">
                                <h6 id="module10-2" data-id="documentVerification2">
                                    SEQR Documents:
                                </h6>
                            </a>
                            <div id="documentVerification2" class="hide-object">
                                <ol type="a">
                                    <li>You can see the list of “Requesting Person”.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Document Verification/Document Verification 2.png')}}" id="documentVerificationImage2" class="manual text-info">View</a></li>

                                    <li>
                                        Every record has a “Info” button. If you click on the info sign it opens a detailed page of record.
                                        <ul>
                                            <li><b>Transactional Report</b>  Click on Transactional Report to view the generated report.</li>
                                            <li><b>Summury Report</b>  Click on Summury Report to view the generated report.</li>
                                        </ul>
                                    </li>
                                </ol>
                            </div>
                        </p>            
                    </section>
                    <!-- Stock -->
                    <section>
                        <h2 id="module11">Stock</h2>
                        <p>
                            <a href="javascript:toggleDiv('Stock1');">
                                <h6 id="module11-1" data-id="Stock1">
                                    Stationary Stock
                                </h6>
                            </a>
                            <ol id="Stock1" class="hide-object">
                                <li>You can see the “Stationary stock” by clicking on “Stock”.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Stock/Stock 1.png')}}" id="stockImage1" class="manual text-info">View</a></li>
                                <li>
                                    Click on the “Add Stationary” tab to add Stationary.<br>Form will be opened.<br>Select “Card Category”, “Academic Year”, “Date of Received”, Enter “Serial No. Form”, “Serial No. To” and “Quantity” press the “Save” button to store the record.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Stock/Stock 2.png')}}" id="stockImage2" class="manual text-info">View</a>
                                </li>
                            </ol>
                        </p>

                        <p>
                            <a href="javascript:toggleDiv('Stock2');">
                                <h6 id="module11-2" data-id="Stock2">
                                    Damaged Stock
                                </h6>
                            </a>
                            <ol id="Stock2" class="hide-object">
                                <li>You can see the “Damaged stock” by clicking on “Stock”.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Stock/Stock 3.png')}}" id="stockImage3" class="manual text-info">View</a></li>
                                <li>
                                    Click on the “Add Card” tab to add Damage serial number.<br>Form will be opened.<br>Select “Card Category”, Enter “Serial No. of Card”, Select “Type”, Enter “Remark”, Select “Exam Name”, “Degree”, “Branch”, “Semester” and Enter “Student Reg. Number” press the “Save” button to store the record.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Stock/Stock 4.png')}}" id="stockImage4" class="manual text-info">View</a>
                                </li>
                                <li>
                                    Click on Download Report to view the generated report.
                                </li>
                            </ol>
                        </p>

                        <p>
                            <a href="javascript:toggleDiv('Stock3');">
                                <h6 id="module11-3" data-id="Stock3">
                                    Consumption Stock
                                </h6>
                            </a>
                            <ol id="Stock3" class="hide-object">
                                <li>You can see the “Consumption Stock” by clicking on “Stock”.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Stock/Stock 5.png')}}" id="stockImage5" class="manual text-info">View</a></li>
                                <li>
                                    To view stock Select “Session”, “Degree”, “Branch”, “Scheme”, “Term”, “Student Type”, “Section” and “Card Type” press the “Show” button to view the generated report.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Stock/Stock 6.png')}}" id="stockImage6" class="manual text-info">View</a>
                                </li>
                            </ol>
                        </p>

                        <p>
                            <a href="javascript:toggleDiv('Stock3');">
                                <h6 id="module11-3" data-id="Stock3">
                                    Download Consumption Report
                                </h6>
                            </a>
                            <ol id="Stock3" class="hide-object">
                                <li>You can see the “Consumption Stock” by clicking on “Stock”.</li>
                                <li>
                                    To view stock Select “Session”, “Degree”, “Branch”, “Scheme”, “Term”, “Student Type”, “Section” and “Card Type” press the “Show” button to view the generated report.
                                </li>
                            </ol>
                        </p>

                        <p>
                            <a href="javascript:toggleDiv('Stock4');">
                                <h6 id="module11-2" data-id="Stock4">
                                    Download Consumption Report
                                </h6>
                            </a>
                            <div id="Stock4" class="hide-object">
                               <p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the Download “Consumption Report” by clicking on “Stock”.</p>
                                <ol>
                                    <li>
                                        Report 1
                                        <ul>
                                            <li>You can download the excel file of “List of students with grade card serial number for exam”.</li>
                                            <li>To download excel file Select “Session”, “Degree”, “Branch”, “Scheme”, “Term”, “Section”, “From Date” and “To Date” press the Download Excel tab to view the generated report.</li>
                                        </ul>
                                    </li>

                                    <li>
                                        Report 2
                                        <ul>
                                            <li>You can download excel file of “Summury of exam wise consumption”.</li>
                                            <li>To download excel file, Select “Session” press the Download Excel tab to view the generated report.</li>
                                        </ul>
                                    </li>

                                    <li>
                                        Report 3
                                        <ul>
                                            <li>You can download excel file of “Branch wise consumption”.</li>
                                            <li>To download excel sheet, Select “Branch” press the Download Excel tab to view the generated report.</li>
                                        </ul>
                                    </li>

                                    <li>
                                        Report 4
                                        <ul>
                                            <li>You can download excel file of “Semester wise consumption”.</li>
                                            <li>To download excel file, Select “Semester” press the Download Excel tab to view the generated report.</li>
                                        </ul>
                                    </li>

                                    <li>
                                        Report 5
                                        <ul>
                                            <li>You can download excel file of “Balance Grade Cards”.</li>
                                            <li>To download excel file, press the Download Excel tab to view the generated report.</li>
                                        </ul>
                                    </li>
                                </ol>
                            </div>
                        </p>
                    </section>
                    <!-- Masters -->
                    <!-- Sandboxing -->
                    <section>
                        <h2 id="module12">Sandboxing</h2>
                        <p>
                            <a href="javascript:toggleDiv('Sandboxing1');">
                                <h6 id="module12-1" data-id="Sandboxing1">
                                    Certificate Management
                                </h6>
                            </a>
                            <div id="Sandboxing1" class="hide-object">
                                <p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the “Active Certificate” and “Inactive Certificate” tabs on the left side by clicking on “Certificate Management”.</p>
                                <ol>
                                    <li>
                                            <b>Active Certificate   </b>
                                            <ol type="a">
                                            <li>You can see the list of “Active Certificate” by clicking on the “Active Certificate” tab.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 1.png')}}" id="sandboxingImage1" class="manual text-info">View</a></li>
                                            <li>Every record has a “Info” button. Click on the info sign and it opens the “Student info QR code” page.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 3.png')}}" id="sandboxingImage3" class="manual text-info">View</a></li>
                                            <li>If you want Download QR Code click on the “Download OR code” tab.</li>
                                            <li>Every record has a toggle button. You can enable and disable live student certificate.</li>
                                        </ol>
                                    </li>
                                    <li>
                                        <b>Inactive Certificate</b>
                                        <ol type="a">
                                            <li>You can see the list of “Inactive Certificate” by clicking on the “Inactive Certificate” tab.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 2.png')}}" id="sandboxingImage2" class="manual text-info">View</a></li>
                                            <li>Every record has a “Info” button. Click on the info sign and it opens the “Student info QR code” page.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 3.png')}}" id="sandboxingImage3" class="manual text-info">View</a></li>
                                            <li>If you Download QR Code click on the “Download OR code” tab.</li>
                                            <li>Every record has a toggle button. You can enable and disable live student certificate.</li>
                                        </ol>
                                    </li>
                                </ol>
                            </div>
                        </p> 


                        <p>
                            <a href="javascript:toggleDiv('Sandboxing2');">
                                <h6 id="module12-2" data-id="Sandboxing2">
                                    Printing Details
                                </h6>
                            </a>
                            <div id="Sandboxing2" class="hide-object">
                                <p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the “Active” and “Inactive” tab on the left side by clicking on “Printing Details”.</p>
                                <ol>
                                    <li>
                                        <b>Active</b>
                                        <ol type="a">
                                            <li>You can see the list of “Active Certificate” by clicking on the “Active” tab.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 4.png')}}" id="sandboxingImage4" class="manual text-info">View</a></li>
                                            <li>Every record has a “Info” button. Click on the info sign and it opens the “Printing Details Information” page.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 5.png')}}" id="sandboxingImage5" class="manual text-info">View</a></li>
                                        </ol>
                                    </li>
                                    <li>
                                        <b>Inactive</b>
                                        <ol type="a">
                                            <li>You can see the list of “Inactive Certificate” by clicking on the “Inactive” tab.</li>
                                            <li>Every record has a “Info” button. Click on the info sign and it opens the “Printing Details Information” page.</li>
                                        </ol>
                                    </li>
                                </ol>
                            </div>
                        </p> 


                        <p>
                            <a href="javascript:toggleDiv('Sandboxing3');">
                                <h6 id="module12-3" data-id="Sandboxing3">
                                    Template Data
                                </h6>
                            </a>
                            <div id="Sandboxing3" class="hide-object">
                                <p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the list of “Template Data” by clicking on the “Sandboxing” tab.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 6.png')}}" id="sandboxingImage6" class="manual text-info">View</a></p>
                                <ol>
                                    <li>
                                        <b>Printing Report</b>
                                        <ol type="a">
                                            <li>You can see the list of “Printing Report” by clicking on the “Sandboxing” tab.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 7.png')}}" id="sandboxingImage7" class="manual text-info">View</a></li>
                                            <li>You will see a list of “Template”.</li>
                                            <li>You can download Excel file of every record. Go on “Excel filename” click on “Excel file download” tab to download excel file.</li>
                                        </ol>
                                    </li>
                                </ol>
                            </div>
                        </p>  


                        <p>
                            <a href="javascript:toggleDiv('Sandboxing4');">
                                <h6 id="module12-4" data-id="Sandboxing4">
                                    Scan History
                                </h6>
                            </a>
                            <div id="Sandboxing4" class="hide-object">
                                <p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the “WebApp”, “Android” and “iPhone” tabs on the left side by clicking on “Scan History”.
                                <ol>
                                    <li>
                                        <b>WebApp</b>
                                        <ol type="a">
                                            <li>You can see the list of “WebApp” by clicking on the “WebApp” tab.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 8.png')}}" id="sandboxingImage8" class="manual text-info">View</a></p></li>
                                            <li>You will see a list of “Scanned Data”.</li>
                                            <li>Every record has a “Success” button. Click on the Success button and it opens the “Student’s info” page.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 11.png')}}" id="sandboxingImage11" class="manual text-info">View</a></li>
                                            <li>To download Certificate. Go to the certificate filename and click the “pdf file link”.</li>
                                        </ol>
                                    </li>
                                    <li>
                                        <b>Android</b>
                                        <ol type="a">
                                            <li>You can see the list of “Android” by clicking on the “Android” tab.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 9.png')}}" id="sandboxingImage9" class="manual text-info">View</a></p></li>
                                            <li>You will see a list of “Scanned Data”.</li>
                                            <li>Every record has a “Success” button. Click on the Success button and it opens the “Student’s info” page.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 11.png')}}" id="sandboxingImage11" class="manual text-info">View</a></li>
                                            <li>To download Certificate. Go to the certificate filename and click the “pdf file link”.</li>
                                        </ol>
                                    </li>
                                    <li>
                                        <b>iPhone</b>
                                        <ol type="a">
                                            <li>You can see the list of “iPhone” by clicking on the “iPhone” tab.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 10.png')}}" id="sandboxingImage10" class="manual text-info">View</a></p></li>
                                            <li>You will see a list of “Scanned Data”.</li>
                                            <li>Every record has a “Success” button. Click on the Success button and it opens the “Student’s info” page.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 11.png')}}" id="sandboxingImage11" class="manual text-info">View</a></li>
                                            <li>To download Certificate. Go to the certificate filename and click the “pdf file link”.</li>
                                        </ol>
                                    </li>
                                </ol>
                            </div>
                        </p>


                        <p>
                            <a href="javascript:toggleDiv('Sandboxing5');">
                                <h6 id="module12-5" data-id="Sandboxing5">
                                    Payment Transactions
                                </h6>
                            </a>
                            <div id="Sandboxing5" class="hide-object">
                                <p style="margin: 0px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You can see the “Success” and “Failed” tab on the left side by clicking on “Payment Transactions”.</p>
                                <ol>
                                    <li>
                                            <b>Success</b>
                                            <ol type="a">
                                            <li>You can see the list of “Success Payment Transactions” details by clicking on “Success”.>&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 12.png')}}" id="sandboxingImage12" class="manual text-info">View</a></p></li>
                                        </ol>
                                    </li>
                                    <li>
                                        <b>Failed</b>
                                        <ol type="a">
                                            <li>You can see the list of “Failed Payment Transactions” details by clicking on “Failed”.&nbsp;<a href="javascript:void(0);" file="{{asset('manual/docs/Sandboxing/Sandboxing 13.png')}}" id="sandboxingImage13" class="manual text-info">View</a></li>
                                        </ol>
                                    </li>
                                </ol>
                            </div>
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