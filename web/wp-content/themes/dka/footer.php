<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
?>
</article>
<div id="push"><!--//--></div>

</div><!-- end #wrap -->

</div><!-- container -->

<!-- sticky footer -->
<footer>
  <div class="container text-center">

  <?php
      wp_nav_menu( array(
          'theme_location' => 'secondary',
          'depth'      => 1,
          'container'  => false,
          'menu_class' => 'nav-footer',
          'fallback_cb' => false,
          'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s<li><a target="_blank" href="https://www.facebook.com/danskkulturarv" class="social" title="'.sprintf(esc_attr__('%s on Facebook','dka'),get_bloginfo( 'name' )).'"><i class="icon-facebook-sign"></i></a><a target="_blank" href="http://instagram.com/danskkulturarv" class="social" title="'.sprintf(esc_attr__('%s on Instagram','dka'),get_bloginfo( 'name' )).'"><i class="icon-instagram"></i></a></li></ul>'
          )
      );
  ?>

    <div class="copyright">Copyright &#169; 2012-<?php echo date('Y'); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><?php bloginfo( 'name' ); ?></a></div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
