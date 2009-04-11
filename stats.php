<?php 
require('functions.php');
require('config.php');

$limit = 25;
?>
<html>
<head>
<title>Brum Freecycle Stats</title>
<script type="text/javascript" src="jquery.js"></script>
<link rel="stylesheet" type="text/css" href="freecycle.css">
</head>
<body>
<style>
body {
  background-color: #ffffff;
}
</style>
<?php

//echo "<img align='right' src='http://chart.apis.google.com/chart?cht=p&chtt=".(count($offered)+count($taken)+count($wanted)+count($other))."+posts&chd=t:".count($offered).",".count($taken).",".count($wanted).",".count($other)."&chs=250x100&chl=Offer|Taken|Wanted|Unknown'>";

  // random figures
  // earliest record
  // total number of posts
  // number of posters making only a single post
  // taken rate
  $earliest = $db->getOne("select unix_timestamp(datetime) as time from posts order by datetime asc");
  $posts = $db->getOne("select count(*) from posts");
  $posters = $db->getOne("select count(*) from posters");
  $pluginUsers = $db->getCol("select distinct poster from posts where detail like '%memberplugin%'");
  
  // construct data structure for posters into $p
  $p = array();
  $result = $db->getAll("select posters.id, posters.email, type from posts, posters where posters.id = posts.poster_id");
  foreach($result as $r) {
    $p[$r['id']]['id'] = $r['id'];
    $p[$r['id']]['email'] = $r['email'];
    $p[$r['id']][$r['type']]++;
    $p[$r['id']]['total']++;
    $$r['type']++;
  }
  
  //echo "offered:$offered, taken:$taken, wanted: $wanted";
    //echo "<img align='right' src='http://chart.apis.google.com/chart?cht=p&chtt=$offered+$taken+$wanted&chd=t:$offered,$taken,$wanted&chs=250x100&chl=Offer|Taken|Wanted'>";
  
  // get some tallies
  foreach($p as $row) {
    if($row['total'] == 1) { // single posters
      $oneposters++;
      if($row['offered']==1) $onepostofferers++;
      else if($row['wanted']==1) $onepostwanters++;
      else $onepostothers++;
    }
    if($row['total'] == 2) { // double posters
      $twoposters++;
      if($row['offered']==1 and $row['wanted']==1) $oneoffs++;
    }
    $freq[$row['total']]++;
  }
  
  echo "<div class='box'><h1>Some numbers</h1>";
  echo "Since the database was started ".relative_time($earliest)." there have been $posts posts. That includes <span class='offered'>$offered offers</span>, <span class='taken'>$taken takens</span> and <span class='wanted'>$wanted wanteds</span>.";
  echo "<p>Of the $posters freecyclers, $oneposters (".round($oneposters/$posters*100)."%) posted only once ($onepostofferers offering, $onepostwanters wanting and $onepostothers others). $oneoffs posted both an offer and a taken.<p>";
  echo "<p>".count($pluginUsers)." users&sup1; have used memberplugin.</p>";
  echo "<p>The whole post tally is below:<br />";
  arsort($freq);
  //echo "<pre>";print_r($freq);echo "</pre>";
  foreach($freq as $key => $value) echo "$value posted $key times<br />";
  echo "</div>";
  
  // sort by total posts desc
  foreach($p as $key => $q) $total[$key] = $p[$key]['total'];
  array_multisort($total, SORT_DESC, $p);
  
  echo "<table><tr><th>e-mail</th><th>note</th><th class='offered'>O</th><th class='taken'>T</th><th class='wanted'>W</th><th class='unknown'>?</th><th >total</th></tr>";
  foreach($p as $row) {
    $note = in_array($row['email'], $pluginUsers)?'&sup1;':'';
    echo "<tr><td><a href='showposter.php?id=$row[id]'>".protectEmail($row['email'])."</a></td><td>$note</td><td class='offered'>$row[offered]</td><td class='taken'>$row[taken]</td><td class='wanted'>$row[wanted]</td><td class='unknown'>$row[unknown]</td><td>$row[total]</td></tr>";
  }
  echo "</table>";
  
//  foreach($p as $q) if()
  //echo "<pre>".print_r($p)."</pre>";
  
  
  echo "<div class='box thin'><h1>Top postcodes by posts</h1>";
  $areas = $db->getAll("select area, count(*) as freq from posts group by area order by freq desc, area limit $limit");
   
  foreach($areas as $a) { 
     if(empty($a['area'])) $a['area'] = "???"; 
     echo "$a[area] ($a[freq])<br />";
  }
  echo "</div>";
  
  echo "<div class='box thin'><h1>Top postcodes by offers</h1>";
  $areas = $db->getAll("select area, count(*) as freq from posts where type='offered' group by area order by freq desc, area limit $limit");
   
  foreach($areas as $a) { 
    if(empty($a['area'])) $a['area'] = "???"; 
    echo "$a[area] ($a[freq])<br />";
  }
  echo "</div>";
  
  echo "<div class='box thin'><h1>Top postcodes by wanted</h1>";
  $areas = $db->getAll("select area, count(*) as freq from posts where type='wanted' group by area order by freq desc, area limit $limit");
   
  foreach($areas as $a) { 
    if(empty($a['area'])) $a['area'] = "???"; 
    echo "$a[area] ($a[freq])<br />";
  }
  echo "</div>";
  /*
  echo "<div class='box'><h1>Top freecyclers by posts</h1>";
  $areas = $db->getAll("select poster_id, email, count(*) as freq from posts, posters where posters.id = posts.poster_id group by email order by freq desc, email limit $limit");
   
  foreach($areas as $a) {
    if(empty($a['email'])) $a['email'] = "???"; 
    echo "<a href='showposter.php?id=$a[poster_id]'>".protectEmail($a['email'])."</a> ($a[freq])<br />";
  }
  echo "</div>";
  
  echo "<div class='box'><h1>Top freecyclers by offers</h1>";
  $areas = $db->getAll("select poster_id, email, count(*) as freq from posts, posters where posters.id = posts.poster_id and type='offered' group by email order by freq desc, email limit $limit");
   
  foreach($areas as $a) { 
    if(empty($a['email'])) $a['email'] = "???"; 
    echo "<a href='showposter.php?id=$a[poster_id]'>".protectEmail($a['email'])."</a> ($a[freq])<br />";
  }
  echo "</div>";
  
  echo "<div class='box'><h1>Top freecyclers by wanted</h1>";
  $areas = $db->getAll("select poster_id, email, count(*) as freq from posts, posters where posters.id = posts.poster_id and type='wanted' group by email order by freq desc, email limit $limit");
   
  foreach($areas as $a) { 
    if(empty($a['email'])) $a['email'] = "???"; 
    echo "<a href='showposter.php?id=$a[poster_id]'>".protectEmail($a['email'])."</a> ($a[freq])<br />";
  }
   echo "</div>";
   
   echo "<div class='box'><h1>Top freecyclers by taken</h1>";
  $areas = $db->getAll("select poster_id, email, count(*) as freq from posts, posters where posters.id = posts.poster_id and type='taken' group by email order by freq desc, email limit $limit");
   
  foreach($areas as $a) { 
    if(empty($a['email'])) $a['email'] = "???"; 
    echo "<a href='showposter.php?id=$a[poster_id]'>".protectEmail($a['email'])."</a> ($a[freq])<br />";
  }
   echo "</div>";
   */
?>
