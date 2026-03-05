@extends('superadmin.layout.layout')

@section('content')

<html>
  <head>

  </head>
  <body>
    <div id="columnchart_values" style=""></div>
  </body>
</html>


@stop

@section('style')

<style>
      body{
        overflow: hidden !important;
      }

      #columnchart_values{
        overflow-x: scroll !important;
      }
</style>
@stop
@section('script')

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {

    var data = google.visualization.arrayToDataTable([
         ['Institute', 'Active Documents(%)'],
          @php
              foreach($result as $key => $val) {
                  echo "['".$key."',".$val."],";
              }
          @endphp
      ]);
    var options = {
        tooltip: { isHtml: true, fontSize: 7  },
        title: "Institute Chart",
        width: 1700,
        height: 400,
        bar: {groupWidth: "60%"},
        legend: { position: "top" },
        vAxis: {
          minValue: 0,
        },
        scales: {
            xAxes: [{
                barThickness: 20,
            }]
        }
      };

    var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));

    chart.draw(data,options);
  }
</script>

@stop
