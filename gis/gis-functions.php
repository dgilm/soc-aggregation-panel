<?php

require_once ('gis-config.php');
require_once ('gis-database.php');

define ("BEGIN_HTML_TABLE", '<table class="zebra-striped condensed-table">');

/*
 *  Get Url associated with a client_id given 
 */
function __client_url($client_id)
{
    $client = new Client();
    $filter = array('client_id' => $client_id);
    foreach ($client->getList($filter) as $row) {
        return $row['data']['client_url'];
    }
}

/* 
 * Urls are defined in gis-config.php
 *
 * This function replaces {client_id} and {body_text} with
 * their appropiate values and generates a valid href link
 */
function print_url($table_index, $value, $client_id)
{
    $ret = $value;

    if (array_key_exists("url", $table_index))
    {
        $url = $table_index["url"];
    
        /* Replace {body_text} with the field value */
        $url = str_replace('{body_text}', urlencode($value), $url);
        
        /* Replace {client_url} with the real client url */
        $url = str_replace('{client_url}', __client_url($client_id), $url);

        $ret = "<a href='$url' target='_blank'>$value</a>";
    }
    
    return $ret;
}

/*
 * Each database value could be associated with a threshold value
 * Print {critical,warning,notice,success} labels according with these trhesholds
 * Add also a suffix to the text value if is defined
 */
function print_label($table_index, $value, $suffix)
{
    $label = "";

    /* Critical on RED */
    if (array_key_exists("threshold_critical", $table_index))
    {
        if ($value >= $table_index["threshold_critical"])
            $label = " <span class='label important'>$value$suffix</span>";
    }

    /* Warning on YELLOW */
    if (!$label && array_key_exists("threshold_warning", $table_index))
    {
        if ($value >= $table_index["threshold_warning"])
            $label = " <span class='label warning'>$value$suffix</span>";
    }

    /* Notice on BLUE */
    if (!$label && array_key_exists("threshold_notice", $table_index))
    {
        if ($value >= $table_index["threshold_notice"])
            $label = " <span class='label notice'>$value$suffix</span>";
    }

    /* Success on GREEN */
    if (!$label && array_key_exists("threshold_success", $table_index))
    {
        if ($value >= $table_index["threshold_success"])
            $label = " <span class='label success'>$value$suffix</span>";
    }

    $val = ($label)? $label: $value.$suffix;
    return $val;
}

/*
 * Print an html table for each database collection
 */
function print_table($table_name, $dbcursor)
{
    global $COLLECTION_MAP;

    /* Need to fill properly $COLLECTION_MAP in order to print the table */
    if (!array_key_exists($table_name, $COLLECTION_MAP))
        return;

    $table = $COLLECTION_MAP[$table_name];
    
    print BEGIN_HTML_TABLE;

    /* first, print table headers */
    print "<thead>
           <tr>
           <th>Client</th>";
    foreach ($table as $key => $value)
    {
        print "<th>".$value['title']."</th>";
    }
    print "</tr>
           </thead>";
    
    /* print table content */
    print "<tbody>";
    foreach ($dbcursor as $obj)
    {
        $client_id = $obj['client_id'];
        print "<tr>
                 <td>$client_id</td>";       
        foreach ($table as $t_key => $t_value)
        {
            foreach ($obj['data'] as $obj_k=>$obj_v)
            {
                if ($t_key == $obj_k)
                {
                    /* get defined suffix, like %, Mb, etc. */
                    $suffix = array_key_exists("suffix", $table[$t_key])?
                        $table[$t_key]["suffix"] : "";

                    /* get warning and critical labels
                       and concatenate with suffix */
                    $obj_v = print_label($table[$t_key], $obj_v, $suffix);

                    /* get url link if present */
                    $obj_v = print_url($table[$t_key], $obj_v, $client_id);
                    
                    print "<td>$obj_v</td>";

                    break;
                }
            }
        }
        print "</tr>";
    }
    print "</tbody>
           </table>";
}

/*
    Print data structure for each collection
    Debug purposes. Data structure looks like:

    $collection = 
    {
        "client_id": client_id,
        "data":
        [ 
            { "attr1": attr1, "attr2": attr2, ..., "attrN": attrN },
            { "attr1": attr1, "attr2": attr2, ..., "attrN": attrN }
        ]
    }
*/
function debug_collection($obj)
{
    print "<ul>";
    foreach ($obj['data'] as $row)
    {
        print "<li>";
        print "<b>[client: ".$obj['client_id']."] </b>";
        foreach ($row as $k=>$v)
        {
            print "[$k: $v] ";
        }
        print "</li>";
    }
    print "</ul>";
}

