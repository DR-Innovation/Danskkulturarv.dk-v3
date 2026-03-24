<?php
/**
 * Saved Facebook Feeds Shortcode Listing Page
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Handle Duplicate Action
if ( isset( $_GET['action'] ) && $_GET['action'] === 'duplicate' && isset( $_GET['id'] ) ) {
    $id_to_copy = intval( $_GET['id'] );
    $saved_feeds = get_option( 'fb_widget_saved_shortcodes', array() );

    if ( isset( $saved_feeds[ $id_to_copy ] ) ) {
        // Clone the existing feed data
        $new_feed = $saved_feeds[ $id_to_copy ];

        // Append " (Copy)" to the name so it's easy to identify
        $new_feed['name'] = $new_feed['name'] . ' ' . __( '(Copy)', 'social-feeds-for-wordpress' );

        // Add to the list and save
        $saved_feeds[] = $new_feed;
        update_option( 'fb_widget_saved_shortcodes', $saved_feeds );

        // Redirect to clean the URL
        $clean_url = admin_url( 'admin.php?page=bz-social-feeds-listing' );
        echo "<script>window.location.href='{$clean_url}';</script>";
        exit;
    }
}

/**
 * Handle Delete Action
 */
if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['id'] ) ) {
    $id_to_delete = intval( $_GET['id'] );
    $saved_feeds  = get_option( 'fb_widget_saved_shortcodes', array() );

    if ( isset( $saved_feeds[ $id_to_delete ] ) ) {
        unset( $saved_feeds[ $id_to_delete ] );
        // Re-index and save
        update_option( 'fb_widget_saved_shortcodes', array_values( $saved_feeds ) );

        // Redirect to clean the URL
        $clean_url = admin_url( 'admin.php?page=bz-social-feeds-listing' );
        echo "<script>window.location.href='{$clean_url}';</script>";
        exit;
    }
}

// Fetch the latest saved feeds
$saved_feeds = get_option( 'fb_widget_saved_shortcodes', array() );
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'Facebook feeds', 'social-feeds-for-wordpress' ); ?></h1>
    <a href="<?php echo admin_url( 'admin.php?page=bz-social-feeds-listing&action=new' ); ?>" class="page-title-action"><?php _e( 'Add New', 'social-feeds-for-wordpress' ); ?></a>
    <hr class="wp-header-end">

    <p><?php _e( 'Below is a list of your saved Facebook feed configurations. Copy the shortcode and paste it into any Post, Page or Widget.', 'social-feeds-for-wordpress' ); ?></p>

    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
            <tr>
                <th scope="col" style="width: 20%;"><?php _e( 'Feed Name', 'social-feeds-for-wordpress' ); ?></th>
                <th scope="col" style="width: 55%;"><?php _e( 'Shortcode', 'social-feeds-for-wordpress' ); ?></th>
                <th scope="col" style="width: 15%;"><?php _e( 'Actions', 'social-feeds-for-wordpress' ); ?></th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php if ( ! empty( $saved_feeds ) ) : ?>
                <?php foreach ( $saved_feeds as $id => $feed ) : ?>
                    <tr>
                        <td class="column-title has-row-actions column-primary">
                            <strong><?php echo esc_html( $feed['name'] ); ?></strong>
                            <div class="row-actions">
                                <span class="edit"><a href="<?php echo admin_url( 'admin.php?page=bz-social-feeds-listing&action=edit&id=' . $id ); ?>"><?php _e( 'Edit', 'social-feeds-for-wordpress' ); ?></a> | </span>
                                <span class="duplicate"><a href="<?php echo admin_url( 'admin.php?page=bz-social-feeds-listing&action=duplicate&id=' . $id ); ?>"><?php _e( 'Duplicate', 'social-feeds-for-wordpress' ); ?></a> | </span>
                                <span class="trash"><a href="<?php echo admin_url( 'admin.php?page=bz-social-feeds-listing&action=delete&id=' . $id ); ?>" class="submitdelete" onclick="return confirm('<?php _e( 'Are you sure, you want to delete this Feed?', 'social-feeds-for-wordpress' ); ?>');"><?php _e( 'Delete', 'social-feeds-for-wordpress' ); ?></a></span>
                            </div>
                        </td>
                        <td class="column-shortcode">
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="text" readonly id="sc-input-<?php echo $id; ?>"
                                       value="<?php echo esc_attr( stripslashes( $feed['shortcode'] ) ); ?>"
                                       style="width: 100%; font-family: monospace; background: #f0f0f1; border-color: #ccd0d4;">

                                <button type="button" class="button fb-copy-btn" data-target="sc-input-<?php echo $id; ?>">
                                    <?php _e( 'Copy', 'social-feeds-for-wordpress' ); ?>
                                </button>
                            </div>
                        </td>
                        <td class="column-actions">
                            <a href="<?php echo admin_url( 'admin.php?page=bz-social-feeds-listing&action=edit&id=' . $id ); ?>" class="button">
                                <?php _e( 'Edit', 'social-feeds-for-wordpress' ); ?>
                            </a>
                            <a href="<?php echo admin_url( 'admin.php?page=bz-social-feeds-listing&action=duplicate&id=' . $id ); ?>" class="button">
                                <?php _e( 'Duplicate', 'social-feeds-for-wordpress' ); ?>
                            </a>
                            <a href="<?php echo admin_url( 'admin.php?page=bz-social-feeds-listing&action=delete&id=' . $id ); ?>" class="button">
                                <?php _e( 'Delete', 'social-feeds-for-wordpress' ); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="no-items">
                    <td colspan="3">
                        <?php _e( 'No Feeds found.', 'social-feeds-for-wordpress' ); ?>
                        <a href="<?php echo admin_url( 'admin.php?page=bz-social-feeds-listing&action=new' ); ?>"><?php _e( 'Add New', 'social-feeds-for-wordpress' ); ?></a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyButtons = document.querySelectorAll('.fb-copy-btn');

    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const inputField = document.getElementById(targetId);

            if (!inputField) return;

            // Use the modern Clipboard API
            navigator.clipboard.writeText(inputField.value).then(() => {
                // Visual Feedback
                const originalText = this.innerText;
                this.innerText = '<?php _e( "Copied!", "social-feeds-for-wordpress" ); ?>';
                this.classList.add('button-primary');
                this.style.width = '80px'; // Ensure button doesn't jump size

                setTimeout(() => {
                    this.innerText = originalText;
                    this.classList.remove('button-primary');
                }, 2000);
            }).catch(err => {
                // Fallback for non-HTTPS or older browsers
                inputField.select();
                document.execCommand('copy');
                this.innerText = 'Copied!';
                setTimeout(() => { this.innerText = 'Copy'; }, 2000);
            });
        });
    });
});
</script>

<style>
    .fb-copy-btn {
        min-width: 70px;
        text-align: center;
        transition: all 0.2s ease-in-out;
    }
</style>
