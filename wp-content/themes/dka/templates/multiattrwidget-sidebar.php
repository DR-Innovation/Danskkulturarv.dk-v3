<?php
/**
 * @package WordPress
 * @subpackage DKA
 */

?>
		<!--<div>
			<i class="icon-eye-open"></i> <?php _e('Views','dka'); ?> <strong class="pull-right"><?php echo WPChaosClient::get_object()->views; ?></strong>
		</div>-->
<?php if(current_user_can(WPDKA::PUBLISH_STATE_CAPABILITY)): ?>
	<div>
		<?php if (!WPChaosClient::get_object()->isPublished): ?> <!--Material is not published (no accesspoint)-->
			<?php if (WPChaosClient::get_object()->hasDKA2MetaDataSchema): ?> <!-- Makes sure object has DKA2 metadata schema -->
				<?php if (WPChaosClient::get_object()->unpublishedByCurator): ?> <!--User has unpublished material-->
					<p><?php _e('This object is unpublished and is not visible for other users.', 'dka'); ?></p>
					<button data-dka-publish="1" id="publishState" class="btn btn-primary btn-fat"><?php _e('Republish this object again', 'dka'); ?></button>
				<?php else: ?> <!--Institution has unpublished object-->
					<p><?php _e('This object is unpublished and is not visible for other users.', 'dka'); ?></p>
					<p><?php _e('Please contact the institution if you want this object republished.', 'dka'); ?></p>
				<?php endif; ?>
			<?php else: ?>
				<p><?php _e('Object is using an old metadata schema. You can not change the publish state for this object.', 'dka'); ?></p>
			<?php endif; ?>
		<?php else: ?> <!--object is published-->
			<?php if (WPChaosClient::get_object()->hasDKA2MetaDataSchema): ?>
				<p><?php _e('This object is published and is visible for other users.', 'dka'); ?></p>
				<button data-dka-publish="0" id="publishState" class="btn btn-danger btn-fat"><?php _e('Unpublish this object', 'dka'); ?></button>
			<?php else: ?>
				<p><?php _e('Object is using an old metadata schema. You can not change the publish state for this object.', 'dka'); ?></p>
			<?php endif; ?>
		<?php endif; ?>
	<hr>
	</div>
<?php endif; ?>
<?php if(WPChaosClient::get_object()->externalurl) : ?>
		<div class="external-link-container">
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
<!--<?php if(0 && WPChaosClient::get_object()->is_embeddable) : // Deaktiveret. ?>
		<div>
			<hr>
			<h4><?php _e('Embed material','dka'); ?></h4>
			<?php if(($page = get_page_by_path('embed',OBJECT,'page'))) : ?>
			<a href="<?php echo get_permalink($page); ?>">Læs om embedding</a>
			<?php endif; ?>
			<textarea onClick="this.select()" class="form-control" rows="3" readonly><?php echo esc_html(WPChaosClient::get_object()->embed); ?></textarea>
		</div>
<?php endif; ?>-->
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
