<?php
require('functions.php');
require('config.php');

$post = $db->getRow("select * from raw_posts where id=$_GET[id]");
echo "<h1>$post[subject]</h1>";
echo "<b>".htmlentities(protectEmail($post['from']))."</b>";
echo " $post[datetime]";
echo "<pre>$post[message]</pre>";
?>
