<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
get_header(); ?>

<div class="container-fluid body-container">
	<!-- start search -->
	<div class="search row"><?php dynamic_sidebar( 'Top' ); ?></div>
	<!-- end search -->

<?php while ( have_posts() ) : the_post(); ?>
				<div class="row">
					<article id="post-front" class="col-xs-12">

						<div class="entry-content">
							<?php the_content(); ?>
						</div>

					</article><!-- #post -->
				</div>

<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>
