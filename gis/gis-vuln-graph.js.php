<?php
Header("content-type: application/x-javascript");

require_once ('gis-config.php');
require_once ('gis-database.php');


/* get count of total vulnerabilities depending on the risk filter */
function get_num_vulns($row)
{
    if (!isset($_GET['risk']))
        return $row['data']['num_vuln_total'];
    elseif ($_GET['risk'] == 'critical')
        return  $row['data']['num_vuln_serious'];
    elseif ($_GET['risk'] == 'high')
        return $row['data']['num_vuln_serious'] +
               $row['data']['num_vuln_high'];
    elseif ($_GET['risk'] == 'medium')
        return $row['data']['num_vuln_serious'] +
               $row['data']['num_vuln_high'] +
               $row['data']['num_vuln_medium'];
    else
        return $row['data']['num_vuln_total'];
}

if (isset($_GET['client_id'])) {

    $collection = new Vulnerability();
    $filter = $collection->filter($_GET);
//    $sort = array('data.num_vuln_total' => -1);
    $obj = $collection->getList($filter, 5);

    $count = array();
    foreach ($obj as $row) {
        $c = $row['data']['host_ip'];
        if (!isset($count[$c]))
            $count[$c] = get_num_vulns($row);
        else
            $count[$c] += get_num_vulns($row);
    }
    $categories = $series = "[";
    foreach ($count as $key=>$value) {
        $categories .= "'$key',";
        $series .= "$value,";
    }
    $categories = rtrim($categories, ",") . "]\n";
    $series = rtrim($series, ",") . "]\n";

} else {

    $collection = new VulnerabilitySummary();
    $filter = $collection->filter($_GET);
    $obj = $collection->getList($filter);

    $categories = $series = "[";
    foreach ($obj as $row) {
        $categories .= "'".$row['client_id']."',";
        $series .= get_num_vulns($row) . ",";
    }
    $categories = rtrim($categories, ",") . "]\n";
    $series = rtrim($series, ",") . "]\n";
}

?>

var chart2; // globally available
$(document).ready(function() {

//      Highcharts.setOptions({
//              colors: ['#058DC7', '#50B432', '#ED561B',
//                       '#DDDF00', '#24CBE5', '#64E572', 
//                       '#FF9655', '#FFF263', '#6AF9C4']
//      });

      chart2 = new Highcharts.Chart({
         chart: {
            renderTo: 'container-vuln',
            type: 'bar'
         },
         title: {
            text: 'Vulnerabilities'
         },
         xAxis: {
            categories: <?=$categories?>
         },
         yAxis: {
            title: {
               text: 'Active Vulnerabilities'
            }
         },
         series: [
            {
                name: 'Vulnerabilities',
                data: <?=$series?>
             }, 
         ],
         plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            },
         },
         credits: {
            enabled: false
         },
      });
   });
   
