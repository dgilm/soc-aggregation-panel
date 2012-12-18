<?php

require_once ('gis-config.php');

class Collection
{
    /* Default collection name to query to */
    public $collection_name;

    /* Default $sort array for getList method */
    public $default_sort = array('client_id' => 1);

    public function __construct($col_name = null)
    {
        global $MONGO_CONFIG;
        $mongo = new Mongo("mongodb://".$MONGO_CONFIG['host']);
        $db = $mongo->selectDB($MONGO_CONFIG['database']);
        $col_name = ($col_name)? $col_name : $this->collection_name;
        $this->collection = $db->selectCollection($col_name);
    }

    public function filter($GET)
    {
        $filter = array();

        /* Filter by client_id */
        if (isset($GET['client_id'])) {
            $id = (string)$_GET['client_id'];
            $filter = array('client_id' => $id);
        }
        return $filter;
    }

    public function label($GET)
    {
        global $RISK_TABLE;

        $label = "";

        /* Mark Client name with a label */
        if (isset($GET['client_id'])) {
            $id = (string)$_GET['client_id'];
            $label .= " <span class='label notice'>$id</span>";
        }

        /* Mark Risk with a label */
        if (isset($_GET['risk'])) {
            $risk = (string)$_GET['risk'];
            $label .= " <span 
                class='label ".$RISK_TABLE[$risk]['label']."'>$risk</span>";
        }

        return $label;
    }

    public function getList($filter = null, $limit = null, $sort = null)
    {
        /* filter options */
        $cursor = ($filter)? 
            $this->collection->find($filter) :
            $this->collection->find();

        /* sort options */
        $sort = ($sort)? $sort : $this->default_sort;
        $cursor->sort($sort);

        /* limit options */
        if (is_int($limit))
            $cursor = $cursor->limit($limit);

        return $cursor;
    }
    
    public function displayInfo()
    {
        print "database: " . $this->db;
        print "collection: " . $this->collection;
    }    
}

class Client extends Collection
{
    public $collection_name = "client";
}

class Security extends Collection
{
    public $collection_name = "security";
}

class Alarm extends Collection
{
    public $collection_name = "alarm";
    public $default_sort = array('data.count' => -1);

    public function filter($GET)
    {
        global $RISK_TABLE;

        /* Filter by client */
        $filter = parent::filter($GET);

        /* Filter by risk */
        if (isset($GET['risk'])) {
            $risk = (string)$_GET['risk'];
            $filter = array_merge (
                array('data.risk' => 
                    array('$gte' => (int)$RISK_TABLE[$risk]['value'])),
                $filter
            );
        }

        return $filter;
    }
}

class Vulnerability extends Collection
{
    /* Vulnerability Database */
    public $collection_name = "vulnerability";

    /* Default $sort array for getList method */
    public $default_sort = array('data.num_vuln_total' => -1);

    public function filter($GET)
    {
        global $RISK_TABLE;

        /* Filter by client */
        $filter = parent::filter($GET);

        /* Filter Vulnerabilities by risk */
        if (isset($GET['risk']))
        {
            $risk = (string)$GET['risk'];

            $filter_tmp = array();

            if ($risk == 'critical')
                $filter_tmp = array(
                    "data.num_vuln_serious" => array('$gt' => 0)
                );

            elseif ($risk == 'high')
                $filter_tmp = array (
                    '$or' => array (
                        array("data.num_vuln_serious" => array('$gt' => 0)),
                        array("data.num_vuln_high"    => array('$gt' => 0))
                        )
                    );

            elseif ($risk == 'medium')
                $filter_tmp = array (
                    '$or' => array (
                        array("data.num_vuln_serious" => array('$gt' => 0)),
                        array("data.num_vuln_high"    => array('$gt' => 0)),
                        array("data.num_vuln_medium"  => array('$gt' => 0))
                        )
                    );

            $filter = array_merge ($filter, $filter_tmp);
        }
        return $filter;
    }

}

class VulnerabilitySummary extends Vulnerability
{
    public $collection_name = "vulnsummary";
}


/*
$client = new Client();
$filter = array('client_id' => 'client-identifier');
foreach ($client->getList($filter) as $row) {
    print '<pre>';
    print_r($row);
    print '</pre>';
}
*/

?>

