<?PHP
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  24th May,2011
	#	Test version  
	#	Media interface input controller to accept post requests from the admin server
\*=========================================================================================*/

require_once("./lib/config.php");
require_once("./lib/classes/action-media.class.php");

$mysqli = new mysqli($dbLogin['dbhost'], $dbLogin['dbusername'], $dbLogin['dbuserpass'], $dbLogin['dbname']);

$dataStream = file_get_contents("php://input");

$dataMess=explode('=',urldecode($dataStream));

if ($dataMess[1]!='') {

	$data=json_decode($dataMess[1],true);
//print_r($data);

//	$outObj = new Default_Model_Output_Class();

	$dataObj = new Default_Model_Action_Class($mysqli);	

	$sqlQuery = "SELECT * FROM command_routes AS cr WHERE cr.cr_action = '".$data['command']."'";

// echo 	$sqlQuery;
	$result = $mysqli->query($sqlQuery);
	$row = $result->fetch_object();
	
	if ($result->num_rows) {

		if ($row->cr_route_type=='queue'){
			$m_data = $dataObj->queueAction($data['data'],$data['command'],$data['cqIndex'],$data['mqIndex'],$data['step'],$data['timestamp']);
//			$m_data = array('status'=>'ACK', 'data'=>$data['data'], 'timestamp'=>time());
		
		}else if ($row->cr_route_type=='direct'){
			$m_data = $dataObj->doDirectAction($row->cr_function,$data['data']);
//			$m_data = array('status'=>'ACK', 'data'=>'Direct action requested', 'timestamp'=>time());

		}

	}else{
		$m_data = array('status'=>'NACK', 'data'=>'Command not known!', 'timestamp'=>time());
	}
	

}else{
	$m_data = array('status'=>'NACK', 'data'=>'No request values set!', 'timestamp'=>time());

}
		if (!isset($m_data['status']) || $m_data['status']!='ACK') {
			$sqlLogging = "INSERT INTO `api_log` (`al_message`, `al_reply`, `al_timestamp`) VALUES ( '".urldecode($dataStream)."', '".serialize($m_data)."', '".date("Y-m-d H:i:s", time())."' )";
			$result = $mysqli->query($sqlLogging);
		}

echo json_encode($m_data);

?>