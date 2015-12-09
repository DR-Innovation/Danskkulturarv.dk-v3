<?php
/**
 * @package WordPress
 * @subpackage DKA
 */

 $chaos_object = WPChaosClient::get_object();

?>
<h1><?php echo $chaos_object->title; ?></h1>
<div class="links">
  <strong>
    <?php if ($chaos_object->organization_link) : ?>
      <a href="<?php echo $chaos_object->organization_link; ?>"
        title="<?php echo $chaos_object->organization; ?>">
        <?php echo $chaos_object->organization; ?></a>
    <?php else : ?>
      <?php echo $chaos_object->organization; ?>
    <?php endif; ?>
  <?php if ($chaos_object->externalurl) : ?>
    - <a target="_blank" href="<?php echo $chaos_object->externalurl; ?>" title="<?php printf(__('Link to the material at %s', 'dka'), $chaos_object->organization); ?>"><?php _e('Link to the material', 'dka') ?></a>
  <?php endif; ?>
  </strong>
</div>
<div class="chaos-details">
  <i title="<?php echo $chaos_object->type_title; ?>" class="<?php echo $chaos_object->type_class; ?>"></i>
  <?php if ($chaos_object->published) : ?>
    <i class="icon-calendar"></i><?php echo ltrim($chaos_object->published, "Året"); ?>
  <?php endif; ?>
  <?php if ($chaos_object->rights) : ?>
    <span><?php str_replace("Copyright © Det Danske Filminstitut","",$chaos_object->rights); ?></span>
  <?php endif; ?>
</div>

<?php if ($chaos_object->description) : ?>
<div class="description">
  <h3>Beskrivelse</h3>
  <?php echo $chaos_object->description; ?>
</div>
<?php endif; ?>

<?php if ($chaos_object->creator) : ?>
<div class="colofon">
  <h3>Kolofon</h3>
  <?php echo $chaos_object->creator; ?>
</div>
<?php endif; ?>

<?php if ($chaos_object->contributor) : ?>
<div class="contributors">
  <h3>Medvirkende</h3>
  <?php echo $chaos_object->contributor; ?>
</div>
<?php endif; ?>

<div class="social big-screen hidden-sm hidden-xs">
  <?php dka_social_share(array('link' => $chaos_object->url)); ?>
</div>

<div class="social hidden-md hidden-lg">
  <h3>Del</h3>
  <?php dka_social_share(array('link' => $chaos_object->url)); ?>
</div>

<div class="row">
  <?php if ($chaos_object->tags) : ?>
  <div class="col-sm-6 col-xs-12">
    <h3><?php _e('Tags', 'dka'); ?></h3>
    <?php echo $chaos_object->tags; ?>
  </div>
  <?php endif; ?>
  <?php if (class_exists('WPDKATags') && intval(get_option('wpdkatags-status', 0)) > 0) : //iff status is active or frozen ?>
  <div class="col-sm-6 col-xs-12">
    <h3>
      <?php _e('User Tags', 'wpdkatags'); ?>
      <?php if (current_user_can(WPDKATags::CAPABILITY)) : ?>
      <button style="padding:2px 5px;" class="btn btn-sm btn-default" id="object-taggable"
        data-dka-taggable="<?php echo !$chaos_object->taggable; ?>">
        <?php $chaos_object->taggable ? _e('Disable', 'dka') : _e('Enable', 'dka'); ?>
      </button>
      <?php endif; ?>
    </h3>
    <div class="usertags-wrap">
      <?php echo $chaos_object->usertags; ?>
    </div>
  </div>
  <?php endif; ?>
</div>


<?php if (class_exists('WPDKACollections') && current_user_can('edit_posts') && count($chaos_object->collections_raw) > 0): ?>
<div class="collection-container">
  <?php echo $chaos_object->collections; ?>
</div>
<?php endif; ?>


<?php if (current_user_can(WPDKA::PUBLISH_STATE_CAPABILITY)): ?> <!--Makes sure the user has the capability to unpublish or republish-->
<hr>
<div class="publish">
  <h3>Publicer/afpublicer</h3>
 <?php if (!$chaos_object->isPublished): ?> <!--Material is not published (no accesspoint)-->
   <?php if ($chaos_object->hasDKA2MetaDataSchema): ?> <!-- Makes sure object has DKA2 metadata schema -->
     <?php if ($chaos_object->unpublishedByCurator): ?> <!--User has unpublished material-->
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
   <?php if ($chaos_object->hasDKA2MetaDataSchema): ?>
     <p><?php _e('This object is published and is visible for other users.', 'dka'); ?></p>
     <button data-dka-publish="0" id="publishState" class="btn btn-danger"><?php _e('Unpublish this object', 'dka'); ?></button>
   <?php else: ?>
     <p><?php _e('Object is using an old metadata schema. You can not change the publish state for this object.', 'dka'); ?></p>
   <?php endif; ?>
 <?php endif; ?>
</div>
<?php endif; ?>
