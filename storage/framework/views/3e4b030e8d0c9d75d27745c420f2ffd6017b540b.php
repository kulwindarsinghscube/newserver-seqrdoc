<script type="text/javascript">
    $('.manual').on('click', function(event) { 
        var settings = {
            width  : '100%',
            height : '500'
        };      
        var file = $(this).attr('file');
        var ext = file.substring(file.lastIndexOf('.') + 1);     
        if (/^(jpg|jpeg|gif|png|JPG|PNG)$/.test(ext)) {
            $(this).after(function () {
                var id = $(this).attr('id');            
                var gdvId = (typeof id !== 'undefined' && id !== false) ? id + '-gdocsviewer' : '';
                $("#"+gdvId).remove();
                path=file; 
                docTitle=$(this).text(); 
                var MyTitle = $(this).attr('title'); 
                if(docTitle=="View"){
                    $(this).text('Close');
                }else{
                    $(this).text('View');
                    closemanual('\''+gdvId+'\'');
                }
                if(MyTitle==undefined){                 
                //return '<div id="' + gdvId + '" class="gdocsviewer"><a href="javascript:void(0);" onclick="closemanual(\''+ gdvId +'\')" class="doc-close" idname="' + gdvId + '" title="Close" style="float:left"><small>Close</small></a><div style="clear:both"></div><img src="' + path + '" class="img-responsive" style="float:left" /></div><div style="clear:both"></div>';
                return '<div id="' + gdvId + '" class="gdocsviewer"><div style="clear:both"></div><img src="' + path + '" class="img-responsive" style="float:left" /></div><div style="clear:both"></div>';
                }else{
                return '<div id="' + gdvId + '" class="gdocsviewer"><small class="text-left text-success">'+MyTitle+'</small><div style="clear:both"></div><a href="javascript:void(0);" onclick="closemanual(\''+ gdvId +'\')" class="doc-close" idname="' + gdvId + '" title="Close" style="float:left"><small>Close</small></a><div style="clear:both"></div><img src="' + path + '" class="img-responsive" style="float:left" /></div><div style="clear:both"></div>';
                }
            })
        }
      
    }); 

    function closemanual(id) { 
        // id= id.replace("''",'');
        // alert(id);
        // 4-gdocsviewer
        $("#"+id).remove();
    }
</script>

<!-- open modal  -->
<script type="text/javascript">
    $(document).ready(function(){
        // Font Master
        $('#fontModalClick').click(function(){
            $('#fontModal').modal('show');
        });


        // Background Template Management
        $('#backgroundTemplateManagementClick').click(function(){
            $('#backgroundTemplateManagementModal').modal('show');
        });


        // Background Template Management
        $('#templateManagementClick').click(function(){
            $('#templateManagementModal').modal('show');
        });

        // Process Excel
        $('#processExcelClick').click(function(){
            $('#processExcelModal').modal('show');
        });


        // Dynamic Image Management
        $('#dynamicImageManagementClick').click(function(){
            $('#dynamicImageManagementModal').modal('show');
        });


        // Generate Id Cards
        $('#generateIdCardsClick').click(function(){
            $('#generateIdCardsModal').modal('show');
        });


        // Id Cards Status
        $('#idCardsStatusClick').click(function(){
            $('#idCardsStatusModal').modal('show');
        });


        // PDF 2 PDF Template
        $('#pdf2pdfClick').click(function(){
            $('#pdf2pdfModal').modal('show');
        });

        

        // Payment Gateway
        $('#paymentGatewayClick').click(function(){
            $('#paymentGatewayModal').modal('show');
        });


        // PG Configuration
        $('#pgConfigurationClick').click(function(){
            $('#pgConfigurationModal').modal('show');
        });


        // PG Configuration
        $('#documentRateMasterClick').click(function(){
            $('#documentRateMasterModal').modal('show');
        });


        // Certificate Management
        $('#certificateManagementClick').click(function(){
            $('#certificateManagementModal').modal('show');
        });


        // Printing Details
        $('#printingDetailsClick').click(function(){
            $('#printingDetailsModal').modal('show');
        });


        // Sessions Master
        $('#sessionsMasterClick').click(function(){
            $('#sessionsMasterModal').modal('show');
        });

        // Degree Master
        $('#degreeMasterClick').click(function(){
            $('#degreeMasterModal').modal('show');
        });

        // Branch Master
        $('#branchMasterClick').click(function(){
            $('#branchMasterModal').modal('show');
        });

        // Semester Master
        $('#semesterMasterClick').click(function(){
            $('#semesterMasterModal').modal('show');
        });


        // Old Documents 
        $('#oldDocumentsClick').click(function(){
            $('#oldDocumentsModal').modal('show');
        });


        // SEQR Documents 
        $('#seqrDocumentsClick').click(function(){
            $('#seqrDocumentsModal').modal('show');
        });


        // stationary Stock
        $('#stationaryStockClick').click(function(){
            $('#stationaryStockModal').modal('show');
        });


        // stationary Stock
        $('#damagedStockClick').click(function(){
            $('#damagedStockModal').modal('show');
        });


        // Consumption Report Stock
        $('#consumptionReportClick').click(function(){
            $('#consumptionReportModal').modal('show');
        });


        // Consumption Report Export Stock
        $('#consumptionReportExportClick').click(function(){
            $('#consumptionReportExportModal').modal('show');
        });


        // Institute Master
        $('#instituteMasterClick').click(function(){
            $('#instituteMasterModal').modal('show');
        });


        // User Master
        $('#userMasterClick').click(function(){
            $('#userMasterModal').modal('show');
        });


        // Student Master
        $('#studentMasterClick').click(function(){
            $('#studentMasterModal').modal('show');
        });


        // Admin Master
        $('#adminMasterClick').click(function(){
            $('#adminMasterModal').modal('show');
        });

        // Role Master
        $('#roleMasterClick').click(function(){
            $('#roleMasterModal').modal('show');
        });

        // Setting Master
        $('#settingMasterClick').click(function(){
            $('#settingMasterModal').modal('show');
        });



        // Sandboxing Certificate Management
        $('#certificateManagementSandboxingClick').click(function(){
            $('#certificateManagementSandboxingModal').modal('show');
        });


        // Sandboxing Printing Details
        $('#printingDetailsSandboxingClick').click(function(){
            $('#printingDetailsSandboxingModal').modal('show');
        });


        // Sandboxing Template Data
        $('#templateDataSandboxingClick').click(function(){
            $('#templateDataSandboxingModal').modal('show');
        });


        // Sandboxing Scan History
        $('#scanHistorySandboxingClick').click(function(){
            $('#scanHistorySandboxingModal').modal('show');
        });


        // Sandboxing Payment Transaction
        $('#paymentTransactionSandboxingClick').click(function(){
            $('#paymentTransactionSandboxingModal').modal('show');
        });


        // Template Data Report
        $('#templateDataReportClick').click(function(){
            $('#templateDataReportModal').modal('show');
        });


        // Printing Report Report
        $('#printingReportReportClick').click(function(){
            $('#printingReportReportModal').modal('show');
        });


        // Scan History Report
        $('#scanHistoryReportClick').click(function(){
            $('#scanHistoryReportModal').modal('show');
        });


        // payment Transactions Report
        $('#paymentTransactionsReportClick').click(function(){
            $('#paymentTransactionsReportModal').modal('show');
        });

        // User Session Manager Report
        $('#userSessionManagerReportClick').click(function(){
            $('#userSessionManagerReportModal').modal('show');
        });
    



        
            
        


    });    
</script><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/layout/modal_script.blade.php ENDPATH**/ ?>