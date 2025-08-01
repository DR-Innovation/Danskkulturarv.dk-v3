<?php

/**
 * Display a loop of posts.
 *
 * Class SiteOrigin_Panels_Widgets_PostLoop
 */
class SiteOrigin_Panels_Widgets_PostLoop extends WP_Widget {
	public static $rendering_loop;

	public static $current_loop_template;
	public static $current_loop_instance;
	public static $current_pagination_id;

	/**
	 * @var SiteOrigin_Panels_Widgets_PostLoop_Helper
	 */
	private $helper;

	public function __construct() {
		parent::__construct(
			'siteorigin-panels-postloop',
			__( 'Post Loop', 'siteorigin-panels' ),
			array(
				'description' => __( 'Displays a post loop.', 'siteorigin-panels' ),
			),
			array(
				'width' => 800,
			)
		);
	}

	/**
	 * Are we currently rendering a post loop
	 *
	 * @return bool
	 */
	public static function is_rendering_loop() {
		return self::$rendering_loop;
	}

	/**
	 * Which post loop is currently being rendered
	 *
	 * @return array
	 */
	public static function get_current_loop_template() {
		return self::$current_loop_template;
	}

	/**
	 * Which post loop is currently being rendered
	 *
	 * @return array
	 */
	public static function get_current_loop_instance() {
		return self::$current_loop_instance;
	}

	/**
	 * The pagination id used in custom format pagination links
	 *
	 * @return array
	 */
	public static function get_current_pagination_id() {
		return self::$current_pagination_id;
	}

	/**
	 * Update the widget
	 *
	 * @param array $new
	 * @param array $old
	 *
	 * @return array
	 */
	public function update( $new, $old ) {
		$new['pagination_id'] = rand();

		if ( class_exists( 'SiteOrigin_Widget' ) && class_exists( 'SiteOrigin_Widget_Field_Posts' ) ) {
			$helper = $this->get_helper_widget( $this->get_loop_templates() );

			return $helper->update( $new, $old );
		} else {
			$new['more'] = ! empty( $new['more'] );

			return $new;
		}
	}

	private static function is_legacy_widget_block_preview() {
		if ( empty( $_GET['legacy-widget-preview'] ) ) {
			return false;
		}

		$id_base = isset( $_GET['legacy-widget-preview']['idBase'] ) ? sanitize_key( wp_unslash( $_GET['legacy-widget-preview']['idBase'] ) ) : '';

		return 'siteorigin-panels-postloop' === $id_base || 'siteorigin-panels-builder' === $id_base;
	}

	private static function is_layout_block_preview() {
		return isset( $_POST['action'] ) && $_POST['action'] == 'so_panels_layout_block_preview';
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( empty( $instance['template'] ) ) {
			return;
		}

		// The Post Loop widget should only preview in WP Admin if it's a Layout Block preview, or a legacy Widget Block preview.
		if ( is_admin() && ! self::is_legacy_widget_block_preview() && ! self::is_layout_block_preview() ) {
			return;
		}

		static $depth = 0;
		$depth++;

		if ( $depth > 1 ) {
			// Because of infinite loops, don't render this post loop if its inside another
			$depth--;
			echo $args['before_widget'] . $args['after_widget'];

			return;
		}

		$query_args = $instance;
		//If Widgets Bundle post selector is available and a posts query has been saved using it.
		if ( function_exists( 'siteorigin_widget_post_selector_process_query' ) && ! empty( $instance['posts'] ) ) {
			$query_args = siteorigin_widget_post_selector_process_query( $instance['posts'] );
			$query_args['additional'] = empty( $instance['additional'] ) ? array() : $instance['additional'];
		} else {
			if ( ! empty( $instance['posts'] ) ) {
				// This is using the new WB 1.9 posts field
				$query_args = wp_parse_args( $instance['posts'], $query_args );
			}

			if ( ! empty( $query_args['sticky'] ) ) {
				switch( $query_args['sticky'] ) {
					case 'ignore':
						$query_args['ignore_sticky_posts'] = 1;
						break;

					case 'only':
						$query_args['post__in'] = get_option( 'sticky_posts' );
						break;

					case 'exclude':
						$query_args['post__not_in'] = get_option( 'sticky_posts' );
						break;
				}
			}
			unset( $query_args['template'] );
			unset( $query_args['title'] );
			unset( $query_args['sticky'] );

			if ( empty( $query_args['additional'] ) ) {
				$query_args['additional'] = array();
			}
		}
		$query_args = wp_parse_args( $query_args['additional'], $query_args );
		unset( $query_args['additional'] );

		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() ) {
			if ( apply_filters( 'siteorigin_panels_post_loop_custom_pagination', false  ) ) {
				if ( isset( $instance['pagination_id'] ) ) {
					self::$current_pagination_id = $instance['pagination_id'];

					if (
						isset( $_GET[ 'page-' . self::$current_pagination_id ] ) &&
						is_numeric( $_GET[ 'page-' . self::$current_pagination_id ] )
					) {
						$query_args['paged'] = (int) $_GET[ 'page-' . self::$current_pagination_id ];
					}
				}
			} else {
				if ( get_query_var( 'paged' ) ) {
					// When the widget appears on a sub page.
					$query_args['paged'] = get_query_var( 'paged' );
				} elseif ( strpos( $_SERVER['REQUEST_URI'], '/page/' ) !== false ) {
					// When the widget appears on the home page.
					preg_match( '/\/page\/([0-9]+)\//', $_SERVER['REQUEST_URI'], $matches );

					if ( ! empty( $matches[1] ) ) {
						$query_args['paged'] = (int) $matches[1];
					} else {
						$query_args['paged'] = 1;
					}
				}
			}

			if ( ! isset( $query_args['paged'] ) ) {
				$query_args['paged'] = 1;
			}
		} else {
			// Get current page number when we're not using permalinks
			$query_args['paged'] = isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1;
		}

		// Exclude the current post to prevent possible infinite loop

		global $siteorigin_panels_current_post;

		if (
			apply_filters( 'siteorigin_panels_postloop_exclude_current_post', true ) &&
			! empty( $siteorigin_panels_current_post )
		) {
			if ( ! empty( $query_args['post__not_in'] ) ) {
				if ( ! is_array( $query_args['post__not_in'] ) ) {
					$query_args['post__not_in'] = explode( ',', $query_args['post__not_in'] );
					$query_args['post__not_in'] = array_map( 'intval', $query_args['post__not_in'] );
				}
				$query_args['post__not_in'][] = $siteorigin_panels_current_post;
			} else {
				$query_args['post__not_in'] = array( $siteorigin_panels_current_post );
			}
		}

		if ( ! empty( $query_args['post__in'] ) && ! is_array( $query_args['post__in'] ) ) {
			$query_args['post__in'] = explode( ',', $query_args['post__in'] );
			$query_args['post__in'] = array_map( 'intval', $query_args['post__in'] );
		}

		// Create the query
		query_posts( apply_filters( 'siteorigin_panels_postloop_query_args', $query_args ) );
		echo $args['before_widget'];

		// Filter the title
		$instance['title'] = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . wp_kses_post( $instance['title'] ) . $args['after_title'];
		}

		global $more;
		$old_more = $more;
		$more = empty( $instance['more'] );

		self::$rendering_loop = true;
		self::$current_loop_instance = $instance;
		self::$current_loop_template = $instance['template'];

		if ( preg_match( '/\/content*/', '/' . $instance['template'] ) ) {
			while ( have_posts() ) {
				the_post();
				self::locate_template( $instance['template'], true, false );
			}
		} else {
			self::locate_template( $instance['template'], true, false );
		}

		self::$rendering_loop = false;
		self::$current_loop_instance = null;
		self::$current_loop_template = null;
		self::$current_pagination_id = null;

		echo $args['after_widget'];

		// Reset everything
		wp_reset_query();
		$depth--;
	}

	/**
	 * Display the form for the post loop.
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		$templates = $this->get_loop_templates();

		if ( empty( $templates ) ) {
			?><p><?php _e( "Your theme doesn't have any post loops.", 'siteorigin-panels' ); ?></p><?php
			return;
		}

		// If the Widgets Bundle is installed and the post selector is available, use that.
		// Otherwise revert back to our own form fields.
		if ( class_exists( 'SiteOrigin_Widget' ) && class_exists( 'SiteOrigin_Widget_Field_Posts' ) ) {
			$helper = $this->get_helper_widget( $templates );
			$helper->form( $instance );
		} else {
			$instance = wp_parse_args( $instance, array(
				'title' => '',
				'template' => 'loop.php',

				// Query args
				'post_type' => 'post',
				'posts_per_page' => '',

				'order' => 'DESC',
				'orderby' => 'date',

				'sticky' => '',

				'additional' => '',
				'more' => false,
			) );

			?>
			<div class="siteorigin-widget-content">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'siteorigin-panels' ); ?></label>
					<input type="text" class="widefat siteorigin-widget-field" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>">
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>">
							<?php esc_html_e( 'Template', 'siteorigin-panels' ); ?>
					</label>
					<select
						id="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'template' ) ); ?>"
						class="siteorigin-widget-field"
					>
						<?php foreach ( $templates as $template ) { ?>
							<option value="<?php echo esc_attr( $template ); ?>" <?php selected( $instance['template'], $template ); ?>>
								<?php
								$headers = get_file_data( self::locate_template( $template ), array(
									'loop_name' => 'Loop Name',
								) );
								echo esc_html( ! empty( $headers['loop_name'] ) ? $headers['loop_name'] : $template );
								?>
							</option>
						<?php } ?>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'more' ) ); ?>">
						<?php esc_html_e( 'More Link', 'siteorigin-panels' ); ?>
					</label>
					<input
						type="checkbox"
						class="siteorigin-widget-field"
						id="<?php echo esc_attr( $this->get_field_id( 'more' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'more' ) ); ?>"
						<?php checked( $instance['more'] ); ?>
					/>
					<br/>
					<small>
						<?php esc_html_e( 'If the template supports it, cut posts and display the more link.', 'siteorigin-panels' ); ?>
					</small>
				</p>
				<?php
				if ( ! empty( $instance['posts'] ) ) {
					$instance = wp_parse_args( $instance['posts'], $instance );
					unset( $instance['posts'] );
					//unset post__in and taxonomies?
				}
				// Get all the loop template files
				$post_types = get_post_types( array( 'public' => true ) );
				$post_types = array_values( $post_types );
				$post_types = array_diff( $post_types, array( 'attachment', 'revision', 'nav_menu_item' ) );
				?>
				<p>
					<label
						for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"
					>
						<?php esc_html_e( 'Post Type', 'siteorigin-panels' ); ?>
					</label>
					<select
						id="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'post_type' ) ); ?>"
						class="siteorigin-widget-field"
						value="<?php echo esc_attr( $instance['post_type'] ); ?>"
					>
						<?php foreach ( $post_types as $type ) { ?>
							<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $instance['post_type'], $type ); ?>>
								<?php echo esc_html( $type ); ?>
							</option>
						<?php } ?>
					</select>
				</p>

				<p>
					<label
						for="<?php echo esc_attr( $this->get_field_id( 'posts_per_page' ) ); ?>"
					>
						<?php esc_html_e( 'Posts Per Page', 'siteorigin-panels' ); ?>
					</label>
					<input
						type="text"
						class="small-text siteorigin-widget-field"
						id="<?php echo esc_attr( $this->get_field_id( 'posts_per_page' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'posts_per_page' ) ); ?>" class="siteorigin-widget-field"
						value="<?php echo esc_attr( $instance['posts_per_page'] ); ?>"
					/>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>">
						<?php esc_html_e( 'Order By', 'siteorigin-panels' ); ?>
					</label>

					<select
						id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>"
						class="siteorigin-widget-field"
						value="<?php echo esc_attr( $instance['orderby'] ); ?>"
					>
						<option value="none" <?php selected( $instance['orderby'], 'none' ); ?>>
							<?php esc_html_e( 'None', 'siteorigin-panels' ); ?>
						</option>
						<option value="ID" <?php selected( $instance['orderby'], 'ID' ); ?>>
							<?php esc_html_e( 'Post ID', 'siteorigin-panels' ); ?>
						</option>
						<option value="author" <?php selected( $instance['orderby'], 'author' ); ?>>
							<?php esc_html_e( 'Author', 'siteorigin-panels' ); ?>
						</option>
						<option value="name" <?php selected( $instance['orderby'], 'name' ); ?>>
							<?php esc_html_e( 'Name', 'siteorigin-panels' ); ?>
						</option>
						<option value="name" <?php selected( $instance['orderby'], 'name' ); ?>>
							<?php esc_html_e( 'Name', 'siteorigin-panels' ); ?>
						</option>
						<option value="date" <?php selected( $instance['orderby'], 'date' ); ?>>
							<?php esc_html_e( 'Date', 'siteorigin-panels' ); ?>
						</option>
						<option value="modified" <?php selected( $instance['orderby'], 'modified' ); ?>>
							<?php esc_html_e( 'Modified', 'siteorigin-panels' ); ?>
						</option>
						<option value="parent" <?php selected( $instance['orderby'], 'parent' ); ?>>
							<?php esc_html_e( 'Parent', 'siteorigin-panels' ); ?>
						</option>
						<option value="rand" <?php selected( $instance['orderby'], 'rand' ); ?>>
							<?php esc_html_e( 'Random', 'siteorigin-panels' ); ?>
						</option>
						<option value="comment_count" <?php selected( $instance['orderby'], 'comment_count' ); ?>>
							<?php esc_html_e( 'Comment Count', 'siteorigin-panels' ); ?>
						</option>
						<option value="menu_order" <?php selected( $instance['orderby'], 'menu_order' ); ?>>
							<?php esc_html_e( 'Menu Order', 'siteorigin-panels' ); ?>
						</option>
						<option value="post__in" <?php selected( $instance['orderby'], 'post__in' ); ?>>
							<?php esc_html_e( 'Post In Order', 'siteorigin-panels' ); ?>
						</option>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>">
						<?php esc_html_e( 'Order', 'siteorigin-panels' ); ?>
					</label>
					<select
						id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"
						class="siteorigin-widget-field"
						name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>"
						value="<?php echo esc_attr( $instance['order'] ); ?>"
					>
						<option value="DESC" <?php selected( $instance['order'], 'DESC' ); ?>>
							<?php esc_html_e( 'Descending', 'siteorigin-panels' ); ?>
						</option>
						<option value="ASC" <?php selected( $instance['order'], 'ASC' ); ?>>
							<?php esc_html_e( 'Ascending', 'siteorigin-panels' ); ?>
						</option>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'sticky' ) ); ?>">
						<?php esc_html_e( 'Sticky Posts', 'siteorigin-panels' ); ?>
					</label>
					<select
						id="<?php echo esc_attr( $this->get_field_id( 'sticky' ) ); ?>"
						class="siteorigin-widget-field"
						name="<?php echo esc_attr( $this->get_field_name( 'sticky' ) ); ?>"
						value="<?php echo esc_attr( $instance['sticky'] ); ?>"
					>
						<option value="" <?php selected( $instance['sticky'], '' ); ?>>
							<?php esc_html_e( 'Default', 'siteorigin-panels' ); ?>
						</option>
						<option value="ignore" <?php selected( $instance['sticky'], 'ignore' ); ?>>
							<?php esc_html_e( 'Ignore Sticky', 'siteorigin-panels' ); ?>
						</option>
						<option value="exclude" <?php selected( $instance['sticky'], 'exclude' ); ?>>
							<?php esc_html_e( 'Exclude Sticky', 'siteorigin-panels' ); ?>
						</option>
						<option value="only" <?php selected( $instance['sticky'], 'only' ); ?>>
							<?php esc_html_e( 'Only Sticky', 'siteorigin-panels' ); ?>
						</option>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'additional' ) ); ?>">
						<?php esc_html_e( 'Additional ', 'siteorigin-panels' ); ?>
					</label>
					<input
						type="text"
						class="widefat siteorigin-widget-field"
						id="<?php echo esc_attr( $this->get_field_id( 'additional' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'additional' ) ); ?>" class="siteorigin-widget-field" value="<?php echo esc_attr( $instance['additional'] ); ?>"
					/>
					<small>
						<?php
						echo preg_replace(
							'/1\{ *(.*?) *\}/',
							'<a href="http://codex.wordpress.org/Function_Reference/query_posts">$1</a>',
							esc_html__( 'Additional query arguments. See 1{query_posts}.', 'siteorigin-panels' )
						);
						?>
					</small>
				</p>

				<a href="https://siteorigin.com/page-builder/bundled-widgets/post-loop-widget/" class="siteorigin-widget-help-link siteorigin-panels-help-link" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Help', 'siteorigin-panels' ); ?>
				</a>
			</div>
			<?php
		}
	}

	/**
	 * Get all the existing files
	 *
	 * @return array
	 */
	public function get_loop_templates() {
		$templates = array();

		$template_files = array(
			'loop*.php',
			'*/loop*.php',
			'content*.php',
			'*/content*.php',
		);

		$template_dirs = array( get_template_directory(), get_stylesheet_directory() );
		$template_dirs = apply_filters( 'siteorigin_panels_postloop_template_directory', $template_dirs );
		$template_dirs = array_unique( $template_dirs );

		foreach ( $template_dirs  as $dir ) {
			foreach ( $template_files as $template_file ) {
				foreach ( (array) glob( $dir . '/' . $template_file ) as $file ) {
					if ( file_exists( $file ) ) {
						$templates[] = str_replace( $dir . '/', '', $file );
					}
				}
			}
		}
		$templates = array_unique( apply_filters( 'siteorigin_panels_postloop_templates', $templates ) );
		$templates = array_filter( $templates, array( $this, 'validate_template_file' ) );

		// Update array indexes to ensure logical indexing
		sort( $templates );

		return $templates;
	}

	/**
	 * Checks if a template file is valid
	 *
	 * @return bool
	 */
	public function validate_template_file( $filename ) {
		return validate_file( $filename ) == 0 &&
			substr( $filename, -4 ) == '.php' &&
			self::locate_template( $filename ) != '';
	}

	/**
	 * Find the location of a given template. Either in the theme or in the plugin directory.
	 *
	 * @param bool $load
	 * @param bool $require_once
	 *
	 * @return string The template location.
	 */
	public static function locate_template( $template_names, $load = false, $require_once = true ) {
		$located = '';

		foreach ( (array) $template_names as $template_name ) {
			$located = locate_template( $template_name, false );

			if ( ! $located && file_exists( WP_PLUGIN_DIR . '/' . $template_name ) ) {
				// Template added by a plugin
				$located = WP_PLUGIN_DIR . '/' . $template_name;
			}
		}

		if ( $load && '' != $located ) {
			load_template( $located, $require_once );
		}

		return $located;
	}

	/**
	 * Get the helper widget based on the Widgets Bundle's classes.
	 *
	 * @param $templates array Blog loop templates.
	 *
	 * @return mixed
	 */
	private function get_helper_widget( $templates ) {
		if ( empty( $this->helper ) &&
			class_exists( 'SiteOrigin_Widget' ) &&
			class_exists( 'SiteOrigin_Widget_Field_Posts' )
		) {
			$this->helper = new SiteOrigin_Panels_Widgets_PostLoop_Helper( $templates );
		}
		// These ensure the form fields name attributes are correct.
		$this->helper->id_base = $this->id_base;
		$this->helper->id = $this->id;
		$this->helper->number = $this->number;

		return $this->helper;
	}
}
