<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
?>
<h1><?php echo_chaos(title); ?></h1>
<div class="links">
  <strong>
    <?php if (get_chaos(organization_link)) : ?>
      <a href="<?php echo_chaos(organization_link); ?>"
        title="<?php echo_chaos(organization); ?>">
        <?php echo_chaos(organization); ?></a>
    <?php else : ?>
      <?php echo_chaos(organization); ?>
    <?php endif; ?>
  <?php if (get_chaos(externalurl)) : ?>
    - <a target="_blank" href="<?php echo_chaos(externalurl); ?>" title="<?php printf(__('Link to the material at %s', 'dka'), get_chaos(organization)); ?>"><?php _e('Link to the material', 'dka') ?></a>
  <?php endif; ?>
  </strong>
</div>
<div class="chaos-details">
  <i title="<?php echo_chaos(type_title); ?>" class="<?php echo_chaos(type_class); ?>"></i>
  <?php if (get_chaos(published)) : ?>
    <i class="icon-calendar"></i><?php echo ltrim(get_chaos(published), "Året"); ?>
  <?php endif; ?>
  <?php if (get_chaos(rights)) : ?>
    <span><?php echo_chaos(rights); ?></span>
  <?php endif; ?>
</div>

<div class="description">
  <h3>Beskrivelse</h3>
  <?php echo_chaos(description); ?>
</div>

<?php if (get_chaos(creator)) : ?>
<div class="colofon">
  <h3>Kolofon</h3>
  <?php echo_chaos(creator); ?>
</div>
<?php endif; ?>

<?php if (get_chaos(contributor)) : ?>
<div class="contributors">
  <h3>Medvirkende</h3>
  <?php echo_chaos(contributor); ?>
</div>
<?php endif; ?>

<div class="social big-screen hidden-sm hidden-xs">
  <?php dka_social_share(array('link' => get_chaos(url))); ?>
</div>


<div class="social hidden-md hidden-lg">
  <h3>Del</h3>
  <?php dka_social_share(array('link' => get_chaos(url))); ?>
</div>

<hr>

<div class="row">
  <div class="col-sm-6 col-xs-12">
    <h3><?php _e('Tags', 'dka'); ?></h3>
    <?php echo_chaos(tags); ?>
  </div>
  <?php if (class_exists('WPDKATags') && intval(get_option('wpdkatags-status', 0)) > 0) : //iff status is active or frozen ?>
  <div class="col-sm-6 col-xs-12">
    <h3>
      <?php _e('User Tags', 'wpdkatags'); ?>
      <?php if (current_user_can(WPDKATags::CAPABILITY)) : ?>
      <button style="padding:2px 5px;" class="btn btn-sm btn-default" id="object-taggable"
        data-dka-taggable="<?php echo !get_chaos(taggable); ?>">
        <?php get_chaos(taggable) ? _e('Disable', 'dka') : _e('Enable', 'dka'); ?>
      </button>
      <?php endif; ?>
    </h3>
    <div class="usertags-wrap">
      <?php echo_chaos(usertags); ?>
    </div>
  </div>
  <?php endif; ?>
</div>


<?php if (class_exists('WPDKACollections') && current_user_can('edit_posts') && count(get_chaos(collections_raw)) > 0): ?>
<div class="collection-container">
  <?php echo_chaos(collections); ?>
</div>
<?php endif; ?>


<?php if (current_user_can(WPDKA::PUBLISH_STATE_CAPABILITY)): ?> <!--Makes sure the user has the capability to unpublish or republish-->
<hr>
<div class="publish">
  <h3>Publicer/afpublicer</h3>
 <?php if (!get_chaos(isPublished)): ?> <!--Material is not published (no accesspoint)-->
   <?php if (get_chaos(hasDKA2MetaDataSchema)): ?> <!-- Makes sure object has DKA2 metadata schema -->
     <?php if (get_chaos(unpublishedByCurator)): ?> <!--User has unpublished material-->
       <p><?php _e('This object is unpublished and is not visible for other users.', 'dka'); ?></p>
       <button data-dka-publish="1" id="publishState" class="btn btn-primary"><?php _e('Republish this object again', 'dka'); ?></button>
     <?php else: ?> <!--Institution has unpublished object-->
       <p><?php _e('This object is unpublished and is not visible for other users.', 'dka'); ?></p>
       <p><?php _e('Please contact the institution if you want this object republished.', 'dka'); ?></p>
     <?php endif; ?>
   <?php else: ?>
     <p><?php _e('Object is using an old metadata schema. You can not change the publish state for this object.', 'dka'); ?></p>
   <?php endif; ?>
 <?php else: ?> <!--object is published-->
   <?php if (get_chaos(hasDKA2MetaDataSchema)): ?>
     <p><?php _e('This object is published and is visible for other users.', 'dka'); ?></p>
     <button data-dka-publish="0" id="publishState" class="btn btn-danger"><?php _e('Unpublish this object', 'dka'); ?></button>
   <?php else: ?>
     <p><?php _e('Object is using an old metadata schema. You can not change the publish state for this object.', 'dka'); ?></p>
   <?php endif; ?>
 <?php endif; ?>
</div>
<?php endif; ?>
