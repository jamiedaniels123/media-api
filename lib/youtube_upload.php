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
require_once("../lib/config.php");
$mysqli = new mysqli($dbLogin['dbhost'], $dbLogin['dbusername'], $dbLogin['dbuserpass'], $dbLogin['dbname']);

$echo=true; # set to false for cron usage - which doesn't work anyway because of the session_start ...
# echo "<h2>Podcasts - YouTube Upload Queue</h2>\n";

appendToLog('YouTube queue process script called', false);

$error=0;
$error_text="";
$totalDone=0;
$totalFail=0;

# get the next track in the queue
# ignore any tracks that have already been uploaded or have encountered an error
// bulk uploader que query $sql="SELECT * FROM podcast_youtube_queue WHERE done_flag='N' AND error_code=0 ORDER BY id";
# echo "Queue entry:<br />$sql<br /><br />\n"; # DEBUG
$result0 = $mysqli->query("	SELECT * 
										FROM queue_commands AS cq, command_routes AS cr 
										WHERE cr.cr_action=cq.cq_command 
										AND cq.cq_command IN('media-youtube-upload','media-youtube-metadata')
										AND cq.cq_status = 'N' 
										ORDER BY cq.cq_time");
if (isset($result0->num_rows)) {

// Process the outstanding commands for each message
	$row0 = $result0->fetch_object();

	$m_data= $dataObj->doQueueAction($row0->cr_function, unserialize($row0->cq_data), $row0->cq_index, $row0->cq_cq_index);	

	
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
		
// call to upload class here


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

	// Log the command and response
}
?>
