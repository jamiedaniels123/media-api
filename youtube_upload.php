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

require_once("lib/config.php");
require_once("lib/classes/upload.class.php");

// Initialise objects

	$clientLibraryPath = $paths['server-path']."lib/Zend/Gdata";

	$oldPath = set_include_path(get_include_path() . PATH_SEPARATOR . $clientLibraryPath);
echo $oldPath;
 require_once './lib/Zend/Loader.php'; # this loads all of the API classes

	$mysqli = new mysqli($dbLogin['dbhost'], $dbLogin['dbusername'], $dbLogin['dbuserpass'], $dbLogin['dbname']);

	$result0 = $mysqli->query("	SELECT * 
											FROM queue_commands AS cq, command_routes AS cr 
											WHERE cr.cr_action=cq.cq_command 
											AND cq.cq_command IN('media-youtube-upload','media-youtube-metadata')
											AND cq.cq_status = 'N' 
											ORDER BY cq.cq_time LIMIT 1");

	if ($row0 = $result0->fetch_object()) {
	
	// Process the next  commands for each message
		$cq_data = json_decode($row0->cq_data, true);

		$APIkey=$youTubeChanAPI[$youtube_channel]; # pick relevant API key
		# $APIkey="AI39si5a2JYuDp6JQKFffQ8VEjmRlFjcp5-4Moly_gSlbkuJeF9UbtWop1whqFciq23FmWcCuZMHMfEg04qO_dzXXxMnZjRaBA"; # KMi API key DEBUG
		
		Zend_Loader::loadClass('Zend_Gdata_YouTube');
		$yt = new Zend_Gdata_YouTube();
		
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		
		if (isset($cq_data['meta_data']['channel'])) 
			$youtube_channel = $cq_data['meta_data']['channel'];
		else
			$youtube_channel = "default";

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

		$youTubeObj = new Default_Model_YouTube_Class($mysqli, $myVideoEntry, $yt);	
		
		appendToLog("Attempting to upload <b>".$fullpath."</b> [ ".$ContentType." ] ...<br />", $echo);
		
		// create a new VideoEntry object
		$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();

		if (	$row0->cq_command == 'media-youtube-upload')
			$m_data= $youTubeObj->uploadToYoutube($cq_data, $cqIndex);	
		else if (	$row0->cq_command == 'media-youtube-metadata')
			$m_data= $youTubeObj->updateYoutubeData($cq_data, $cqIndex);	
	}

?>
