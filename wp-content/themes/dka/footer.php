<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
?>
</article>
<div id="push"><!--//--></div>

</div><!-- end #wrap -->

<!-- sticky footer -->
<footer>
	<div class="container text-center">
		<div class="bloginfo-footer"><?php bloginfo( 'name' ); ?> <span>- <?php bloginfo( 'description' ); ?></span></div>
		
<?php 
    wp_nav_menu( array(
        'theme_location' => 'secondary',
        'depth'      => 1,
        'container'  => false,
        'menu_class' => 'nav-footer',
        'fallback_cb' => false,
        'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s<a href="#" class="social"><i class="icon-facebook-sign"></i></a><a href="#" class="social"><i class="icon-twitter"></i></a></ul>'
        )
    );
?>

		<div class="copyright">Copyright &#169; 2012-<?php echo date('Y'); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><?php bloginfo( 'name' ); ?></a></div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>