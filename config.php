<?php
$host = "localhost";
$dbname = "CHANGEME";
$dbuser = "CHANGEME";
$dbpass = "CHANGEME";

$mysql = new mysqli($host, $dbuser, $dbpass, $dbname);
$mysql->set_charset('utf8mb4');
