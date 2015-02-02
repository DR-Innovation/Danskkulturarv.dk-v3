function initPlayer(id, options) {
	var player = jwplayer(id);
	
	player.onPlay(function() {
		$(".jwlogo").prop("title", WPDKAPlayer.goToOriginalPage);
	});
	if ($('#'+id).width() <= 320) {
		options.skin = options.skin_embed;
		if (options.logo) {
			options.logo.file = options.logo.file_mini;
		}
	}
	player.setup(options);

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