<?php
# TEST SCRIPT
# modify the metadata of an already-uploaded movie
# Uses Zend/Google GData library in /ZendGData

include_once './common.inc.php'; # NB: includes session_start();

$youtube_channel="oulearn";

$clientLibraryPath = '/web/ou-podcast01.open.ac.uk/ZendGdata/library';
$oldPath = set_include_path(get_include_path() . PATH_SEPARATOR . $clientLibraryPath);

require_once 'Zend/Loader.php'; # this loads all of the API classes

$APIkey=$youTubeChanAPI[$youtube_channel]; # pick relevant API key
# $APIkey="AI39si5a2JYuDp6JQKFffQ8VEjmRlFjcp5-4Moly_gSlbkuJeF9UbtWop1whqFciq23FmWcCuZMHMfEg04qO_dzXXxMnZjRaBA"; # KMi API key DEBUG

Zend_Loader::loadClass('Zend_Gdata_YouTube');
$yt = new Zend_Gdata_YouTube();

Zend_Loader::loadClass('Zend_Gdata_ClientLogin'); 

$youChanUser=$youTubeChanUser[$youtube_channel];
$youChanPass=$youTubeChanPass[$youtube_channel];

# echo "Authentication: $APIkey | $youChanUser | $youChanPass<br /><br />\n"; # DEBUG

$authenticationURL= 'https://www.google.com/accounts/ClientLogin';
$httpClient = Zend_Gdata_ClientLogin::getHttpClient(
              $username = $youChanUser,
              $password = $youChanPass,
              $service = 'youtube',
              $client = null,
              $source = 'Podcast', // a short string identifying your application
              $loginToken = null,
              $loginCaptcha = null,
              $authenticationURL);

$yt = new Zend_Gdata_YouTube($httpClient, 'Podcast upload service', 'Podcast upload service', $APIkey);

$sql="SELECT p.id, p.youtube_title, p.youtube_description, p.youtube_id, q.youtube_channel FROM podcast_items p JOIN podcast_youtube_queue q ON q.track_id=p.id WHERE q.done_flag='Y' AND q.error_code=0 AND q.youtube_channel='oulearn' ORDER BY p.id";
$result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
while ($row=mysql_fetch_array($result)) {
  $tid=$row['id'];
  $youtube_title=stripslashes($row['youtube_title']);
  $youtube_description=stripslashes($row['youtube_description']);
  $whichVid=$row['youtube_id'];
  $youtube_channel=$row['youtube_channel'];
  # look for something like (Part 2 of 22)
  
  if (preg_match('/\(Part ([0-9]+) of ([0-9]+)\)/i',$youtube_description,$xofy)) {
    # if (is_int($xofy[1]) && is_int($xofy[2])) {
      $new_title=$youtube_title." (".$xofy[1]."/".$xofy[2].")";
      echo "<h3>Video ".$whichVid." - ".$youtube_title."</h3>\n";
      
      echo nl2br($youtube_description)."<br>\n";
      echo "Title becomes: <b>".$new_title."</b>";
      
      echo "<hr />\n";
    # }
  }
  
  
  



  
}

/*


# get all IDs of all uploaded videos in the channel
$ytIds=array();
$videoFeed=$yt->getuserUploads('KMiUKOU'); # all videos in our Channel
foreach ($videoFeed as $videoEntry) {
  $ytId=$videoEntry->getVideoId();
  $ytIds[]=$ytId;
  echo "<li> ".$ytId;
  if ($ytId==$whichVid) {
    $putUrl = $videoEntry->getEditLink()->getHref();
    $videoEntry->setVideoTitle($new_title);
    $videoEntry->setVideoDescription($youtube_description);
    $yt->updateEntry($videoEntry, $putUrl);
    echo "<br />Video ".$ytId." has been updated";
  }
}
*/

?>