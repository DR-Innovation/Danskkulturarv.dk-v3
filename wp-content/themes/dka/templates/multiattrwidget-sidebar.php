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
<?php if(WPChaosClient::get_object()->rights) : ?>	
		<div class="rights-container">
			<hr>
			<?php echo WPChaosClient::get_object()->rights; ?>
		</div>
<?php endif; ?>
		<hr>
		<div class="social">
			<?php dka_social_share(array("link"=>WPChaosClient::get_object()->url)); ?>
		</div>
		<div>
			<hr>
			<h4><?php _e('Embed material','wpdka'); ?></h4>
			<?php if(($page = get_page_by_path('embed',OBJECT,'page'))) : ?>
			<a href="<?php echo get_permalink($page); ?>">Læs om indlejring</a>
			<?php endif; ?>
			<textarea class="form-control" rows="3" readonly><?php echo esc_html(WPChaosClient::get_object()->embed); ?></textarea>
		</div>
		<div>
			<hr>
			<h4><?php _e('Tags','dka'); ?></h4>
			<?php echo WPChaosClient::get_object()->tags; ?>
		</div>
<?php if(class_exists('WPDKATags') && intval(get_option('wpdkatags-status',0)) > 0) : //iff status is active or frozen ?>
		<div>
			<hr>
			<h4>
				<?php _e('User Tags','wpdkatags'); ?>
<?php if(current_user_can(WPDKATags::CAPABILITY)) : ?>
				<button style="padding:2px 5px;" class="btn btn-sm btn-default" id="object-taggable" data-dka-taggable="<?php echo !WPChaosClient::get_object()->taggable; ?>"><?php WPChaosClient::get_object()->taggable ? _e('Disable','dka') : _e('Enable','dka'); ?></button>
<?php endif; ?>
			</h4>
			<?php echo WPChaosClient::get_object()->usertags; ?>
		</div>
<?php endif; ?>

<?php if (class_exists('WPDKACollections') && current_user_can('edit_posts') && count(WPChaosClient::get_object()->collections_raw) > 0): ?>
		<div class="collection-container">
			<hr>
			<?php echo WPChaosClient::get_object()->collections; ?>
		</div>
 <?php endif; ?>
