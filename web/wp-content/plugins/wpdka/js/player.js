function initPlayer(id, options) {
	var player = jwplayer(id);

	// It seems like the onPlay method is deprecated.
	// I'll leave this bit here in case the functionality is actually needed.
	// But it does not seem to do much.
	// player.onPlay(function() {
	// 	$(".jwlogo").prop("title", WPDKAPlayer.goToOriginalPage);
	// });
	if ($('#'+id).width() <= 320) {
		options.skin = options.skin_embed;
		if (options.logo) {
			options.logo.file = options.logo.file_mini;
		}
	}
	player.setup(options);

	// Resize player height to 60 when the height is 0.
	// We assume that if the player has zero height it is playing audio.
	// In that case we resize the height in order to show the timeline.
	player.on('play', function () {
		var visualQuality = this.getVisualQuality();
		if (visualQuality.level.height === 0) {
			this.resize(this.getWidth(), 60);
		}
	});

	var stopped = false, stoptimeend = false;
	if (options['startoffset']  && options['startoffset'] !== undefined) {
		// Time offset. Using jwplayer seek function.
		player.onReady(function() { this.seek(parseInt(options['startoffset']))});
		// Stop player after seek
		player.onPlay(function () { if (!stopped) { stopped = true; this.pause(); } });
	}

	if (options['stoptime']  && options['stoptime'] !== undefined) {
		player.onTime(function () { if (!stoptimeend && this.getPosition() >= options['stoptime']) { stoptimeend = true; this.pause(); } });
	}

	if (options['autostart']) {
		var played = true;
		player.onPause(function() { if (played && !stoptimeend) { played = false; this.play(); } });
	}
}
