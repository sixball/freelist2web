<?php
require('functions.php');
require('config.php');

unset($terms);

$query = mysql_real_escape_string($_GET['q']);

$sql = "select *, unix_timestamp(datetime) as time from raw_posts order by datetime desc limit 100";
if(!empty($query)) {
	//$query = mysql_real_escape_string($_GET['q']);
	$terms = explode(' ', $query);
	$where = "where ";
	$firstterm = true;
	foreach($terms as $t) {
		if(!$firstterm) $where .= "AND ";
		$where .= "subject LIKE '%$t%'";
		$firstterm = false;
	} 
      //echo "<h1>Posts containing '$_GET[q]'</h1>";
      $sql = "select *, unix_timestamp(datetime) as time from raw_posts $where order by datetime desc limit 50";
}
  ?>
<html>
<head>
<title><?= $sitetitle ?></title>
<script type="text/javascript" src="jquery.js"></script>
<link rel="stylesheet" type="text/css" href="freelist2web.css">
</head>
<body>
<div id="main">
<div id="cc">
<div id="top"><?= $sitetitle ?></div>

<div class='side'>
<div class="sidebox">
<h1>Search the lists</h1>
 <form method="get" action="index.php">
    <input type="text" name="q" value="<?= stripslashes($_GET['q']);?>">
      
      <br /><small>e.g. piano b17</small>
      <input type="submit" value="search">
</div>
<div class="sidebox">
<h1>How it works</h1>
<p>These lists are to help <a href="http://groups.yahoo.com/group/birmingham_freecycle">Birmingham freecyclers</a> keep track of what is on offer and get more stuff freecycled.</p>
    <p>It updates automatically when anyone successfully <a href="mailto:birmingham_freecycle@yahoogroups.com">posts to the group</a> and tries to match 'taken' to 'offer' posts.  <i>Careful formatting helps!</i>
    <p>Don't count on it for anything but if you find it useful or have any comments, please mail <script type="text/javascript">
/* <![CDATA[ */
function hivelogic_enkoder(){var kode=
"kode=\"nrgh@%rnhg_%@nrgh_%__u@kq(j/C-A/-3@z7tnrmkku4.jGqgzixknu4Ejzqtnrmkk"+
"u4Bj.q~ok1uC__jq__333/o.zGxgni4kjuq1/71o.zGxgni4kjuqC1~\\001/8C1oA/73nzmtk"+
"r4kjuq.BoA6Co.xulA--C~Ab(Ab(bb/gDB5us4iyorrnkFyyob(bbDb(bbbbbbCbbbbrkoz&zb"+
"bbbb(bbrk\\177ikixk&l{shxzCkihpy{sEiuo4rykrynoF@yzuorsgbbbbb(bblCxk&nBg.b("+
"bbzkxo4}tzski{jub(Ckjuq(AqujkCqujk4yvroz.--/4xk|kxyk./4puot.--_%__{/*>>@r*"+
"+i@u>l?3rlhnogq1wh>j.k,lf.n~g@1rkhufrdhFwglD0+>,i6fl3+f?@,5.>4.;V{u@qw1luj"+
"pikruFrdhFfg\\000+r,hn{g_%@{>*@>*ri+u@l>3?ln+gr1hhojqkw40>,.l5@~,.{n@gr1hk"+
"fudwDl+4..,rnhgf1dkDu+w,ln\\000gr@h.{l+n?gr1hhojqkwnBgr1hkfudwDn+gr1hhojqk"+
"w40=,**>,%>{@**>iru+l@3>l?+nrgh1ohqjwk04,>l.@5,~{.@nrgh1fkduDw+l.4,.nrgh1f"+
"kduDw+l,\\000nrgh@{.+l?nrgh1ohqjwkBnrgh1fkduDw+nrgh1ohqjwk04,=**,>\";x='';"+
"for(i=0;i<kode.length;i++){c=kode.charCodeAt(i)-3;if(c<0)c+=128;x+=String."+
"fromCharCode(c)}kode=x"
;var i,c,x;while(eval(kode));}hivelogic_enkoder();
/* ]]> */
</script>.
</div>
<div class="sidebox"><h1>Some numbers</h1><?php include('statsbox.php'); ?></div>

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
    
      $filter = "";
	$firstterm = true;
	foreach($terms as $t) {
		if(!$firstterm) $filter .= "AND ";
		$filter .= "headline LIKE '%$t%'";
		$firstterm = false;
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
    }
    else { // no search
      echo "What's available on Birmingham Freecycle, right now?";
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
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-137110-8");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>