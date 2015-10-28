<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
get_header(); ?>


<div class="container body-container">


<div class="row">
  <?php while ( have_posts() ) : the_post(); ?>

  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="">
      <h1 class="entry-title">
        <?php the_title(); ?>
      </h1>

      <div class="entry-content">
        <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'dka' ) ); ?>
        <?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'dka' ), 'after' => '</div>' ) ); ?>
      </div><!-- .entry-content -->
    </div>

  </article><!-- #post -->

<?php endwhile; // end of the loop. ?>

<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>
