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
<style type="text/css">
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
  margin-left: 20px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}
input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}
 /*range css*/
$range-width: 100% !default;

.range-slider {
  width: $range-width;
}
.range-slider__range {
  -webkit-appearance: none;
  width: calc(100% - (#{$range-label-width + 13px}));
  height: $range-track-height;
  border-radius: 5px;
  background: $range-track-color;
  outline: none;
  padding: 0;
  margin: 0;

  &::-webkit-slider-thumb {
    appearance: none;
    width: $range-handle-size;
    height: $range-handle-size;
    border-radius: 50%;
    background: $range-handle-color;
    cursor: pointer;
    transition: background .15s ease-in-out;

    &:hover {
      background: $range-handle-color-hover;
    }
  }

  &:active::-webkit-slider-thumb {
    background: $range-handle-color-hover;
  }

  &::-moz-range-thumb {
    width: $range-handle-size;
    height: $range-handle-size;
    border: 0;
    border-radius: 50%;
    background: $range-handle-color;
    cursor: pointer;
    transition: background .15s ease-in-out;

    &:hover {
      background: $range-handle-color-hover;
    }
  }

  &:active::-moz-range-thumb {
    background: $range-handle-color-hover;
  }

  &:focus {
    
    &::-webkit-slider-thumb {
      box-shadow: 0 0 0 3px $shade-0,
                  0 0 0 6px $teal;
    }
  }
}
.range-slider__value {
  display: inline-block;
  position: relative;
  width: $range-label-width;
  color: $shade-0;
  line-height: 20px;
  text-align: center;
  border-radius: 3px;
  background: $range-label-color;
  padding: 5px 10px;
  margin-left: 8px;

  &:after {
    position: absolute;
    top: 8px;
    left: -7px;
    width: 0;
    height: 0;
    border-top: 7px solid transparent;
    border-right: 7px solid $range-label-color;
    border-bottom: 7px solid transparent;
    content: '';
  }
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
<?php //print_r($sites); exit; ?>
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

            <?php 
                    $sitesDropdown='';
                    foreach ($sites as $readSite) { 
                      $sitesDropdown .='<option value="'.$readSite.'">'.$readSite.'</option>';
                    } 

                   
                   // $currently_selected = date('Y');
                  /*    $monthYearDropDown='';
                      $earliest_year = 2018;
                      $latest_year = date('Y');

                      foreach (range($latest_year, $earliest_year) as $i) {
                        //echo '<option value="' . $i . '"' . ($i === $currently_selected ? ' selected="selected"' : '') . '>' . $i . '</option>';

                        
                        if((date('m')>=12&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="12-'.$i.'" data-month="12" data-year="'.$i.'">DEC-'.$i.'</option>';
                        }
                        if((date('m')>=11&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="11-'.$i.'" data-month="11" data-year="'.$i.'">NOV-'.$i.'</option>';
                        }
                        if((date('m')>=10&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="10-'.$i.'" data-month="10" data-year="'.$i.'">OCT-'.$i.'</option>';
                        }
                        if((date('m')>=9&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="09-'.$i.'" data-month="09" data-year="'.$i.'">SEP-'.$i.'</option>';
                        }
                        if((date('m')>=8&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="08-'.$i.'" data-month="08" data-year="'.$i.'">AUG-'.$i.'</option>';
                        }
                        if((date('m')>=7&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="07-'.$i.'" data-month="07" data-year="'.$i.'">JUL-'.$i.'</option>';
                        }
                        if((date('m')>=6&&$i<=$latest_year)||($i!=$latest_year)){
                          $monthYearDropDown .= '<option value="06-'.$i.'" data-month="06" data-year="'.$i.'">JUN-'.$i.'</option>';
                        }
                        if((date('m')>=5&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="05-'.$i.'" data-month="05" data-year="'.$i.'">MAY-'.$i.'</option>';
                        }
                        if((date('m')>=4&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="04-'.$i.'" data-month="04" data-year="'.$i.'">APR-'.$i.'</option>';
                        }
                        if((date('m')>=3&&$i<=$latest_year)||($i!=$latest_year)){
                          $monthYearDropDown .= '<option value="03-'.$i.'" data-month="03" data-year="'.$i.'">MAR-'.$i.'</option>';
                        }
                        if((date('m')>=2&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="02-'.$i.'" data-month="02" data-year="'.$i.'">FEB-'.$i.'</option>';
                        }
                        if((date('m')>=1&&$i<=$latest_year)||($i!=$latest_year)){
                        $monthYearDropDown .= '<option value="01-'.$i.'" data-month="01" data-year="'.$i.'">JAN-'.$i.'</option>';
                        }
                      }*/
                        
                      
                        
            ?>
              <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                  <div class="input-group alert alert-info" style="width: 100%;margin: auto;">
                    <h4 class="modal-title" id="filterLoader" style="display: none;color: rgb(51 102 204);font-size: 14px;">Please wait ..... <img src="/backend/images/loading.gif"></h4>
                    <input type="hidden" name="report" value="0" id='report'>
                    <input type="hidden" name="projectName" value="" id='projectName'>
                    
                    <div class="row">
                      <div class="col-xs-12 col-sm-12 col-md-4">
                      <select class="selectpicker" name="cmp1Filter" id="cmp1Filter"  style="padding-top:10px;" required aria-required="true" >
                          <option value="false" selected>Select Instance1</option>

                              <?php echo $sitesDropdown; ?>
                      </select>
                    </div>
                     <div class="col-xs-12 col-sm-12 col-md-4">
                      <select class="selectpicker" name="cmp2Filter" id="cmp2Filter"  style="padding-top:10px;" required aria-required="true" >
                          <option value="false" selected>Select Instance2</option>

                              <?php echo $sitesDropdown; ?>
                      </select>
                    </div>
                     <div class="col-xs-12 col-sm-12 col-md-4">
                      <select class="selectpicker" name="cmp3Filter" id="cmp3Filter"  style="padding-top:10px;" required aria-required="true" >
                          <option value="false" selected>Select Instance3</option>

                              <?php echo $sitesDropdown; ?>
                      </select>
                    </div>
                  </div>

                  <div class="row" style="margin-top: 10px;">
                    <div class="col-xs-12 col-sm-12 col-md-3">
                    <!-- <div class="form-group"> -->
                    <label class="control-label" for="lifeTimeToggle" style="vertical-align: middle;">Select Period :</label>
                    <label class="switch" style="vertical-align: middle;">  
                      <input type="checkbox" class="form-control" id="lifeTimeToggle" name="lifeTimeToggle" value="1">
                      <span class="slider round"></span>
                    </label>
                   <input type="hidden" class="form-control" id="isLifetime" name="isLifetime" value="1">
                  <!-- </div>  -->
                  
                  </div>
                  <div class="col-xs-12 col-sm-12 col-md-6">
                    <div id="lifeTimeDiv">
                    <label class="control-label" style="font-size: 16px;background-color: #fff;padding: 5px 25px 5px 25px;border-radius: 5px;margin-left: 91px;color: green;">Lifetime Period</label>
                     </div>
                     <div id="monthYearDiv" style="position: absolute;margin: 3px 30px;color: #000000bd;display: none;">
                        <!-- <select class="selectpicker" name="filterFrom" id="filterFrom"  style="padding-top:10px;" required aria-required="true" >
                          <option value="false">Select From</option>
                          <?php //echo $monthYearDropDown;?>
                        </select> -->


                        <!-- <select class="selectpicker" name="filterTo" id="filterTo"  style="padding-top:10px;" required aria-required="true" >
                          <option value="false">Select To</option>
                          <?php //echo $monthYearDropDown;?>
                        </select> -->
                        <div class="col-xs-12 col-sm-12 col-md-1" style=" padding-top: 5px;">
                           <label>From:</label>
                         </div>
                        <div class="col-xs-12 col-sm-12 col-md-5" style="padding-left:30px;">
                        <input type="month" class="form-control" id="filterFrom" name="filterFrom" value="<?php echo date('Y-m');?>" min="2017-01" max="<?php echo date('Y-m');?>" placeholder="From">
                      </div>
                      <div class="col-xs-12 col-sm-12 col-md-1" style="text-align: right; padding-top: 5px;">
                           <label>To:</label>
                         </div>
                      <div class="col-xs-12 col-sm-12 col-md-5">
                        <input type="month" class="form-control" id="filterTo" name="filterTo" value="<?php echo date('Y-m');?>" min="2017-01" max="<?php echo date('Y-m');?>" placeholder="From">
                      </div>
                    </div>

                  </div>
                  <div class="col-xs-12 col-sm-12 col-md-3">
                   <span class="input-group-btn" data-toggle="tooltip" title="" data-original-title="Filter"><button class="btn btn-success" id="submit2" title="Filter Data" style="border-radius: 5px;"><i class="fa fa-arrow-right"></i></button></span>
                    <!-- <span class="input-group-btn" data-toggle="tooltip" title="" data-original-title="Export Data" style="position: absolute;margin-top: -34px;margin-left: 60px;"><button class="btn btn-success" id="exportAllData" title="Export Data" style="margin-left: 5px;border-radius: 5px;"><i class="fa fa-file-excel-o"></i></button></span> -->
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

  $('#lifeTimeToggle').on('change',function(){
   var value = $('#isLifetime').val();

   if(value==0){
    $('#isLifetime').val(1);
    $('#monthYearDiv').hide();
    $('#lifeTimeDiv').show();
   }else{
    $('#isLifetime').val(0);
    $('#monthYearDiv').show();
    $('#lifeTimeDiv').hide();
   }
//  console.log(value);
});
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
    google.load('visualization', {packages:['corechart','line'], 'callback' : function()
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
                 url: '<?= URL::route('superadmin.getconsumptioncomparison') ?>',
                 success: function(jsondataArr) {
                    if(typeof jsondataArr.success !== 'undefined' && jsondataArr.success==false){
                      // console.log(jsondataArr);
                      toastr["error"](jsondataArr.msg);
                      $("#filterLoader").hide();
                    }else{
                      var jsondata=jsondataArr.data;
                      //var projectCount=jsondataArr.projectCount;
                      //var totalActive=jsondataArr.totalActive;
                      //var totalInActive=jsondataArr.totalInActive;
                      var jsondata = jsondata.map( Object.values );

                      var dataCount=jsondata.length;
                      if(dataCount==0){
                        $(".alert-danger .message").html('No data to display.');
                        $(".alert-danger").fadeIn();
                        return false;
                      }      
                      //console.log(dataCount);               
                      //var dataCountH=parseInt(dataCount)-1;
                      // var dataCountH=parseInt(dataCount);
                      // if(dataCountH>10){
                      //    var height=parseInt(dataCountH)*50;
                      // }else if(dataCountH>5){
                      //    var height=parseInt(dataCountH)*50;
                      // }else if(dataCountH>2){
                      //    var height=parseInt(dataCountH)*50;
                      // }else{
                      //    var height=parseInt(dataCountH)*50;
                      // }
                     // console.log(height);
                      var height=800;
                      $('#columnchart_values').css('min-height', height+'px');
                      $('#columnchart_values').css('max-height', height+'px');
                      $('#columnchart_values').css('height',height+'px');
                      //$('#chartContainer').css('max-height', parseInt(height)*parseInt(2)+'px');
                     // $('#chartContainer').css('overflow-y','scroll');
                      // $('#chartContainer').css('max-height','400px');
                    var data = new google.visualization.DataTable();

                    var jsonInstances=jsondataArr.instances;
                    // Define the chart to be drawn.
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Month');
                    for(i=0;i<jsonInstances.length;i++){
                    
                    data.addColumn('number', jsonInstances[i]);
                    data.addColumn({type: 'string', role: 'annotation'});
                    }


            //data.addColumn('number', 'Tokyo');
            //data.addColumn('number', 'New York');
            // data.addColumn('number', 'Berlin');
            // data.addColumn('number', 'London');
            // data.addRows([
            //    ['Jan',  7.0, -0.2, -0.9, 3.9],
            //    ['Feb',  6.9, 0.8, 0.6, 4.2],
            //    ['Mar',  9.5,  5.7, 3.5, 5.7],
            //    ['Apr',  14.5, 11.3, 8.4, 8.5],
            //    ['May',  18.2, 17.0, 13.5, 11.9],
            //    ['Jun',  21.5, 22.0, 17.0, 15.2],
               
            //    ['Jul',  25.2, 24.8, 18.6, 17.0],
            //    ['Aug',  26.5, 24.1, 17.9, 16.6],
            //    ['Sep',  23.3, 20.1, 14.3, 14.2],
            //    ['Oct',  18.3, 14.1, 9.0, 10.3],
            //    ['Nov',  13.9,  8.6, 3.9, 6.6],
            //    ['Dec',  9.6,  2.5,  1.0, 4.8]
            // ]);
                    // data.addColumn('string', 'Institute');
                    // data.addColumn('number', 'Active Documents ('+totalActive+')');
                    // data.addColumn({type: 'string', role: 'annotation'});
                    // data.addColumn('number', 'Inactive Documents ('+totalInActive+')');
                    // data.addColumn({type: 'string', role: 'annotation'});
                   
                    // A column for custom tooltip content
                    //data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
                    //data.addColumn('number', 'Cost ('+jsondataArr.totalCost+'k)');
                   // data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
                    data.addRows(jsondata);
                    var options = {
                                    title: 'Month Wise Generation Chart For Multiple Instances',
                                    chartArea: {width: '60%',top:50},
                                     annotations: {
                                      textStyle: {
                                        fontSize: 13,
                                      }
                                    },
                                    hAxis: {
                                      title: 'Month-Year',
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
                                      minValue: 0,
                                      title: 'Documents Count',
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


                  // function selectHandler() {
                  //     var selectedItem = chart.getSelection()[0];
                  //     if (selectedItem) {

                  //       if(selectedItem.row!=null){
                  //         console.log(selectedItem);
                  //       var instituteName = data.getValue(selectedItem.row, 0);
                  //       instituteDetails(instituteName,selectedItem.column);
                  //       }
                  //     }
                  //   }


                  var chart = new google.visualization.LineChart(document.getElementById('columnchart_values'));
                  chart.draw(data, options);
                  // Every time the table fires the "select" event, it should call your
                  // selectHandler() function.
                //  google.visualization.events.addListener(chart, 'select', selectHandler);
                  $("#filterLoader").hide();
                   }
                 }
            });    
      

      }
    });
    return true;
  }
});
}

 //filter();


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
