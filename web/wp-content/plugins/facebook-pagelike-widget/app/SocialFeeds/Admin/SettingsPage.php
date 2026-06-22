<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Load Data
$saved_feeds = get_option( 'fb_widget_saved_shortcodes', array() );
$edit_id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : -1;
$current     = ( $edit_id >= 0 && isset( $saved_feeds[$edit_id] ) ) ? $saved_feeds[$edit_id] : null;

// 2. Handle Save/Update Post
if ( isset( $_POST['fb_save_shortcode'] ) ) {
    check_admin_referer( 'fb_save_action', 'fb_nonce' );

    $new_name      = sanitize_text_field( $_POST['feed_name'] );
    $new_shortcode = stripslashes( $_POST['generated_shortcode'] );

    $entry = array(
        'name'      => $new_name,
        'shortcode' => $new_shortcode
    );

    if ( $edit_id >= 0 && isset( $saved_feeds[$edit_id] ) ) {
        $saved_feeds[$edit_id] = $entry;
    } else {
        $saved_feeds[] = $entry;
    }

    update_option( 'fb_widget_saved_shortcodes', $saved_feeds );

    $listing_url = admin_url( 'admin.php?page=bz-social-feeds-listing' );
    echo '<div class="updated"><p>Configuration saved! Redirecting...</p></div>';
    echo "<script>window.location.href='{$listing_url}';</script>";
    exit;
}

/**
 * Pre-fill Logic
 */
$defaults = array(
    'fb_url' => 'https://www.facebook.com/WordPress',
    'width'  => '340', 'height' => '500', 'tabs' => 'timeline',
    'small'  => 'false', 'adapt' => 'true', 'cover' => 'false',
    'face'   => 'true', 'lazy' => 'false', 'lang' => 'en_US'
);

if ( $current ) {
    $sc = $current['shortcode'];
    preg_match('/fb_url="([^"]+)"/', $sc, $m_url);
    preg_match('/width="([^"]+)"/', $sc, $m_w);
    preg_match('/height="([^"]+)"/', $sc, $m_h);
    preg_match('/data_tabs="([^"]+)"/', $sc, $m_t);
    preg_match('/data_small_header="([^"]+)"/', $sc, $m_sm);
    preg_match('/data_adapt_container_width="([^"]+)"/', $sc, $m_ad);
    preg_match('/data_hide_cover="([^"]+)"/', $sc, $m_hc);
    preg_match('/data_show_facepile="([^"]+)"/', $sc, $m_fp);
    preg_match('/data_lazy="([^"]+)"/', $sc, $m_lz);
    preg_match('/lang="([^"]+)"/', $sc, $m_lg);

    $vals = array(
        'fb_url' => $m_url[1] ?? $defaults['fb_url'],
        'width'  => $m_w[1]   ?? $defaults['width'],
        'height' => $m_h[1]   ?? $defaults['height'],
        'tabs'   => $m_t[1]   ?? $defaults['tabs'],
        'small'  => $m_sm[1]  ?? $defaults['small'],
        'adapt'  => $m_ad[1]  ?? $defaults['adapt'],
        'cover'  => $m_hc[1]  ?? $defaults['cover'],
        'face'   => $m_fp[1]  ?? $defaults['face'],
        'lazy'   => $m_lz[1]  ?? $defaults['lazy'],
        'lang'   => $m_lg[1]  ?? $defaults['lang']
    );
} else {
    $vals = $defaults;
}
?>

<div class="wrap">
    <nav class="nav-tab-wrapper wp-clearfix" style="margin-bottom: 20px;">
        <a href="#generator" class="nav-tab nav-tab-active" id="link-tab-generator"><?php echo ( $edit_id >= 0 ) ? 'Edit Feed' : 'Feed Shortcode Generator'; ?></a>
        <a href="#instructions" class="nav-tab" id="link-tab-instructions">Instructions</a>
    </nav>
    <div id="section-generator" class="fb-tab-content">
        <div style="display: flex; gap: 20px; align-items: flex-start;">
            <div style="flex: 2; background: #fff; padding: 20px; border: 1px solid #ccd0d4;" >
                <form id="fb-full-gen-form">
                    <table class="form-table">
                        <tr>
                            <th>Facebook Page URL</th>
                            <td><input type="text" name="fb_url" class="regular-text" value="<?php echo esc_attr($vals['fb_url']); ?>"></td>
                        </tr>
                        <tr>
                            <th>Tabs</th>
                            <td>
                                <?php $current_tabs = explode(',', $vals['tabs']); ?>
                                <label><input type="checkbox" name="tabs" value="timeline" <?php checked(in_array('timeline', $current_tabs)); ?>> Timeline</label><br>
                                <label><input type="checkbox" name="tabs" value="events" <?php checked(in_array('events', $current_tabs)); ?>> Events</label><br>
                                <label><input type="checkbox" name="tabs" value="messages" <?php checked(in_array('messages', $current_tabs)); ?>> Messages</label>
                            </td>
                        </tr>
                        <tr>
                            <th>Dimensions</th>
                            <td>
                                Width: <input type="number" name="width" value="<?php echo esc_attr($vals['width']); ?>" style="width:70px;">
                                Height: <input type="number" name="height" value="<?php echo esc_attr($vals['height']); ?>" style="width:70px;">
                            </td>
                        </tr>
                        <tr>
                            <th>Display Options</th>
                            <td>
                                <label><input type="checkbox" name="adapt" <?php checked($vals['adapt'], 'true'); ?>> Adapt Container Width</label><br>
                                <label><input type="checkbox" name="small_hdr" <?php checked($vals['small'], 'true'); ?>> Use Small Header</label><br>
                                <label><input type="checkbox" name="hide_cvr" <?php checked($vals['cover'], 'true'); ?>> Hide Cover Photo</label><br>
                                <label><input type="checkbox" name="facepile" <?php checked($vals['face'], 'true'); ?>> Show Friend's Faces</label><br>
                                <label><input type="checkbox" name="lazy" <?php checked($vals['lazy'], 'true'); ?>> Enable Lazy Loading</label>
                            </td>
                        </tr>
                        <!-- <tr>
                            <th>Language</th>
                            <td><input type="text" name="lang" value="<?php echo esc_attr($vals['lang']); ?>" style="width:80px;"></td>
                        </tr> -->
                        <tr>
                            <th><label for="fb_lang">Language</label></th>
                            <td>
                                <?php
                                // 1. Locate the JSON file (Assumes it is in the same folder as your class files)
                                $locales_path = FB_WIDGET_PLUGIN_BASE_URL . 'FacebookLocales.json';
                                $locales_data = array();

                                if ( file_exists( $locales_path ) ) {
                                    $json_content = file_get_contents( $locales_path );
                                    $locales_data = json_decode( $json_content, true );
                                }

                                // 2. Fallback if file is missing or empty
                                if ( empty( $locales_data ) ) {
                                    $locales_data = array( 'English (US)' => 'en_US' );
                                }
                                ?>
                                <select name="lang" id="fb_lang" style="width:250px;">
                                    <?php foreach ( $locales_data as $lang_name => $lang_code ) : ?>
                                        <option value="<?php echo esc_attr( $lang_code ); ?>" <?php selected( $vals['lang'], $lang_code ); ?>>
                                            <?php echo esc_html( $lang_name ); ?> (<?php echo esc_html( $lang_code ); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Select the language for the Facebook button and plugin text.</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <div style="flex: 1; background: #f0f0f1; padding: 20px; border: 1px solid #ccd0d4; position: sticky; top: 40px;">
                <h3>Generated Shortcode</h3>
                <textarea id="live-sc" readonly
                        style="width:100%; height:160px; font-family: monospace; cursor: pointer; background: #fff; margin-bottom: 10px;"></textarea>

                <button type="button" id="copy-btn-gen" class="button button-secondary" style="margin-bottom: 20px;">
                    <span class="dashicons dashicons-clipboard"></span>
                    <span id="copy-text-gen">Copy Shortcode</span>
                </button>

                <form method="post" style="border-top: 1px solid #ccd0d4; padding-top: 20px;">
                    <?php wp_nonce_field( 'fb_save_action', 'fb_nonce' ); ?>
                    <input type="hidden" name="generated_shortcode" id="hidden-sc-input">

                    <p><label><strong>Feed Name: (for your reference)</strong></label>
                    <input type="text" name="feed_name" value="<?php echo $current ? esc_attr($current['name']) : ''; ?>" required style="width:100%; margin-top:5px;"></p>

                    <button type="submit" name="fb_save_shortcode" class="button button-primary" style="height: 40px;">
                        <?php echo ( $edit_id >= 0 ) ? 'Update Feed' : 'Save Feed'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div id="section-instructions" class="fb-tab-content" style="display:none;">
        <h1>Display Your Feed</h1>
        <div style="display: flex; gap: 20px; align-items: flex-start;">
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4;">
                <div class="postbox" style="border: none;">
                    <h2 class="hndle"><span>How to use this plugin</span></h2>
                    <div class="inside instruction-grid">
                        <div class="instruction-column">
                            <h3><span class="dashicons dashicons-layout"></span> WordPress Block (Gutenberg)</h3>
                            <p>This is the recommended way for modern WordPress sites:</p>
                            <ol>
                                <li>Edit any Post or Page.</li>
                                <li>Add the <strong>"Facebook Page Feeds"</strong> block.</li>
                                <li>In the sidebar settings, pick your saved configuration by name.</li>
                            </ol>
                        </div>
                        <div class="instruction-column">
                            <h3><span class="dashicons dashicons-shortcode"></span> WordPress Widget / Classic</h3>
                            <p>For sidebars or older editors:</p>
                            <ol>
                                <li>Go to <strong>Appearance > Widgets</strong>.</li>
                                <li>Add a <strong>Shortcode block</strong> or <strong>Custom HTML widget</strong>.</li>
                                <li>Paste the generated shortcode from the "All Facebook feeds" page.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('fb-full-gen-form');
    const display = document.getElementById('live-sc');
    const hidden = document.getElementById('hidden-sc-input');
    const copyBtn = document.getElementById('copy-btn-gen');
    const copyText = document.getElementById('copy-text-gen');

    function update() {
        const data = new FormData(form);
        const tabs = [];
        form.querySelectorAll('input[name="tabs"]:checked').forEach(i => tabs.push(i.value));

        const sc = `[fb_widget fb_url="${data.get('fb_url')}" ` +
                   `width="${data.get('width')}" height="${data.get('height')}" ` +
                   `data_tabs="${tabs.join(',') || 'timeline'}" ` +
                   `data_small_header="${form.querySelector('input[name="small_hdr"]').checked}" ` +
                   `data_adapt_container_width="${form.querySelector('input[name="adapt"]').checked}" ` +
                   `data_hide_cover="${form.querySelector('input[name="hide_cvr"]').checked}" ` +
                   `data_show_facepile="${form.querySelector('input[name="facepile"]').checked}" ` +
                   `data_lazy="${form.querySelector('input[name="lazy"]').checked}" ` +
                   `lang="${data.get('lang')}"]`;

        display.value = sc;
        hidden.value = sc;
    }

    // 1. Select all on click
    display.addEventListener('click', function() {
        this.select();
        this.setSelectionRange(0, 99999);
    });

    // 2. Copy Button Logic
    copyBtn.addEventListener('click', function() {
        navigator.clipboard.writeText(display.value).then(() => {
            const oldTxt = copyText.innerText;
            copyText.innerText = 'Copied!';
            copyBtn.classList.replace('button-secondary', 'button-primary');

            setTimeout(() => {
                copyText.innerText = oldTxt;
                copyBtn.classList.replace('button-primary', 'button-secondary');
            }, 2000);
        });
    });

    form.addEventListener('input', update);
    update();

    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.fb-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();

            // Update Tab UI
            tabs.forEach(t => t.classList.remove('nav-tab-active'));
            this.classList.add('nav-tab-active');

            // Update Content UI
            const target = this.getAttribute('href').replace('#', 'section-');
            console.log(target)
            contents.forEach(c => c.style.display = 'none');
            document.getElementById(target).style.display = 'block';
        });
    });

    // Inside your DOMContentLoaded script
    const langSelect = form.querySelector('select[name="lang"]');
    langSelect.addEventListener('change', update);

})();
</script>
