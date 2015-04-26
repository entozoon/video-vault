<?php
require 'config.php';


// don't fully clear videos, as you'd lose all the watched values
//clearVideos();

echo 'Checking for deleted videos..<br />';
checkForDeletedVideos();
echo 'Building video array..<br />';
buildVideoArray($dir);
echo 'Organising and saving videos..<br />';
organiseAndSaveVideos();