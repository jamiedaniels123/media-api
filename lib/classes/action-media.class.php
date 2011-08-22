<?php
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  20th Feb,2011
	#	Test version  
	#  Class File to handle file service actions and provide responses.
\*=========================================================================================*/

class Default_Model_Action_Class
 {
    protected $m_mysqli;
	
	/**  * Constructor  */
    function Default_Model_Action_Class($mysqli){
		$this->m_mysqli = $mysqli;
	}  

    function PsExec($commandJob) {

        $command = $commandJob.' > /dev/null 2>&1 & echo $!';
        exec($command ,$op);
        $pid = (int)$op[0];
//		print_r ($op);
        if($pid!="") return $pid;

        return false;
    }

//---------The methods for background process management ----------------------------------------------------------------------

    function PsExists($pid) {

        exec("ps ax | grep $pid 2>&1", $output);

        while( list(,$row) = each($output) ) {

                $row_array = explode(" ", $row);
                $check_pid = $row_array[0];

                if($pid == $check_pid) {
                        return true;
                }
        }

        return false;
    }

    function PsKill($pid) {
        exec("kill -9 $pid", $output);
    }

	public function startCheckProcess($apCommand, $cq_index) {

	global $timeout;
	
// Check poll process and launch if not running. The Poll process polls both Media and Encoder APIs for completed tasks.
		$result0 = $this->m_mysqli->query("
			SELECT ap_process_id, ap_cq_index, ap_script, ap_status, DATEDIFF(minute, ap_timestamp, ap_last_checked) as ap_time
			FROM api_process 
			WHERE ap_status = 'Y' 
			ORDER BY ap_timestamp DESC");
		$j=0;
		if ($result0->num_rows >=1) {
			while(	$row0 = $result0->fetch_object()) {
				if ($row0->ap_cq_index == $cq_index) $j=1;
				if ($this->PsExists($row0->ap_process_id)) {
					if ($row->ap_time < $timout) {
						$this->m_mysqli->query("
							UPDATE `api_process` 
							SET `ap_status`='Y', `ap_last_checked`='".date("Y-m-d H:i:s", time())."' 
							WHERE `ap_process_id`=  '".$row0->ap_process_id."' ");
					} else {
						$this->PsKill($row0->ap_process_id);
						$this->m_mysqli->query("
							UPDATE `api_process` 
							SET `ap_status`='N', `ap_last_checked`='".date("Y-m-d H:i:s", time())."' 
							WHERE `ap_process_id`=  '".$row0->ap_process_id."' ");
					}
				} else  {
						$this->m_mysqli->query("
							UPDATE `api_process` 
							SET `ap_status`='N', `ap_last_checked`='".date("Y-m-d H:i:s", time())."' 
							WHERE `ap_process_id`=  '".$row0->ap_process_id."' ");
				}
			}
		}
		if ($j==0) {
				$processID=$this->PsExec($apCommand);
				if ($processID==false) $status='N'; else $status='Y';  
				$result = $this->m_mysqli->query("
					INSERT INTO `api_process` (`ap_process_id`, `ap_cq_index`, `ap_script`, `ap_timestamp`, `ap_status`) 
					VALUES ( '".$processID."',  '".$cq_index."',  '".$apCommand."', '".date("Y-m-d H:i:s", time())."', '".$status."' )");
		}

	}

//---------The basic methods for file management ----------------------------------------------------------------------

	function recurse_copy($src,$dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	} 	
	
	function delTree($dir) {
		$files = glob( $dir . '*', GLOB_MARK );
		foreach( $files as $file ){
			if( substr( $file, -1 ) == '/' )
				$this->delTree( $file );
			else
				unlink( $file );
		}
	   
		if (is_dir($dir)) rmdir( $dir );
   
	} 
		
	public function read_folder_directory($dir = "root_dir/dir"){

		$listDir = array();
		if($handler = opendir($dir)) {
			while (($sub = readdir($handler)) !== FALSE) {
				if ($sub != "." && $sub != ".." && $sub != "Thumb.db" && $sub != "Thumbs.db") {
					if(is_file($dir."/".$sub)) {
						$listDir[] = array("file"=>$sub, "size"=>filesize ($dir."/".$sub), "date"=>fileatime ($dir."/".$sub));
					}elseif(is_dir($dir."/".$sub)){
						$listDir[$sub] = $this->read_folder_directory($dir."/".$sub);
					}
				}
			}
			closedir($handler);
		}
		return $listDir;
	} 

// ------ Managing actions ----------------------------------------------------------------------------------

	public function queueAction($mArr,$action,$cqIndex,$mqIndex,$step,$timestamp)
	{	

		$retData= array( 	'command'=>$action, 'number'=>'', 'data'=>$mArr, 'status'=>'NACK', 'error'=>'' ) ;

		$this->m_mysqli->query("
			INSERT INTO `queue_commands` (`cq_command`, `cq_cq_index`, `cq_mq_index`, `cq_step`, `cq_data`, `cq_time`, `cq_update`, `cq_status`) 
			VALUES ('".$action."','".$cqIndex."','".$mqIndex."','".$step."','".json_encode($mArr)."','".date("Y-m-d H:i:s", $timestamp)."', '', 'N')");
		$error = $this->m_mysqli->error;
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

	public function doQueueAction($function, $mArr, $cqIndex, $cqCqIndex)
	{			

			$retData = $this->$function($mArr, 1, $cqCqIndex);
			if ($retData['result']=='Y' || $retData['result']=='F') {
				$result = $this->m_mysqli->query("
					UPDATE `queue_commands` 
					SET `cq_update` = '".date("Y-m-d H:i:s", time())."' ,`cq_status`= '".$retData['result']."', cq_result='".json_encode($retData)."' 
					WHERE cq_index='".$cqIndex."' ");
			}
	}

	public function doDirectAction($function, $mArr)
	{

			$retData = $this->$function($mArr,1);
		return $retData;
	}

// ------ The actions from commands ----------------------------------------------------------------------------------

	public function doMediaMoveFile($mArr, $mNum, $cqIndex)
	{

		global $paths, $debug;

		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';
		$nameArr = pathinfo($mArr['source_filename']);

		$dest_path = $paths['destination'].$mArr['destination_path'];
		$dest_file_path = $dest_path.$mArr['destination_filename'];
		$src_file_path = $paths['source'].$cqIndex."_".urlencode($mArr['source_path'].$mArr['source_filename']);
		$t_dest_path = rtrim($dest_path,'\/');
		
		if (!is_dir($t_dest_path)) {
			mkdir( $t_dest_path, 0755, true);
			usleep ( 100000 );
		}
		if (is_file( $dest_file_path )) {
			unlink( $dest_file_path );
			usleep ( 100000 );
		}
		if ( rename( $src_file_path, $dest_file_path)) {
			$retData['debug'] = $src_file_path." to ".$dest_file_path;
			$retData['result']='Y';
			$retData['number']=1;
		} else {
			$retData['error'] = $src_file_path." to ".$dest_file_path;
			$retData['result']='F';
			$retData['number']=0;
		}
		return $retData;
	}

	public function doMediaRenameFile($mArr, $mNum, $cqIndex)
	{
		global $paths;

		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';

		$src_path = $paths['destination'].$mArr['source_path'];
		$dest_path = $paths['destination'].$mArr['destination_path'];
		$src_file_path = $src_path.$mArr['source_filename'];
		$dest_file_path = $dest_path.$mArr['destination_filename'];
		$t_dest_path = rtrim($dest_path,'\/');
		
		if (!is_dir($t_dest_path )) {
			mkdir($dest_path, 0755, true);
			usleep ( 100000 );
		}
				
		if ( rename( $src_file_path, $dest_file_path)) {
			$retData['result']='Y';
			$retData['number']=1;
		} else {
			$retData['result']='F';
			$retData['number']=1;
		}
		return $retData;
	}

	public function doMediaRenameFolder($mArr, $mNum, $cqIndex)
	{
		global $paths;

		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';

		$src_path = $paths['destination'].$mArr['source_path'];
		$dest_path = $paths['destination'].$mArr['destination_path'];
		$t_dest_path = rtrim($dest_path,'\/');

		if (is_dir($t_dest_path)) {
			$retData['result']='F';
			$retData['number']=0;
			$retData['error']="Folder ".$dest_path." already exists!";
		} else {
			if ( rename( $src_path, $dest_path)) {
				$retData['result']='Y';
				$retData['number']=1;
			} else {
				$retData['result']='F';
				$retData['number']=1;
			}
		}
		return $retData;
	}

	public function doMediaDeleteFile($mArr, $mNum, $cqIndex)
	{
		global $paths;

		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';

		$dest_path = $paths['destination'].$mArr['destination_path'];
		$dest_file_path = $dest_path.$mArr['destination_filename'];

		if(is_file( $dest_file_path)) {
			if (unlink( $dest_file_path)){  
				$retData['result']='Y';
				$retData['number']=1;
			} else {
				$retData['result']='F';
				$retData['number']=0;
				$retData['error']="Delete fail of ".$dest_file_path." !";
			}
		} else {
				$retData['result']='Y';
				$retData['number']=0;			
				$retData['error']="No file ".$dest_file_path." !";
		}
		return $retData;
	}

	public function doMediaDeleteFolder($mArr, $mNum, $cqIndex)
	{
		global $paths;

		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';

		$dest_path = $paths['destination'].$mArr['destination_path'];
		$t_dest_path = rtrim($dest_path,'\/');

		if ( is_dir( $t_dest_path && rtrim($mArr['destination_path'],'\/')!='')) {
			$this->delTree($t_dest_path);
			if ( !is_dir($t_dest_path)) {
				$retData['result']='Y';	
				$retData['number']=1;
			} else {
				$retData['result']='F';
				$retData['number']=0;
				$retData['error']="Delete fail of ".$dest_path." !";
			}
		}else{
			$retData['result']='Y';
			$retData['number']=0;
			$retData['error']="No folder ".$dest_path." !";
		}
		return $retData;
	}

	public function doMediaCopyFolder($mArr, $mNum, $cqIndex)
	{
		global $paths;

		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';

		$src_path = $paths['destination'].$mArr['source_path'];
		$dest_path = $paths['destination'].$mArr['destination_path'];
		$t_dest_path = rtrim($dest_path,'\/');

		if (is_dir($t_dest_path)) {
			$retData['result']='F';
			$retData['number']=0;
			$retData['error']="Folder ".$full_path." already exists!";
		} else {
			$this->recurse_copy(rtrim($src_path,'\/'),$t_dest_path);
			if (is_dir(rtrim($dest_path,'\/'))){
				$retData['folderArr'] = $this->read_folder_directory($t_dest_path);
				$retData['result']='Y';
				$retData['number']=1;
			} else {
				$retData['result']='F';
				$retData['number']=0;
				$retData['error']="No folder created ".$dest_path." !";
			}
			
		}
				
		return $retData;
	}

	public function doMediaUpdateMetadata($mArr, $mNum, $cqIndex)
	{
		global $debug,$paths,$getID3;

		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';
		$arrTemp=json_encode($mArr);
// error_log("nameArr.path =".$arrTemp);  // debug

		$dest_path = $paths['destination'].$mArr['destination_path'];
		$dest_file_path = $dest_path.$mArr['destination_filename'];
		$nameArr = pathinfo($mArr['destination_filename']);
// error_log("Filepath = ".$arrTemp);  // debug
		
		if (file_exists($dest_file_path) && isset($nameArr['extension']) && strtolower($nameArr['extension'])=="mp3") {
		  # update title ID3 tag in file
		  $TaggingFormat = 'UTF-8';
		  $tagwriter = new getid3_writetags;
		  $tagwriter->filename = $dest_file_path;
//		  if (!chmod($dest_file_path, 0755)) $tagwriter->errors[] = "Could not set write permissions on file";
		  $tagwriter->remove_other_tags = true;
		  $tagwriter->tagformats = array('id3v2.3', 'ape');
		  $TagData['title'][] = $mArr['meta_data']['title'];
		  $TagData['genre'][] = $mArr['meta_data']['genre'];
		  $TagData['artist'][] = $mArr['meta_data']['author'];
		  $TagData['album'][] = $mArr['meta_data']['course_code']." ".$mArr['meta_data']['podcast_title'];
		  $TagData['year'][] = date('Y');
		  $TagData['ape']['comments'] = $mArr['meta_data']['comments'];
		  $tagwriter->tag_data = $TagData;
		  if ($tagwriter->WriteTags()) {
			$retData['result']='Y';
			$retData['number']=1;
		  } else {
			$retData['result']='F';
			$retData['number']=1;
			$retData['debug'] = rawurlencode(serialize($tagwriter->errors));
		  }
		}

		return $retData;
	}

	public function doMediaYoutubeUpload($mArr,$mNum,$cqIndex)
	{
		global  $timeout,$paths;
		
		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 1;
		$retData['result'] = 'N';

// Check and/or start 2s polling process
		$apCommand="curl -d \"number=1&time=".$timeout."\" ".$paths['media-api']."youtube_upload.php";	
		$this->startCheckProcess($apCommand,$cqIndex); 

		return $retData;
	}

	public function doMediaYoutubeUpdate($mArr,$mNum,$cqIndex)
	{
		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 1;
		$retData['result'] = 'Y';

		return $retData;
	}


	public function doSetPermisssions($mArr,$mNum,$cqIndex)
	{
		global $paths;

		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';

		$folder_path = $paths['destination'].$mArr['destination_path'];
		$file_path = $folder_path.$mArr['destination_filename'];
		$short_path = $mArr['destination_path'].$mArr['destination_filename'];
		$t_dest_path = rtrim($dest_path,'\/');
		
		if (!is_dir($t_dest_path)) {
			mkdir($folder_path, 0665, true);
			usleep ( 100000 );
		}
				
		if ( rename($paths['source'].urlencode($short_path),$file_path)) {
			$retData['result']='Y';
			$retData['number']=1;
		  } else {
			$retData['result']='F';
			$retData['number']=1;
		}
		return $retData;
	}

	public function doMediaCheckFile($mArr,$mNum,$cqIndex)
	{
		global $paths;

		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';

		$dest_path = $paths['destination'].$mArr['destination_path'];
		$dest_file_path = $dest_path.$mArr['workflow'].$mArr['destination_filename'];

		if ( is_file($dest_file_path) ) {
			$retData['result']='Y';
			$retData['fileSize'] = filesize($dest_file_path);
			$retData['fileDate'] = filemtime($dest_file_path);
		} else {
			$retData['result']='F';
			$retData['number']=0;
			$retData['error']="File ".$dest_file_path." not found!";
		}

		return $retData;
	}

	public function doMediaCheckFolder($mArr,$mNum,$cqIndex)
	{
		global $paths;
		
		$retData=$mArr;
		$retData['cqIndex'] = $cqIndex;
		$retData['number'] = 0;
		$retData['result'] = 'N';

		$dest_path = $paths['destination'].$mArr['destination_path'];
		$t_dest_path = rtrim($dest_path,'\/');

		if (is_dir($t_dest_path)) {
			$retData['result']='Y';
			$retData['folderArr'] = $this->read_folder_directory($t_dest_path);
		} else {
			$retData['result']='Y';
			$retData['number']=0;
		}

		return $retData;
	}

	public function doStatusMedia($mArr,$mNum,$cqIndex)
	{
		$retData= array('cqIndex'=>$cqIndex, 'number'=> 0, 'result'=> 'Y') ;

		return $retData;
	}

	public function doPollMedia($mArr,$mNum)
	{
		
		$retData = array( 'command'=>'poll-media', 'status'=>'ACK', 'number'=>0, 'timestamp'=>time());

		$result0 = $this->m_mysqli->query("
			SELECT * 
			FROM queue_commands AS cq 
			WHERE  cq.cq_status IN ('Y','F') 
			ORDER BY cq.cq_time");
		if ($result0->num_rows) {
			$i=0;
			while(	$row0 = $result0->fetch_object()) { 
				$cqIndexData[$i] = json_decode($row0->cq_result, true);
				$cqIndexData[$i]['status']= $row0->cq_status;
				$cqIndexData[$i]['cqIndex']= $row0->cq_cq_index;
				$cqIndexData[$i]['mqIndex']= $row0->cq_mq_index;
				$cqIndexData[$i]['step']= $row0->cq_step;
				$this->m_mysqli->query("
					UPDATE `queue_commands` 
					SET `cq_status`= 'R' where cq_index='".$row0->cq_index."' ");
				$i++;
			}
			if (isset($cqIndexData)) {
				$retData['data']=$cqIndexData; 
				$retData['status']= 'Y';
				$retData['number']= $i+1;
// error_log("Error = ".json_encode($retData));  // debug
			} else {
				$retData['data']='Media api - Nothing to do!';
			}
		}
		return $retData;
	}


}
?>