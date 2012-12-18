<?php
Header("content-type: application/x-javascript");

require_once ('gis-config.php');
require_once ('gis-database.php');

$collection = new Alarm();
$filter = $collection->filter($_GET);

if (isset($_GET['client_id'])) {

    $obj = $collection->getList($filter, 5);

    $categories = $series = "[";
    foreach ($obj as $row) {
        $c = $row['client_id'];
        $a = $row['data']['alarm'];
        $categories .= "'$a',";
        $series .= $row['data']['count'].",";
    }
    $categories = rtrim($categories, ",") . "]\n";
    $series = rtrim($series, ",") . "]\n";

} else {

    $obj = $collection->getList($filter);

    $count = array();
    foreach ($obj as $row) {
        $c = $row['client_id'];
        $a = $row['data']['alarm'];
        if (!isset($count[$c]))
            $count[$c] = $row['data']['count'];
        else
            $count[$c] += $row['data']['count'];
    }

    $categories = $series = "[";
    foreach ($count as $key=>$value) {
        $categories .= "'$key',";
        $series .= "$value,";
    }
    $categories = rtrim($categories, ",") . "]\n";
    $series = rtrim($series, ",") . "]\n";
}
?>

var chart1; // globally available
$(document).ready(function() {
      chart1 = new Highcharts.Chart({
         chart: {
            renderTo: 'container-alarm',
            type: 'bar'
         },
         title: {
            text: 'Alarms'
         },
         xAxis: {
            categories: <?=$categories?>
         },
         yAxis: {
            title: {
               text: 'Number of Alarms'
            }
         },
         series: [
            {
                name: 'Open Alarms',
                data: <?=$series?>
             }, 
         ],
         plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
         },
         credits: {
            enabled: false
         },
      });
   });
   
