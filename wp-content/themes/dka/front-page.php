<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
			
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="entry-content">
						<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'dka' ) ); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'dka' ), 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->

					<!-- Short code for new front page
					<?php 
						do_shortcode( '[general_information]' );
						do_shortcode( '[collection_slider]38d197d1-4ead-7945-8090-62ba05d76eef,fd1e3946-cc41-ff47-8c3a-ec3f3db56858[/collection_slider]' );
					?>
				-->

				</article><!-- #post -->

<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>