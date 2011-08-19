<?PHP
/*========================================================================================*\
	#	Coder    :  Ian Newton
	#	Date     :  25th May,2011
	#	Test version  
/*========================================================================================*/

//___Debug_________________________________________________________________________________________//

// ini_set('display_errors', 1);

//___DB CONNECTION_________________________________________________________________________________//

$dbLogin = array (
	'dbhost' => "localhost", 
	'dbname' => "media-api-dev", 
	'dbusername' => "in625", 
	'dbuserpass' => "ge5HUQes"
	);

//___TIME ZONE_________________________________________________________________________________//

date_default_timezone_set("Europe/London");

$timeout = 600;

//___API_NAME_________________________________________________________________________________//

	$apiName = "media-api";
	$version = "dev";

//___FILE PATHs_________________________________________________________________________________//

$paths = array( 
	'server-path' => '/data/web/media-podcast-api-dev.open.ac.uk/www/', 
	'source' => '/data/web/media-podcast-api-dev.open.ac.uk/file-transfer/destination/', 
	'destination' => '/data/web/media-podcast-dev.open.ac.uk/www/feeds/',
	'media-api' => 'http://media-podcast-api-dev.open.ac.uk/'
	);

//___YOU TUBE CHANNEL CONNECTIONS _________________________________________________________________________________//

# YouTube channel usernames and passwords - note that each channel requires its own API key
# $youTubeChanUser['KMiUKOU']="c.p.valentine@open.ac.uk"; # email address
# $youTubeChanPass['KMiUKOU']="OUp0dcastTest2011"; # password
# $youTubeChanAPI['KMiUKOU']="AI39si5a2JYuDp6JQKFffQ8VEjmRlFjcp5-4Moly_gSlbkuJeF9UbtWop1whqFciq23FmWcCuZMHMfEg04qO_dzXXxMnZjRaBA"; # API key
# $youTubeChanDefault['KMiUKOU']=false; # which is the default channel

$youTubeChanUser = array (
	'Corporate' => "ouhomeYT@gmail.com", 
	'oulearn' => "oulearnYT@gmail.com",
	'oulife' => "oulifeYT@gmail.com",
	'ouresearch' => "ouresearchYT@gmail.com",
	'outest' => "oupodcasttest@gmail.com"
	);
$youTubeChanPass = array (
	'Corporate' =>"obuonline1",
	'oulearn' => "obuonline1",
	'oulife' => "obuonline1",
	'ouresearch' => "obuonline1",
	'outest' => "cupoftea"
	);
$youTubeChanAPI = array (
	'Corporate' => "AI39si7R7adDOEZM75TyPfdrTxvGL2XYPpZG3byk1Zt54ri4CJwk3FuEBMJOs9odg83hyMx2IIsMOfO1O_c1xe2imsIR6bWedQ",
	'oulearn' => "AI39si6-TH1En-Bmg1o2FU3zLGR8faC-r1DJI9ThtwbIXozLmz4gUPS7Ma7hQKGtupd4XBEjW2zUevFaK72bKxTKH8eMAkd0Xg",
	'oulife' => "AI39si62LI_D2tX-ExRLskgCPcvJOWFlof_HgJJv1xipck34non0KvM2foNUma-OLEFDKFKHkH4MV6CsGTFgWYbOeSmCmn6LTg",
	'ouresearch' => "AI39si6T2IfUaoMRzZWGcjR13Nd5nL8a9OMD2MJIedSPTbfc5pnYGuwLATf9GVFyNd_Gz96A2MK4Qk-EYMNbdwxEvbfmhRc0Cw",
	'outest' => "AI39si6TfhbLcUmajNNoAlULfgK_I8nD9y3zV1BuMEFq75s3G7kltpfhsgy1VR3ZhUcgx5u1DCuYdgr3B5SvRVlunE4n6P5bbg"
	);
$youTubeChanDefault = array (
	'Corporate' => false,
	'oulearn' => true,
	'oulife' => false,
	'ouresearch' => false,
	'outest' => false
	);

# YouTube version date - so we can restrict the list to files only transcoded after this date
# comparison done on the slightly inaccurately-named uploaded_when field in the podcast_item_media table
$youTubeVerDate="2006-06-15 00:00:00";



?>