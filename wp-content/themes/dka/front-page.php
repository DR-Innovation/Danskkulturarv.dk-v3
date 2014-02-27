<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
get_header(); ?>

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