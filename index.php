<?php
require 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
	<title>Video Helper</title>
	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="button saveVideos clear">Save Videos</div>
<div class="status"></div>
<?php
/*
	To do
	Make it run saveVideos once an hour, storing up all the stuff
	Make it flexbox, with a nice VHS logo or something
	check it doesn't bork up with Films (2014)
	IP security
	Proper $dir
*/

function echoVideos($videos) {
	echo '<ul class="videos">';
	if (!empty($videos)) {
		foreach ($videos as $name=>$season) {
			echo '<li>'.$name.'<ul>';
			foreach ($videos[$name] as $season=>$episode) {
				echo '<li>'.$season.'<ul>';
				foreach ($videos[$name][$season] as $episode=>$details) {
					echo '<li>'.$episode.'</li>';
				}
				echo '</ul></li>';
			}
			echo '</ul></li>';
		}
	}
	echo '</ul>';
}

$videos = getVideos();
$videos = organiseVideos($videos);
$videos = sortVideos($videos);
#print_r($videos);
echoVideos($videos);


#echo "\n\n\n\n\n";print_r($videos);

?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
$(function() {

$('.saveVideos').click(function() {
	$('.status').html('Saving Videos..');

	$.post('saveVideos.php', {
	}, function(echo) {
		//c(echo);
	})
	.fail(function(data) {
		$('.status').html('Get Videos Error!');
		c(data);
	})
	.done(function(data) {
		$('.status').html('Videos saved. Reloading..');
		setTimeout(function() { location.reload(); }, 2000);
	});
});

$('.videos>li').click(function() {
	$(this).children('ul').slideToggle();
});

});

function c(c) { console.log(c); }
</script>
</body>
</html>
