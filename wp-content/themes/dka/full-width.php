<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
/*
Template Name: 100% iframes
*/
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'full-width-style', get_stylesheet_directory_uri().'/css/full-width.css' );
});
get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
  <div class="row">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
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
