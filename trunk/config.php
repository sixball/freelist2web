<?php
// set config
$db_user = 'root';
$db_password = '';
$database = '';

$wwwroot = ""; 
$sitetitle = "site title goes here";

// get db going
require_once('DB.php');
$db = DB::connect("mysql://$db_user:$db_password@localhost/$database");


if(DB::iserror($db)) {
  die($db->getMessage());
}
$db->setFetchMode(DB_FETCHMODE_ASSOC);

mysql_query("set SESSION time_zone = '+00:00'");
?>
