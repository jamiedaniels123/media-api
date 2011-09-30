<?php
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  20th Feb,2011
	#	Test version  
	#  Encoder Class File to handle file service actions and provide responses.
\*=========================================================================================*/

class Default_Model_YouTube_Class
 {
    protected $m_mysqli, $m_myVideoEntry, $m_yt;
	
	/**  * Constructor  */
    function Default_Model_YouTube_Class($mysqli, $myVideoEntry, $yt){
		$this->m_mysqli = $mysqli;
		$this->m_myVideoEntry = $myVideoEntry;
		$this->m_yt = $yt;		
	}  

// ------ User stuff

	# function to check length of tags for YouTube, which doesn't allow tags longer than 30 characters
	# the tags should be a comma-delimited list
	function YouTubeTags($tags) {
		$tagsA=explode(',',$tags);
		$okTags=array();
		foreach($tagsA as $tag) {
			if (strlen($tag) < 31) $okTags[]=$tag;
		}
		$tags=implode(',',$okTags);

		return $tags;
	}

	
	public function uploadToYoutube($mArr, $cqIndex){

	global $paths, $mimeTypes;
		error_log('entered upload to youtube');
// print_r($mArr);	
		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';
		$debug = "";
		$tid=$mArr['podcast_item_id'];
	  	$pid=$mArr['podcast_item_id'];
		
		$youtube_title=stripslashes($mArr['meta_data']['title']); # custom title for YouTube
		$youtube_description=stripslashes($mArr['meta_data']['description']); # custom desciption for YouTube

		# only include tags shorter than 31 characters
		$youtube_tags=$this->YouTubeTags(stripslashes($mArr['meta_data']['youtube_tags']));
		
		$filename=$mArr['destination_filename'];
		$fullpath= $paths['destination'].$mArr['destination_path'].$filename;
		$extn=substr(strtolower(strrchr($filename,".")),1);
		$ContentType=$mimeTypes[$extn];
		
		// create a new Zend_Gdata_App_MediaFileSource object
		$filesource = $this->m_yt->newMediaFileSource($fullpath);
		$ContentType='video/quicktime';
		$filesource->setContentType($ContentType); # base on extension
		// set slug header
		$filesource->setSlug($fullpath);
		
		// add the filesource to the video entry
		$this->m_myVideoEntry->setMediaSource($filesource);
		
		$this->m_myVideoEntry->setVideoTitle($mArr['podcast_item_id']." ".$youtube_title); # add the temp shortcode to the front of each track title to make life easier for the post-uploading person
		$this->m_myVideoEntry->setVideoDescription($youtube_description);

		// The category must be a valid YouTube category!
		$this->m_myVideoEntry->setVideoCategory('Education');
		
		// Set keywords. Please note that this must be a comma-separated string
		// and that individual keywords cannot contain whitespace
		$this->m_myVideoEntry->SetVideoTags(array( $youtube_tags ));
		
		// set the video's location -- this is also optional
		$this->m_yt->registerPackage('Zend_Gdata_Geo');
		$this->m_yt->registerPackage('Zend_Gdata_Geo_Extension');
		$where = $this->m_yt->newGeoRssWhere();
		$position = $this->m_yt->newGmlPos('52.024534 -0.708425'); # lat long space delimited
		$where->point = $this->m_yt->newGmlPoint($position);
		$this->m_myVideoEntry->setWhere($where);
		
		$this->m_myVideoEntry->setVideoPrivate(); # set the entry to private - alternate method - VITAL!!!
		
				
		// There is a possible bug here. We are flagging as processed but not recording
		// any failures that will only appear later.
		$result = $this->m_mysqli->query("
			UPDATE `queue_commands` 
			SET `cq_update` = NOW(),`cq_status`= 'P' 
			WHERE cq_index='".$cqIndex."' ");

		$uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';
		
		$youTubeID="";

		// try to upload the video, catching a Zend_Gdata_App_HttpException, 
		// if available, or just a regular Zend_Gdata_App_Exception otherwise
		try {
			$newEntry = $this->m_yt->insertEntry($this->m_myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
			$youTubeID=$newEntry->getVideoId();
			error_log('youtube ID is '.$youTubeID);
		} catch (Zend_Gdata_App_HttpException $httpException) {
			error_log($httpException->getRawResponseBody());			
			echo "<pre>";
			print_r( $httpException->getRawResponseBody() );
			die('fffff');
			$debug[] ="ERROR 1: ".$httpException->getRawResponseBody();
		} catch (Zend_Gdata_App_Exception $e) {
			error_log($e->getMessage());						
			$debug[] ="ERROR 2: ".$e->getMessage();
		}
		if (empty($youTubeID)) {

			$debug[] = "ERROR: upload failed pid=".$pid." | tid=".$tid." | ".$fullpath." ";
			$error=3;
			$retData['result']='F';
			$retData['number']=1;
			$retData['debug'] = $debug;
		} else {
			$debug[] = "Video track ".$youtube_title." successfully uploaded - YouTube code returned as ".$youTubeID." ";
			
			$retData['result']='Y';
			$retData['number']=1;
			$retData['youtube_id'] = $youTubeID;
			$retData['debug'] = $debug;
					
		  }
		  
		  return $retData;
		  
	}

	public function updateYoutubeData($mArr, $cqIndex){
	error_log('attempting to update youtube');
	global $paths, $mimeTypes;

// print_r($mArr);	
		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';
		$debug = "";
		$tid=$mArr['podcast_item_id'];
	  	$pid=$mArr['podcast_item_id'];

		$youtube_id=$mArr['youtube_id'];
		$youtube_title=stripslashes($mArr['meta_data']['title']); # custom title for YouTube
		$youtube_description=stripslashes($mArr['meta_data']['description']); # custom desciption for YouTube

		# only include tags shorter than 31 characters
		$youtube_tags=$this->YouTubeTags(stripslashes($mArr['meta_data']['youtube_tags']));
		error_log('youtube id is '.$youtube_id);
		//$extn=substr(strtolower(strrchr($filename,".")),1);
		//$ContentType=$mimeTypes[$extn];
		
		$this->m_myVideoEntry = $this->m_yt->getVideoEntry($youtube_id, null, true);
		if ($this->m_myVideoEntry->getEditLink() !== null) {
			try {
			
				$this->m_myVideoEntry->setVideoTitle($mArr['podcast_item_id']." ".$youtube_title); # add the temp shortcode to the front of each track title to make life easier for the post-uploading person
				$this->m_myVideoEntry->setVideoDescription($youtube_description);
				
				// The category must be a valid YouTube category!
				$this->m_myVideoEntry->setVideoCategory('Education');
				
				// Set keywords. Please note that this must be a comma-separated string
				// and that individual keywords cannot contain whitespace
					$this->m_myVideoEntry->SetVideoTags( array( $youtube_tags ) );
				
				$this->m_yt->updateEntry($this->m_myVideoEntry);
				error_log('succesful update');
			} catch (Exception $e) {
				error_log('error updating'. $e->getMessage() );
				$error=3;
				$retData['result']='P';
				$retData['number']=1;
				$retData['debug'] = "Error: ".$e->getMessage();
			} 
			if ($retData['result'] = 'N') {
				$retData['result']='Y';
				$retData['number']=1;
				$retData['debug'] = $debug;			
			}
		}

	return $retData;
		  
	}

}
?>