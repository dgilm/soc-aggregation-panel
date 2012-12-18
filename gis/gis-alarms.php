<?php

require_once('gis-config.php');
require_once('gis-html.php');
require_once('gis-functions.php');

print '<html>';
gis_headers($_GET);
print '<body>';
gis_topbar("Alarms");
print '<div class="container-fluid">';
gis_sidebar(@$_GET['client_id'], @$_GET['risk']);
print '<div class="content">';

$title = $COLLECTIONS['alarm']['title'];
$subtitle = $COLLECTIONS['alarm']['subtitle'];

$collection = new Alarm();
$filter = $collection->filter($_GET);
$label = $collection->label($_GET);
$obj = $collection->getList($filter);

/* Header h2. */
$title = "<h2>$title$label <small>$subtitle</small></h2>";
print $title;

if ($obj->count() > 0)
{
    /* Alarm graph */
    print '<div id="container-alarm" style="width:100%; height:300px"></div>';

    /* Alarm table */
    print_table($collection->collection_name, $obj);

} else {
    print 'No results';
}

print "</div>
    </div>
  </body>
</html>";

?>


