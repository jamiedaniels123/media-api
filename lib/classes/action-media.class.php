<?php
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  20th Feb,2011
	#	Test version  
	#  Class File to handle file service actions and provide responses.
\*=========================================================================================*/

class Default_Model_Action_Class
 {
	
	/**  * Constructor  */
    function Default_Model_Action_Class($mysqli){}  

//---------The basic methods for file management ----------------------------------------------------------------------

	function objectToArray($d) {
		if (is_object($d)) {
			$d = get_object_vars($d);
		}
	
		if (is_array($d)) {
			return array_map(__FUNCTION__, $d);
		}
		else {
			return $d;
		}
	}
	   
	function delTree($dir) {
		$files = glob( $dir . '*', GLOB_MARK );
		foreach( $files as $file ){
			if( substr( $file, -1 ) == '/' )
				delTree( $file );
			else
				unlink( $file );
		}
	   
		if (is_dir($dir)) rmdir( $dir );
   
	} 
	
	function deleteAll($directory, $empty = true) {
		if(substr($directory,-1) == "/") {
			$directory = substr($directory,0,-1);
		}
	
		if(!file_exists($directory) || !is_dir($directory)) {
			return false;
		} elseif(!is_readable($directory)) {
			return false;
		} else {
			$directoryHandle = opendir($directory);
		   
			while ($contents = readdir($directoryHandle)) {
				if($contents != '.' && $contents != '..') {
					$path = $directory . "/" . $contents;
				   
					if(is_dir($path)) {
						deleteAll($path);
					} else {
						unlink($path);
					}
				}
			}
		   
			closedir($directoryHandle);
	
			if($empty == false) {
				if(!rmdir($directory)) {
					return false;
				}
			}
		   
			return true;
		}
	} 
	
	
	function getFilesFromDir($dir) {
	
	  $files = array();
	  if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if(is_dir($dir.'/'.$file)) {
					$dir2 = $dir.'/'.$file;
					$files[] = getFilesFromDir($dir2);
				}
				else {
				  $files[] = $dir.'/'.$file;
				}
			}
		}
		closedir($handle);
	  }
	
	  return array_flat($files);
	}
	
	function array_flat($array) {
	
	  foreach($array as $a) {
		if(is_array($a)) {
		  $tmp = array_merge($tmp, array_flat($a));
		}
		else {
		  $tmp[] = $a;
		}
	  }
	
	  return $tmp;
	}

// ------ Managing actions ----------------------------------------------------------------------------------

	public function queueAction($mArr,$action,$cqIndex,$mqIndex,$step,$timestamp)
	{	
		global $mysqli;

		$retData= array( 	'command'=>$action, 'number'=>'', 'data'=>$mArr, 'status'=>'NACK', 'error'=>'' ) ;

		$mysqli->query("	INSERT 
								INTO `queue_commands` (`cq_command`, `cq_cq_index`, `cq_mq_index`, `cq_step`, `cq_data`, `cq_time`, `cq_update`, `cq_status`) 
								VALUES ('".$action."','".$cqIndex."','".$mqIndex."','".$step."','".serialize($mArr)."','".date("Y-m-d H:i:s", $timestamp)."', '', 'N')");
		$error = $mysqli->error;
		if ($error=='') { 
			$retData['status']='ACK';
			$retData['number']=1;
			$retData['error']=''; 
		} else { 
			$retData['status']='NACK';
			$retData['number']=0;
			$retData['error']=$error;
		}
		return $retData;
	}

	public function doQueueAction($function, $mArr, $cqIndex)
	{
		global $mysqli,$outObj,$mediaUrl;

			$retData = $this->$function($mArr,1,$cqIndex);
			if ($retData['result']=='Y') {
				$result = $mysqli->query(" UPDATE `queue_commands` 
													SET `cq_update` = '".date("Y-m-d H:i:s", time())."' ,`cq_status`= 'Y', cq_result='".serialize($mArr)."' 
													WHERE cq_index='".$cqIndex."' ");
			}
	}

	public function doDirectAction($function, $mArr)
	{
		global $mysqli,$outObj,$mediaUrl;
			$retData = $this->$function($mArr,1);
		return $retData;
	}

// ------ The actions from commands ----------------------------------------------------------------------------------

	public function doMediaMoveFile($mArr,$mNum,$cqIndex)
	{
		global $paths, $debug;

		$retData= array('cqIndex'=>$cqIndex, 'filename'=> $mArr['filename'], 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], 'number'=> 0, 'result'=> 'N') ;

		$full_path = rtrim($paths['destination'].$mArr['destination_path'],'\/');
		if (!is_dir($full_path)) {
			mkdir($paths['destination'].$mArr['destination_path'], 0755, true);
			usleep ( 100000 );
		}
		$debug = $paths['source'].$mArr['destination_path'].$mArr['filename'].",".$paths['destination'].$mArr['destination_path'].$mArr['filename'];
		if ( rename($paths['source'].urlencode($mArr['destination_path'].$mArr['filename']),$paths['destination'].$mArr['destination_path'].$mArr['filename'])) {
			$retData['result']='Y';
			$retData['number']=1;
		}
		return $retData;
	}

	public function doMediaRenameFile($mArr,$mNum,$cqIndex)
	{
		global $paths;

		$retData= array('cqIndex'=>$cqIndex, 'filename'=> $mArr['filename'], 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], 'number'=> 0, 'result'=> 'N') ;

		$full_path = rtrim($paths['destination'].$mArr['destination_path'],'\/');
		if (!is_dir($full_path)) {
			mkdir($paths['destination'].$mArr['destination_path'], 0755, true);
			usleep ( 100000 );
		}
				
		if ( rename($paths['destination'].$mArr['destination_path'].$mArr['filename'],$paths['destination'].$mArr['destination_path'].$mArr['filename'])) {
			$retData['result']='Y';
			$retData['number']=1;
		}
		return $retData;
	}

	public function doMediaRenameFolder($mArr,$mNum,$cqIndex)
	{
		global $paths;

		$retData= array('cqIndex'=>$cqIndex, 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], 'number'=> 0, 'result'=> 'N') ;

		$full_path = rtrim($paths['destination'].$mArr['destination_path'],'\/');
		if (!is_dir($full_path)) {
			mkdir($paths['destination'].$mArr['destination_path'], 0755, true);
			usleep ( 100000 );
		}
				
		if ( rename($paths['destination'].$mArr['source_path'],$paths['destination'].$mArr['destination_path'])) {
			$retData['result']='Y';
			$retData['number']=1;
		}
		return $retData;
	}

	public function doMediaDeleteFile($mArr,$mNum,$cqIndex)
	{
		global $paths;

		$retData= array('cqIndex'=>$cqIndex, 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], 'number'=> 0, 'result'=> 'N') ;
			if(!is_dir( $paths['destination'].$mArr['source_path'].$mArr['filename'])) {
				if (unlink( $paths['destination'].$mArr['source_path'].$mArr['filename'])) $retData['result']='Y';
			}
		return $retData;
	}

	public function doMediaDeleteFolder($mArr,$mNum,$cqIndex)
	{
		global $paths;

		$retData= array('cqIndex'=>$cqIndex, 'source_path'=> $mArr['source_path'], 'path'=>$paths['destination'], 'number'=> $mNum, 'result'=> 'N') ;
// a:2:{s:11:"source_path";s:16:"1504_sdfdfsdsdf/";s:19:"collection_deletion";i:1;}		
		if ( is_dir( rtrim($paths['destination'].$mArr['source_path'], '\/' ))) {
			$this->deleteAll($paths['destination'].$mArr['source_path'],true);
			if (rmdir($paths['destination'].$mArr['source_path'])) $retData['result']='Y';	
		}else{
			 $retData['result']='Y';
		}
		return $retData;
	}

	public function doUpdateMetadata($mArr,$mNum,$cqIndex)
	{
		global $paths;

		$retData= array('cqIndex'=>$cqIndex, 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], 'number'=> 0, 'result'=> 'Y') ;

		if (file_exists($paths['destination'].$mArr['destination_path'].$mArr['filename']) AND strtolower($fileformat)=="mp3") {
		  # update title ID3 tag in file
		  $TaggingFormat = 'UTF-8';
		  $getID3 = new getID3;
		  getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'write.php', __FILE__);
		  $tagwriter = new getid3_writetags;
		  $tagwriter->filename = $paths['destination'].$mArr['destination_path'].$mArr['filename'];
		  $tagwriter->tagformats = array('id3v2.3', 'ape');
		  $TagData['title'][] = $mArr['metaData']['title'];
		  $TagData['genre'][] = $mArr['metaData']['genere'];
		  $TagData['artist'][] = $mArr['metaData']['author'];
		  $TagData['album'][] = $mArr['metaData']['course_code']." ".$mArr['metaData']['podcast_title'];
		  $TagData['year'][] = date('Y');
		  $TagData['ape']['comments'] = "Item from ".$mArr['metaData']['podcast_title'];
		  $tagwriter->tag_data = $TagData;
		  if ($tagwriter->WriteTags()) {
			# $message.=" and ID3 tags written to $filename</I>";
		  } else {
			$message.=" <I>but was unable to write ID3 tags to $filename</I>";
		  }
		  unset($getID3);
		}

		return $retData;
	}

	public function doSetPermisssions($mArr,$mNum,$cqIndex)
	{
		global $paths;

		$retData= array('cqIndex'=>$cqIndex, 'filename'=> $mArr['filename'], 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], 'number'=> 0, 'result'=> 'N') ;

		$full_path = rtrim($paths['destination'].$mArr['destination_path'],'\/');
		if (!is_dir($full_path)) {
			mkdir($paths['destination'].$mArr['destination_path'], 0755, true);
			usleep ( 100000 );
		}
				
		if ( rename($paths['source'].urlencode($mArr['destination_path'].$mArr['filename']),$paths['destination'].$mArr['destination_path'].$mArr['filename'])) {
			$retData['result']='Y';
			$retData['number']=1;
		}
		return $retData;
	}

	public function doMediaCheckFile($mArr,$mNum,$cqIndex)
	{
		$retData= array('cqIndex'=>$cqIndex, 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], $retData['fileSize']=>0, $retData['fileDate']=>0 ,'number'=> 0, 'result'=> 'N') ;
		if ( is_file( $destination['media'].$mArr['workflow'].$mArr['filename']) ) {
			$retData['result']='Y';
			$retData['fileSize'] = filesize($destination['media'].$mArr['workflow'].$mArr['filename']);
			$retData['fileDate'] = filemtime($destination['media'].$mArr['workflow'].$mArr['filename']);
		}

		return $retData;
	}

	public function doMediaCheckFolder($mArr,$mNum,$cqIndex)
	{
		$retData= array('cqIndex'=>$cqIndex, 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], $retData['folderFiles']=>0 , $retData['fileDate']=>0 ,'number'=> 0, 'result'=> 'N') ;
		if ( is_file( $destination['media'].$mArr['workflow'].$mArr['filename']) ) {
			$retData['result']='Y';
			$retData['folderFiles'] = $this->getFilesFromDir($destination['media'].$mArr['destination_path']);
			$retData['folderDate'] = filemtime($destination['media'].$mArr['destination_path']);
		}

		return $retData;
	}

	public function doStatusMedia($mArr,$mNum,$cqIndex)
	{
		$retData= array('cqIndex'=>$cqIndex, 'status'=> 'ACK', 'number'=> 0, 'result'=> 'Y') ;

		return $retData;
	}

	public function doPollMedia($mArr,$mNum)
	{
		global $mysqli;
		
		$retData = array( 'command'=>'poll-media', 'status'=>'ACK', 'number'=>0, 'timestamp'=>time());

		$result0 = $mysqli->query("	SELECT * 
												FROM queue_commands AS cq 
												WHERE  cq.cq_status = 'Y' 
												ORDER BY cq.cq_time");
		if ($result0->num_rows) {
			$i=0;
			while(	$row0 = $result0->fetch_object()) { 
				$cqIndexData[] = array(	'status'=>$row0->cq_status, 'data'=>unserialize($row0->cq_result), 'cqIndex'=>$row0->cq_cq_index, 'mqIndex'=>$row0->cq_mq_index, 'step'=>$row0->cq_step  );
				$mysqli->query("UPDATE `queue_commands` SET `cq_status`= 'R' where cq_index='".$row0->cq_index."' ");
				$i++;
			}
			if (isset($cqIndexData)) {
				$retData['data']=$cqIndexData; 
				$retData['status']= 'Y';
				$retData['number']= $i;
			} else {
				$retData['data']='Media api - Nothing to do!';
			}
		}
		return $retData;
	}


}
?>