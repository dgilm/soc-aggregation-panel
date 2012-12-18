<?php

require_once ('gis-database.php');

function gis_headers($GET=null) { ?>

  <head>
    <meta charset="utf-8">
    <title>GIS-Horca</title>
    
    <!-- Bootstrap css -->
    <link rel="stylesheet"
          href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css">
    <!--
    TODO: Bootstrap 2.0
    <link rel="stylesheet"
          href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css">
    -->

    <!-- favicon -->
    <link rel="shortcut icon" href="gis-favicon.png">
    <link rel="icon" href="gis-favicon.png">

    <!-- refresh every 30 seconds -->
    <meta http-equiv="refresh" content="30">

    <!-- Highcharts with JQuery -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <script src="js/highcharts.js" type="text/javascript"></script>

    <!-- Highchart custom graphs -->
    <?php if (strpos($_SERVER['PHP_SELF'], 'gis-alarms.php')) { ?>
    <script src="gis-alarm-graph.js.php?<?=http_build_query($GET)?>" 
            type="text/javascript"></script>
    <?php } elseif (strpos($_SERVER['PHP_SELF'], 'gis-vuln.php')) { ?>
    <script src="gis-vuln-graph.js.php?<?=http_build_query($GET)?>" 
            type="text/javascript"></script>
    <?php } ?>
    
    <!-- Custom CSS -->
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-left: 20px;
        padding-right: 10px;
      }
      table th, table td {
        text-align: center;
        text-vertical-align: center;
      }
    </style>
 
  </head>
<?php }

function gis_topbar($active="Summary") 
{
    $links = array (
        "Summary"           => "gis.php",
        "Alarms"            => "gis-alarms.php",
        "Vulnerabilities"   => "gis-vuln.php?risk=medium",
        /*
        "System"            => "gis#system",
        "Inventory"         => "gis#inventory",
        "Metrics"           => "#",
        "Availability"      => "#",
        */
    );

?>

    <!-- topbar -->
    <div class="topbar">
      <div class="topbar-inner">
        <div class="container-fluid">
          <a class="brand" href="#">GIS-Horca</a>
          <ul class="nav">
<?php
    foreach ($links as $name=>$url) {
        $a = ($name == $active)? " class='active'" : "";
        print "<li$a><a href='$url'>$name</a></li>";
    }
?>
          </ul>
          <p class="pull-right">Logged in as <a href="#">username</a></p>
        </div>
      </div>
    </div>
    <!-- end topbar -->
<?php }

function gis_sidebar($client_filter=null, $severity_filter=null) {

    $client = new Client();
    $row = $client->getList();

?>

      <!-- sidebar -->
      <div class="sidebar">
        <div class="well">

          <!-- Filter by Client ID -->
          <h5>Filter by client:</h5>
          <ul>
<?php
    foreach ($row as $c)
    {
        $id = $c['client_id'];
        $link_client = ($severity_filter)? 
            "?client_id=$id&risk=$severity_filter" : "?client_id=$id";

        print "<li><a href='$link_client'>$id</a></li>";
    }
?>
          </ul>
          <!-- End Filter by Client ID -->

          <!-- Filter by Severity -->
<?php
    $link_critical  = ($client_filter)?
        "?client_id=$client_filter&risk=critical"   : "?risk=critical";
    $link_high      = ($client_filter)?
        "?client_id=$client_filter&risk=high"       : "?risk=high";
    $link_medium    = ($client_filter)?
        "?client_id=$client_filter&risk=medium"     : "?risk=medium";
    $link_low       = ($client_filter)?
        "?client_id=$client_filter&risk=low"        : "?risk=low";
?>
          <h5>Filter by Severity:</h5>
          <ul>
            <li><a href="<?=$link_critical?>">Critical</a></li>
            <li><a href="<?=$link_high?>">>= High</a></li>
            <li><a href="<?=$link_medium?>">>= Medium</a></li>
            <li><a href="<?=$link_low?>">>= Low</a></li>
          </ul>
          <!-- End Filter by Severity -->

        </div>
      </div>
      <!-- end sidebar -->

<?php }

?>

