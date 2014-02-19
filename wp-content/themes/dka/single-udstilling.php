<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
get_header(); ?>

			<?php while ( have_posts() ) : the_post(); ?>
				<div class="row">
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h1 class="entry-title">
							<?php the_title(); ?>
						</h1>

						<div class="entry-content">
							<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'dka' ) ); ?>
							<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'dka' ), 'after' => '</div>' ) ); ?>
						</div><!-- .entry-content -->
					</article><!-- #post -->
				</div>

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>