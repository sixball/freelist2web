<?php
require('functions.php');
require('config.php');

unset($terms); // search terms
$query = mysql_real_escape_string($_GET['q']); // sanitised query

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
<div class='side'>
<div class="sidebox">
<h1>Search the lists</h1>
<form method="get" action="index.php">
    <input type="text" name="q" value="<?= stripslashes($query);?>">
      
      <br /><small>e.g. piano b17</small>
      <input type="submit" value="search">
</div>
<div class="sidebox">
<?php 
	if(file_exists("blurb.html")) {
		include('blurb.html');
	}
	else echo "<h1>no blurb!</h1>Create a blurb.html and it will appear here."; 
?>
</div>
<div class="sidebox"><h1>Some numbers</h1><?php include('statsbox.php'); ?></div>
<div class="sidebox">Powered by: <a href="http://freelist2web.googlecode.com">freelist2web</a></div>
</div><!-- end column -->
<div class="itemlist">
    <?php

    if(!empty($query)) { // result of search
	  $terms = explode(' ', $query);
/*
	Search works on the principle of: 
	  matching all the terms in any of the areas
	  where area is a postcode or member of $areas array
	  so: headline like piano and headline like upright and (headline like harborne or area=b17) 
	  later: headline like piano and headline like upright and (area like harborne or area like b17)
*/
	$filter = "";
	$text = ""; // explains search results
	$firstterm = true;
	foreach($terms as $t) {
	    if(in_array(strtolower($t), $areas) or preg_match("/".$postcode_pattern."/i", $t)) {
	      $searchareas[] = $t;
	    }
	    else {
		if(!$firstterm) {
		  $filter .= " AND ";
		  $text .= " and ";
		}
		$filter .= "headline LIKE '%$t%'";
		$text .= "<b>".stripslashes(stripslashes($t))."</b>";
		$firstterm = false;
	    }
	} 

	if($text == "") $text = "Items";
	else $text = "Items with $text in the title";

	// restrict to include any searchareas
	if(count($searchareas) > 0) { // some areas specified
	  $text .= " from ";
	  if(!$firstterm) {
	    $filter .= " AND "; // appending to ordinary search terms
	  }	
	  $filter .= '(';
	  $firstterm = true; // within this clause
	  foreach($searchareas as $searcharea) {
		if(!$firstterm) {
		  $filter .= " OR "; // matching ANY area
		    $text .= " or "; 
		}
		if(in_array(strtolower($searcharea), $areas)) $filter .= "headline LIKE '%$searcharea%'"; 
		else $filter .= "area LIKE '%$searcharea%'"; // LIKE should be case-insensitive
		$text .= "<b>".$searcharea."</b>"; 
		$firstterm = false;
	      }
	  $filter .= ')';
	}
  
	echo $text;

	$types = array('offered', 'wanted');
	foreach($types as $type) {
	  echo "<h2>$type:</h2>";
	  $posts = getPosts($filter." and type='$type'", 10);
	  if($posts) {
	    foreach($posts as $item) printItem($item); // offered
	  }
	  else echo "<p><i>no items found!</i></p>";
	}
    }
    else { // no search
      echo "What's being offered on $list_name right now?";
      $filter = "type='offered'";
      echo "<div id='showtaken'><input id='showTaken' type='checkbox' onClick='if(this.checked) $(\".taken\").show(\"normal\"); else $(\".taken\").hide(\"normal\")'> <small>show taken items</small></div>";
      $posts = getPosts($filter);
      if($posts) {
	foreach($posts as $item) printItem($item); // offered
      }
    }
    echo "<div class='column'>";
?>

</div><!-- end column container -->
</div><!-- end main -->
</body>
</html>
