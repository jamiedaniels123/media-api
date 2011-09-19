<?PHP
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  24th May,2011
	#	Test version  
	#	controller to process actions queued in the media_actions table and report status to the admin server
\*=========================================================================================*/

require_once("../lib/config.php");
require_once("../lib/classes/action-media.class.php");
require_once('../lib/getid3/getid3.php');
require_once("../cron.php");

?>