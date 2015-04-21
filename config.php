<?php
error_reporting(E_ALL);

require 'functions.php';
$servername = "localhost";
$database = "video-helper";
$username = "root";
$password = "mysql";


switch($_SERVER["HTTP_HOST"]) {

	// general
	case "localhost:88":
		$password = "";
	break;

	default:
	break;
}

try {
	$conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
	// set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	#echo "Connected successfully";
}
catch(PDOException $e) {
	echo "Connection failed: " . $e->getMessage();
}


$debugging = false;

// Path must be the full, escaped path, e.g. $dir = 'C:\wherever';
#$dir = "\\\\evermore\\unsorted";
#$dir = "unsorted";
$dir = 'E:\www\m.ichael\video-vault\Unsorted';

$videoFiletypes = ['webm','mkv','flv','vob','ogv','avi','mov','yuv','rm','rmvb','asf','mp4','m4p','m4v','mpg','mp2','mpeg','mpe','mpv','mpg','mpeg','m2v','m4v','3gp','3g2','nsv','h264'];
$videos = [];



if ($debugging==true) {
	echo '<pre>';
	array_push($videoFiletypes, 'txt');
}
set_time_limit(600);

?>