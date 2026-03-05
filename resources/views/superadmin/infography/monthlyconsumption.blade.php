@extends('superadmin.layout.layout')

@section('content')

<style type="text/css">
  
  #columnchart_values {
 /*  overflow-x: scroll;
    overflow-y: scroll; */  
    width: 100%;
    min-height: 1000px;
}
</style>
<div class="container">
  <div class="col-xs-12">
    <div class="clearfix">
     <div id="">
       <div class="container-fluid">
      <!--   <div class="row">
         <div class="col-lg-12">
          <h1 class="page-header"><i class="fa fa-fw fa-foursquare"></i> Font master
             <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('fontmaster') }}</ol>
          </h1>
        </div>
      </div> -->
     <!--  <div class="hello"> -->

       <div class="modal fade" id="templateDetails" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Instance Templates : <span id="instanceName"></span></h4>
              </div>
              <div class="modal-body">
           
          <div class="row">
                <div class="col-xs-12">
                <table id="master_table" class="table table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Template Name</th>
                            <th>Temlate Type</th>
                            <th>Active Documents</th>
                            <th>Inactive Documents</th>
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                </table>
            </div>

                
              </div>
            </div>
          </div>
        </div>
      </div>
          <form id="reportData">

          <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                  <div class="input-group alert alert-info" style="    margin: auto;width: 55%;">
                    <h4 class="modal-title" id="filterLoader" style="display: none;color: rgb(51 102 204);font-size: 14px;">Please wait ..... <img src="/backend/images/loading.gif"></h4>
                    <input type="hidden" name="report" value="0" id='report'>
                    <input type="hidden" name="projectName" value="" id='projectName'>
                    
                    <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-5">
                    <select class="selectpicker" name="monthFilter" id="monthFilter"  style="padding-top:10px;" required aria-required="true" >
                        <option value="false" disabled="">Select Month</option>
                            <option value="01" selected>JAN</option>
                            <option value="02">FEB</option>
                            <option value="03">MAR</option>
                            <option value="04">APR</option>
                            <option value="05">MAY</option>
                            <option value="06">JUN</option>
                            <option value="07">JUL</option>
                            <option value="08">AUG</option>
                            <option value="09">SEP</option>
                            <option value="10">OCT</option>
                            <option value="11">NOV</option>
                            <option value="12">DEC</option>
                    </select>
                  </div>
                  <div class="col-xs-12 col-sm-12 col-md-5">
                    <select class="selectpicker" name="yearFilter" id="yearFilter"  style="padding-top:10px;" required data-live-search="true" aria-required="true" >
                        <option value="false" disabled="">Select Year</option>
                   <?php
                    $currently_selected = date('Y');
                      $earliest_year = 2018;
                      $latest_year = date('Y');

                      foreach (range($latest_year, $earliest_year) as $i) {
                        echo '<option value="' . $i . '"' . ($i === $currently_selected ? ' selected="selected"' : '') . '>' . $i . '</option>';}
                        ?>
                    </select>
                  </div>
                  <div class="col-xs-12 col-sm-12 col-md-2" style="padding:0;">
                   <span class="input-group-btn" data-toggle="tooltip" title="" data-original-title="Filter"><button class="btn btn-success" id="submit2" title="Filter Data" style="border-radius: 5px;"><i class="fa fa-arrow-right"></i></button></span>
                    <span class="input-group-btn" data-toggle="tooltip" title="" data-original-title="Export Data" style="    position: absolute;
    float: right;
    right: 0;
    margin-right: 50px;
    margin-top: -34px;"><button class="btn btn-success" id="exportAllData" title="Export Data" style="margin-left: 5px;border-radius: 5px;"><i class="fa fa-file-excel-o"></i></button></span>
                  </div>
                    
                  </div>
                </div>
              </div>
              </div>
            </form>

          <div class="row" id="chartContainer">
              <div class="col-xs-12 col-sm-12 col-md-12">
                <div id="columnchart_values"></div>
              </div>
          </div>
    <!-- </div> -->

  </div>
</div>
</div>
</div>
</div>

@stop

@section('style')

<!-- <style>
      body{
        overflow: hidden !important;
      }

      #columnchart_values{
        overflow-x: scroll !important;
      }
</style> -->
@stop
@section('script')

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
 // google.charts.load('visualization', {'packages':['corechart']});
 // google.charts.setOnLoadCallback(drawChart);

  
   $('#monthFilter').val('<?php echo date('m'); ?>');
    $('#monthFilter').selectpicker('refresh');
    $('#yearFilter').val('<?php echo date('Y'); ?>');
    $('#yearFilter').selectpicker('refresh');
    //updateDataTable('<?php echo date('m'); ?>','<?php echo date('Y'); ?>');


/*$("#clearDateFilter").click(function(e){
    e.preventDefault();
$('#report').val('0');
$('#monthFilter').val('<?php echo date('m'); ?>');
$('#yearFileter').val('<?php echo date('Y'); ?>');
$('#filterProject').val('');
 $('#monthFilter').selectpicker('refresh');
$('#yearFileter').selectpicker('refresh');
 $('#filterProject').selectpicker('refresh');
  filter();
});*/

$("#submit2").click(function(e){
    e.preventDefault();
     filter();
});

/*$('#monthFilter').change(function(){
       var month=$('#monthFilter').val();
       var year=$('#yearFilter').val();
       filter();
    });

    $('#yearFilter').change(function(){
       var month=$('#monthFilter').val();
       var year=$('#yearFilter').val();
       filter();
    });*/


function filter(){
  $.ajax({
  url: 'https://www.google.com/jsapi?callback',
  cache: true,
  dataType: 'script',
  success: function(){
    google.load('visualization', {packages:['corechart'], 'callback' : function()
      {
           
             var $data = $('#reportData').serialize();
            $.ajax({
                 type: "GET",
                 dataType: "json",
                 data:$data,
                 beforeSend: function (){
                $("#filterLoader").show();
                },
                // data: {fromDate: fromDate,toDate:toDate,projectIds:projectIds,report:report,fromDate: fromDate,toDate:toDate,projectIds:projectIds,report:report},
                 url: '<?= URL::route('superadmin.getmothlyconsumption') ?>',
                 success: function(jsondataArr) {
                    
                      var jsondata=jsondataArr.data;
                      var projectCount=jsondataArr.projectCount;
                      var totalActive=jsondataArr.totalActive;
                      var totalInActive=jsondataArr.totalInActive;
                      var jsondata = jsondata.map( Object.values );

                      var dataCount=jsondata.length;
                      if(dataCount==0){
                        $(".alert-danger .message").html('No data to display.');
                        $(".alert-danger").fadeIn();
                        return false;
                      }      
                      //console.log(dataCount);               
                      //var dataCountH=parseInt(dataCount)-1;
                      var dataCountH=parseInt(dataCount);
                      if(dataCountH>10){
                         var height=parseInt(dataCountH)*75;
                      }else if(dataCountH>5){
                         var height=parseInt(dataCountH)*75;
                      }else if(dataCountH>2){
                         var height=parseInt(dataCountH)*100;
                      }else{
                         var height=parseInt(dataCountH)*200;
                      }
                     // console.log(height);
                     // var height=600;
                      $('#columnchart_values').css('min-height', height+'px');
                      $('#columnchart_values').css('max-height', height+'px');
                      $('#columnchart_values').css('height',height+'px');
                      //$('#chartContainer').css('max-height', parseInt(height)*parseInt(2)+'px');
                     // $('#chartContainer').css('overflow-y','scroll');
                      // $('#chartContainer').css('max-height','400px');
                    var data = new google.visualization.DataTable();

                    data.addColumn('string', 'Institute');
                    data.addColumn('number', 'Active Documents ('+totalActive+')');
                    data.addColumn({type: 'string', role: 'annotation'});
                    data.addColumn('number', 'Inactive Documents ('+totalInActive+')');
                    data.addColumn({type: 'string', role: 'annotation'});
                   
                    // A column for custom tooltip content
                    //data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
                    //data.addColumn('number', 'Cost ('+jsondataArr.totalCost+'k)');
                   // data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
                    data.addRows(jsondata);
                    var options = {
                                    title: 'Monthly Consumption Documents',
                                    chartArea: {width: '60%',top:50},
                                     annotations: {
                                      textStyle: {
                                        fontSize: 13,
                                      }
                                    },
                                    hAxis: {
                                      title: 'Documents Count',
                                      minValue: 0,
                                      textStyle: {
                                        bold: true,
                                        fontSize: 12,
                                        color: '#4d4d4d'
                                      },
                                      titleTextStyle: {
                                        bold: true,
                                        fontSize: 18,
                                        color: '#4d4d4d'
                                      },
                                    },
                                    vAxis: {
                                      title: 'Instances - '+projectCount,
                                      textStyle: {
                                        fontSize: 14,
                                        bold: true,
                                        color: '#848484'
                                      },
                                      titleTextStyle: {
                                        fontSize: 14,
                                        bold: true,
                                        color: '#848484'
                                      }
                                    },
                                    tooltip: {isHtml: true}
                                  };


                function selectHandler() {
                    var selectedItem = chart.getSelection()[0];
                    if (selectedItem) {

                      if(selectedItem.row!=null){
                        console.log(selectedItem);
                      var instituteName = data.getValue(selectedItem.row, 0);
                      instituteDetails(instituteName,selectedItem.column);
                      }
                    }
                  }


                  var chart = new google.visualization.BarChart(document.getElementById('columnchart_values'));
                  chart.draw(data, options);
                  // Every time the table fires the "select" event, it should call your
                  // selectHandler() function.
                  google.visualization.events.addListener(chart, 'select', selectHandler);
                  $("#filterLoader").hide();

                 }
            });    
       

      }
    });
    return true;
  }
});
}

 filter();


 $('#exportAllData').click(function(e){
    e.preventDefault();
  var token = "<?= csrf_token()?>";

                  var $data = $('#reportData').serialize();
                  var newForm = jQuery('<form>', {
                                        'action': '<?= URL::route('superadmin.mothlyconsumptionexport') ?>',
                                        'method':'POST',
                                        'target': '_top'
                                    }).append(jQuery('<input>', {
                                        'name': '_token',
                                        'value': token,
                                        'type': 'hidden'
                                    })).append(jQuery('<input>', {
                                        'name': 'monthFilter',
                                        'value': $('#monthFilter').val(),
                                        'type': 'hidden'
                                    })).append(jQuery('<input>', {
                                        'name': 'yearFilter',
                                        'value': $('#yearFilter').val(),
                                        'type': 'hidden'
                                    }));
                                    $(document.body).append(newForm);
                                 //console.log(newForm);
                                  newForm.submit();
 });


function instituteDetails(instance,type){
  //console.log($instituteName);

  $('#instanceName').html(instance);
  $("#master_table").hide();
   var url = "<?=route('superadmin.mothlyconsumptiondetail')?>"+"?instance="+instance+"&type="+type+"&monthFilter="+$('#monthFilter').val()+"&yearFilter="+$('#yearFilter').val(); 
  // console.log(url);    
            $('#master_table').DataTable().destroy();
            var oTable = $('#master_table').DataTable({
                "bProcessing": false,
                "bServerSide": true,
                "autoWidth": true,
                "aaSorting": [
                    [1, "ASC"]
                ],
                /*lengthMenu: [
                    [ 50, 100,200,500],
                    [ '50','100','200','500']
                ],*/
                "sAjaxSource": url,
                "aoColumns": [
                    {mData: "rownum", bSortable:false,sWidth: "12%"},
                    { "mData": "template_name",sWidth: "22%",bSortable: true},
                    { "mData": "template_type",sWidth: "22%",bSortable: false,sClass:"text-center"},
                    { "mData": "active_documents",sWidth: "22%",bSortable: true,bVisible:true,sClass:"text-center"},
                    { "mData": "inactive_documents",sWidth: "22%",bSortable: true,sClass:"text-center"},
                   
                ],
                "rowCallback": function (nRow, aData, iDisplayIndex) {
                  var oSettings = this.fnSettings ();
                  $("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                  return nRow;
                },
              
            });
  oTable.on('draw',function(){
    $("#master_table").show();
    $(".loader").addClass('hidden');
  });         

    $('#templateDetails').modal('show');    
          
}
</script>

@stop
