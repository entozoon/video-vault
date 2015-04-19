<?php
require 'functions.php';
$servername = "localhost";
$database = "video-helper";
$username = "root";
$password = "mysql";

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

#$dir = "\\\\evermore\\unsorted";
$dir = "unsorted";

$videoFiletypes = ['webm','mkv','flv','vob','ogv','avi','mov','yuv','rm','rmvb','asf','mp4','m4p','m4v','mpg','mp2','mpeg','mpe','mpv','mpg','mpeg','m2v','m4v','3gp','3g2','nsv','h264'];
$videos = [];



if ($debugging==true) {
	echo '<pre>';
	array_push($videoFiletypes, 'txt');
}
set_time_limit(600);

?>