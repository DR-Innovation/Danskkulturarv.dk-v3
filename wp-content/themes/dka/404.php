<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
get_header(); ?>

<div class="container body-container">

	<!-- start search -->
	<div class="search row"><?php dynamic_sidebar( 'Top' ); ?></div>
	<!-- end search -->
			<article id="post-0" class="post error404 no-results not-found">
				<header class="entry-header">
					<h1 class="entry-title"><?php _e( 'Page not found', 'dka' ); ?></h1>
				</header>

				<div class="entry-content">
					<p><?php _e( 'The page you are looking for does not exist. Please recheck the entered address.', 'dka' ); ?></p>
				</div><!-- .entry-content -->
			</article><!-- #post-0 -->

<?php get_footer(); ?>
