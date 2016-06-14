<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
 /*
 Template Name: IframePage
 */
get_header(); ?>


<div class="container body-container full-width">

<?php while ( have_posts() ) : the_post(); ?>

  <div class="entry-content">
    <?php the_content(); ?>
  </div>

<?php endwhile; ?>

<?php get_footer(); ?>
