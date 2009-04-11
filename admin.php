<?php
require('functions.php');
require('config.php');
$auth = 'changeme';
$start=0;
if(isset($_GET['start'])) $start = $_GET['start'];
$range=500;
if($_GET['auth'] != $auth) exit();

if($_GET['op'] == 'truncate') {
  $db->query("truncate table `posts`");
}

if($_GET['op'] == 'regenerate') {
  $posts = $db->getAll("select * from raw_posts limit $start, $range");
  foreach($posts as $p) {
    extractFromPost($p);
  }
  $start += $range; // for next incremental range
}

?>
<html>
<head>
<title><?= $sitetitle ?></title>
<script type="text/javascript" src="jquery.js"></script>
<link rel="stylesheet" type="text/css" href="freelist2web.css">
</head>
<body>
<?php
echo $display;


 // link forward
if($_GET['op'] == 'link') {
  $posts = $db->getAll("select * from posts");// limit 0, 100");
  foreach($posts as $p) {
    $follows = $db->getAll("select * from posts where poster_id=$p[poster_id] 
        and id != $p[id] and datetime > '$p[datetime]' and type='taken'");
    //echo "select * from posts where poster_id=$p[poster_id] 
        //and id != $p[id] and datetime > '$p[datetime]';<br/>";
    foreach($follows as $f) {
      if(hookup($p['headline'], $f['headline'])) {
        //echo "update posts set link=$f[id] where id=$p[id];<br/>";
        $db->query("update posts set link=$f[id] where id=$p[id]");
        $db->query("update posts set link=$p[id] where id=$f[id]");// link back
      }
    }
  }
}
?>
<a href="?auth=<?= $auth ?>&op=regenerate&start=<?= $start ?>">regenerate (<?= $start ?>)</a> - 
<a href="?auth=<?= $auth ?>&op=truncate">truncate</a> - 
<a href="?auth=<?= $auth ?>&op=link">link</a>
<?php

echo "<h2>modding</h2>";
printMods();

echo "<h2>last 10 posts</h2>";
$posts = $db->getAll("select *, unix_timestamp(datetime) as time from posts order by id desc limit 10");
foreach($posts as $post) {
  //echo $post['poster'].' ';
  newPrintItem($post, true);
}

echo "<h2>last 50 unmatched takens</h2>";
$posts = $db->getAll("select *, unix_timestamp(datetime) as time from posts where type='taken' and link=0 order by datetime desc limit 50");
foreach($posts as $post) {
  echo $post['poster'].' ';
  newPrintItem($post, true);
  $laters = $db->getAll("select *, unix_timestamp(datetime) as time from posts where poster_id='$post[poster_id]' and id!=$post[id] and datetime<'$post[datetime]' and (type='offered' or type='unknown') order by datetime desc");
  echo "<div style='margin-left: 100px'>";
  foreach($laters as $later) {
    newPrintItem($later, true);
  }
  echo "</div>";
}

/*
echo "<h1>raw posts</h1>";
$subjects = $db->getCol("select subject from raw_posts");
// raw post dump
echo "<table><tr><th>id</th><th>from</th><th>subject</th><th>message</th><th>datetime</th><th></th></tr>";
$posts = $db->getAll("select * from raw_posts order by datetime desc limit 100");

echo "</table>";

// processed posts
// raw post dump
echo "<h1>processed posts</h1><table><tr><th>id</th><th>raw_id</th><th>poster</th><th>headline</th><th>detail</th><th>datetime</th><th>type</th><th>area</th></tr>";
$posts = $db->getAll("select * from posts order by datetime desc limit 0, 100");
foreach($posts as $p) {
  echo "<tr><td>$p[id]</td><td>$p[raw_id]</td><td>".htmlentities($p['poster'])."</td><td>$p[headline]</td><td>$p[detail]</td><td>$p[datetime]</td><td>$p[type]</td><td>$p[area]</td></tr>";
}
  echo "</table>";
  */
?>
</body>