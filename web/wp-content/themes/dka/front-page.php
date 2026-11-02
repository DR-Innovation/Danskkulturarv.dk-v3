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
  <!-- site closing bar -->
  <div style="background-color: #ffa600; padding: 16px 0;">
    <div style="max-width: 1170px; margin: 0 auto; float: none;">Dansk Kulturarv lukker pr. 4. januar 2027.
      Læs mere her: <a href="https://dr.dk/danskkulturarv" style="text-decoration: underline; color: black;">dr.dk/danskkulturarv</a>
    </div>
  </div>
  <!-- end site closing bar -->

    <?php while (have_posts()) : the_post(); ?>
    <div class="row no-negative">
      <article class="col-xs-12" id="post-front">

        <div class="entry-content">
          <?php the_content(); ?>
        </div>

      </article><!-- #post -->
    </div>
    <?php endwhile; // end of the loop.?>

<?php get_footer(); ?>
