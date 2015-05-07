<?php
require 'config.php';

if (!empty($_POST)) {
	if (isset($_POST['settings'])) {
		$settings = $_POST['settings'];
		// Set any old setting, not particularly secure but we're not public
		foreach ($settings as $key => $value) {
			if (isset($settings[$key])) { // allow null
				setSetting($key, $value);
			}
		}
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Video Vault</title>
	<link href="images/favicon.ico" type="image/x-icon" rel="shortcut icon" />

	<meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0" />

	<link rel="apple-touch-icon" sizes="57x57" href="images/app-icon-57.png" />
	<link rel="apple-touch-icon" sizes="72x72" href="images/app-icon-72.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="images/app-icon-114.png" />
	<link rel="apple-touch-icon" sizes="144x144" href="images/app-icon-144.png" />

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
			<div class="status clear"></div>
			<div class="button regenerateVideos">Update<br />Videos</div>
			<?php if (!empty(getSetting('stop'))) { ?>
				<div class="button stopPlayback">
					<div class="stopPlayback__fa"></div>
					<div class="stopPlayback__content">Stop</div>
				</div>
			<?php } ?>
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

	// kinda works for positioning, not ideal
	vlc.exe --no-embedded-video --no-qt-video-autoresize --autoscale --width=200 --height=10 --video-x=100 --video-y=100 e:\silver.avi

	Star a show
	remains in list, but also appears at top, with ability to unstar (font awesome)

	unwatched/watched buttons - watched should make all previous episodes and seasons watched!

*/


$videos = getVideos();
$videos = organiseVideos($videos);
$videos = sortVideos($videos);
#echo '<pre>';print_r($videos);
echoVideos($videos);


#echo "\n\n\n\n\n";print_r($videos);

?>
			<hr />
			<h1>Admin</h1>
			<?php //part of update videos /*<div class="button button--small checkDeletedVideos">Remove Deleted Videos</div>*/ ?>
			<form action="" method="post">

				<label><strong>Video Folder, for example:</strong></label>
				<div contentEditable="true">r:\manor\unsorted</div>
				<input type="text" name="settings[videofolder]" placeholder="Empty (won't work!!)" value="<?php echo htmlentities(getSetting('videofolder')); ?>" />
				<br />

				<label><strong>Executable path and parameters, for example:</strong></label>
				<div contentEditable="true">"e:\Program Files\vlc\vlc.exe" -I qt --fullscreen --qt-fullscreen-screennumber=0 --directx-volume=1.25</div>
				<input type="text" name="settings[executable]" placeholder='Empty (uses default program)' value="<?php echo htmlentities(getSetting('executable')); ?>" />
				<br />

				<label><strong>Executable to stop videos, for example:</strong></label>
				<div contentEditable="true">taskkill /f /im vlc.exe</div>
				<input type="text" name="settings[Stop]" placeholder='Empty (removes functionality)' value="<?php echo htmlentities(getSetting('stop')); ?>" />
				<br />

				<button type="submit" class="button button--medium">Submit</button>
			</form>
			<br />
			<hr />
			<div class="button button--small clearVideos">Clear Videos<br />(wipes everything!)</div>
			<hr />
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

$('.stopPlayback').click(function() {
	$.post('stopPlayback.php', {
	}, function(echo) {
		c(echo);
	})
	.fail(function(data) {
		$('.status').html('Stop playback error!');
		c(data);
	})
	.done(function(data) {
	})
});

$('.videos .toggle').click(function() {
	$(this).siblings('.toggle__content').slideToggle();
});


$('.videos__episode').click(function() {
	//$('.status').html('Playing video..');
	$('.videos__episode').removeClass('videos__episode--playing')
	$(this).addClass('videos__episode--watched videos__episode--playing');

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
	})
});


$('.clearVideos').click(function() {
	//$('.status').html('Playing video..');
	$.post('clearVideos.php', {
	}, function(echo) {
		//c(echo);
	})
	.fail(function(data) {
		$('.status').html('Clear videos error!');
		c(data);
	})
	.done(function(data) {
		location.reload();
	})
});

/*
$('.checkDeletedVideos').click(function() {
	//$('.status').html('Playing video..');
	$.post('checkDeletedVideos.php', {
	}, function(echo) {
		//c(echo);
	})
	.fail(function(data) {
		$('.status').html('checkDeletedVideos error!');
		c(data);
	})
	.done(function(data) {
		location.reload();
	})
});
*/
resetWatchedClasses();

});

function resetWatchedClasses() {
	// If a whole season/show has been viewed, set watched classes
	// This could be php'd, but let's take a load off it's shoulders.
	// Probably a more concise way of writing this but.. brainfart
	$('.videos__season').removeClass('videos__seasion--watched').each(function() {
		var watched = true;
		$(this).find('.videos__episode').each(function() {
			if (!$(this).hasClass('videos__episode--watched')) watched = false;
		});
		if (watched) $(this).addClass('videos__season--watched')	;
	});
	$('.videos__show').removeClass('videos__show--watched').each(function() {
		var watched = true;
		$(this).find('.videos__season').each(function() {
			if (!$(this).hasClass('videos__season--watched')) watched = false;
		});
		if (watched) $(this).addClass('videos__show--watched')	;
	});

}

function c(c) { console.log(c); }
</script>
</body>
</html>
