#!/usr/bin/php -q 
<?php
// set config
$db_user = '';
$db_password = '';
$database = '';

$db = mysql_connect('localhost', $db_user, $db_password);
mysql_select_db($database, $db) or die('Could not select database.');

mysql_query("set SESSION time_zone = '+00:00'");

// read from stdin
$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
    $email .= fread($fd, 1024);
}
fclose($fd);

// handle email
$lines = explode("\n", $email);

// empty vars
$from = "";
$subject = "";
$headers = "";
$message = "";
$splittingheaders = true;

// load into arrays and variables
for ($i=0; $i < count($lines); $i++) {
    if ($splittingheaders) {
        // this is a header
        $headers .= $lines[$i]."\n";

        // look out for special headers
        if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
            $subject = $matches[1];
        }
        if (preg_match("/^From: (.*)/", $lines[$i], $matches)) {
            $from = $matches[1];
        }
    } else {
        // not a header, but message
        $message .= $lines[$i]."\n";
    }

    if (trim($lines[$i])=="") {
        // empty line, header section has ended
        $splittingheaders = false;
    }
}

// make safe
$from = mysql_real_escape_string($from);
$subject = mysql_real_escape_string($subject);
$message = mysql_real_escape_string($message);
$headers = mysql_real_escape_string($headers);

// then insert into db
$message = trim($message);
$query = "insert into raw_posts(`from`,`subject`, `message`, `datetime`, `headers`) values('$from', '$subject', '$message', now(), '$headers')";
mysql_query($query, $db);

$id = mysql_insert_id($db);
mysql_close($db);

// get proper db going
require_once('DB.php');
$options = array('debug'=>2);
$db =& DB::connect("mysql://$db_user:$db_password@localhost/$database", $options);
if(DB::iserror($db)) {
  error_log($db->getMessage()." with mysql://$db_user:$db_password@localhost/$database \n", 3, 'errors.txt'); 
}
//*/

//$p = array('from' => $from, 'subject' => $subject, 'message' => $message, 'id'=>$id);
include('functions.php');
$db->setFetchMode(DB_FETCHMODE_ASSOC);
//extractFromPost($p);
processPost($id);
/*
if(DB::iserror($db)) {
  $file = fopen("cyclelog.txt", 'a');
  //fwrite($file, print_r($headers, true).'\n');
  fwrite($file, 'error message: '.$db->getMessage().'\n');
  fclose($file);
//  die($db->getMessage());
}

//*/
/* first write the log
$file = fopen("cyclelog.txt", 'a');
fwrite($file, print_r($headers, true).'\n');
//fwrite($file, $query.'\n');
fclose($file);
*/
return NULL;
?>
