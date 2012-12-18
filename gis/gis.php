<?php

require_once('gis-html.php');
require_once('gis-config.php');
require_once('gis-functions.php');
require_once('gis-database.php');

print '<html>';
gis_headers();
print '<body>';
gis_topbar();

print '<div class="content">';

foreach ($SUMMARY_COLLECTIONS as $c=>$v) {

    $collection = new Collection($c);
    $obj = $collection->getList();

    print "<a name='$c' />";
    print "<h2>".ucwords($v['title'])." <small>".$v['subtitle']."</small></h2>";
    print_table($c, $obj);
}

print "</div>
  </body>
</html>";

?>


