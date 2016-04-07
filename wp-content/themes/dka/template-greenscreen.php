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

$shareTwitterText = 'Her+skal+der+stå+noget+andet+';
$shareTwitter = 'https://twitter.com/intent/tweet?text=' . $shareTwitterText;
$shareFacebook = 'https://www.facebook.com/sharer/sharer.php?url%5D=';

$error = true;
$title = '';
$pageThumbnail = '';
$sessionCode = '';
$videoCode = '';
$pageVideo = '';

if (isset($_GET["session"])) {
	$sessionCode = htmlspecialchars($_GET["session"]);
	$jsonUrl = $sessionUrl . $sessionCode;
	$jsonContent = file_get_contents($jsonUrl);
	$jsonObject = json_decode($jsonContent, true);

	if ($jsonObject['errorMessage']) {

	} else {
		$title = $jsonObject['title'];
		$pageThumbnail = $jsonObject['videos'][0]['thumbnailPath'];
		$error = false;
	}
}

if (isset($_GET["video"])) {
	$videoCode = htmlspecialchars($_GET["video"]);
	$jsonUrl = $videoUrl . $videoCode;
	$jsonContent = file_get_contents($jsonUrl);
	$jsonObject = json_decode($jsonContent, true);

	echo $jsonObject['errorMessage'];

	if ($jsonObject['errorMessage']) {

	} else {
		$pageThumbnail = $jsonObject['thumbnailPath'];
		$pageVideo = $jsonObject['videoPath'];
		$error = false;
	}
}

if ($pageThumbnail) {
	add_filter('wpchaos-head-meta',function($metadatas) {
		global $pageThumbnail;
		$metadatas['og:image']['content'] = $pageThumbnail;
		$metadatas['twitter:image']['content'] = $pageThumbnail;
		return $metadatas;
	});
}

get_header();

?>


<?php while ( have_posts() ) : the_post(); ?>
	<div class="row">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h1 class="entry-title">
				<?php the_title(); ?>
			</h1>
			<p>
				<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'dka' ) ); ?>
			</p>
			<?php if (!$sessionCode && !$videoCode) : ;?>
				<form method="get" action="<?php echo $rootUrl ?>">
					<input type="text" name="session" value="" placeholder="Indtast din kode her" />
					<input type="submit" value="Se videoer" />
				</form>
			<?php endif; ?>
		</article>
	</div>
<?php endwhile; ?>

<?php
	if ($error != true) {

		if ($sessionCode) {
			echo '<h2>' . $title . '</h2>';

			if($jsonObject['videos']) {

				foreach ($jsonObject['videos'] as $key=>$val) {
					$video = $val['videoPath'];
					$thumbnail = $val['thumbnailPath'];
					$videoCode = $val['sessionVideoCode'];
					$shareUrl = $pageUrl . '?video=' . $videoCode;
					$twitterUrl = $shareTwitter . 'http://' . $shareUrl;
					$facebookUrl = $shareFacebook . 'http://' . $shareUrl;
					echo '
						<video controls src="' . $video . '" poster="' . $thumbnail . '">
							Din browser understøtter desværre ikke disse videoformater.
							Skift venligst til en nyere.
						</video>
						<h3>Del denne video</h3>
						<a href="http://'. $shareUrl .'" class="share-url">'. $shareUrl .'</p>
						<a href="'. $facebookUrl .'"><i class="icon-facebook"></i></a>
						<a href="'. $twitterUrl .'"><i class="icon-twitter"></i></a>
					';
				}
			}

		} else if ($videoCode) {
			$shareUrl = $pageUrl . '?video=' . $videoCode;
			$twitterUrl = $shareTwitter . $shareUrl;
			$facebookUrl = $shareFacebook . $shareUrl;
			echo '
				<video controls src="' . $pageVideo . '" poster="' . $pageThumbnail . '">
					Din browser understøtter desværre ikke disse videoformater.
					Skift venligst til en nyere.
				</video>
				<h3>Del denne video</h3>
				<a href="http://'. $shareUrl .'" class="share-url">'. $shareUrl .'</p>
				<a href="'. $facebookUrl .'"><i class="icon-facebook"></i></a>
				<a href="'. $twitterUrl .'"><i class="icon-twitter"></i></a>
				';
		}
	}
?>

<?php get_footer(); ?>
