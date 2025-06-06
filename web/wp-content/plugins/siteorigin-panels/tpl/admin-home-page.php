<?php
$settings = siteorigin_panels_setting();
$builder_supports = apply_filters( 'siteorigin_panels_builder_supports', array(), $post, $panels_data );
?>

<div class="wrap" id="panels-home-page">
	<form
		action="<?php echo esc_url( add_query_arg( 'page', 'so_panels_home_page' ) ); ?>"
		class="hide-if-no-js siteorigin-panels-builder-form"
		method="post"
		id="panels-home-page-form"
		data-type="custom_home_page"
		data-post-id="<?php echo (int) get_the_ID(); ?>"
		data-preview-url="<?php echo esc_url( SiteOrigin_Panels::preview_url() ); ?>"
		data-builder-supports="<?php echo wp_json_encode( $builder_supports ); ?>"
		>
		<div id="icon-index" class="icon32"><br></div>
		<h2>
			<label class="so-toggle-switch">
				<input class="so-toggle-switch-input" type="checkbox" <?php checked( ( get_option( 'siteorigin_panels_home_page_id' ) && get_option( 'siteorigin_panels_home_page_id' ) == get_option( 'page_on_front' ) && get_option( 'show_on_front' ) == 'page' ) ); ?> name="siteorigin_panels_home_enabled">
				<span class="so-toggle-switch-label" data-on="<?php esc_html_e( 'On', 'siteorigin-panels' ); ?>" data-off="<?php esc_html_e( 'Off', 'siteorigin-panels' ); ?>"></span>
				<span class="so-toggle-switch-handle"></span>
			</label>

			<?php esc_html_e( 'Custom Home Page', 'siteorigin-panels' ); ?>

			<?php if ( get_option( 'siteorigin_panels_home_page_id' ) && ( $the_page = get_post( get_option( 'siteorigin_panels_home_page_id' ) ) ) ) { ?>
				<div id="panels-view-as-page">
					<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $the_page->ID . '&action=edit' ) ); ?>" class="add-new-h2"><?php esc_html_e( 'Edit As Page', 'siteorigin-panels' ); ?></a>
				</div>
			<?php } ?>
		</h2>

		<?php
		if ( isset( $_POST['_sopanels_home_nonce'] ) && wp_verify_nonce( $_POST['_sopanels_home_nonce'], 'save' ) ) {
			global $post;
			?>
			<div id="message" class="updated">
				<p>
					<?php
					echo preg_replace(
						'/1\{ *(.*?) *\}/',
						'<a href="' . esc_url( get_the_permalink( $post ) ) . '">$1</a>',
						esc_html__( 'Home page updated. 1{View page}.', 'siteorigin-panels' )
					);
					?>
				</p>
			</div>
		<?php } ?>

		<div class="siteorigin-panels-builder-container so-panels-loading">

		</div>

		<script type="text/javascript">
			( function( builderId, panelsData ){
				// Create the panels_data input
				document.write( '<input name="panels_data" type="hidden" class="siteorigin-panels-data-field" id="panels-data-field-' + builderId + '" />' );
				document.getElementById( 'panels-data-field-' + builderId ).value = JSON.stringify( panelsData );
			} )( "home-page", <?php echo wp_json_encode( $panels_data ); ?> );
		</script>

		<p><input type="submit" class="button button-primary" id="panels-save-home-page" value="<?php esc_attr_e( 'Save Home Page', 'siteorigin-panels' ); ?>" /></p>
		<input type="hidden" id="post_content" name="post_content"/>
		<?php wp_nonce_field( 'save', '_sopanels_home_nonce' ); ?>
	</form>
	<noscript><p><?php esc_html_e( 'This interface requires Javascript', 'siteorigin-panels' ); ?></p></noscript>
</div>
