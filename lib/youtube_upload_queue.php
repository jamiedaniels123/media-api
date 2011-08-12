<?php
/*
# YouTube video upload queue processor - CPV 20110330
# uploads one file at a time to the relevant YouTube channel
# script calls itself to do the next track until there are no tracks left in the queue
# Uses Zend/Google GData library in /ZendGData

# Error codes:
0 - no error
1 - database missing
2 - media file missing
3 - YouTube upload failed - various reasons - see the saved text

*/

include_once './common.inc.php'; # NB: includes session_start();

$echo=true; # set to false for cron usage - which doesn't work anyway because of the session_start ...
# echo "<h2>Podcasts - YouTube Upload Queue</h2>\n";

appendToLog('YouTube queue process script called', false);

$error=0;
$error_text="";
$totalDone=0;
$totalFail=0;

# get the next track in the queue
# ignore any tracks that have already been uploaded or have encountered an error
$sql="SELECT * FROM podcast_youtube_queue WHERE done_flag='N' AND error_code=0 ORDER BY id";
# echo "Queue entry:<br />$sql<br /><br />\n"; # DEBUG
$result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
$totalLeft=mysql_num_rows($result);
$row=mysql_fetch_array($result); # this will get the first row even though we've selected all above

if (!is_array($row)) {
  # echo "<p><b>No tracks left to upload</b></p>\n";
  $totalLeft=0;
} else {
  $qid=$row['id'];
  $pid=$row['podcast_id'];
  $tid=$row['track_id'];
  $temp_title=stripslashes($row['temp_title']); # same for each track in a podcast
  $youtube_channel=$row['youtube_channel']; # mandatory
  $queued_by=$row['queued_by'];
  $queued_when=$row['queued_when'];
  
  # get matching podcast and track metadata
  $sql="SELECT p.title AS podcastTitle, p.summary AS podcastSummary, p.custom_id, p.course_code, p.keywords, p.youtube_channel, 
  i.id, i.shortcode, i.youtube_title, i.youtube_description, i.youtube_tags FROM podcast_items i LEFT JOIN podcasts p ON p.id=i.podcast_id WHERE p.id=$pid AND i.id=$tid LIMIT 1";
  # echo "metadata:<br />$sql<br /><br />\n"; # DEBUG
  $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
  $row=mysql_fetch_array($result);
  
  if (!is_array($row)) {
    appendToLog("<p><b>ERROR: missing or unmatched track or podcast metadata (pid=$pid | tid=$tid)</b></p>", $echo);
    $error=1;
    $totalFail++;
    $totalLeft--;
    $error_text="Missing or unmatched track or podcast metadata";
  } else {
    $shortcode=$row['shortcode'];
    $custom_id=$row['custom_id'];
    $course_code=stripslashes($row['course_code']);
    $youtube_channel=$row['youtube_channel']; # which channel to send to - stored at the podcast level
    $youtube_title=stripslashes($row['youtube_title']); # custom title for YouTube
    $youtube_description=stripslashes($row['youtube_description']); # custom desciption for YouTube
    # $youtube_tags=stripslashes($row['youtube_tags']).", \"collectionID_".$custom_id."\""; # comma-delimited keywords for YouTube
    # $youtube_tags="123456789 012345678901234567890 , test, another-test"; # DEBUG
    # appendToLog($youtube_tags); # DEBUG
    # $youtube_tags=stripslashes($row['youtube_tags']);
    # only include tags shorter than 31 characters
    $youtube_tags=YouTubeTags(stripslashes($row['youtube_tags']));
    
    # get media filename
    $sql="SELECT filename, UNIX_TIMESTAMP(CONVERT_TZ(uploaded_when, '+0:00', 'SYSTEM')) AS transDate FROM podcast_item_media WHERE podcast_item=$tid AND media_type='youtube' LIMIT 1";
    $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
    # echo "media file:<br />$sql<br /><br />\n"; # DEBUG
    $row=mysql_fetch_array($result);
    $transDate=$row['transDate']; # transcoding date
    $filename=$row['filename'];
    $fullpath=$podcastsPath.$custom_id."/".$mediaFolder['youtube'].$filename;
    $extn=substr(strtolower(strrchr($filename,".")),1);
    $ContentType=$mimesTypes[$extn];
    
    # check media file actually exists
    if (!file_exists($fullpath)) {
      appendToLog("<p><b>ERROR: media file missing (pid=$pid | tid=$tid | $media_filepath)</b></p>", $echo);
      $error=2;
      $totalFail++;
      $totalLeft--;
      $error_text="Media file missing";
    } else {
    
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

      appendToLog("Attempting to upload <b>".$fullpath."</b> [ ".$ContentType." ] ...<br />", $echo);
      
      // create a new VideoEntry object
      $myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
      
      // create a new Zend_Gdata_App_MediaFileSource object
      $filesource = $yt->newMediaFileSource($fullpath);
      $ContentType='video/quicktime';
      $filesource->setContentType($ContentType); # base on extension
      // set slug header
      $filesource->setSlug($fullpath);
      
      // add the filesource to the video entry
      $myVideoEntry->setMediaSource($filesource);
      
      $myVideoEntry->setVideoTitle($temp_title." ".$youtube_title); # add the temp shortcode to the front of each track title to make life easier for the post-uploading person
      $myVideoEntry->setVideoDescription($youtube_description);
      
      // The category must be a valid YouTube category!
      $myVideoEntry->setVideoCategory('Education');
      
      // Set keywords. Please note that this must be a comma-separated string
      // and that individual keywords cannot contain whitespace
      $myVideoEntry->SetVideoTags($youtube_tags);
      
      // set some developer tags -- this is optional
      // (see Searching by Developer Tags for more details)
      $myVideoEntry->setVideoDeveloperTags(array('fromPodcast', $shortcode));
      
      // set the video's location -- this is also optional
      $yt->registerPackage('Zend_Gdata_Geo');
      $yt->registerPackage('Zend_Gdata_Geo_Extension');
      $where = $yt->newGeoRssWhere();
      $position = $yt->newGmlPos('52.024534 -0.708425'); # lat long space delimited
      $where->point = $yt->newGmlPoint($position);
      $myVideoEntry->setWhere($where);
      
      $myVideoEntry->setVideoPrivate(); # set the entry to private - alternate method - VITAL!!!
      
      # update the queue table entry
      # **** NOT SURE I SHOULD DO THIS HERE as there could be an error ****
      $sql="UPDATE podcast_youtube_queue SET done_flag='P' WHERE id=$qid LIMIT 1"; # processing
      $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
      
      $uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';

      $youTubeID="";
      // try to upload the video, catching a Zend_Gdata_App_HttpException, 
      // if available, or just a regular Zend_Gdata_App_Exception otherwise
      try {
        $newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
        $youTubeID=$newEntry->getVideoId();
      } catch (Zend_Gdata_App_HttpException $httpException) {
        $error_text="ERROR 1: ".$httpException->getRawResponseBody();
        appendToLog($error_text, $echo);
      } catch (Zend_Gdata_App_Exception $e) {
        $error_text="ERROR 2: ".$e->getMessage();
        appendToLog($error_text, $echo);
      }
      if (empty($youTubeID)) {
        appendToLog("<p><b>ERROR: upload failed (pid=$pid | tid=$tid | $fullpath)</b></p>");
        $error=3;
      } else {
        appendToLog("<p>Video track '<i>".$youtube_title."</i>' successfully uploaded - YouTube code returned as <b>".$youTubeID."</b></p>");
        
        # store the newly-issued YouTube ID in the track table
        $sql="UPDATE podcast_items SET youtube_flag='Y', youtube_id='".safe($youTubeID)."' WHERE id=$tid AND podcast_id=$pid LIMIT 1";
        $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
        
        # update queue database
        $sql="UPDATE podcast_youtube_queue SET uploaded_when='".date('Y-m-d H:i:s')."', done_flag='Y' WHERE id=$qid LIMIT 1"; # success
        $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
      }
      # NB: have to make sure we move onto the next record if there is a failure - otherwise it will just keep trying over and over again
    }
  }
}
if ($error > 0) {
  $sql="UPDATE podcast_youtube_queue SET done_flag='F', error_code=$error, error_text='".safe($error_text)."' WHERE id=$qid LIMIT 1";
  $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
}

$sql="SELECT id FROM podcast_youtube_queue WHERE done_flag='N'";
$result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
$totalLeft=mysql_num_rows($result);
/*
if ($totalLeft > 0) {
  echo "<p>There are $totalLeft tracks left in the queue.<br /><br />Click to <a href='".$_SERVER['PHP_SELF']."'>jump to next in queue</a>.</p>\n";
} else {
  echo "<p>There are no tracks left in the queue. $totalDone tracks were uploaded. $totalFail tracks failed to upload.</p>\n";
}
*/
# script calls itself again if there are any files left in the queue
if ($totalLeft > 0) {
  //$location="Location: ".$_SERVER['PHP_SELF'];
  //header($location);
  
  $selfUrl = "http://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["PHP_SELF"];	
	exec("curl ".$selfUrl." > /dev/null &");
	echo "Queued - curl ".$selfUrl." > /dev/null &<br>\n";
} else {
  # ... or jumps to the queue view page
  $location="Location: youtube_view_queue.php?message=".urlencode("Queue processing complete");
  header($location);
}
?>
