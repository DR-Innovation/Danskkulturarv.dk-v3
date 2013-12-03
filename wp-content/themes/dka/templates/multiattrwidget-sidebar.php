<?php
/**
 * @package WordPress
 * @subpackage DKA
 */

?>
		<div>
			<i class="icon-eye-open"></i> <?php _e('Views','dka'); ?> <strong class="pull-right"><?php echo WPChaosClient::get_object()->views; ?></strong>
		</div>
<?php if(WPChaosClient::get_object()->externalurl) : ?>
		<div class="external-link-container">
			<hr>
			<i class="icon-external-link"></i> <a target="_blank" href="<?php echo WPChaosClient::get_object()->externalurl; ?>" title="<?php printf(__('Read more at %s','dka'),WPChaosClient::get_object()->organization); ?>"><?php printf(__('Read more at %s','dka'),WPChaosClient::get_object()->organization); ?></a>
		</div>
<?php endif; ?>		
		<div class="rights-container">
			<hr>
			<?php echo WPChaosClient::get_object()->rights; ?>
		</div>
		<hr>
		<div class="social">
			<?php dka_social_share(array("link"=>WPChaosClient::get_object()->url)); ?>
		</div>
		<div>
			<hr>
			<h4><?php _e('Tags','dka'); ?></h4>
			<?php echo WPChaosClient::get_object()->tags; ?>
		</div>
<?php if(intval(get_option('wpdkatags-status',0)) > 0) : //iff status is active or frozen ?>
		<div>
			<hr>
			<h4><?php _e('User Tags','wpdkatags'); ?></h4>
			<?php echo WPChaosClient::get_object()->usertags; ?>
		</div>
<?php endif; ?>

<?php if (class_exists('WPDKACollections') && current_user_can('edit_posts')): ?>
		<div class="collection-container">
			<hr>
			<?php echo WPChaosClient::get_object()->collections; ?>
		</div>
 <?php endif; ?>
