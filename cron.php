<?PHP
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  24th May,2011
	#	Test version  
	#	Media-api chron controller to process queued commands
\*=========================================================================================*/

// Initialise objects
	$mysqli = new mysqli($dbLogin['dbhost'], $dbLogin['dbusername'], $dbLogin['dbuserpass'], $dbLogin['dbname']);
	$dataObj = new Default_Model_Action_Class($mysqli);	

// Get the actions from the queue table

	$result0 = $mysqli->query("	SELECT * 
											FROM queue_commands AS cq, command_routes AS cr 
											WHERE cr.cr_action=cq.cq_command 
											AND cq.cq_status = 'N' 
											ORDER BY cq.cq_time");
	if (isset($result0->num_rows)) {
	
// Process the outstanding commands for each message
		while(	$row0 = $result0->fetch_object()) { 
			$m_data= $dataObj->doQueueAction($row0->cr_function, unserialize($row0->cq_data), $row0->cq_index);	
		}
	}

// Clean up old completed commands

	$mysqli->query("	DELETE FROM `queue_commands` 
							WHERE DATE(cq_time) < date_sub(curdate(), interval 12 hour)  
							AND `cq_status`='R' ");

// Report the status of completed tasks
?>