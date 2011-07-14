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

		$retData= array( 
			'command'=>$action, 
			'number'=>'', 
			'data'=>$mArr, 
			'status'=>'N', 
			'error'=>''
			) ;
		$sqlQuery = "INSERT INTO `queue_commands` (`cq_command`, `cq_cq_index`, `cq_mq_index`, `cq_step`, `cq_data`, `cq_time`, `cq_update`, `cq_status`) VALUES ('".$action."','".$cqIndex."','".$mqIndex."','".$step."','".serialize($mArr)."','".date("Y-m-d H:i:s", $timestamp)."', '', 'N')"; 
		$mysqli->query($sqlQuery);
		$error = $mysqli->error;
		if ($error=='') { 
			$retData['status']='ACK';
			$retData['number']=1;
			$retData['error']=''; 
		} else { 
			$retData['status']='N';
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
				$sqlQuery = "UPDATE `queue_commands` SET `cq_update` = '".date("Y-m-d H:i:s", time())."' ,`cq_status`= 'Y', cq_result='".serialize($mArr)."' where cq_index='".$cqIndex."' ";
	//	echo $sqlQuery;
				$result = $mysqli->query($sqlQuery);
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
		$retData= array('cqIndex'=>$cqIndex, 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], 'number'=> 0, 'result'=> 'N') ;
			if( substr( $file, -1 ) == '/' )
				delTree( $file );
			else
				unlink( $file );

		return $retData;
	}

	public function doMediaDeleteFolder($mArr,$mNum,$cqIndex)
	{
		$retData= array('cqIndex'=>$cqIndex, 'folder'=> '', 'path'=> '','number'=> $mNum, 'result'=> 'N') ;

		return $retData;
	}

	public function doMediaUpdateMetadata($mArr,$mNum,$cqIndex)
	{
		$retData= array('cqIndex'=>$cqIndex, 'source_path'=> $mArr['source_path'], 'destination_path'=> $mArr['destination_path'], 'number'=> 0, 'result'=> 'N') ;

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
			$foo = getFilesFromDir($dir);
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

		$sqlQuery0 = "SELECT * FROM queue_commands AS cq WHERE  cq.cq_status = 'Y' ORDER BY cq.cq_time";
//	echo $sqlQuery0;
		$result0 = $mysqli->query($sqlQuery0);
		if ($result0->num_rows) {
			$i=0;
			while(	$row0 = $result0->fetch_object()) { 
				$cqIndexData[] = array(	'status'=>$row0->cq_status, 'data'=>unserialize($row0->cq_result), 'cqIndex'=>$row0->cq_cq_index, 'mqIndex'=>$row0->cq_mq_index, 'step'=>$row0->cq_step  );
				$sqlQuery = "UPDATE `queue_commands` SET `cq_status`= 'R' where cq_index='".$row0->cq_index."' ";
				$result = $mysqli->query($sqlQuery);
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