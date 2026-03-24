<?php
/**
 * Render for Facebook Page Feeds Block
 */

// 1. Get the saved index from the block attribute
$index = isset($attributes['selectedFeedIndex']) ? $attributes['selectedFeedIndex'] : '';

// 2. Fetch the latest data from the database
$saved_feeds = get_option('fb_widget_saved_shortcodes', array());
$shortcode = '';

// 3. Match the index to the fresh shortcode string
if ($index !== '' && isset($saved_feeds[$index])) {
    $shortcode = $saved_feeds[$index]['shortcode'];
}

$wrapper_attributes = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attributes; ?>>
    <?php if (!empty($attributes['title'])) : ?>
        <h2 class="widget-title" style="margin-bottom:10px;"><?php echo esc_html($attributes['title']); ?></h2>
    <?php endif; ?>

    <?php if (!empty($shortcode)) : ?>

        <?php echo do_shortcode(stripslashes($shortcode)); ?>

    <?php else: ?>

        <?php if ( is_user_logged_in() && current_user_can('edit_posts') ) : ?>
            <div style="padding: 20px; border: 1px dashed #ccc; background: #f9f9f9; text-align: center;">
                <p style="margin: 0; color: #666;">
                    <strong><?php _e('Facebook Feed Block:', 'social-feeds-for-wordpress'); ?></strong><br>
                    <?php if ( empty($saved_feeds) ) : ?>
                        <?php _e('No feeds found.', 'social-feeds-for-wordpress'); ?>
                        <a href="<?php echo admin_url('admin.php?page=bz-social-feeds-listing&action=new'); ?>" target="_blank">
                            <?php _e('Create your first feed here.', 'social-feeds-for-wordpress'); ?>
                        </a>
                    <?php else: ?>
                        <?php _e('Please edit this page and select a feed configuration in the block settings.', 'social-feeds-for-wordpress'); ?>
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <?php endif; ?>

    <?php endif; ?>
</div>
