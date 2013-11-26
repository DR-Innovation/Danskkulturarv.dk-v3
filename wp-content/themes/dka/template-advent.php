<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
/*
Template Name: Advent 2013
*/
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'christmas-style', get_stylesheet_directory_uri().'/css/christmas-style.css' );
});
get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
			
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title">
						<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'dka' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h1>		

					<div class="entry-content">
						<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'dka' ) ); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'dka' ), 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->

<?php $children = new WP_Query(array(
	'post_parent' => get_the_ID(),
	'post_status' => 'publish,future',
	'post_type' => 'page',
	'orderby' => 'post_title',
	'order' => 'asc'
));
if($children->have_posts()) : ?>
					<div class="container christmas-calendar">
						<ul class="row">
<?php 	while($children->have_posts()) : $children->the_post(); ?>
<?php
$class = 'door-frame';
$content = '<div class="door">'.get_the_title().'</div>';
if(get_post_status() == 'publish') {
	$class .= ' available';
	$content .= '<a class="door-inside" href="'.get_permalink().'">Klik</a>';
}
$content = '<div class="'.$class.'">'.$content.'</div>';
 ?>
							<li class="door-container">
								<?php echo $content; ?>
							</li>
<?php 	endwhile; wp_reset_postdata(); ?>
						</ul>
						<script type="text/javascript">
						jQuery(document).ready(function($) {
							$('.available').bind('touchstart touchend', function(e) {
								e.preventDefault();
								$(this).toggleClass('christmas-touched');
							});
						});
						</script>
					</div>
<?php endif; ?>

				</article><!-- #post -->

<?php endwhile; // end of the loop. ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>