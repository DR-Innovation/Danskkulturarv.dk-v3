<?php
/**
 */
get_header(); ?>

<div class="container-fluid body-container front-page simple-search">
  <!-- start search -->
  <div class="dark-search">
    <div class="search row"><?php dynamic_sidebar('Top'); ?></div>
  </div>
  <!-- end search -->

    <?php while (have_posts()) : the_post(); ?>
    <div class="row no-negative">
      <article class="col-xs-12" id="post-front">

        <div class="entry-content">
          <?php the_content(); ?>
        </div>

      </article><!-- #post -->
    </div>
    <?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>
