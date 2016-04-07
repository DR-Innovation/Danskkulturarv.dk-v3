<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
/*
Template Name: Greenscreen
*/
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'greenscreen-style', get_stylesheet_directory_uri().'/css/greenscreen.css' );
});

$sessionUrl = 'http://54.93.75.39/dr/drgreenscreenweb/services/getSession.php?settingsID=2&sessionCode=';
$videoUrl = 'http://54.93.75.39/dr/drgreenscreenweb/services/getSessionVideo.php?settingsID=2&sessionVideoCode=';
$rootUrl = strtok($_SERVER["REQUEST_URI"],'?');
$pageUrl = $_SERVER["HTTP_HOST"] . strtok($_SERVER["REQUEST_URI"],'?');
$canonicalUrl = $_SERVER["REQUEST_URI"];

$shareTwitterText = 'Her+skal+der+stå+noget+andet+';
$shareTwitter = 'https://twitter.com/intent/tweet?text=' . $shareTwitterText;
$shareFacebook = 'https://www.facebook.com/sharer/sharer.php?s=100&url%5D=';
$shareFaceV2 = 'https://www.facebook.com/v2.5/dialog/share?redirect_uri=http%3A%2F%2Fwww.facebook.com%2Fdialog%2Freturn%2Fclose&display=popup&client_id=919834428127076&href=';

$error = true;
$title = '';
$pageThumbnail = '';
$sessionCode = '';
$videoCode = '';
$pageVideo = '';
$errorMessage = '';
$inSession = false;


if (isset($_GET["session"])) {
	$sessionCode = htmlspecialchars($_GET["session"]);
	$jsonUrl = $sessionUrl . $sessionCode;
	$jsonContent = file_get_contents($jsonUrl);
	$jsonObject = json_decode($jsonContent, true);

	if ($jsonObject['errorMessage']) {
		$errorMessage = $jsonObject['errorMessage'];
	} else {
		$title = $jsonObject['title'];
		$pageThumbnail = $jsonObject['videos'][0]['thumbnailPath'];
		$error = false;
	}
	$inSession = true;
}

if (isset($_GET["video"])) {
	$videoCode = htmlspecialchars($_GET["video"]);
	$jsonUrl = $videoUrl . $videoCode;
	$jsonContent = file_get_contents($jsonUrl);
	$jsonObject = json_decode($jsonContent, true);

	if ($jsonObject['errorMessage']) {
		$errorMessage = $jsonObject['errorMessage'];
	} else {
		$pageThumbnail = $jsonObject['thumbnailPath'];
		$pageVideo = $jsonObject['videoPath'];
		$error = false;
	}
	$inSession = true;
}

add_filter('wpchaos-head-meta',function($metadatas) {
	global $pageThumbnail, $pageVideo;
	if ($pageThumbnail) {
		$metadatas['og:image']['content'] = $pageThumbnail;
		$metadatas['twitter:image']['content'] = $pageThumbnail;
	}
	if ($pageVideo) {
		$metadatas['twitter:card']['content'] = 'player';
		$metadatas['twitter:player:stream:content_type']['content'] = 'video/mp4';
		$metadatas['twitter:player:stream']['content'] = $pageVideo;
		$metadatas['og:type']['content'] = 'video.other';
		$metadatas['og:video:type']['content'] = 'video/mp4';
		$metadatas['og:video:url']['content'] = $pageVideo;
		$metadatas['og:video:secure_url']['content'] = $pageVideo;
	}
	return $metadatas;
});


// function custom_canonical() {
// 	global $canonicalUrl;
// 	echo '<link rel="canonical" href="'. $canonicalUrl .'">';
// }
remove_action( 'wp_head', 'rel_canonical' );
// add_action( 'wp_head', 'custom_canonical' );

get_header();

?>


<?php while ( have_posts() ) : the_post(); ?>
	<div class="row">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h1 class="entry-title">
				<?php the_title(); ?>
			</h1>
			<?php if ($inSession === false) : ;?>
				<p>
					Her finder du din historiske video. Glæd dig, nyd den eller krum dine tær – og del på Facebook.
					<br />
					Indtast din personlige kode herunder, som du finder på dit postkort.
				</p>
			<?php endif; ?>
			<?php if (($inSession === false) || boolval($errorMessage)) : ;?>
				<form method="get" action="<?php echo $rootUrl ?>">
					<input type="text" name="session" value="" placeholder="Indtast din kode" />
					<input type="submit" value="Se din video" />
				</form>
			<?php endif; ?>
		</article>
	</div>
<?php endwhile; ?>

<?php
	if ($error != true) {

		if ($sessionCode) {
			echo '<h2>' . $title . '</h2>
			<p>Her kan du se dig selv i historien.</p>
			<p>Del din video på Facebook og like DR Historie på Facebook – så deltager du i konkurrencen om dagens bedste historiske video. Vinderen offentliggøres på DR Historie på Facebook, og præmieres med et par ”Historien om Danmark” fold-selv VR-briller.</p>
			<p>Andre kan ikke se din film før du selv vælger at dele den på Facebook.</p>
			';

			if($jsonObject['videos']) {

				foreach ($jsonObject['videos'] as $key=>$val) {
					$video = $val['videoPath'];
					$thumbnail = $val['thumbnailPath'];
					$videoCode = $val['sessionVideoCode'];
					$shareUrl = $pageUrl . '?video=' . $videoCode;
					$twitterUrl = $shareTwitter . urlencode('http://' . $shareUrl);
					$facebookUrl = $shareFaceV2 . urlencode('http://' . $shareUrl);
					echo '
						<video controls src="' . $video . '" poster="' . $thumbnail . '">
							Din browser understøtter desværre ikke disse videoformater.
							Skift venligst til en nyere.
						</video>
						<h3>Del denne video</h3>
						<a href="http://'. $shareUrl .'" class="share-url">'. $shareUrl .'</p>
						<a href="'. $facebookUrl .'"><i class="icon-facebook"></i></a>
					';
					// <a href="'. $twitterUrl .'"><i class="icon-twitter"></i></a>
				}
			}

		} else if ($videoCode) {
			$shareUrl = $pageUrl . '?video=' . $videoCode;
			$twitterUrl = $shareTwitter . urlencode('http://' . $shareUrl);
			$facebookUrl = $shareFaceV2 . urlencode('http://' . $shareUrl);
			echo '
				<video controls src="' . $pageVideo . '" poster="' . $pageThumbnail . '">
					Din browser understøtter desværre ikke disse videoformater.
					Skift venligst til en nyere.
				</video>
				<h3>Del denne video</h3>
				<a href="http://'. $shareUrl .'" class="share-url">'. $shareUrl .'</p>
				<a href="'. $facebookUrl .'"><i class="icon-facebook"></i></a>
				';
				// <a href="'. $twitterUrl .'"><i class="icon-twitter"></i></a>
		}
	} else if ($errorMessage) {
		echo '
		<div class="error">
			<h3>Ups, der skete en fejl</h3>
			<p>'. $errorMessage .'</p>
		</div>
		';
	} else if (isset($_GET["video"]) || isset($_GET["session"])){
		echo '
		<div class="error">
			<h3>Ups, der skete en fejl</h3>
			<p>Det ligner vi har serverproblemer, prøv venligst igen senere.</p>
		</div>
		';
	}
?>

<?php get_footer(); ?>
