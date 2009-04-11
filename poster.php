<?php
require('functions.php');
require('config.php');
// should sanitize
$id = $_GET['id'];
$email = $db->getOne("select email from posters where id=$id");
?>
<html>
<head>
<title>Live Brum Freecycling</title>
<script type="text/javascript" src="jquery.js"></script>
<link rel="stylesheet" type="text/css" href="freecycle.css">
</head>
<body>
<div class='column'>
<h1>All posts from <?php echo protectEmail($email); ?></h1>
<?php
  $posts = $db->getAll("select *, unix_timestamp(datetime) as time from posts where poster_id=$id");
  foreach($posts as $post) {
    //var_dump($post);
    newPrintItem($post, false, true);
  }
?>
</div>
</body>
