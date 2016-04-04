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


get_header(); ?>


<?php while ( have_posts() ) : the_post(); ?>
	<div class="row">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h1 class="entry-title">
				<?php the_title(); ?>
			</h1>
			<p>
				<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'dka' ) ); ?>
			</p>
			<form method="get" action="http://www.danskkulturarv.dk/dr-greenscreen/">
				<input type="text" name="session" value="" />
				<input type="submit" value="session" />
			</form>
		</article>
	</div>
<?php endwhile; ?>

<?php
	$sessionCode = htmlspecialchars($_GET["session"]);
	$sessionUrl = 'http://54.93.75.39/dr/drgreenscreenweb/services/getSession.php?settingsID=2&sessionCode=';

	if ($sessionCode) {
		$jsonUrl = $sessionUrl . $sessionCode;
		$jsonContent = file_get_contents($jsonUrl);
		$jsonObject = json_decode($jsonContent, true);

		$title = $jsonObject['title'];
		echo '<h2>' . $title . '</h2>';

		foreach ($jsonObject['videos'] as $key=>$val) {
			echo '
				<video controls src="' . $val['videoPath'] . '" poster="' . $val['thumbnailPath'] . '">
					Din browser understøtter desværre ikke disse videoformater.
					Skift venligst til en nyere.
				</video>';
		}
	}
?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
