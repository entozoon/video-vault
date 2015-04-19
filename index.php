<?php

/*
	To do
	Make it flexbox, with a nice VHS logo or something
*/

$debugging = true;
$dir = "Unsorted";
$videoFiletypes = ['webm','mkv','flv','vob','ogv','avi','mov','yuv','rm','rmvb','asf','mp4','m4p','m4v','mpg','mp2','mpeg','mpe','mpv','mpg','mpeg','m2v','m4v','3gp','3g2','nsv'];

if ($debugging==true) array_push($videoFiletypes, 'txt');

$videos = [];
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
				// explode .
				$video['fragments'] = explode('.', $video['name']);

				// find episode number...
				$count = -1;
				foreach ($video['fragments'] as $fragment) {
					$count++;
					if ($count==0) continue; // not going to be at the start of the file
					echo $fragment;
				}

				// If not a supported filetype, give up and go home
				if (!in_array($video['filetype'], $videoFiletypes)) continue;

				// If we're golden, push to videos array
				array_push($videos, $video);
			}
		}
	}
	return $videos;
}

buildVideoArray($dir);

echo '<pre>';print_r($videos);


?>