<?php 
$earliest = $db->getOne("select unix_timestamp(datetime) as time from posts order by datetime asc");
  $posts = $db->getOne("select count(*) from posts");
  $posters = $db->getOne("select count(*) from posters");
  $pluginUsers = $db->getCol("select distinct poster from posts where detail like '%memberplugin%'");
  
   $offeredposts = $db->getOne("select count(*) from posts where type='offered'");
  $wantedposts = $db->getOne("select count(*) from posts where type='wanted'");
  $takenposts = $db->getOne("select count(*) from posts where type='taken'");

  echo "<ul><li>".relative_time($earliest)." since lists were started<li>$posters freecyclers<li>$posts posts recorded<ul><li> $offeredposts offers <li>$takenposts takens <li> $wantedposts wanteds</ul></ul>";
?>