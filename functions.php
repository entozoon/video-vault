<?php

function clearVideos() {
	global $conn;

	$sql = "TRUNCATE TABLE videos";
	$q = $conn->prepare($sql);
	$q->execute();
	if (!$q) { die("Execute query error, because: ". $conn->errorInfo()); }
}

function getVideos() {
	global $conn;

	$sql = "SELECT * FROM videos";
	$q = $conn->prepare($sql);
	$q->execute();
	if (!$q) { die("Execute query error, because: ". $conn->errorInfo()); }
	$result = $q->fetchAll();
	#print_r($result);
	return $result;
}

function organiseVideos($videos) {
	$organised = [];
	foreach ($videos as $video) {
		if (empty($organised[$video["name"]])) {
			$organised[$video["name"]] = [];
		}
		if (empty($organised[$video["name"]][$video["season"]])) {
			$organised[$video["name"]][$video["season"]] = [];
		}
		if (empty($organised[$video["name"]][$video["season"]][$video["episode"]])) {
			$organised[$video["name"]][$video["season"]][$video["episode"]] =array(
					"path" => $video["path"]
			);
		}
	}
	return $organised;
}

// only run this on page load, rather than saveVideos
function sortVideos($organised) {
	foreach ($organised as $name=>$seasons) {
		ksort($organised[$name]);
		foreach ($organised[$name] as $season=>$ep) {
			ksort($organised[$name][$season]);
			foreach ($organised[$name][$season] as $episode=>$details) {
				ksort($organised[$name][$season][$episode]);
			}
		}
	}
	return $organised;
}


function echoVideos($videos) {
	echo '<ul class="videos">';
	if (!empty($videos)) {
		foreach ($videos as $name=>$season) {
			echo '<li class="videos__show"><span class="toggle">'.$name.'</span><ul class="videos__seasons toggle__content">';
			foreach ($videos[$name] as $season=>$episode) {
				echo '<li class="videos__season"><span class="toggle">Season '.$season.'</span><ul class="videos__episodes toggle__content">';
				foreach ($videos[$name][$season] as $episode=>$details) {
					echo '<li class="videos__episode" data-path="'.$details['path'].'">Episode '.$episode.' - '.$details['path'].'</li>';
				}
				echo '</ul></li>';
			}
			echo '</ul></li>';
		}
	}
	echo '</ul>';
}

function playVideo($path) {
	#echo $path;
	echo shell_exec('start "Opened by Video Vault" "'.$path.'" && exit');
	/*
	$commandString = "start /b c:\\php\\php.EXE C:\\Inetpub\\wwwroot\\mysite.com\\phpforktest.php --passmsg=$testmsg";
	pclose(popen($commandString, 'r'));
	*/
}

?>