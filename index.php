<?php
require 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
	<title>Video Vault</title>
	<link href="images/favicon.ico" type="image/x-icon" rel="shortcut icon" />
	<link href='http://fonts.googleapis.com/css?family=Play:400,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php if ($debugging==true) echo '<pre>'; ?>

<div class="container-fluid">

	<div class="header row">
		<div class="col-xs-6">
			<a class="header__logo" href="/"><img src="images/header_logo.png" alt="Video Vault" /></a>
		</div>
		<div class="col-xs-6 text-right">
			<div class="button regenerateVideos">Update<br />Videos</div>
			<div class="status clear"></div>
		</div>
	</div>

	<div class="content row">
		<div class="col-xs-12">

<?php
/*
	To dos:
	Make it run regenerateVideos once an hour, storing up all the stuff
	Make it flexbox, with a nice VHS logo or something
	check it doesn't bork up with Films (2014)
	IP security, just the fact it's a local server 127.0.0.1 should be enough actually
	or (no need for input) just use icanhazip and test against REMOTE_ADDR
	IN FACT, it's only ever gonna be on localhost so fuck it.
	Proper $dir
*/


$videos = getVideos();
$videos = organiseVideos($videos);
$videos = sortVideos($videos);
#echo '<pre>';print_r($videos);
echoVideos($videos);


#echo "\n\n\n\n\n";print_r($videos);

?>
		</div>
	</div>
</div>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
$(function() {

$('.regenerateVideos').click(function() {
	$('.videos, .regenerateVideos').remove();
	$('.status').html('Regenerating videos <i class="fa fa-refresh fa-spin"></i>');
	// regenerateVideos.php also clears videos
	$.post('regenerateVideos.php', {
	}, function(echo) {
		//c(echo);
	})
	.fail(function(data) {
		$('.status').html('Regenerate videos error!');
		c(data);
	})
	.done(function(data) {
		$('.status').html('Videos saved, reloading..');
		setTimeout(function() { location.reload(); }, 500);
	});
});

$('.videos .toggle').click(function() {
	$(this).siblings('.toggle__content').slideToggle();
});


$('.videos__episode').click(function() {
	$('.status').html('Playing video..');

	$.post('playVideo.php', {
		path: $(this).attr('data-path'),
		id: $(this).attr('data-id')
	}, function(echo) {
		c(echo);
	})
	.fail(function(data) {
		$('.status').html('Playing video error!');
		c(data);
	})
	.done(function(data) {
		$('.status').html();
	})
});

});

function c(c) { console.log(c); }
</script>
</body>
</html>
