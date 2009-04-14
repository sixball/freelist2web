<?php
if (!function_exists("stripos")) {
  function stripos($str,$needle) {
    if($str == '' or $needle == '') return false;
    return strpos(strtolower($str),strtolower($needle));
  }
}

function printItem($item, $followup = NULL) {
  //echo "<div onMouseOver='this.style.backgroundColor=\"#e1e1e1\"' onMouseOut='this.style.backgroundColor=\"#f1f1f1\"' onClick='$(this.lastChild).slideToggle()' ";
  echo "<div ";
  if($followup) echo "class='item taken'";
  else echo "class='item'";
  echo ">";

  $from = protectEmail($item['from']);
  $subject = stripType($item['subject']);
  echo " <div class='time'>".relative_time($item['time'])."</div>";
  
  // zoom control
  echo "<img src='zoom.png' onClick='$(parentNode.lastChild).toggle();this.attr(\"onMouseOver\", \"this.src=\")'> ";
  
  
  if($followup) echo "<strike>$subject</strike>";
  else echo $subject;
  
  echo "<div class='detail'>";
  
  // insert any follow-up
  if($followup) echo "<div class='followup'>$followup[subject] <small>[".relative_time($item['time'])."]</small><br />".nl2br($followup['message']."</div>");
  echo "$from ";//<i>$item[datetime]</i>".
  echo "<br />".nl2br($item['message']);
  echo "<br/><div class='original'><a target='postpopup' href='postpopup.php?id=$item[id]' onclick=\"window.open('postpopup.php?id=$item[id]', 'postpopup', 'width=600,height=400,scrollbars=yes,z-lock=yes')\">show original</a></div><br />";
  echo "</div></div>";
}

function newPrintItem($item, $showType = false) {
  //echo "<div onMouseOver='this.style.backgroundColor=\"#e1e1e1\"' onMouseOut='this.style.backgroundColor=\"#f1f1f1\"' onClick='$(this.lastChild).slideToggle()' ";
  global $db;
  if($item['link'] > 0 && $item['type'] == 'offered') $followup = $db->getRow("select * from posts where id=$item[link]");
  echo "<div ";
  if($followup) echo "class='item taken'";
  else echo "class='item'";
  echo ">";
  
  $poster = protectEmail($item['poster']);
  if($item['link'] == 0 && $item['type'] == 'taken') echo '*';
  echo " <div class='time'>".relative_time($item['time'])."</div>";
  echo " <div class='postcode'><a href='?view=test&area=$item[area]'>$item[area]</a></div>";
  
  // zoom control
  echo "<img src='zoom.png' onClick='$(parentNode.lastChild).slideToggle()'> ";
  
  if($showType) {
    $item['headline'] = strtoupper($item['type']).": ".$item['headline'];
  }
  if($followup) echo "<strike>$item[headline]</strike>";
  else echo $item['headline'];
  
  echo "<div class='detail'>";
  
  if($followup) echo "$followup[headline] <small>[".relative_time($item['time'])."]</small><br />".nl2br($followup['detail']."<hr/>");
  echo "<a href='showposter.php?id=$item[poster_id]'>$poster</a> ";//<i>$item[datetime]</i>".
  echo "<br />".$item['detail'];//nl2br($item['detail']);
  echo "<br/><div class='original'><a target='postpopup' href='postpopup.php?id=$item[raw_id]' onclick=\"window.open('postpopup.php?id=$item[raw_id]', 'postpopup', 'width=600,height=400,scrollbars=yes,z-lock=yes')\">show original</a></div><br />";
  echo "</div></div>";
}

function protectEmail($address) {
  return preg_replace("/@(.*)/", "@...", $address);
}

function extractFromPost($p) {
  global $db, $postcode_pattern;
  //error_log(print_r($p, true)." \n", 3, 'p.txt'); 
  $p['headline'] = preg_replace("/\[birmingham_freecycle\] /", "", $p['subject']);
  preg_match("/\<(.+@.+)\>/", $p['from'], $match);
  $p['email'] = $match[1];
  
  // add poster to db if not there already and get id
  $poster_id = $db->getOne("select id from posters where email='$p[email]'");
  if(empty($poster_id)) {
    $db->query("insert into posters(email) values('$p[email]')");
    $poster_id = $db->getOne("select id from posters where email='$p[email]'");
  }
  
  preg_match("/(B\d+)/i", $p['subject'], $match);
  $p['postcode'] = strtoupper($match[1]);
  $p['headline'] = stripType($p['headline']);
  $p['headline'] = preg_replace("/([- ,\.]*\(?$postcode_pattern\)?[!]*)/i", "", $p['headline']);
  $types = array("unknown"=>0, "offered"=>1, "taken"=>2, "wanted"=>3);
  //echo typeOfPost($p['subject']);
  $p['type'] = typeOfPost($p['subject']);
  $p['detail'] = stripYahooBoiler($p['message']);
  
  $query = "insert into posts(raw_id, poster, poster_id, headline, detail, `datetime`, type, area, link) values($p[id], '$p[email]', $poster_id, '$p[headline]', '$p[detail]', '$p[datetime]', '$p[type]', '$p[postcode]', 0)";
  $db->query($query);
  
  return mysql_insert_id();    
}

function debug($message, $file='debug.txt') {
  error_log($message."\n", 3, $file);
}

function fireAlerts($postID) {
  global $db;
  $notify = array();
  // get the trigger post
  $post = $db->getRow("SELECT * from raw_posts where id=$postID");
  // return if post is a 'taken' or 'received' notification?  maybe extra field in alert
  // get list of active alerts
  $alerts = $db->getAll("select * from alerts where status>0");
  if($alerts==NULL) return;
  // for each alert
  $notify = array();
  foreach($alerts as $alert) {
      debug(print_r($alert, true));
      $matchterms = true;
      // explode search string into terms
      $terms = explode(' ', $alert['terms']);
      // for each term
      foreach($terms as $term) {
	$toSearch = $post['subject'];
        if(stripos($toSearch, $term) === false) $matchterms = false;
        if(!$matchterms) break;
      }
      // NOTE: implicit AND terms within alert.  implicit OR terms via separate alerts
      // areas field terms are OR
/* TODO
      $matchareas = false;
      if(empty($alert['area'])) $matchareas = true;
      $areas = explode(' ', $alert['area']); // only currently works for postcodes - not two word placenames - put placename in terms search
      foreach($areas as $area) {
        if(stripos($post['area'], $term) !== false) $matchareas = true;
        if($matchareas) break;
      }
      */
      // if every term can be found in post add mail to notifications list with search string: notify[mail][] = searchstring
      if($matchterms) {
	//debug("'".$post['headline']."' matches '".$alert['terms']."'");
        $notify[$alert['email']][] = $alert['terms'];
      }
  }
  //debug("notify: ".print_r($notify,true));

  // for each notification in list
  foreach($notify as $mail => $n) {
    // construct body
    $body = "The following post matched:\r\n ";
    foreach($n as $t) {
      $body .= "\r\n".$t;  
    }
    $body .= "\r\n----\r\n";
    $body .= $post['message'];
    $headers = "From: $admin_email" . "\r\n" .
      "Reply-To: $admin_email" . "\r\n" .
      'X-Mailer: PHP/' . phpversion();
    // include post message
    mail($admin_email, "Freecycle alert: $post[subject]", $body, $headers); // TODO: change to user mail
  }
}

// need to merge processPost with extractFromPost - really?
function processPost($id) {
  global $db;
  $p = $db->getRow("select * from raw_posts where id=$id");
  $id = extractFromPost($p);
  debug($p['subject'].'\n', "subjectlog.txt");
  fireAlerts($p['id']);
}

// NOTE: reflect any changes here in extractFromPost also
function stripType($subject = 'no subject - muppet!') {
  return preg_replace('/^(offered|offer|re offer|reoffer|re-offer|re: offer|taken|wanted)( stc ?)?[- =:;\.,\*]*/i', '', ltrim($subject));
}

function stripYahooBoiler($message) {
  $pos = strpos($message, '-----------');
  if($pos !== false) $message = rtrim(substr($message, 0, $pos));
  $pos = strpos($message, '[Non-text portions of this message have been removed]');
  if($pos !== false) $message = rtrim(substr($message, 0, $pos));
  return $message;
}

function followup($item) {
  global $taken;
  $so = stripType($item['subject']);
  if($taken) {
  	foreach($taken as $t) {
    	if($item['from'] == $t['from']) {
      	$sf = stripType($t['subject']);
      
      	if(hookup($sf, $so)) {
        	return $t;
      	}
      }
    }
  }
  return NULL;
  // echo "<pre>"; print_r($taken); print_r($item); echo "</pre>";
}

// tries to find the initial offer that this followup concerns
// useful for improving matching in looking for orphans
function offerFor($item) {
  global $offered;
  $sf = stripType($item['subject']);
  foreach($offer as $o) {
    if($item['from'] == $o['from']) {
      $sf = stripType($o['subject']);
      
      if(hookup($so, $sf)) return $t;
    }
  }
  return NULL;
}

// takes a pair of strings and tries to match them up
// assumes subject type is stripped and from same poster
function hookup($a, $b) {
  $a = trim($a); $b = trim($b);
  
  if($a == '' or $b == '') return false;// too easy
  
  // straight comparison
  if($a == $b) return true;
  
  // one in another
  if(stripos($a, $b) !== false || stripos($b, $a) !== false) return true;

  // finally shuffled subsets
  $as = explode(" ", $a); $bs = explode(" ", $b);
  //print_r($as); print_r($bs);
  $contained = true;
  foreach($as as $s) {
    if(stripos($b, $s) === false) {
      $contained = false;
      break;
    }
  }
  if($contained) return true;
  
  $contained = true;
  foreach($bs as $s) {
    if(stripos($a, $s) === false) {
      $contained = false;
      break;
    }
  }
   if($contained) return true;
   else return false;
}

// assumes subject starts with post type
function typeOfPost($subject) {
  $subject = str_replace("[birmingham_freecycle] ", "", $subject);
  //echo $subject;
  if(stripos($subject, "offer") === 0 || 
    stripos($subject, "reoffer") === 0 || 
    stripos($subject, "re:offer") === 0 || 
    stripos($subject, "re:offered") === 0 || 
    stripos($subject, "re: offer") === 0 || 
    stripos($subject, "re offer") === 0 || 
    stripos($subject, "re-offer") === 0 || 
    stripos($subject, "re-offered") === 0) 
  return "offered"; // lot of creative variations!
    
  else if(stripos($subject, "wanted") === 0) return "wanted";
    
  else if(stripos($subject, "taken") === 0) return "taken";
    
  else return "unknown";
}

function getPosts($filter = NULL, $max = 50) {
  global $db;
  if($filter != NULL) $conditions = "where ".$filter;
  $limit = "limit ".$max;
  $results = $db->getAll("select *,unix_timestamp(datetime) as time from posts $conditions order by datetime desc $limit");
  if(DB::iserror($db)) die($db->getMessage());
  return $results;
}

function showPostcodeCloud() {
  global $db;
  echo "<p><b><a href='?view=test' >All</a> areas:</b> ";
    $areas = $db->getAll("select area, count(*) as freq from posts group by area order by area");
   
    foreach($areas as $a) {
      if(!empty($a['area'])) {
        
        echo "<font size='".(log($a['freq']*5)+2)."px'><a href='?view=test&area=$a[area]' title='$a[freq] posts'>$a[area]</a></font> ";
          $shade = 180 - 5*$a['freq'];
         //echo "<a style='color:rgb($shade, $shade, 255)' href='?view=test&area=$a[area]' title='$a[freq] posts'>$a[area]</a> ";
         }
    }
}

function relative_time($time) {
  $age = time() - $time;
  
  if($age < 0) $time = "in the past!";
  
  // less than one minute
  else if($age < 10) $time = "just now";
  
  // less than one minute
  else if($age < 60) $time = $age." secs ago";

  // less than one hour ago
  else if ($age < 60*60) {
    $time = round($age/60);
    if($time == 1) $time.=" min ago";
    else $time.=" mins ago";
  }
  /*
  // less than 2 hours ago (big difference between 60 and 119 minutes!)
  else if ($age < 60*60*2) {
    $time = $age/60;
    //if($time == 1) $time.=" hours ago";
    $time.=" hours ago";
  }
  */
  // less than one day ago
  else if ($age < 60*60*24) {
    $time = round($age/60/60);
    if($time == 1) $time.=" hour ago";
    else $time.=" hours ago";
  }

  // more than a day ago, i.e. several days
  else {
    $time = round($age/60/60/24);
    if($time == 1) $time.=" day ago";
    else $time.=" days ago";
  }

  return $time;//." ".date("g:ia");
}

function printMods() {
  global $db;
  $mods = $db->getAll("SELECT `headers`,`datetime` FROM `raw_posts` WHERE `headers` LIKE '%eGroups-Approved-By%' order by datetime desc limit 100");
  foreach($mods as $mod) {
    $out = array();
    $approval = preg_match('/eGroups-Approved-By: .+\n/', $mod['headers'], $out);
    echo substr($out[0], 21)."<br/>";
  }
}
?>