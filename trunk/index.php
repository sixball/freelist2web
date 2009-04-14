<?php
require('functions.php');
require('config.php');

unset($terms); // search terms
$query = mysql_real_escape_string($_GET['q']); // sanitised query
/*
$sql = "select *, unix_timestamp(datetime) as time from raw_posts order by datetime desc limit 100";
if(!empty($query)) {
	$terms = explode(' ', $query);
	$where = "where ";
	$firstterm = true;
	foreach($terms as $t) {
		if(!$firstterm) $where .= "AND ";
		$where .= "subject LIKE '%$t%'";
		$firstterm = false;
	} 
      $sql = "select *, unix_timestamp(datetime) as time from raw_posts $where order by datetime desc limit 50";
}
*/
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
    <input type="text" name="q" value="<?= stripslashes($_GET['q']);?>">
      
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
<?php
  // display the lists

  // wanted list
  if('wanted' == $_GET['view']) {
    $posts = getPosts("type='wanted'");
    echo "<div class='column'><h2>Wanted:</h2>";
    echo "show me <a href='?view=offered'>offered</a> or <a href='?view=unsorted'>unsorted</a> items"; 
    foreach($posts as $p) newPrintItem($p, NULL, true);
    echo "</div>";
  }

  // unsorted list
  else if('unsorted' == $_GET['view']) {
    $posts = getPosts("type='unknown'");
    echo "<div class='column'>";
    echo "<h2>Unsorted:</h2>";//<i>Couldn't place these from the subject - sorry!</i>";
    echo "show me <a href='?view=offered'>offered</a> or <a href='?view=wanted'>wanted</a> items"; 
    foreach($posts as $p) newPrintItem($p, NULL);
    //else echo "<i>nothing to show</i>";
    echo "</div>";
  }

  else if($_GET['view'] == "test") {
    
    //showPostcodeCloud();
    
    ?>
    <form method="get" action="">
    <input type="hidden" name="view" value="test">
    Look for <input type="text" name="q" value="<?= stripslashes($_GET['q']);?>">
     in <select name="area" XonChange="chat.location = 'chat.php?filtername='+this.options[this.selectedIndex].value;">
<option value="all">All</option>
<?php 
    $areas = $db->getAll("select area, count(*) as freq from posts group by area order by area");
    foreach($areas as $a) 
      if($a['area']!='') {
        echo "<option value='$a[area]'";
        if($a['area'] == $_GET['area']) echo " selected='selected'";
        echo ">$a[area]</option>";
      }
?>
</select>
    <input type="submit" value="search">
    </form>
    <?php
    
      /*
	Search works on the principle of: 
	  matching all the terms in any of the areas
	  where area is a postcode or member of $areas array
	  so: headline like piano and headline like upright and (headline like sharborne or area=b17) 
	  later: headline like piano and headline like upright and (area like harborne or area like b17)
	test: piano northfield b31
*/
      $filter = "";
	$firstterm = true;
	foreach($terms as $t) {
	    if(in_array(strtolower($t, $areas)) or preg_match($postcodePattern, $t)) {
	      $searchareas[] = $t;
	    }
	    else if(!$firstterm) $filter .= "AND ";
		{
		  $filter .= "headline LIKE '%$t%'";
		  $firstterm = false;
		}
	} 

	// restrict to include any searchareas
	if(count($searchareas) > 0) { // some areas specified
	  if(!$firstterm) $filter .= "AND "; // appending to ordinary search terms
	  $filter .= '(';
	  $firstterm = true; // within this clause
	  foreach($searchareas as $searcharea) {
		{
		  if(!$firstterm) $filter .= "OR "; // matching ANY area
		  if(in_array(strtolower($t))) $filter .= "headline LIKE '%$t%'"; 
		  else $filter .= "area LIKE '%$t%'"; // LIKE should be case-insensitive
		  $firstterm = false;
		}
	  }
	  $filter .= ')';
	}
   // if($_GET['area'] != 'all') $filter = " AND area='$_GET[area]'";
  /*
    if(isset($_GET['q'])) {
      $q = stripslashes($_GET['q']);
      echo "<h1>Posts containing '$query'</h1>";
      $filter .= " AND headline LIKE '%$q%'";
    }
    */
    // offered column
     echo "<input id='hideTaken' type='checkbox' onClick='if(this.checked) $(\".taken\").hide(\"slow\"); else $(\".taken\").show(\"slow\")'> <small>hide (apparently) taken items</small>";
    $posts = getPosts("type='offered'".$filter);
    echo "<div class='column'><h2>Offered:</h2>";
    foreach($posts as $p) newPrintItem($p, NULL);
    echo "</div>";
    
    // taken column
    $posts = getPosts("type='taken'".$filter);
    echo "<div class='column'><h2>Taken:</h2>";
    foreach($posts as $p) newPrintItem($p, NULL);
    echo "</div>";
  }

  else if('offered' == $_GET['view']) {
    $posts = getPosts("type='offered'");
    echo "<div class='column'>";
    echo "<h2>Offered:</h2>";//<i>Couldn't place these from the subject - sorry!</i>";
    echo "show me <a href='?view=wanted'>wanted</a> or <a href='?view=unsorted'>unsorted</a> items"; 
    if($posts) foreach($posts as $p) newPrintItem($p, NULL);
    else echo "<i>nothing to show</i>";
    echo "</div>";
  }

  else {
    echo "<div class='itemlist'>";
    ?>
    
</form>
    <?php

    if(!empty($query)) { // result of search
	   $terms = explode(' ', $query);
/*
    	 echo "Items with ";
    	 $firstterm = true;
    	 foreach($terms as $t) {
    		  if(!$firstterm) {
		    echo ' and ';
		    $filter .= " and ";
		  }
    		echo "<b>".stripslashes(stripslashes($t))."</b>";
		$filter .= "headline LIKE '%$t%'";
    		$firstterm = false;
    	}
    	echo " in the title";
	$types = array('offered', 'wanted');
	foreach($types as $type) {
	  echo "<h2>$type:</h2>";
	  $posts = getPosts($filter." and type='$type'", 10);
	  if($posts) {
	    foreach($posts as $item) newPrintItem($item); // offered
	  }
	  else echo "<p><i>no items found!</i></p>";
	}
*/
 /*
	Search works on the principle of: 
	  matching all the terms in any of the areas
	  where area is a postcode or member of $areas array
	  so: headline like piano and headline like upright and (headline like sharborne or area=b17) 
	  later: headline like piano and headline like upright and (area like harborne or area like b17)
	test: piano northfield b31
	TODO: write explanatory text as above
*/
	$filter = "";
	$firstterm = true;
	foreach($terms as $t) {
	    if(in_array(strtolower($t), $areas) or preg_match("/".$postcode_pattern."/i", $t)) {
	      $searchareas[] = $t;
	    }
	    else {
		if(!$firstterm) $filter .= " AND ";
		$filter .= "headline LIKE '%$t%'";
		$firstterm = false;
	    }
	} 

	// restrict to include any searchareas
	if(count($searchareas) > 0) { // some areas specified
	  if(!$firstterm) $filter .= " AND "; // appending to ordinary search terms
	  $filter .= '(';
	  $firstterm = true; // within this clause
	  foreach($searchareas as $searcharea) {
		{
		  if(!$firstterm) $filter .= " OR "; // matching ANY area
		  if(in_array(strtolower($searcharea), $areas)) $filter .= "headline LIKE '%$searcharea%'"; 
		  else $filter .= "area LIKE '%$searcharea%'"; // LIKE should be case-insensitive
		  $firstterm = false;
		}
	  }
	  $filter .= ')';
	}
	$types = array('offered', 'wanted');
	foreach($types as $type) {
	  echo "<h2>$type:</h2>";
	  $posts = getPosts($filter." and type='$type'", 10);
	  if($posts) {
	    foreach($posts as $item) newPrintItem($item); // offered
	  }
	  else echo "<p><i>no items found!</i></p>";
	}
    }
    else { // no search
      echo "What's available on $list_name, right now?";
      $filter = "type='offered'";
      echo "<div id='showtaken'><input id='showTaken' type='checkbox' onClick='if(this.checked) $(\".taken\").show(\"normal\"); else $(\".taken\").hide(\"normal\")'> <small>show taken items</small></div>";
      $posts = getPosts($filter);
      if($posts) {
        foreach($posts as $item) newPrintItem($item); // offered
      }
    }
    
    //echo "</div>";
    echo "<div class='column'>";
  }
?>

</div><!-- end column container -->
</div><!-- end main -->
</body>
</html>
