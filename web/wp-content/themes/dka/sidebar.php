<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
?>

		<div class="col-lg-3 widget-area"  id="secondary" role="complementary">
			<?php if(is_active_sidebar('sidebar-1')) : ?>
				<ul class="nav info">
					<?php dynamic_sidebar( 'sidebar-1' ); ?>
				</ul>
			<?php endif;?>
		</div>
