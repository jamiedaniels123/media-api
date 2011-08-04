<?PHP
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  25th May,2011
	#	Test version  
/*========================================================================================*/

//___Debug_________________________________________________________________________________________//

//	ini_set(display_errors,On);

//___DB CONNECTION_________________________________________________________________________________//

$dbLogin = array (
	'dbhost' => "localhost", 
	'dbname' => "media-api", 
	'dbusername' => "in625", 
	'dbuserpass' => "ge5HUQes"
	);

//___TIME ZONE_________________________________________________________________________________//

date_default_timezone_set("Europe/London");

//___FILE PATHs_________________________________________________________________________________//

$paths = array( 
	'source' => '/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/', 
	'destination' => '/data/web/media-podcast-dev.open.ac.uk/www/feeds/'
	);


?>