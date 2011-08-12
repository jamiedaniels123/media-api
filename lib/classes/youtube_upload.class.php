<?php
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  20th Feb,2011
	#	Test version  
	#  Encoder Class File to handle file service actions and provide responses.
\*=========================================================================================*/

class Default_Model_Youtube_Class
 {
    protected $m_mysqli;
	
	/**  * Constructor  */
    function Default_Model_Action_Class($mysqli){
		$this->m_mysqli = $mysqli;
	}  

// ------ User stuff

	
	public function upload_to_youtube(){

		global $mysqli;

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
?>