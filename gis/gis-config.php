<?php

$MONGO_CONFIG = array (
    "host"      => "localhost",
    "database"  => "horca",
);

$RISK_TABLE = array (
    'critical' => array (
        'value'         => 7,
        'label'         => 'important',
    ),
    'high' => array (
        'value'         => 5,
        'label'         => 'important',
    ),
    'medium' => array (
        'value'         => 2,
        'label'         => 'warning',
    ),
    'low' => array (
        'value'         => 1,
        'label'         => 'success',
    ),
);

/* Main client collection with full about the client */
$CLIENT_COLLECTION = "client";

/* show the following collections in the summary page */
$SUMMARY_COLLECTIONS = array (
    "security" => array (
        "title"     => "Security",
        "subtitle"  => "Most important security indicators",
    ),
    "system" => array (
        "title"     => "System",
        "subtitle"  => "Running processes and system utilization",
    ),
    "inventory" => array (
        "title"     => "Inventory",
        "subtitle"  => "Host properties, installed packages and versions with updates",
    ),
    "database" => array (
        "title"     => "Database",
        "subtitle"  => "Database health checks",
    ),
    "vulnsummary" => array (
        "title"     => "Vulnerability",
        "subtitle"  => "Active Vulnerabilities",
    ),
    "geolocation" => array (
        "title"     => "Geolocation",
        "subtitle"  => "Client Geographic location",
    ),
);

/* extra collections for other pages */
$COLLECTIONS = array_merge 
(
    /* Summary collections, showed in main page */
    (array)$SUMMARY_COLLECTIONS,
    
    /* Client information */
    (array)$CLIENT_COLLECTION,
    
    /* Alarm page */
    array (
        "alarm" => array (
            "title"     => "Open Alarms",
            "subtitle"  => "All open Alarms grouped by alarm name",
        )
    ),
    
    /* Vulnerability page */
    array (
        "vulnerability" => array (
            "title"     => "Active Vulnerabilities",
            "subtitle"  => "All active Vulnerabilities grouped by host",
        )
    )
);


$COLLECTION_MAP = array
(
    "client" => array (
        "client_name" => array (
            "index"     => "client_name",
            "title"     => "Client Name",
            "descr"     => "",
        ),
        "client_url" => array (
            "index"     => "client_url",
            "title"     => "Client Url",
            "descr"     => "",
        ),
    ),
    "system" => array (
        "hostname" => array (
            "index"     => "hostname",
            "title"     => "Hostname",
            "descr"     => "System's host name",
        ),
        "distro" => array (
            "index"     => "distro",
            "title"     => "Linux Distro",
            "descr"      => ""
        ),
        "cpu_percent" => array (
            "index"     => "cpu_percent",
            "title"     => "CPU percent",
            "descr"     => "Current system-wide CPU utilization",
            "suffix"    => "%",
            "threshold_warning"  => 80,
            "threshold_critical" => 90,
        ),
        "loadaverage" => array (
            "index"     => "loadaverage",
            "title"     => "Load average",
            "descr"      => "",
            "threshold_warning"  => 0.50,
            "threshold_critical" => 1.00,
        ),
        "memory_percent" => array (
            "index"     => "memory_percent",
            "title"     => "Memory percent",
            "descr"     => "",
            "suffix"    => "%",
            "threshold_warning"  => 70,
            "threshold_critical" => 90,
        ),
        "used_memory" => array (
            "index"     => "used_memory",
            "title"     => "Used memory",
            "descr"     => "",
            "suffix"    => "MB",
            "threshold_warning"  => 4096,
            "threshold_critical" => 6144,
        ),
        "processes" => array (
            "index"     => "processes",
            "title"     => "Processes",
            "descr"     => "Number of process running on the host",
            "threshold_warning"  => 100,
            "threshold_critical" => 150,
            "url"                => "{client_url}/ossim/sysinfo/index.php",
        ),
        "iface_recv" => array (
            "index"     => "iface_recv",
            "title"     => "Network Recv",
            "descr"     => "",
            "suffix"    => "KB/s",
            "threshold_warning"  => 50,
            "threshold_critical" => 100,
        ),
        "iface_sent" => array (
            "index"     => "iface_sent",
            "title"     => "Network Sent",
            "descr"     => "",
            "suffix"    => "KB/s",
            "threshold_warning"  => 50,
            "threshold_critical" => 100,
        ),
    ),
    
    "database" => array (
        "num_siem_events" => array (
            "index"     => "num_siem_events",
            "title"     => "#SiemEvents",
            "descr"     => "",
            "threshold_warning"  => "3000000",
            "threshold_critical" => "5000000",
            "url"                => "{client_url}/ossim/forensics/base_qry_main.php?clear_allcriteria=1&num_result_rows=-1&submit=Query+DB&current_view=-1&sort_order=time_d",
        ),
        "num_backlog_events" => array (
            "index"     => "num_backlog_events",
            "title"     => "#BacklogEvents",
            "descr"     => "",
            "threshold_warning"  => "50000",
            "threshold_critical" => "100000",
            "url"                => "{client_url}/ossim/control_panel/backlog.php",
        ),
        "ossim_size" => array (
            "index"     => "ossim_size",
            "title"     => "Ossim DB Size",
            "descr"     => "",
            "suffix"    => "MB",
            "threshold_warning"  => "100",
            "threshold_critical" => "200"
        ),
        "snort_size" => array (
            "index"     => "snort_size",
            "title"     => "Snort DB Size",
            "descr"     => "",
            "suffix"    => "MB",
            "threshold_warning"  => "1000",
            "threshold_critical" => "4000"
        ),
        "osvdb_size" => array (
            "index"     => "osvdb_size",
            "title"     => "Osvdb DB Size",
            "descr"     => "",
            "suffix"    => "MB",
            "threshold_warning"  => "150",
            "threshold_critical" => "200"
        )
    ),

    "alarm" => array (
        "count" => array (
            "index"     => "count",
            "title"     => "Count",
            "descr"     => "",
            "threshold_notice" => "2",
        ),
        "date" => array (
            "index"     => "date",
            "title"     => "Last Alarm Date",
            "descr"     => "",
        ),
        "alarm" => array (
            "index"     => "alarm",
            "title"     => "Alarm",
            "descr"     => "",
            "url"       => "{client_url}/ossim/control_panel/alarm_console.php?query={body_text}",
        ),
        "risk" => array (
            "index"     => "risk",
            "title"     => "Risk",
            "descr"     => "",
            "threshold_success" => $RISK_TABLE['low']['value'],
            "threshold_warning" => $RISK_TABLE['medium']['value'],
            "threshold_critical" => $RISK_TABLE['high']['value'],
        ),
        "source" => array (
            "index"     => "source",
            "title"     => "Src",
            "descr"     => "",
            "url"       => "{client_url}/ossim/control_panel/alarm_console.php?src_ip={body_text}",
        ),
        "destination" => array (
            "index"     => "destination",
            "title"     => "Dst",
            "descr"     => "",
            "url"       => "{client_url}/ossim/control_panel/alarm_console.php?dst_ip={body_text}",
        )
    ),

    "security" => array (
        "num_alarms" => array (
            "index"     => "num_alarms",
            "title"     => "#Alarms",
            "descr"     => "",
            "threshold_success"   => 1,
            "threshold_warning"  => 10,
            "threshold_critical" => 25,
        ),
        "riskiest_alarm" => array (
            "index"     => "riskiest_alarm",
            "title"     => "Riskiest Alarm",
            "descr"     => "",
        ),
        "most_repeated_alarm" => array (
            "index"     => "most_repeated_alarm",
            "title"     => "Most repeated Alarm",
            "descr"     => "",
        ),
        "compromised_host" => array (
            "index"     => "compromised_host",
            "title"     => "Compromised?",
            "descr"     => "",
            "url"       => "{client_url}/ossim/report/host_report.php?asset_type=host&asset_key={body_text}",
        ),
        "attacked_host" => array (
            "index"     => "attacked_host",
            "title"     => "Attacked?",
            "descr"     => "",
            "url"       => "{client_url}/ossim/report/host_report.php?asset_type=host&asset_key={body_text}",
        ),
        "vulnerabilities" => array (
            "index"     => "vulnerabilities",
            "title"     => "#Vulnerabilities",
            "descr"     => "",
            "threshold_success"   => 0,
            "threshold_warning"  => 10,
            "threshold_critical" => 25,
        ),
        "num_incidents" => array (
            "index"     => "num_incidents",
            "title"     => "#Incidents",
            "descr"     => "",
            "threshold_success"   => 0,
            "threshold_warning"  => 10,
            "threshold_critical" => 25,
        ),
        "num_connected_sensors" => array (
            "index"     => "num_connected_sensors",
            "title"     => "#Connected Sensors",
            "descr"     => "",
            "url"       => "{client_url}/ossim/sensor/sensor_plugins.php",
        )
    ),

    "vulnsummary" => array (
        "num_vuln_total" => array (
            "index"     => "num_vuln_total",
            "title"     => "#Vuln total",
            "descr"     => "",
            "threshold_notice"  => 0,
        ),
        "num_vuln_serious" => array (
            "index"     => "num_vuln_serious",
            "title"     => "#Vuln Critical",
            "descr"     => "",
            "threshold_warning"  => 1,
            "threshold_critical" => 1,
        ),
        "num_vuln_high" => array (
            "index"     => "num_vuln_high",
            "title"     => "#Vuln High",
            "descr"     => "",
            "threshold_warning"  => 5,
            "threshold_critical" => 10,
        ),
        "num_vuln_medium" => array (
            "index"     => "num_vuln_medium",
            "title"     => "#Vuln Medium",
            "descr"     => "",
            "threshold_warning"  => 20,
            "threshold_critical" => 30,
        ),
        "num_vuln_low" => array (
            "index"     => "num_vuln_low",
            "title"     => "#Vuln Low",
            "descr"     => "",
            "threshold_warning"  => 50,
            "threshold_critical" => 100,
        ),
    ),
    
    "vulnerability" => array (
        "num_vuln_total" => array (
            "index"             => "num_vuln_total",
            "title"             => "#Vuln Total",
            "descr"             => "",
            "threshold_notice"  => 1,
        ),
        "host_ip" => array (
            "index"     => "host_ip",
            "title"     => "Host IP",
            "descr"     => "",
            "url"       => "{client_url}/ossim/vulnmeter/index.php?value={body_text}&type=hn&",
        ),
        "hostname" => array (
            "index"     => "hostname",
            "title"     => "Host Name",
            "descr"     => "",
        ),
        "num_vuln_serious" => array (
            "index"             => "num_vuln_serious",
            "title"             => "#Vuln Critical",
            "descr"             => "",
            "threshold_critical"  => 1,
        ),
        "num_vuln_high" => array (
            "index"             => "num_vuln_high",
            "title"             => "#Vuln High",
            "descr"             => "",
            "threshold_critical"  => 1,
        ),
        "num_vuln_medium" => array (
            "index"             => "num_vuln_medium",
            "title"             => "#Vuln Medium",
            "descr"             => "",
            "threshold_warning"  => 1,
        ),
        "num_vuln_low" => array (
            "index"             => "num_vuln_low",
            "title"             => "#Vuln Low",
            "descr"             => "",
            "threshold_success"  => 1,
        ),
    ),
    
    "inventory" => array(
        "kernel" => array (
            "index"     => "kernel",
            "title"     => "kernel",
            "descr"     => "running operating system",
        ),
        "ip" => array (
            "index"     => "ip",
            "title"     => "IP Address",
            "descr"     => "System's IP address",
        ),
        "num_cores" => array (
            "index"     => "num_cores",
            "title"     => "#Cores",
            "descr"     => "",
        ),
        "model_name" => array (
            "index"     => "model_name",
            "title"     => "CPU model name",
            "descr"     => "",
        ),
        "pkg_inst" => array (
            "index"     => "pkg_inst",
            "title"     => "Installed Packages",
            "descr"     => "Installed packages on the System",
            "threshold_warning" => 700,
            "threshold_critical" => 1000,
        ),
        "ossim_server_version" => array (
            "index"     => "ossim_server_version",
            "title"     => "Ossim Server Version",
            "descr"     => "Ossim Server Version",
        ),
    ),

    "geolocation" => array (
        "city" => array (
            "index"     => "city",
            "title"     => "City",
            "descr"     => "",
            "url"       => "http://maps.google.com?q={body_text}",
        ),
        "region_name" => array (
            "index"     => "region_name",
            "title"     => "Region",
            "descr"     => "",
            "url"       => "http://maps.google.com?q={body_text}",
        ),
        "country_name" => array (
            "index"     => "country_name",
            "title"     => "Country",
            "descr"     => "",
        ),
        "time_zone" => array (
            "index"     => "time_zone",
            "title"     => "Time Zone",
            "descr"     => "",
        ),
        "geolocation" => array (
            "index"     => "geolocation",
            "title"     => "Latitude/Longitude",
            "descr"     => "",
            "url"       => "http://maps.google.com?q={body_text}&z=12",
        ),
    )

);


?>
