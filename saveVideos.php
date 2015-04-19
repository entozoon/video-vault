<?php
require 'config.php';



function buildVideoArray($dir) {
	global $videos;
	global $videoFiletypes;
	$files = scandir($dir, SCANDIR_SORT_ASCENDING);

	foreach ($files as $file) {
		if ($file != '.' && $file != '..') {
			if (is_dir($dir.'\\'.$file)) {
				buildVideoArray($dir.'\\'.$file);
			} else {
				$video = [];
				// Store up some tasty info about the video
				$explode = explode('.', $file);
				$video['path'] = $dir.'\\'.$file;
				$video['filetype'] = $explode[count($explode)-1];
				array_pop($explode);
				$video['name'] = implode('.', $explode);

				// replace spaces with .
				$video['fragments'] = str_replace(' ', '.', $video['name']);
				// replace _ with .
				$video['fragments'] = str_replace('_', '.', $video['name']);
				// explode .
				$video['fragments'] = explode('.', $video['fragments']);

				// find episode number...
				$video['episeason'] = $video['season'] = $video['episode'] = '';
				$count = -1;
				foreach ($video['fragments'] as $fragment) {
					$count++;
					if ($count==0) continue; // not going to be at the start of the file
					if (!empty($video['episeason'])) continue;

					// if 104 ..  1021 ..
					if (is_numeric($fragment)) {
						$video['episeason'] = $fragment;
						// 104
						if (strlen($fragment)==3) {
							$video['season'] = substr($fragment, 0, 1);
							$video['episode'] = substr($fragment, 1);
							$video['episeasonPosition'] = $count;
						}
						// 1021
						else if (strlen($fragment)==4) {
							$video['season'] = substr($fragment, 0, 2);
							$video['episode'] = substr($fragment, 2);
							$video['episeasonPosition'] = $count;
						}
					}
					// if s1e4 .. s01e04 s10e21 ..
					else {
						$pattern = '/^S[0-9]+E[0-9]+/';
						if (preg_match($pattern, $fragment)) {
							$thinking = str_replace(array('S','s','E','e'), 'dwa', $fragment);
							$thinking = explode('dwa', $thinking);
							$video['season'] = $thinking[1];
							$video['episode'] = $thinking[2];
							$video['episeasonPosition'] = $count;
						}
					}
				}

				// strip any trailing zeros and such
				if (!empty($video['season'])) $video['season'] = (float)$video['season'];
				if (!empty($video['episode'])) $video['episode'] = (float)$video['episode'];

				// Using the episode position, deduce the show name
				if ($video['episeasonPosition']>0 || !empty($video['fragments'])) {
					$video['name'] = array_splice($video['fragments'], 0, $video['episeasonPosition']);
					$video['name'] = implode(' ',$video['name']);
				}

				// If not a supported filetype, give up and go home
				if (!in_array($video['filetype'], $videoFiletypes)) continue;

				// remove unnecessary stuffs
				unset($video['episeason']);
				unset($video['fragments']);
				unset($video['filetype']);
				unset($video['episeasonPosition']);

				if (!empty($video['name'])) {
					// strip silly chars from name
					$video['name'] = str_replace(array("'",'-'),'',$video['name']);

					// make it case insensitive
					$video['name'] = ucwords(strtolower($video['name']));

					// If we're golden, push to videos array
					array_push($videos, $video);
				}
			}
		}
	}
}

function organiseAndSaveVideos() {
	global $videos;

	$organised = organiseVideos($videos);

	// Trying to optimise by removing the ksorts at this point,
	// letting it put them into the database randomly and letting mysql worry
	// Sort seasons and episodes by their key
	foreach ($organised as $name=>$seasons) {
		#ksort($organised[$name]);
		foreach ($organised[$name] as $season=>$ep) {
			#ksort($organised[$name][$season]);
			foreach ($organised[$name][$season] as $episode=>$details) {
				#ksort($organised[$name][$season][$episode]);
				insertVideoIntoDatabase($name, $season, $episode, $details['path']);
			}
		}
	}

	$videos = $organised;
	unset($organised);
}

function insertVideoIntoDatabase($name, $season, $episode, $path) {
	global $conn;

	// check if it already exists
	$sql = "SELECT `name` FROM videos
		WHERE `name` = :name
		AND `season` = :season
		AND `episode` = :episode";
	$q = $conn->prepare($sql);
	$q->bindParam(':name', $name, PDO::PARAM_STR);
	$q->bindParam(':season', $season, PDO::PARAM_STR);
	$q->bindParam(':episode', $episode, PDO::PARAM_STR);
	$q->execute();
	if (!$q) { die("Execute query error, because: ". $conn->errorInfo()); }
	$result = $q->fetchAll();
	#var_dump($result);

	// Insert a new entry
	if (empty($result)) {
		//$sql = "INSERT INTO videos (id,name,season,episode,path,watched)
		//	VALUES (NULL,:name,:season,:episode,:path,NULL)
		//	ON DUPLICATE KEY UPDATE name = values(name)";
		//$sql = "INSERT INTO videos (id,name,season,episode,path,watched)
		//	VALUES (NULL,:name,:season,:episode,:path,NULL)
		//	if NOT EXISTS (select `name` from videos where `name` = :nameduplicate) LIMIT 1";
		$sql = "INSERT INTO videos (id,name,season,episode,path,watched)
			VALUES (NULL,:name,:season,:episode,:path,NULL)";
		#echo $sql;
		$q = $conn->prepare($sql);
		$q->bindParam(':name', $name, PDO::PARAM_STR);
		$q->bindParam(':season', $season, PDO::PARAM_STR);
		$q->bindParam(':episode', $episode, PDO::PARAM_STR);
		$q->bindParam(':path', $path, PDO::PARAM_STR);
		$q->execute();
		if (!$q) { die("Execute query error, because: ". $conn->errorInfo()); }
	}
}

buildVideoArray($dir);
organiseAndSaveVideos();