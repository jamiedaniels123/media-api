<?php
# common.inc.php

session_start();

# attempt to increase the max execution time - normally set in php.ini 20100218 CPV
set_time_limit(240); # 4 minutes

error_reporting(E_ALL);
ini_set('display_errors','1');
//ini_set('user_agent','PHP');

# SOAP functions for SAMS service
require_once('nusoap/nusoap.php');

# OU intranet IP addresses - used to disallow access to intranet-only feeds
// BH Mod 20080429 - revised 194.66 range, to limit to 194.66.128.0 -> 194.66.143.255
$ipfilter="137.108.*.*,194.66.128-143.*";

# mySQL database server settings and connection
//$server="localhost";
//$db_name="podcast";
//$username="podcastData";
//$password='87sg34$0fbar4';

$connection=mysql_connect($server,$username,$password); # or die("Could not connect to mySQL server '$server'");
$db=mysql_select_db($db_name,$connection); # or die("Could not select database '$db_name'");
// @mysql_query("SET NAMES 'utf8'",$connection); # important: ensures that all data sumbitted and retrieved from the MySQL database is in UTF-8 format

# contact details for outgoing emails
$sysAdminName="OU Podcast system";
# $sysAdminEmail="b.hawkridge@open.ac.uk";
$sysAdminEmail="podcast-admin@open.ac.uk"; # changed CPV 20090622 to avoid messages getting stuck in Ben's mail filter

# section for uploaded image files (NOT media)
# array of accepted MIME types for file uploads
$mimesAccepted=array("image/pjpeg","image/jpeg","image/png","image/x-png");
# array of icons that represent the above
$mimesIcons=array("jpg.gif","jpg.gif","png.gif","png.gif");

# artwork archive upload MIMEs
$artworkAccepted=array("application/zip","application/x-zip-compressed","application/x-zip");

# accepted media MIME types
# look up between file extension and the corresponding 'correct' MIME type
# not sure if this is the right way to do this - might have to go by file extensions instead
$mimesTypes=array();
$mimesTypes['mp3']="audio/mpeg";
$mimesTypes['m4a']="audio/x-m4a";
$mimesTypes['m4b']="audio/x-m4b";
//$mimesTypes['3gp']="video/3gpp";
$mimesTypes['3gp']="video/x-m4v";
$mimesTypes['mp4']="video/mp4";
$mimesTypes['m4v']="video/x-m4v";
$mimesTypes['mov']="video/quicktime";
$mimesTypes['pdf']="application/pdf";
$mimesTypes['epub']="application/epub+zip";
$mimesTypes['scc']="application/x-rpt";
$mimesTypes['xml']="application/ttml+xml";  // at present assumed to be closed captions in DFXP format


# format type
$formatTypes=array();
$formatTypes['mp3']="audio";
$formatTypes['m4a']="audio";
$formatTypes['m4b']="audio";
$formatTypes['3gp']="video";
$formatTypes['mp4']="video";
$formatTypes['m4v']="video";
$formatTypes['mov']="video";
$formatTypes['pdf']="other";
$formatTypes['epub']="other";
$formatTypes['scc']="cc";  // closed captions
$formatTypes['xml']="cc";  // at present assumed to be closed captions in DFXP format

# media status values
$mediaStatus=array();
$mediaStatus[-2]="transcoder unable to return file";
$mediaStatus[-1]="transcoding failed";
$mediaStatus[0]="no media file";
$mediaStatus[1]="awaiting transcoding choice";
$mediaStatus[2]="transcoding in progress";
$mediaStatus[3]="transcoded but awaiting approval";
$mediaStatus[9]="media available";

// optional media types for feeds (primarily iTunes U related, but also YouTube), used to define the sub directory
// Note: The default feed (ie not in a sub directory) contains all tracks, however unless specified (below) the media folders
//       contain a specific media type and may NOT contain all the tracks if a Podcast is a mix of audio, video and pdf's.

$mediaFolder=array();
$mediaFolder['3gp']="3gp/";               // iTunes U - 3gp video only
$mediaFolder['audio-mp3']="audio/";       // iTunes U - audio only (contains both MP3 and AAC - both m4a and m4b - latter audiobooks)
$mediaFolder['audio-m4a']="audio/";       // iTunes U - audio only (contains both MP3 and AAC - both m4a and m4b - latter audiobooks)
$mediaFolder['audio-m4b']="audio/";       // iTunes U - audio only (contains both MP3 and AAC - both m4a and m4b - latter audiobooks)
$mediaFolder['desktop']="desktop/";       // iTunes U - desktop quality (640 wide) video only
$mediaFolder['hd']="hd/";                 // iTunes U - real HD video only
$mediaFolder['iphone']="iphone/";         // iTunes U - iPhone (wifi) video only (H264 baseline encoded)
$mediaFolder['iphonecellular']="iphone/"; // iTunes U - iPhone 3gp (Edge) video only (H264 encoding)
$mediaFolder['ipod']="ipod/";             // iTunes U - iPod video only (H264 baseline encoded)
$mediaFolder['large']="large/";           // iTunes U - video only (native video dimensions)
$mediaFolder['transcript']="transcript/"; // transcripts of corresponding track entry (for audio and video tracks only)
$mediaFolder['youtube']="youtube/";       // YouTube - encoded for uploading to YouTube, has different trailer
$mediaFolder['extra']="extra/";           // (see note below)
//  BH 20080531 - added 'extra' as a media type, which essentially is a free for all rss feed, ie intended
//                to take any supported media, but expect PDF's mainly

// BH 20090607 - new feed types - note these feed contain ALL tracks in support of changes in iTunes U presentation
//               and in the case of Podcasts to add support for higher quality video options in playback

$mediaFolder['high']="high/";               // Podcast - feed for Higher Quality Podcast files (audio and video)
$mediaFolder['ipod-all']="ipod-all/";       // iTunes U - contains all tracks (inc. none transcript pdf's), where audio and video are intended for iPod's
$mediaFolder['desktop-all']="desktop-all/"; // iTunes U - contains all tracks (inc. none transcript pdf's), where audio and video are 'desktop' quality

// BH 20101023 - added ePub and CoverArt as new media - 
$mediaFolder['epub']="epub/";               // iTunes U - ePub files
$mediaFolder['coverart']="coverart/";       // CoverArt is intended for bulk import as against a distinct media type at this time

// BH 20110519 - added Closed caption support as new media - in two formats, SCC and DFXP (aka TTML)
//  - SCC (.scc) is intended for embedding in MP4 files specifically for Apple QuickTime support on iTunes, iOS etc
//    with the embedding done via at present OS X CLI tool called 'subler'
//  - DFXP (.xml) is an XML based format supported by Flash and other players and could be converted into SRT or
//    embedded into MP4 tracks using the MPEG4 subtitle standard - some Flash players can read this embedded track

$mediaFolder['cc-scc']="closed-captions/";               // SCC based closed Caption files
$mediaFolder['cc-dfxp']="closed-captions/";               // SCC based closed Caption files


// iTunes U Title Suffixes
$iTunesTitleSuffix=array();
$iTunesTitleSuffix['3gp']=" - Mobile video";
$iTunesTitleSuffix['audio-mp3']=" - Audio";
$iTunesTitleSuffix['audio-m4a']=" - Audio";
$iTunesTitleSuffix['audio-m4b']=" - AudioBook";
$iTunesTitleSuffix['desktop']=" - iPad/Mac/PC video";
$iTunesTitleSuffix['hd']=" - HD video";
$iTunesTitleSuffix['iphone']=" - iPhone video";
$iTunesTitleSuffix['iphonecellular']=" - iPhone (Edge) video";
$iTunesTitleSuffix['ipod']=" - iPod/iPhone video";
$iTunesTitleSuffix['large']=" - HQ video";
$iTunesTitleSuffix['transcript']=" - Transcript";
$iTunesTitleSuffix['youtube']=" - YouTube video";
$iTunesTitleSuffix['extra']=" - Extras";
$iTunesTitleSuffix['high']=" - HQ video";
$iTunesTitleSuffix['ipod-all']=" - for iPod/iPhone";
$iTunesTitleSuffix['desktop-all']=" - for iPad/Mac/PC";
$iTunesTitleSuffix['epub']="";

# YouTube channel usernames and passwords - note that each channel requires its own API key
# $youTubeChanUser['KMiUKOU']="c.p.valentine@open.ac.uk"; # email address
# $youTubeChanPass['KMiUKOU']="OUp0dcastTest2011"; # password
# $youTubeChanAPI['KMiUKOU']="AI39si5a2JYuDp6JQKFffQ8VEjmRlFjcp5-4Moly_gSlbkuJeF9UbtWop1whqFciq23FmWcCuZMHMfEg04qO_dzXXxMnZjRaBA"; # API key
# $youTubeChanDefault['KMiUKOU']=false; # which is the default channel
$youTubeChanUser['Corporate']="ouhomeYT@gmail.com";
$youTubeChanPass['Corporate']="obuonline1";
$youTubeChanAPI['Corporate']="AI39si7R7adDOEZM75TyPfdrTxvGL2XYPpZG3byk1Zt54ri4CJwk3FuEBMJOs9odg83hyMx2IIsMOfO1O_c1xe2imsIR6bWedQ";
$youTubeChanDefault['Corporate']=false;
$youTubeChanUser['oulearn']="oulearnYT@gmail.com";
$youTubeChanPass['oulearn']="obuonline1";
$youTubeChanAPI['oulearn']="AI39si6-TH1En-Bmg1o2FU3zLGR8faC-r1DJI9ThtwbIXozLmz4gUPS7Ma7hQKGtupd4XBEjW2zUevFaK72bKxTKH8eMAkd0Xg";
$youTubeChanDefault['oulearn']=true;
$youTubeChanUser['oulife']="oulifeYT@gmail.com";
$youTubeChanPass['oulife']="obuonline1";
$youTubeChanAPI['oulife']="AI39si62LI_D2tX-ExRLskgCPcvJOWFlof_HgJJv1xipck34non0KvM2foNUma-OLEFDKFKHkH4MV6CsGTFgWYbOeSmCmn6LTg";
$youTubeChanDefault['oulife']=false;
$youTubeChanUser['ouresearch']="ouresearchYT@gmail.com";
$youTubeChanPass['ouresearch']="obuonline1";
$youTubeChanAPI['ouresearch']="AI39si6T2IfUaoMRzZWGcjR13Nd5nL8a9OMD2MJIedSPTbfc5pnYGuwLATf9GVFyNd_Gz96A2MK4Qk-EYMNbdwxEvbfmhRc0Cw";
$youTubeChanDefault['ouresearch']=false;

# YouTube version date - so we can restrict the list to files only transcoded after this date
# comparison done on the slightly inaccurately-named uploaded_when field in the podcast_item_media table
$youTubeVerDate="2006-06-15 00:00:00";

# server name (hard wired as there are cases where the domain name switches to server name, cause not traced)
$serverName="http://podcast.open.ac.uk";
$mediaServerName="http://podcast.open.ac.uk";  // used as path to media, to allow moving of media (ie feeds) to alternative servers
$itunesMediaServerName="http://media-podcast.open.ac.uk"; //used to help manage iTunes U content typically stored out on Amazon S3
//$serverName="http://".$_SERVER['SERVER_NAME'];
$webRootPath="/web/ou-podcast01.open.ac.uk/";

# folders for upload and transcoding process via Filechucker
//$upload_path = "/filechucker-files/";  // BH 2010616 moved outside of /web/ which is mounted SAN storage to use the system storage which is fast and is also the same storage that the file in uploaded to
$upload_path = $webRootPath."upload/files/";
$drop_path = $webRootPath."upload/drop/";
$unattached_path=$webRootPath."upload/processed/unattached/";
// $originals_path is intended for files that have additional processing after transcoding, at present this is limited to Closed Captions
// that are 'cleaned' to ensure they are valid - the original file is stored in a sub-directory (closed-captions) off this directory
$originals_path=$webRootPath."upload/processed/originals/";

# ultimate location for podcast images and files - needs a trailing slash
# NOTE that two variables are needed for each
$imagesFolder="/feeds/";
$imagesPath=$webRootPath."feeds/";
$podcastsFolder="/feeds/";   // was /podcast_files/ BH 20080406
$podcastsPath=$webRootPath."feeds/";  // was podcast_files/ BH 20080406
$feedpath="/feeds/"; # folder to which XML and media requests are made
$rss2filename="rss2.xml";
$csvAlbumFilename="album.csv";

$artworkFolder="/artwork/"; # location of ZIP archives of podcast artwork (for Apple)
$artworkPath=$webRootPath."artwork/";

# media size - for player in podcast.php and for re-sized podcast and track images
//$playerWidth=640;
//$playerHeight=360; # doesn't include 20 pixels for controller
$playerWidth=608;
$playerHeight=342; # doesn't include 20 pixels for controller

# items-per-page settings
$perPage=10;
$pageListLimit=5; # maximum number of numbers to display (hard to explain)

// SAMS Information

// check for cookie HS7BDF - extract real name
//
// HS7BDF cookie assumed to be of the form
//   First Name Second Name
//   \####
//
// eg Ben Hawkridge\3840
//
// purpose of number at end unknown
if (isset($_COOKIE['HS7BDF'])) {
  $HS7BDFcookie=$_COOKIE['HS7BDF'];
  $SAMS_fullname=substr($HS7BDFcookie, 0, strpos($HS7BDFcookie, "\\"));
} else {
  $HS7BDFcookie="";
  $SAMS_fullname="";
}

// SAMSCookie assumed to be of the form 
//   40 character session code
//   oucu (variable length) 
//   %2E (.)
//   personal identifier (8 digit student id)
//   %2E (.)
//   staff number (8 digits)
//   %2E (.)
//   tutor number (8 digits)
//   %2E (.)
//
// eg 66bbf4914201b8887381b41855a03ae647c384e1bh6%2E%2E00000000%2E%2E
if (isset($_COOKIE['SAMSsession'])) {
  $SAMScookie=$_COOKIE['SAMSsession'];
  $cookieArray=explode(".",$SAMScookie);
  $SAMS_oucu=substr($cookieArray[0],40);
  $SAMS_pi=$cookieArray[1];
  $SAMS_staffid=$cookieArray[2];
  $SAMS_tutorid=$cookieArray[3];
} else {
  $SAMScookie="";
  $SAMS_oucu="";
  $SAMS_pi="";
  $SAMS_staffid="";
  $SAMS_tutorid="";
}

$SAMSfirstname="";
$SAMSlastname="";
$SAMSemail="";

if (empty($SAMS_oucu)) {
  # echo "<!-- no SAMS cookie -->\n"; # DEBUG
  // assume user either not a SAMS user or has logged off, reset all session variables
  $_SESSION['ses_sams_user']=NULL;
  unset($_SESSION['ses_sams_user']);
  $_SESSION['ses_userid']=NULL;
  $_SESSION['ses_accessLevel']=0;
  $_SESSION['sams_logoff_url']=NULL;
  $_SESSION['ses_fullname']=NULL;
  $_SESSION['ses_email']=NULL;
  $_SESSION['ses_iTunesU']=NULL;
  $_SESSION['ses_YouTube']=NULL;        
  $_SESSION['IDList']=NULL;
} else {
  // currently logged in with an OUCU, check to see if session OUCU exists
  if (isset($_SESSION['ses_sams_user'])) {
    # echo "<!-- ses_sams_user already set -->\n"; # DEBUG
    // previous session OUCU exists, check same as current OUCU, if not update user access based on new OUCU
    if ($_SESSION['ses_sams_user']!=$SAMS_oucu) {
      // look up the new logged-in user
      $_SESSION['ses_sams_user']=$SAMS_oucu;
      $sql="SELECT *,UNIX_TIMESTAMP(last_login) AS last_login FROM users WHERE oucu='".$SAMS_oucu."' LIMIT 1";
      $result=mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
      if (mysql_num_rows($result) > 0) {
        $userRec=mysql_fetch_array($result);
        $_SESSION['ses_userid']=$userRec['id'];
        $_SESSION['ses_fullname']=stripslashes(trim($userRec['firstname']." ".$userRec['lastname']));
        $_SESSION['ses_accessLevel']=$userRec['accessLevel'];
        $_SESSION['ses_email']=$userRec['email'];
        $_SESSION['ses_iTunesU']=$userRec['iTunesU'];
        $_SESSION['ses_YouTube']=$userRec['YouTube'];        
        $_SESSION['IDList']="registered"; # would normally contain eg: staff ID but we don't care so long as its not blank
        // record when user last used system
        $sql="UPDATE users SET last_login='".date('Y-m-d H:i:s')."' WHERE oucu='".$SAMS_oucu."' LIMIT 1";
        # echo "<!-- minor update of user record using $sql -->\n"; # DEBUG
        $result=mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
      } else {
        $_SESSION['ses_sams_user']=NULL;
        unset($_SESSION['ses_sams_user']);
        $_SESSION['ses_userid']=NULL;
        $_SESSION['ses_accessLevel']=0;
        $_SESSION['ses_fullname']=NULL;
        $_SESSION['ses_email']=NULL;  
        $_SESSION['ses_iTunesU']=NULL;
        $_SESSION['ses_YouTube']=NULL;        
        $_SESSION['IDList']=NULL;
      }
    }
  } else {
    # echo "<!-- accessLevel not set - grab OUCU and look up user -->\n"; # DEBUG
    // session OUCU doesn't exist, check user access based on OUCU
    $_SESSION['ses_sams_user']=$SAMS_oucu;
    // look up the logged-in user
    $sql="SELECT *,UNIX_TIMESTAMP(last_login) AS last_login FROM users WHERE oucu='".$SAMS_oucu."' LIMIT 1";
    $result=mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
    
    if (mysql_num_rows($result) > 0) {
      # echo "<!-- user record found -->\n"; # DEBUG
      $userRec=mysql_fetch_array($result);
      $_SESSION['ses_userid']=$userRec['id'];
      // set access level for this user
      $_SESSION['ses_accessLevel']=$userRec['accessLevel'];
      // set whether user has access to iTunes U or YouTube admin channel functions      
      $_SESSION['ses_iTunesU']=$userRec['iTunesU'];
      $_SESSION['ses_YouTube']=$userRec['YouTube'];        
      
      $sqlPart="";
      # grab this user's latest firstname, lastname and email address from the new SAMS webservice
      // Requires SAMS SOAP service
      //
      // Full abilities of SOAP service not know beyond the ability to get separate First and Last names
      // and users primary e-mail address.
    	$proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
    	$proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
    	$proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
    	$proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
    	$client = new soapclient('http://csintra6.open.ac.uk/samswebservices/samsvalidationws/samsvalidationws.asmx?WSDL', true,
    							$proxyhost, $proxyport, $proxyusername, $proxypassword);
    	$err = $client->getError();
    	if ($err) echo '<h2>SAMS Service Constructor error</h2><pre>'.$err.'</pre>';
    	$client->soap_defencoding = 'utf-8';
    	
    	$params = "<ValidateCookie xmlns=\"http://open.ac.uk/SAMSValidationWS\"><sCookie>$SAMScookie</sCookie></ValidateCookie>";
    	$result = $client->call('ValidateCookie', $params);
    	// Check for a fault
    	if ($client->fault) {
    		//
    	} else {
    		// Check for errors
    		$err = $client->getError();
    		if ($err) {
    			// 
    		} else {
    			// save the result in globals
    			if ($result["ValidateCookieResult"]["ValidationReturnCode"]==1) {
    			
    				$SAMSfirstname=trim($result["ValidateCookieResult"]["Forename"]);
    				$SAMSfirstname=utf8_decode($SAMSfirstname);
    				# $SAMSfirstname=addslashes($SAMSfirstname);
    				
    				$SAMSlastname=trim($result["ValidateCookieResult"]["Lastname"]);
    				$SAMSlastname=utf8_decode($SAMSlastname);
    				# $SAMSlastname=addslashes($SAMSlastname);
    
    				$SAMSemail=trim($result["ValidateCookieResult"]["EmailAddress"]);
    				$SAMSemail=utf8_decode($SAMSemail);
    				# $SAMSemail=addslashes($SAMSemail);
            
            # store in session variables
            $_SESSION['ses_fullname']=trim($SAMSfirstname." ".$SAMSlastname);
            $_SESSION['ses_email']=$SAMSemail;
    
            # pipe-delimited list of Staff ID, Student ID, Tutor ID
            # this becomes blank if the user is self-registered - added CPV 20091216
            $IDList=trim($result["ValidateCookieResult"]["IDList"]);
            $_SESSION['IDList']=str_replace('|','',$IDList);
            
            # disable this for now since it seems te email address isn't coming through correctly
            $sqlPart="firstname='".safe($SAMSfirstname)."', lastname='".safe($SAMSlastname)."', email='".safe($SAMSemail)."',";
    			}
    		}
    	}
      
      // record when user last used system
      $sql="UPDATE users SET $sqlPart last_login='".date('Y-m-d H:i:s')."' WHERE oucu='".$SAMS_oucu."' LIMIT 1";
      # echo "<!-- major update of user record using $sql -->\n"; # DEBUG
      $result=mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
    } else {
      # this user not found in user table
      $_SESSION['ses_sams_user']=NULL;
      unset($_SESSION['ses_sams_user']);
      $_SESSION['ses_userid']=NULL;
      $_SESSION['ses_accessLevel']=0;
      $_SESSION['ses_fullname']=NULL;   
      $_SESSION['ses_email']=NULL;
      $_SESSION['ses_iTunesU']=NULL;
      $_SESSION['ses_YouTube']=NULL;        
      $_SESSION['IDList']=NULL;
    }
  }
}

// this session variable is set if sams authentication is done via the podcast site through
// the use of $_SERVER['HTTP_SAMS_LOGOFF_URL'] environment variable.

$logoff_url="";
if (isset($_SESSION['sams_logoff_url'])) {
  $logoff_url=$_SESSION['sams_logoff_url'];
}

# does an official logoff from SAMS then comes back to our own page to clear the session data
if ($logoff_url=="") {
  // make assumption https://msds.open.ac.uk/signon/samsoff.aspx
  $logoff_url="https://msds.open.ac.uk/signon/samsoff.aspx";
}
$default_logoff_url=$logoff_url."?URL=http://podcast.open.ac.uk/signout.php";

if (!isset($message)) $message="";

/*
# cookie to change front page appearance on re-visits - no longer shown so removed CPV 20080506
if (isset($_COOKIE['podcast'])) {
  # cookie exists from previous visit
  $cookieValue=$_COOKIE['podcast'];
  $cookieArray=explode("|",$cookieValue);
  # if a later day
  if (date('Y-m-d') >  date('Y-m-d',$cookieArray[1])) {
    if (!empty($SAMS_fullname)) {
      $lastVisit="Welcome back $SAMS_fullname - your last visit was on ".date("d M Y",$cookieArray[1]);
    } else {
      $lastVisit="Welcome back - your last visit was on ".date("d M Y",$cookieArray[1]);
    }
  } else {
    if (!empty($SAMS_fullname)) {
      $lastVisit="Welcome back $SAMS_fullname to the OU podcast system";
    } else {
      $lastVisit="Welcome back to the OU podcast system";
    }
  }
} else {
  # no cookie set, so set it
  $cookieValue=md5($_SESSION['ses_sams_user'])."|".mktime();
  setcookie("podcast",$cookieValue,time()+3600);
  if (!empty($SAMSfirstname)) {
    $lastVisit="Welcome $SAMS_fullname to the OU podcast system";
  } else {
    $lastVisit="Welcome to the OU podcast system";
  }
}
//$lastVisit.=" (".$_SESSION['ses_accessLevel'].")";
*/

$eol="\r\n";  // for mail headers and .htaccess files
$now=date('His');

# common email headers (based on experimentation by CPV)
$emailheaders = "";
$emailheaders.="From: $sysAdminName <$sysAdminEmail>".$eol;
$emailheaders.="Return-Path: $sysAdminName <$sysAdminEmail>".$eol;    // these two to set reply address
$emailheaders.="Message-ID: <".$now.".TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
$emailheaders.="Organization: The Open University".$eol;
$emailheaders.="X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

# change high ASCII characters not supported by UTF-8 for RSS feeds
function rss_safe($str) {
  # entities
  $str = str_replace("&", "&amp;", $str);
  $str = str_replace("'", "&apos;", $str); # single quote
  $str = str_replace('"', '&quot;', $str); # double quote
  $str = str_replace("<", "&lt;", $str);
  $str = str_replace(">", "&gt;", $str);
  # $str = str_replace('“', '&quot;', $str); # smart open double quote
  # $str = str_replace('”', '&quot;', $str); # smart close double quote
  # $str = str_replace('‘', "&apos;", $str); # smart open single quote
  # $str = str_replace('’', "&apos;", $str); # smart close single quote
  
  return $str;
}

# escape all high ASCII characters safely for XML
function xml_escape($s) {
  $result = '';
  $len = strlen($s);
  for ($i = 0; $i < $len; $i++) {
    if ($s{$i} == '&') {
      $result .= '&amp;';
    } else if ($s{$i} == '<') {
      $result .= '&lt;';
    } else if ($s{$i} == '>') {
      $result .= '&gt;';
    } else if ($s{$i} == '\'') {
      $result .= '&apos;';
    } else if ($s{$i} == '"') {
      $result .= '&quot;';
    } else if (ord($s{$i}) > 127) {
      // skipping UTF-8 escape sequences requires a bit of work
      if ((ord($s{$i}) & 0xf0) == 0xf0) {
        $result .= $s{$i++};
        $result .= $s{$i++};
        $result .= $s{$i++};
        $result .= $s{$i};
      } else if ((ord($s{$i}) & 0xe0) == 0xe0) {
        $result .= $s{$i++};
        $result .= $s{$i++};
        $result .= $s{$i};
      } else if ((ord($s{$i}) & 0xc0) == 0xc0) {
        $result .= $s{$i++};
        $result .= $s{$i};
      }
    } else {
      $result .= $s{$i};
    }
  }
  return $result;
}

# reconstuct category (genre) list from category ids
function buildCategoryList($category,$delimiter='<BR>') {
  global $connection;
  $categoryList="";
  if (!empty($category)) {
    # populate categories field
    $sql="SELECT * FROM categories WHERE id IN (".$category.")";
    $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n$sql<BR>\n");
    while ($row=mysql_fetch_array($result)) {
      $categoryList.=stripslashes($row['category']).$delimiter;
    }
    $categoryList=substr($categoryList,0,-(strlen($delimiter))); # remove last delimiter <BR>
  }
  return $categoryList;
}

# make a shortcode for a track based on filename less extension
function makeShortcode($filename) {
  if (!empty($filename)) {
    $prename=substr($filename,0,strrpos($filename,".")); # filename less extension
    $key=substr(md5($prename),0,10); # create shortcode from first 10 characters of the MD5 string of the filename
    return $key;
  } else {
    return "";
  }
}
# make a full short code based on full media file path - as requested by Nick Freear - CPV 20110506
function makeMD5Shortcode($customid, $filename) {
  if (!empty($customid) && !empty($filename)) {
    $key=md5("http://podcast.open.ac.uk/feeds/".$customid."/".$filename);
    return $key;
  } else {
    return "";
  }
}

function split_url($url, $decode=TRUE) {
  $xunressub     = 'a-zA-Z\d\-._~\!$&\'()*+,;=';
  $xpchar        = $xunressub . ':@%';

  $xscheme       = '([a-zA-Z][a-zA-Z\d+-.]*)';

  $xuserinfo     = '((['  . $xunressub . '%]*)' .
                   '(:([' . $xunressub . ':%]*))?)';

  $xipv4         = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})';

  $xipv6         = '(\[([a-fA-F\d.:]+)\])';

  $xhost_name    = '([a-zA-Z\d-.%]+)';

  $xhost         = '(' . $xhost_name . '|' . $xipv4 . '|' . $xipv6 . ')';
  $xport         = '(\d*)';
  $xauthority    = '((' . $xuserinfo . '@)?' . $xhost .
                   '?(:' . $xport . ')?)';

  $xslash_seg    = '(/[' . $xpchar . ']*)';
  $xpath_authabs = '((//' . $xauthority . ')((/[' . $xpchar . ']*)*))';
  $xpath_rel     = '([' . $xpchar . ']+' . $xslash_seg . '*)';
  $xpath_abs     = '(/(' . $xpath_rel . ')?)';
  $xapath        = '(' . $xpath_authabs . '|' . $xpath_abs .
                   '|' . $xpath_rel . ')';

  $xqueryfrag    = '([' . $xpchar . '/?' . ']*)';

  $xurl          = '^(' . $xscheme . ':)?' .  $xapath . '?' .
                   '(\?' . $xqueryfrag . ')?(#' . $xqueryfrag . ')?$';


  // Split the URL into components.
  if ( !preg_match( '!' . $xurl . '!', $url, $m ) )
    return FALSE;

  if ( !empty($m[2]) )        $parts['scheme']  = strtolower($m[2]);

  if ( !empty($m[7]) ) {
    if ( isset( $m[9] ) )   $parts['user']    = $m[9];
    else            $parts['user']    = '';
  }
  if ( !empty($m[10]) )       $parts['pass']    = $m[11];

  if ( !empty($m[13]) )       $h=$parts['host'] = $m[13];
  else if ( !empty($m[14]) )  $parts['host']    = $m[14];
  else if ( !empty($m[16]) )  $parts['host']    = $m[16];
  else if ( !empty( $m[5] ) ) $parts['host']    = '';
  if ( !empty($m[17]) )       $parts['port']    = $m[18];

  if ( !empty($m[19]) )       $parts['path']    = $m[19];
  else if ( !empty($m[21]) )  $parts['path']    = $m[21];
  else if ( !empty($m[25]) )  $parts['path']    = $m[25];

  if ( !empty($m[27]) )       $parts['query']   = $m[28];
  if ( !empty($m[29]) )       $parts['fragment']= $m[30];

  if ( !$decode )
      return $parts;
  if ( !empty($parts['user']) )
      $parts['user']     = rawurldecode( $parts['user'] );
  if ( !empty($parts['pass']) )
      $parts['pass']     = rawurldecode( $parts['pass'] );
  if ( !empty($parts['path']) )
      $parts['path']     = rawurldecode( $parts['path'] );
  if ( isset($h) )
      $parts['host']     = rawurldecode( $parts['host'] );
  if ( !empty($parts['query']) )
      $parts['query']    = rawurldecode( $parts['query'] );
  if ( !empty($parts['fragment']) )
      $parts['fragment'] = rawurldecode( $parts['fragment'] );
  return $parts;
}


# function to get around the stupid problem that explode creates a one-element array if the string is empty
function myexplode($delim,$string) {
  $string=trim($string);
  if (strlen($string)) {
    $array=explode($delim,$string);
  } else {
    $array=array();
  }
  return $array;
}

# modified version of internal nl2br function that turns all new line characters into <br />
# this avoids extra unnecessary <br>s in lists
function my_nl2br($text) {
  $text=nl2br(stripslashes($text));
  $text=str_replace("</li><br />","</li>",$text);
  $text=str_replace("<ul><br />","<ul>",$text);
  $text=str_replace("</ul><br />","</ul>",$text);
  return $text;
}

function html_tidy($text) {
  $text=nl2br(stripslashes($text));
  # make sure we have a new line after paragraphs
  $text=str_replace('</p>','<br>',$text);
  $text=str_replace('</li><br>','</li>',$text);
  # strip all unwanted tags
  $allowedTags="<b><i><em><u><ul><ol><li><br><a>";
  $text=strip_tags($text, $allowedTags);
  # remove attributes from lists
  $text=eregi_replace('<ul[^>]+>','<ul>',$text);
  $text=eregi_replace('<ol[^>]+>','<ol>',$text);
  $text=eregi_replace('<li[^>]+>','<li>',$text);
  $text=str_replace("&nbsp;"," ",$text);
  # replace hnad-made bullet points
  # $text=str_replace("&middot;","<li>",$text);
  $text=str_replace("<br><br />","<br>",$text);
  $text=str_replace("</li><br />","</li>",$text);
  $text=str_replace("<ul><br />","<ul>",$text);
  $text=str_replace("</ul><br />","</ul>",$text);
  return $text;
}

# check a human-supplied date (in format dd-mm-yyyy or similar) and if valid return it as yyyy-mm-dd
function my_checkdate($strDate) {
  if (ereg('^([0-9]{1,2})[-,/]([0-9]{1,2})[-,/](([0-9]{2})|([0-9]{4}))$', $strDate)) {
    $dateArr = split('[-,/]', $strDate);
    $d=$dateArr[0]; $m=$dateArr[1]; $y=$dateArr[2];
    if (intval($y) < 99) $y=2000+intval($y);
    if (checkdate($m, $d, $y)) {
      $isValid=sprintf("%04d-%02d-%02d", $y, $m, $d);
    } else {
      $isValid="invalid";
    }
  } else {
    $isValid="invalid";
  }
  return $isValid;
}

# check a human-supplied date/time (in format dd-mm-yyyy hh:mm:ss or similar) and if valid return it as yyyy-mm-dd hh:mm:ss
function my_checkDateTime($strDate) {
  if (!empty($strDate)) {
    list($date,$time)=split(' ',$strDate);
    if (ereg('^([0-9]{1,2})[-,/.]([0-9]{1,2})[-,/.](([0-9]{2})|([0-9]{4}))$', $date)) {
      $dateArr = split('[-,/.]', $date);
      $d=$dateArr[0]; $m=$dateArr[1]; $y=$dateArr[2];
      if (intval($y) < 99) $y=2000+intval($y);
      if (checkdate($m, $d, $y)) {
        $isValid=sprintf("%04d-%02d-%02d", $y, $m, $d);
      } else {
        $isValid="invalid";
        return $isValid;
      }
    } else {
      $isValid="invalid";
      return $isValid;
    }
    # now test the time
    if (ereg('^([0-9]{1,2})[-:/.]([0-9]{2})[-:/.]{0,1}([0-9]{0,2})$', $time)) {
      $timeArr = split('[-:.]',$time);
      if ($timeArr[0] > 23 || $timeArr[1] > 60 || $timeArr[2] > 60) {
        $isValid="invalid";
        return $isValid;
      } else {
        $isValid.=" ".$time;
      }
    } else {
      $isValid="invalid";
    }
  } else {
    $isValid="invalid";
  }
  return $isValid;
}

# reverse a human-entered date for database storage
function reverseDate($forDate) {
  list($day,$month,$year)=split('[/.-]',$forDate);
  $revDate=$year."-".$month."-".$day;
  return $revDate;
}

# reverse a human-entered date & time for database storage
function reverseDateTime($forDate) {
  list($date,$time)=split(' ',$forDate);
  list($day,$month,$year)=split('[/.-]',$date);
  $revDate=$year."-".$month."-".$day;
  return $revDate." ".$time;
}

# turn a Y-m-d h:i:s format date/time into a Unix timestamp - for comparisons, etc.
function datetimeToUnix($strDate) {
  if ($strDate=="") {
    return 0;
  }
  list($date,$time)=split(' ',$strDate);
  list($year,$month,$day)=split('[/.-]',$date);
  list($hour,$minute,$second)=split(':',$time);
  return mktime($hour,$minute,$second,$month,$day,$year);
}

# function returns a Unix datestamp from a 'backwards' date/time string
function date_tstamp($datestamp) {
  $tzoffset = 0;
  if ($datestamp == "0000-00-00") {
    $datestamp = "0000-00-00 00:00:00";
  }
  list($date,$time) = explode(" ",$datestamp);
  list($year,$month,$day) = explode("-",$date);
  list($hour,$minute,$second) = explode(":",$time);
  $hour = $hour + $tzoffset;
  $tstamp = mktime($hour,$minute,$second,$month,$day,$year);
  return $tstamp;
}

function secs_to_string ($secs, $long=false) {
  // reset hours, mins, and secs we'll be using
  $hours = 0;
  $mins = 0;
  $secs = intval ($secs);
  $t = array(); // hold all 3 time periods to return as string
  $t['hours'] = 0;
  $t['mins'] = 0;
  $t['secs'] = 0;
  
  // take care of mins and left-over secs
  if ($secs >= 60) {
    $mins += (int) floor ($secs / 60);
    $secs = (int) $secs % 60;
        
    // now handle hours and left-over mins    
    if ($mins >= 60) {
      $hours += (int) floor ($mins / 60);
      $mins = $mins % 60;
    }
    // we're done! now save time periods into our array
    $t['hours'] = (intval($hours) < 10) ? "0" . $hours : $hours;
    $t['mins'] = (intval($mins) < 10) ? "0" . $mins : $mins;
  }

  // what's the final amount of secs?
  $t['secs'] = (intval ($secs) < 10) ? "0" . $secs : $secs;
  
  // decide how we should name hours, mins, sec
  $str_hours = ($long) ? "hour" : "hour";

  $str_mins = ($long) ? "minute" : "min";
  $str_secs = ($long) ? "second" : "sec";

  // build the pretty time string in an ugly way
  $time_string = "";
  $time_string .= ($t['hours']) ? $t['hours'] . " $str_hours" . ((intval($t['hours']) == 1) ? "" : "s") : "";
  $time_string .= ($t['mins']) ? (($t['hours']) ? ", " : "") : "";
  $time_string .= ($t['mins']) ? $t['mins'] . " $str_mins" . ((intval($t['mins']) == 1) ? "" : "s") : "";
  $time_string .= ($t['hours'] || $t['mins']) ? (($t['secs'] > 0) ? ", " : "") : "";
  $time_string .= ($t['secs']) ? $t['secs'] . " $str_secs" . ((intval($t['secs']) == 1) ? "" : "s") : "";

  return empty($time_string) ? 0 : $time_string;
}

# display seconds in  "hh:mm:ss" format
function secs_to_string_compact($secs) {
  // grab the string return by the above function
  // and format begin formatting it
  $str = secs_to_string ($secs);
  if (!$str) return 0;
  $hour_pos = strpos ($str, "hour");
  $min_pos = strpos ($str, "min");
  $sec_pos = strpos ($str, "sec");
  
  $h = ($hour_pos) ? intval (substr ($str, 0, $hour_pos)) : 0;
  $m = ($min_pos) ? intval (substr ($str, $min_pos - 3, $min_pos)) : 0;
  $s = ($sec_pos) ? intval (substr ($str, $sec_pos - 3, $sec_pos)) : 0;
  
  $h = ($h < 10) ? "0" . $h : $h;
  $m = ($m < 10) ? "0" . $m : $m;
  $s = ($s < 10) ? "0" . $s : $s;
  
  return ("$h:$m:$s");
}

function SMPTE_to_secs($smpte_string,$timebase=25) {
  //
  // Expects format HH:MM:SS:FF OR HH:MM:SS.SSS
  // Does not support Drop frames format for NTSC (typical provided as HH:MM:SS.FF)
  // Only support 25 and 30 fps as timebase
  // timebase only required for HH:MM:SS:FF format
  
  $timebase = ($timebase==25 || $timebase==30) ? $timebase : 0;
  if ($timebase==0) {
    // unsuported timebase
    return -1;
  } 
  $smtpe_array=explode(":",$smpte_string);
  $h=0;
  $m=0;
  $s=0;
  //echo $smpte_string." | ".$timebase." | ".count($smtpe_array)." | ";
  if (count($smtpe_array)==4) {
    // assume format HH:MM:SS:FF
    $h=intval($smtpe_array[0]);
    $m=intval($smtpe_array[1]);
    $s=$smtpe_array[2] + $smtpe_array[3] / $timebase;
    //echo $h." | ".$m." | ".$s." | ";
    
  } elseif (count($smtpe_array)==3) {
    // assume format HH:MM:SS.SSS
    $h=intval($smtpe_array[0]);
    $m=intval($smtpe_array[1]);
    $s=floatval($smtpe_array[2]);
  } else {
    // don't recognize the $smpte_time
    return -2;
  }
    
  $seconds=$h*3600+$m*60+$s;
  
  return $seconds;
  
}

function secs_to_SMPTE($seconds,$timebase=0) {
  //
  // Expects format HH:MM:SS:FF OR HH:MM:SS.SSS
  // Does not support Drop frames format for NTSC (typical provided as HH:MM:SS.FF)
  // Only support 25 and 30 fps as timebase
  // timebase only required for HH:MM:SS:FF format - if timebase=0 then assume HH:MM:SS.SSS required
  
  $timebase = ($timebase==25 || $timebase==30) ? $timebase : 0;
    
  $hours = floor($seconds/3600);
  $mins = floor(($seconds-($hours*3600))/60);
  $secs= round(($seconds-($hours*3600)-($mins*60)),3);
  
  if ($timebase==0) {
    // assume HH:MM:SS.SSS required
    $h = ($hours < 10) ? "0".$hours : $hours;
    $m = ($mins < 10) ? "0".$mins : $mins;
    $s = ($secs < 10) ? "0".$secs : $secs;
    $smpte_string=$h.":".$m.":".$s;
  } else {
    $wholeSecs = floor($secs); 
    $frames=round(($secs-$wholeSecs)*$timebase,0);
    $h = ($hours < 10) ? "0".$hours : $hours;
    $m = ($mins < 10) ? "0".$mins : $mins;
    $s = ($wholeSecs < 10) ? "0".$wholeSecs : $wholeSecs;
    $f = ($frames < 10) ? "0".$frames : $frames;
    $smpte_string=$h.":".$m.":".$s.":".$f;
  }
  
  return $smpte_string;
  
}

function utf16_to_utf8($str) {
    $c0 = ord($str[0]);
    $c1 = ord($str[1]);

    if ($c0 == 0xFE && $c1 == 0xFF) {
        $be = true;
    } else if ($c0 == 0xFF && $c1 == 0xFE) {
        $be = false;
    } else {
        return $str;
    }

    $str = substr($str, 2);
    $len = strlen($str);
    $dec = '';
    for ($i = 0; $i < $len; $i += 2) {
        $c = ($be) ? ord($str[$i]) << 8 | ord($str[$i + 1]) : 
                ord($str[$i + 1]) << 8 | ord($str[$i]);
        if ($c >= 0x0001 && $c <= 0x007F) {
            $dec .= chr($c);
        } else if ($c > 0x07FF) {
            $dec .= chr(0xE0 | (($c >> 12) & 0x0F));
            $dec .= chr(0x80 | (($c >>  6) & 0x3F));
            $dec .= chr(0x80 | (($c >>  0) & 0x3F));
        } else {
            $dec .= chr(0xC0 | (($c >>  6) & 0x1F));
            $dec .= chr(0x80 | (($c >>  0) & 0x3F));
        }
    }
    return $dec;
}

# function to make a string safe for mySQL storage
function safe($text) {
  $text=trim($text);
  if (get_magic_quotes_gpc()) $text=stripslashes($text); # strip slashes that might have already been added by magic_quotes_gpc
  $text=mysql_real_escape_string($text);
  return $text;
}

# function to convert a string to UTF-8 and make it safe for mySQL storage
function safeUTF($text) {
  //global $connection;  
  $text=trim(iconv('CP1252','UTF-8//TRANSLIT',$text));
  if (get_magic_quotes_gpc()) $text=stripslashes($text); # strip slashes that might have already been added by magic_quotes_gpc
//  $text=mysql_real_escape_string($text,$connection);
  $text=mysql_real_escape_string($text);
  return $text;
}

# function to give the dimensions an image would have to be to fit inside $w x $h pixels
function image_size($w,$h,$imagePath) {
  if (!file_exists($imagePath)) exit("ERROR: file '".$imagePath."' does not exist");
  $size=getimagesize("$imagePath");
  $theWidth=$size[0];
  $widthStart=$theWidth;
  $theHeight=$size[1];
  $heightStart=$theHeight;
  $w=(double) $w;
  $theWidth=(double) $theWidth;
  $h=(double) $h;
  $theHeight=(double) $theHeight;
  $resized=0;
  $wscaling=$w/$theWidth;
  $hscaling=$h/$theHeight;
  if ($wscaling<1 || $hscaling<1) {
    $resized=1;
  }
  if ($wscaling<$hscaling && $wscaling<1) {
    $theWidth=(int) $w;
    $theHeight=$theHeight*$wscaling;
    $theHeight=(int) $theHeight;
  } else {
    if ($hscaling<1) {
      $theHeight=(int) $h;
      $theWidth=$theWidth*$hscaling;
      $theWidth=(int) $theWidth;
    } else {
      $theWidth=(int) $theWidth;
      $theHeight=(int) $theHeight;
    }
  }
  
  # data returned: array of final size, original size, MIME type, resized flag
  $imageData[0]=$theWidth;
  $imageData[1]=$theHeight;
  $imageData[2]=$widthStart;
  $imageData[3]=$heightStart;
  $imageData[4]=$size['mime'];
  $imageData[5]=$resized;
  return $imageData;
}

# make a new JPG image to fit in given dimensions - uses the function above
function resizeToJPG($sourcePath,$targetPath,$max_width,$max_height) {
  $imageData=image_size($max_width,$max_height,$sourcePath);
  # print "Existing image is of type ".$imageData[4]." and is ".$imageData[2]." x ".$imageData[3]."<BR>\n"; # DEBUG line
  # print "In order to fit into ".$max_width." x ".$max_height.", it will be scaled to be ".$imageData[0]." x ".$imageData[1]."<BR>\n"; # DEBUG line

  # read existing image as data into $image, depending on MIME type
  switch ($imageData[4]) {
    case 'image/gif':
      $image = imagecreatefromgif($sourcePath);
      break;
    case 'image/jpeg':
      $image = imagecreatefromjpeg($sourcePath);
      break;
	  case 'image/pjpeg':
		  $image = imagecreatefromjpeg($sourcePath);
		  break;
    case 'image/png':
      $image = imagecreatefrompng($sourcePath);
      break;
    case 'image/wbmp':
      $image = imagecreatefromwbmp($sourcePath);
      break;
    default:
      exit('Sorry, '.$imageData[4].' images are not supported<br />');
      break;
  }
  
  $resImage=imagecreatetruecolor($imageData[0],$imageData[1]); # new blank image
  $bgc=imagecolorallocate($resImage,255,255,255);
  imagecopyresampled($resImage,$image,0,0,0,0,$imageData[0],$imageData[1],$imageData[2],$imageData[3]);

  # save new image into target path
  imagejpeg($resImage,$targetPath);

  @imagedestroy($resImage);
  return($imageData);
}

# make a thumbnail of an image - for GD Library 2, handles JPG, GIF, PNG and BMP
# pass: original file path, target file path, target width
function makeThumbnail($o_file, $t_file, $t_wd = 100) {
  $image_info = getImageSize($o_file); // see EXIF for faster way
  $o_wd = $image_info[0];
  $o_ht = $image_info[1];
  
  switch ($image_info['mime']) {
    case 'image/gif':
      $o_im = imagecreatefromgif($o_file);
      break;
    case 'image/jpeg':
      $o_im = imagecreatefromjpeg($o_file);
      break;
	  case 'image/pjpeg':
		  $o_im = imagecreatefromjpeg($o_file);
		  break;
    case 'image/png':
      $o_im = imagecreatefrompng($o_file);
      break;
    case 'image/wbmp':
      $o_im = imagecreatefromwbmp($o_file);
      break;
    default:
      exit('Sorry, '.$image_info['mime'].' images are not supported<br />');
      break;
  }
  
  # thumnail height = target width / original width * original height
  $t_ht = round($t_wd / $o_wd * $o_ht);
  $t_im = imageCreateTrueColor($t_wd,$t_ht);
 
  imageCopyResampled($t_im, $o_im, 0, 0, 0, 0, $t_wd, $t_ht, $o_wd, $o_ht);
 
  imageJPEG($t_im, $t_file, 80); # save resized image as $t_file
 
  imageDestroy($o_im);
  imageDestroy($t_im);

  return array($t_wd,$t_ht);
}

# add the OU shield as a watermark to an uploaded image
function addWatermark($imageFile) {
  global $imagesFolder;
  
  $watermark = imagecreatefrompng('images/OU_watermark.png');
  $watermark_width=80;
  $watermark_height=33;
  
  $image = imagecreatefromjpeg($imagesFolder."/thumbs/".$id.".jpg");
  $dest_x = $thumbsize[0] - $watermark_width - 5;  
  $dest_y = $thumbsize[1] - $watermark_height - 5;
  
  $watermarkedPath=$imagesFolder."/thumbs/".$id.".jpg"; # overwrite original thumbnail
  
  imagealphablending($watermark,true);
  imagealphablending($image,true);
  
  imagecopy($image, $watermark, $dest_x, $dest_y, 1, 1, $watermark_width-2, $watermark_height-2);
  imagejpeg($image,$watermarkedPath,100);
  imagedestroy($image);  
  imagedestroy($watermark);
}

function constructPodcastTagSelectSql($private, $intranetOnly, $incTagless, $incTagsArray, $excTagsArray) {

  // Creates SQL for finding all podcasts that match criteria. It is really intended to be used in cases
  // where some form of filtering on tags is required.  Take note that empty $incTagsArray array means include
  // all 'tagged' podcasts.  However passing an invalid array (eg string or null) will result in the clause been
  // excluded.

  // example: we want to show public podcasts ($private="Y"),
  //          both internet and intranet ($intranetOnly="X")
  //          want all podcasts except those tagged 13
  //            => all tagless podcasts ($incTagless="Y")
  //            => all tagged podcasts ($incTagsArray=array())
  //            => excluding those tagged 13 ($excTagsArray=array(13))
  // 
  // constructPodcastTagSelectSql( "N", "X", "Y", array(), array(13) )
  //
  // result equals :
  // SELECT DISTINCT * FROM podcasts WHERE private='N' AND ( NOT EXISTS (SELECT * FROM podcast_tags WHERE podcasts.id=podcast_tags.podcast_id) OR ( EXISTS (SELECT * FROM podcast_tags WHERE podcasts.id=podcast_tags.podcast_id) AND NOT EXISTS (SELECT * FROM podcast_tags WHERE podcasts.id=podcast_tags.podcast_id AND ( tag_id=13))))
  
  // NOTE: the tags arrays is expected to contain id numbers related to the tags DB table.  
  // NOTE: does not include ORDER BY directive to allow this to be added as required
  // NOTE: $private="Y" really means 'unlisted' or 'ex-directory'
  
  $sql="SELECT DISTINCT *, UNIX_TIMESTAMP(CONVERT_TZ(created, '+0:00', 'SYSTEM')) AS cDate, UNIX_TIMESTAMP(CONVERT_TZ(modified_when, '+0:00', 'SYSTEM')) AS mDate FROM podcasts WHERE deleted=0 AND valid_feed='Y'";
  
  // check if $private valid string (N, Y, X) - default to 'N' - X means exclude clause
  $privateSql="";
  if (!is_string($private)) {
    $private = "N";
  }
  if ($private=="N" || $private=="Y") {
    $privateSql=" private='".$private."'";
    $sql.=" AND ".$privateSql;
  }
  
  // check if $intranet valid string (N, Y, X) - default to 'N' - X means exclude clause
  // NOTE: If you are on the intranet then you probably want to use X, so you get both intranet only and public podcasts
  $intranetSql="";
  if (!is_string($intranetOnly)) {
    $intranetOnly = "N";
  }
  if ($intranetOnly=="N" || $intranetOnly=="Y") {
    $sql.=" AND intranet_only='".$intranetOnly."'"; 
  }

  // check if $incTagless valid string (Y, N, X) - default to 'Y' - X means exclude clause (same as N as it happens)
  $incTaglessSql="";
  if (!is_string($incTagless) || $incTagless=="Y") {
    $incTaglessSql=" NOT EXISTS (SELECT * FROM podcast_tags WHERE podcasts.id=podcast_tags.podcast_id)";
  }

  // check if $incTagsArray is valid array, if not exclude clause
  $incTagSql="";
  if (is_array($incTagsArray)) {
    // add clause
    $incTagSql=" EXISTS (SELECT * FROM podcast_tags WHERE podcasts.id=podcast_tags.podcast_id";
    if (count($incTagsArray)>0) {
      $incTagSql.=" AND (";
      foreach ($incTagsArray as $tagid) {
        $incTagSql.=" tag_id=".$tagid." OR";
      }   
      $incTagSql=substr($incTagSql,0,-3); // remove last " OR"
      $incTagSql.=")";
    }
    $incTagSql.=")";
  }
  
  // check if $excTagsArray is valid array, if not exclude clause
  $excTagSql="";
  if (is_array($excTagsArray) && count($excTagsArray)>0) {
    $excTagSql=" NOT EXISTS (SELECT * FROM podcast_tags WHERE podcasts.id=podcast_tags.podcast_id AND (";
    foreach ($excTagsArray as $tagid) {
      $excTagSql.=" tag_id=".$tagid." OR";
    }   
    $excTagSql=substr($excTagSql,0,-3); // remove last " OR"
    $excTagSql.="))";
  }
  
  if ($incTagSql!="" || $excTagSql!="" || $incTaglessSql!="") {
    // determine if any previous clauses added
    if ($privateSql=="" && $intranetSql=="") {
      $sql.=" WHERE ";
    } else {
      $sql.=" AND ";
    }
    // 
    if ($incTagSql!="" && $excTagSql!="") {
      $incexcTagSql=$incTagSql." AND ".$excTagSql;   
    } else {
      $incexcTagSql=$incTagSql.$excTagSql;  // note: one or both of the variables will be empty
    }
    
    if ($incTaglessSql!="") {
      if ($incexcTagSql) {
        // combine tagless with tagged clauses
        $sql.="(".$incTaglessSql." OR (".$incexcTagSql."))";
      } else {
        // just tagless clause needed
        $sql.=$incTaglessSql;      
      }
    } else {
      // no tagless clause, just include tag clauses
      $sql.=$incexcTagSql;
    }
  } else {
    // no tag specific sql require adding
    // (do nothing)
  }
  
  return $sql;
}

# function to return the number of podcast items of a given media type
# pass in the podcast ID, the type as one of audio, video or transcript, and the published flag
function countMedia($id, $mediatype='', $pubFlag='', $processed='Y', $duration=0) {
  global $connection;
  
  switch ($mediatype) {
    case 'audio':
      $extrasql=" AND m.media_type='audio-mp3' AND RIGHT(p.original_filename,4)='.wav'";
      //echo "<tr><td colspan='4'>".$extrasql."</td>";
      break;
    case 'video':
      $extrasql=" AND m.media_type='iphone'"; # could be any one of a number of options here, but iPhone is also used as 'root' media
      break;
    case 'transcript':
      $extrasql=" AND m.media_type='transcript'";
      break;
    case 'extra':
      $extrasql=" AND m.media_type='extra'";
      break;
    case 'epub':
      $extrasql=" AND m.media_type='epub'";
      break;
    case 'youtube':
      $extrasql=" AND m.media_type='youtube'";
      break;
    default:
      $extrasql="";
  }
  
  if ($pubFlag=='Y' || $pubFlag=='N') {
    $extrasql.=" AND p.published_flag='".$pubFlag."'";
  } else {
    // ignore publish flag state
  }
  
  if ($processed=='Y') {
    $extrasql.=" AND m.processed_state=9";
  } else {
    $extrasql.=" AND m.processed_state<9";  
  }
  
  if ($duration > 0) {
    $extrasql.=" AND m.duration<".$duration;      
  }
  
  
  $sql="SELECT p.id FROM podcast_items p LEFT JOIN podcast_item_media m ON p.id=m.podcast_item
        WHERE p.podcast_id=".$id." AND p.title!=''".$extrasql;
    
  $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n".$sql."<BR>\n");
  $trackCount=mysql_num_rows($result);

  //if ($mediatype=='audio') {
  //    echo "<td colspan='9'>".$sql."</td><td >".$row['numTracks']."</td></tr>";
  //}      

  return($trackCount);
}

function getMediaDuration($id, $mediatype='', $pubFlag='Y', $durationFilter=0) {

  // gets the media duration for all tracks in a given podcast ($id), can be filtered on type and whether Published or not.
  // Can also be filtered to exclude files of min. duration, eg only include files over 5 minutes duration. (added to help
  // calculate cost of Closed Captions)

  global $connection;
  
  switch ($mediatype) {
    case 'audio':
      $extrasql=" AND m.media_type='audio-mp3' AND RIGHT(p.original_filename,4)='.wav'";
      //echo "<tr><td colspan='4'>".$extrasql."</td>";
      break;
    case 'video':
      $extrasql=" AND m.media_type='iphone'"; # could be any one of a number of options here
      break;
    case 'transcript':
      $extrasql=" AND m.media_type='transcript'";
      break;
    default:
      $extrasql="";
  }
  
  if ($durationFilter > 0) {
    $extrasql.=" AND p.duration>".$durationFilter;
  }
  
  $sql="SELECT SUM(p.duration) FROM podcast_items p LEFT JOIN podcast_item_media m ON p.id=m.podcast_item
        WHERE p.podcast_id=".$id." AND p.published_flag='".$pubFlag."' AND m.processed_state=9 AND p.title!=''".$extrasql;
    
  $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n".$sql."<BR>\n");
  $row=mysql_fetch_array($result);
  $mediaDuration=$row['SUM(p.duration)'];

  //if ($mediatype=='audio') {
  //    echo "<td colspan='9'>".$sql."</td><td >".$row['numTracks']."</td></tr>";
  //}      

  return($mediaDuration);
}

# count the number of tracks already uploaded to YouTube for a given podcast
function countYouTube($pid) {
  global $connection;
  $sql="SELECT COUNT(id) AS numTracks FROM podcast_items WHERE podcast_id=$pid AND youtube_id!=''";
  $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n".$sql."<BR>\n");
  $row=mysql_fetch_array($result);
  return $row['numTracks'];
}

function sec_to_time($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor($seconds % 3600 / 60);
    $seconds = $seconds % 60;

    //return sprintf("%d:%02d:%02d", $hours, $minutes, $seconds);
    return sprintf("%d:%02d", $hours, $minutes);
}

//              FROM podcast_items p LEFT JOIN podcast_item_media m ON p.id=m.podcast_item
//              WHERE p.podcast_id=$id AND p.published_flag='Y' AND m.processed_state=9 AND p.title!='' AND m.media_type='$hackmediatype'";

# function to save debug info to a text log file in the webroot
# set $echo to true to have the text echoed to screen instead
function appendToLog($text,$echo=false) {
  global $webRootPath, $eol;
  if ($echo==false) {
    $fh=@fopen($webRootPath.'debug.log','a');
    @fwrite($fh,date('Ymd H:i:s').' '.strip_tags($text).$eol);
    @fclose($fh);
  } else {
    echo $text;
  }
}

# function to recursively deleted a directory and the folders below it
# @param string $dir Directory name
# @param boolean $deleteRootToo Delete specified top-level directory as well 
function unlinkRecursive($dir, $deleteRootToo) {

  if (!$dh = @opendir($dir)) return;

  while (false !== ($obj = readdir($dh))) {
    if ($obj == '.' || $obj == '..') continue;
    if (is_file($dir . '/' . $obj)) {
      @unlink($dir . '/' . $obj);
    } else if (is_dir($dir . '/' . $obj)) {
      unlinkRecursive($dir.'/'.$obj, true);
    }
  }
  closedir($dh);
  
  if ($deleteRootToo) {
    @rmdir($dir);
  }
  
  return;
}

function refreshHtaccessForPodcast($id) {
  global $podcastsPath;
  global $eol;
  global $connection;

  if (!isset($id)) {
    return -1;
  }
  
    
  // get *current* values for these
  $sql="SELECT intranet_only, media_location, custom_id FROM podcasts WHERE id=".$id." LIMIT 1";
  $result=@mysql_query($sql,$connection) or die("SQL error:<BR>\n".$sql."<BR>\n");
  if (mysql_num_rows($result) == 0) {
    return -2;
  }
  
  $row=mysql_fetch_array($result);
  $intranet_only=$row['intranet_only'];
  $media_location=$row['media_location'];
  $custom_id=$row['custom_id'];
  
  $podcastDirectory=$podcastsPath.$custom_id;

  // content of .htaccess file created in $out
  $out="";
  
  // work out what to put in .htaccess
  if ($intranet_only=='Y') {
    $out.="#
# restrict access to the OU Intranet only
RewriteEngine On

RewriteCond %{REMOTE_ADDR} !^137\.108\.[0-9]+\.[0-9]+$
RewriteCond %{REMOTE_ADDR} !^194\.66\.1[234][0-9]\.[0-9]+$

RewriteCond %{ENV:SAMS} !^PASSED
RewriteRule ^(.*)$ /feeds-sams/validate.php?file=/feeds/".$custom_id."/$1 [L]".$eol;  // do NOT fix $l it is not a PHP variable
  }
  if ($media_location=='s3all') {
    $out.="#
# all media requests diverted to S3 service
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !.*(xml|jpg)$
RewriteCond %{HTTP_USER_AGENT} !^Jakarta.*$
RewriteRule ^(.*)$ http://media-podcast.open.ac.uk.s3.amazonaws.com/feeds/".$custom_id."/$1 [R=302,NC]".$eol;  // do NOT fix $l it is not a PHP variable
  } else if ($media_location=='s3nonOU') {
    $out.="#
# non-OU media requests diverted to S3 service
RewriteEngine on
RewriteCond %{REMOTE_ADDR} !^137\.108\.[0-9]+\.[0-9]+$
RewriteCond %{REMOTE_ADDR} !^194\.66\.[0-9]\.[0-9]+$
RewriteCond %{REQUEST_FILENAME} !.*(xml|jpg)$
RewriteCond %{HTTP_USER_AGENT} !^Jakarta.*$
RewriteRule ^(.*)$ http://media-podcast.open.ac.uk.s3.amazonaws.com/feeds/".$custom_id."/$1 [R=302,NC]".$eol;  // do NOT fix $l it is not a PHP variable
  
    // BH Mod 20080601 added the following 'condition' to the .htaccess rewrite rule (both s3all and s3nonOU) so that queries
    //                 from Apple's iTunes store didn't fail. This appears to be related to issues with HEAD requests on the
    //                 Amazon S3 service but not certain. Note that Jakarta is probably a generic java http client, so not
    //                 full proof that it's from Apple's iTunes store. Full agent string 'Jakarta Commons-HttpClient/3.1'
    //                 but version may change.
    //
    //                 RewriteCond %{HTTP_USER_AGENT} !\Jakarta.*$
    // BH Mod 20080604 Correction: 'RewriteCond %{REQUEST_FILENAME} !\.(xml|jpg)$' to add ^ at start of regular expression
    //                 Correction: 'RewriteCond %{HTTP_USER_AGENT} !Jakarta.*$' to add ^ at start of regular expression
    //                 Note: Was having strange result, some external requests were not been redirected to S3, some were
  }

  // update or remove .htaccess file
  if ($intranet_only=='N' && $media_location=='local') {
    // not intranet only and local media
    if (file_exists($podcastDirectory.'/.htaccess')) unlink($podcastDirectory.'/.htaccess');
  } else {
    // all other cases, write a new .htaccess file, regardless of current contents
    $fh=@fopen($podcastDirectory.'/.htaccess','w');
    fwrite($fh,$out);
    fclose($fh);
  }
  
  return 0;
}

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
?>