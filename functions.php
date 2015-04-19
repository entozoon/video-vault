<?php

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

?>