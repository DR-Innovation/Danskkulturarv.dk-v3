<?php
/**
 * @package WordPress
 * @subpackage DKA
 */

?>
		<?php
			$collections = WPDKACollections::material_get_collections(WPChaosClient::get_object()->GUID);
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
<?php $collections = WPDKACollections::material_get_collections(WPChaosClient::get_object()->GUID);
if (empty($collections)) : ?>
		<div class="collection-container">
			<h4>Indgår i Tema <strong>Test</strong></h4>
			<div class="collections-container">
				<ol class="list-group" style="margin: none; border: 3px;">
					<?php for ($i = 0; $i < 10; $i++): ?> Testing
 							<?php $thumbnail = (WPChaosClient::get_object()->thumbnail ? ' style="background-image: url(\''.WPChaosClient::get_object()->thumbnail.'\')!important;"' : ''); ?>
 							<li class="list-group-item">
 								<h4 class="list-group-item-heading">List group item heading</h4>
 								<div class="media">
 									<a class="pull-left" href="#">
 								   		<div id="collection_image" style="max-height: 75px; max-width: 75px;" class="thumb format">
 								   		</div>
 								 	</a>
 								  	<div class="media-body">
  										Media body bla bla bla...
 								  	</div>
 								</div>
 							</li>
 						<?php endfor; ?>
 					</ol>
				</div>
 			</div>		
 		<?php endif; ?>
 <hr>
 <?php endif; ?>
