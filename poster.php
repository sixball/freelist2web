<?php
require('functions.php');
require('config.php');
// should sanitize
$id = $_GET['id'];
$email = $db->getOne("select email from posters where id=$id");
?>
<html>
<head>

<title><?= $site_title ?></title>
<script type="text/javascript" src="jquery.js"></script>
<link rel="stylesheet" type="text/css" href="freelist2web.css">
</head>
<body>
<div id="main">
<div id="cc">
<div id="top"><?= $site_title ?></div>

<div class='itemlist'>
<h1>All posts from <?php echo protectEmail($email); ?></h1>
<?php

  $types = array('offered', 'wanted');
  foreach($types as $type) {
  echo "<h2>$type:</h2>";
  $posts = getPosts("poster_id=$id and type='$type'");
  if($posts) {
    foreach($posts as $item) printItem($item); // offered
  }
  else echo "<p><i>none</i></p>";
}
?>
</div><!-- itemlist -->
</div><!-- cc -->
</div><!-- main -->
</body>
