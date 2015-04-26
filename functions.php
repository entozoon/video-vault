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

	$sql = "SELECT * FROM videos ORDER BY name";
	$q = $conn->prepare($sql);
	$q->execute();
	if (!$q) { die("Execute query error, because: ". $conn->errorInfo()); }
	$result = $q->fetchAll();
	#print_r($result);
	return $result;
}


// only run this on page load, rather than saveVideos
function sortVideos($organised) {
	foreach ($organised as $name=>$seasons) {
		ksort($organised[$name]); // organises seasons (within $organised[$name])
		foreach ($organised[$name] as $season=>$ep) {
			ksort($organised[$name][$season]); // organise shows
			/*foreach ($organised[$name][$season] as $episode=>$details) {
				ksort($organised[$name][$season][$episode]);
			}*/
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
					$classes = 'videos__episode';
					if ($details['watched']==1) $classes .= ' videos__episode--watched';
					echo '<li class="'.$classes.'" data-id="'.$details['id'].'" data-path="'.$details['path'].'">Episode '.$episode.'</li>';
				}
				echo '</ul></li>';
			}
			echo '</ul></li>';
		}
	}
	echo '</ul>';
}

function setWatched($id) {
	global $conn;

	$sql = "UPDATE videos SET watched=1 WHERE id=$id";
	$q = $conn->prepare($sql);
	$q->execute();
	if (!$q) { die("Execute query error, because: ". $conn->errorInfo()); }
	return true;
}

function playVideo($path, $id) {
	setWatched($id);

	#echo $path;
	echo shell_exec('start "Opened by Video Vault" "'.$path.'" && exit');
	/*
	$commandString = "start /b c:\\php\\php.EXE C:\\Inetpub\\wwwroot\\mysite.com\\phpforktest.php --passmsg=$testmsg";
	pclose(popen($commandString, 'r'));
	*/
}



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
				$video['isShow'] = false;

				// Store up some tasty info about the video
				$explode = explode('.', $file);
				$video['path'] = $dir.'\\'.$file;
				$video['filetype'] = $explode[count($explode)-1];
				array_pop($explode);
				$video['name'] = implode('.', $explode);

				// If not a supported filetype, give up and go home
				if (!in_array($video['filetype'], $videoFiletypes)) continue;

				// replace spaces _ - with .
				$video['fragments'] = $video['name'];
				$video['fragments'] = str_replace(array(' ','_','-'), '.', $video['fragments']);
				// explode .
				$video['fragments'] = explode('.', $video['fragments']);

				// find episode number...
				$video['episeason'] = $video['season'] = $video['episode'] = '';
				$count = -1;
				foreach ($video['fragments'] as $fragment) {
					$count++;
					if ($count==0) continue; // not going to be at the start of the file
					if (!empty($video['episeason'])) continue;
					if ($video['season']!='' && $video['episode']!='') continue;

					// if s1e4 .. s01e04 s10e021 ..
					if (!is_numeric($fragment)) {
						$pattern = '/^[Ss][0-9]+[Ee][0-9]+/';
						if (preg_match($pattern, $fragment)) {

							#echo "\n".$video['name']."\n".$fragment."\n";

							$thinking = str_replace(array('S','s','E','e'), 'dwa', $fragment);
							$thinking = explode('dwa', $thinking);
							$video['season'] = $thinking[1];
							$video['episode'] = $thinking[2];
							$video['episeasonPosition'] = $count;
							$video['isShow'] = true;
						}
					}
					// if 104 ..  1021 ..
					else {
						$video['episeason'] = $fragment;
						// 104
						if (strlen($fragment)==3) {
							$video['season'] = substr($fragment, 0, 1);
							$video['episode'] = substr($fragment, 1);
							$video['episeasonPosition'] = $count;
							$video['isShow'] = true;
						}
						// 1021, but not 19/20+ seasons as that's probably a YY year date
						else if (strlen($fragment)==4 &&
							//substr($fragment,0,2)!='19' &&
							//substr($fragment,0,2)!='20') {
							// Ignore stupid show names, such as say The 4400
							(float)(substr($fragment,0,2))<19) {
							$video['season'] = substr($fragment, 0, 2);
							$video['episode'] = substr($fragment, 2);
							$video['episeasonPosition'] = $count;
							$video['isShow'] = true;
						}
					}
				}

				// If video doesn't appear to be a show, probs a film so give up and go home
				if ($video['isShow']==false) continue;

				// strip any trailing zeros and such
				if (!empty($video['season'])) $video['season'] = (float)$video['season'];
				if (!empty($video['episode'])) $video['episode'] = (float)$video['episode'];

				#echo '<pre>';print_r($video);

				// Using the episode position, deduce the show name
				if ($video['episeasonPosition']>0 || !empty($video['fragments'])) {
					$video['name'] = array_splice($video['fragments'], 0, $video['episeasonPosition']);
					$video['name'] = implode(' ',$video['name']);
				}


				// remove unnecessary stuffs
				unset($video['episeason']);
				unset($video['fragments']);
				unset($video['filetype']);
				unset($video['episeasonPosition']);


				if (!empty($video['name'])) {
					// strip silly chars from name
					$video['name'] = str_replace(array("'","(",")","[","]"),'',$video['name']);

					// trim it up nice and tidy
					$video['name'] = trim($video['name']);

					// make it case insensitive
					$video['name'] = ucwords(strtolower($video['name']));

					// If we're golden, push to videos array
					array_push($videos, $video);

					#echo '<pre>';print_r($video);
				}
			}
		}
	}
}

// sort the raw array of episodes into proper arrays for each show
// this is used in index.php to display shows (and previously regenerateVideos but I can't remember why)
function organiseVideos($videos) {
	#echo '<pre>';print_r($videos);
	$organised = [];
	foreach ($videos as $video) {
		#echo '<pre>';print_r($video);
		if (empty($video["watched"])) $video["watched"]=0;
		if (empty($organised[$video["name"]])) {
			$organised[$video["name"]] = [];
		}
		if (empty($organised[$video["name"]][$video["season"]])) {
			$organised[$video["name"]][$video["season"]] = [];
		}
		if (empty($organised[$video["name"]][$video["season"]][$video["episode"]])) {
			$organised[$video["name"]][$video["season"]][$video["episode"]] =array(
				"id" => $video["id"],
				"watched" => $video["watched"],
				"path" => $video["path"]
			);
		}
	}
	return $organised;
}

function organiseAndSaveVideos() {
	global $videos;

	foreach ($videos as $video) {
		insertVideoIntoDatabase($video['name'], $video['season'], $video['episode'], $video['path']);
	}

	/*
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
	*/
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
			VALUES (NULL,:name,:season,:episode,:path,0)";
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

function deleteVideoByID($id) {
	global $conn;
	$sql = "DELETE from videos WHERE id=$id";
	$q = $conn->prepare($sql);
	$q->execute();
	if (!$q) { die("Execute query error, because: ". $conn->errorInfo()); }
	return true;
}

// Theoretically this should handle renamed files too.. they'll not retain their watched value though
function checkForDeletedVideos() {
	global $conn;

	$videos = getVideos();
	#echo '<pre>';print_r($videos);

	foreach ($videos as $video) {
		if (!file_exists($video['path'])) deleteVideoByID($video['id']);
	}
}

?>