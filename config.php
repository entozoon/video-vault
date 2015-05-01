<?php error_reporting(E_ALL);
require 'functions.php';

$debugging = false;


/*
// must be either the same machine, or the first 9 chars of host and remote match.. i.e. 192.168.1
if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && substr($_SERVER['REMOTE_ADDR'],0,9) != substr($_SERVER['HTTP_HOST'],0,9)) {
	echo 'Video Vault can only be accessed locally, rather than from: '.$_SERVER['REMOTE_ADDR'];
	die();
}
*/


$servername = "localhost";
$database = "video-helper";
$username = "root";
$password = "";

/*
switch($_SERVER["HTTP_HOST"]) {
	// general
	case "localhost:88":
		$password = "";
	break;
	default:
	break;
}
*/
try {
	$conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
	// set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	#echo "Connected successfully";
}
catch(PDOException $e) {
	echo "Connection failed: " . $e->getMessage();
}



$videoFiletypes = ['webm','mkv','flv','vob','ogv','avi','mov','yuv','rm','rmvb','asf','mp4','m4p','m4v','mpg','mp2','mpeg','mpe','mpv','mpg','mpeg','m2v','m4v','3gp','3g2','nsv','h264'];
$videos = [];



set_time_limit(600);

?>