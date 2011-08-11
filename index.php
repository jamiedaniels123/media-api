<?PHP
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  24th May,2011
	#	Test version  
	#	Media-api interface input controller to accept post requests from the admin server
\*=========================================================================================*/

require_once("./lib/config.php");
require_once("./lib/classes/action-media.class.php");
// include the ID3 tagging scripts
$TaggingFormat = 'UTF-8';

require_once('./lib/getid3/getid3.php');
// Initialize getID3 engine
$getID3 = new getID3;
$getID3->setOption(array('encoding'=>$TaggingFormat));

// Initialise objects
$mysqli = new mysqli($dbLogin['dbhost'], $dbLogin['dbusername'], $dbLogin['dbuserpass'], $dbLogin['dbname']);
$dataObj = new Default_Model_Action_Class($mysqli, $getID3);	

// Grab the posted input stream and decode
	$dataStream = file_get_contents("php://input");
	$dataMess=explode('=',urldecode($dataStream));
	
	if ($dataMess[1]!='') {
		$data=json_decode($dataMess[1],true);
	
// Check we know this command/action
		$result = $mysqli->query("
			SELECT * 
			FROM command_routes AS cr 
			WHERE cr.cr_action = '".$data['command']."'");
		$row = $result->fetch_object();
		
		if ($result->num_rows) {
// Put the command on the queue
			if ($row->cr_route_type=='queue'){
				$m_data = $dataObj->queueAction($data['data'],$data['command'],$data['cqIndex'],$data['mqIndex'],$data['step'],$data['timestamp']);
			}else if ($row->cr_route_type=='direct'){
				$m_data = $dataObj->doDirectAction($row->cr_function,$data['data']);
			}
		}else{
			$m_data = array('status'=>'NACK', 'data'=>'Command not known!', 'timestamp'=>time());
		}
	}else{
		$m_data = array('status'=>'NACK', 'data'=>'No request values set!', 'timestamp'=>time());
	
	}

// Log the command and response
	if (!isset($m_data['status']) || $m_data['status']!='ACK') {
		$sqlLogging = "
			INSERT INTO `api_log` (`al_message`, `al_reply`, `al_debug`, `al_timestamp`) 
			VALUES ( '".urldecode($dataStream)."', '".serialize($m_data)."', '".ob_get_contents()."', '".date("Y-m-d H:i:s", time())."' )";
		$result = $mysqli->query($sqlLogging);
	}

// Get rid of any debug and output the result to the caller
	ob_clean();
	file_put_contents("php://output", json_encode($m_data));
//	ob_end_clean();

?>