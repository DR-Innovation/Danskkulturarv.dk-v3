<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
 /*
 Template Name: IframePage
 */
get_header(); ?>


<div class="container body-container">


<div class="row">
  <?php while ( have_posts() ) : the_post(); ?>

  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <div class="entry-content">
      <?php the_content(); ?>
    </div>

  </article><!-- #post -->

<?php endwhile; // end of the loop. ?>

<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>
