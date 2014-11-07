function initPlayer(id, options) {
	var player = jwplayer(id);
	if ($('#'+id).width() <= 320) {
		options['skin'] = options['skin_embed'];
	}
	player.setup(options);
	player.onPlay(function() {
		$(".jwlogo").prop("title", WPDKAPlayer.goToOriginalPage);
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

	if (options['autostart'] && options['autostart'] !== undefined) {
		var played = true;
		
	}

}