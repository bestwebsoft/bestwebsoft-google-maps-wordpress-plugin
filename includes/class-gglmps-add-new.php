<?php
/**
 * Displays the content on the plugin settings page
 */
if ( ! class_exists( 'Gglmps_Single_Tabs' ) ) {
	class Gglmps_Single_Tabs extends Bws_Settings_Tabs {
		private $gglmps_id, $gglmps_maps, $map_data;
		
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $gglmps_plugin_info, $gglmps_maps, $gglmps_options;

			$tabs = array(
				'settings' 		=> array( 'label' => __( 'Settings', 'bws-google-maps' ) )
			);

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $gglmps_plugin_info,
				'prefix' 			 => 'gglmps',
				'default_options' 	 => gglmps_get_options_default(),
				'options' 			 => $gglmps_options,
				'tabs' 				 => $tabs,
				'wp_slug'			 => 'bws-google-maps',
				'pro_page' 			 => 'admin.php?page=bws-google-maps-pro.php',
				'bws_license_plugin' => 'bws-google-maps-pro/bws-google-maps-pro.php',
				'link_key' 			 => '5ae35807d562bf6b5c67db88fefece60',
				'link_pn' 			 => '124'
			) );

			/* Get map ID. */
			$this->gglmps_id = ! empty( $_REQUEST['gglmps_id'] ) ? intval( $_REQUEST['gglmps_id'] ) : "";
			$this->gglmps_maps = $gglmps_maps;				

			if ( empty( $this->gglmps_id ) ) {
				$this->map_data = array(
					'additional_options' => $this->options['additional_options'],
					'basic'              => array(
						'width'			=> $this->options['basic']['width'],
						'width_unit'	=> $this->options['basic']['width_unit'],
						'height'		=> $this->options['basic']['height'],
						'alignment'		=> $this->options['basic']['alignment'],
						'map_type'		=> $this->options['basic']['map_type'],
						'tilt45'		=> $this->options['basic']['tilt45'],
						'zoom'			=> $this->options['basic']['zoom']
					),
					'controls'           => array(
						'map_type'            => $this->options['controls']['map_type'],
						'rotate'              => $this->options['controls']['rotate'],
						'zoom'                => $this->options['controls']['zoom'],
						'scale'               => $this->options['controls']['scale']
					),
					'markers' => array()
				);
			} else {
				if ( ! isset( $this->gglmps_maps[ $this->gglmps_id ] ) ) {
					wp_die(
						sprintf(
							'<div class="error"><p>%1$s <strong>ID#%2$s</strong> %3$s <a href="admin.php?page=gglmps_manager">%4$s</a> %5$s <a href="admin.php?page=gglmps_editor">%6$s</a>.</p></div>',
							__( 'Map with', 'bws-google-maps' ),
							$this->gglmps_id,
							__( 'not found. You can return to the', 'bws-google-maps' ),
							__( 'Maps manager', 'bws-google-maps' ),
							__( 'or create new map in the', 'bws-google-maps' ),
							__( 'Maps editor', 'bws-google-maps' )
						)
					);
				}

				$this->map_data = $this->gglmps_maps[ $this->gglmps_id ]['data'];
			}			

			add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
			add_action( get_parent_class( $this ) . '_display_custom_messages', array( $this, 'display_custom_messages' ) );
		}

		/**
		 * Display custom error\message\notice
		 * @access public
		 * @param  $save_results - array with error\message\notice
		 * @return void
		 */
		public function display_custom_messages( $save_results ) { ?>
			<noscript>
				<div class="error below-h2">
					<p>
						<?php printf(
							'<strong>%1$s</strong> %2$s.',
							__( 'WARNING:', 'bws-google-maps' ),
							__( 'Maps only works with JavaScript enabled.', 'bws-google-maps' )
						); ?>
					</p>
				</div><!-- .error -->
			</noscript><!-- noscript -->
			<?php if ( $this->options['api_key'] == '' ) { ?>
				<div class="error">
					<p>
						<?php printf( '<strong>%1$s</strong> %2$s <a href="admin.php?page=bws-google-maps.php">%3$s</a>.',
							__( 'Notice:', 'bws-google-maps' ),
							__( 'In order to create a new map, you must enter the API key on the', 'bws-google-maps' ),
							__( 'Maps settings page', 'bws-google-maps' )
						); ?>
					</p>
				</div>
			<?php }			
		}

		/**
		 * Save to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {
			global $wpdb;

			$message = $notice = $error = '';

			$gglmps_map_title = ! empty( $_REQUEST['gglmps_map_title'] ) ? trim( stripslashes( sanitize_text_field( $_REQUEST['gglmps_map_title'] ) ) ) : __( 'No title', 'bws-google-maps' );

			$this->map_data = array(
				'additional_options' => isset( $_REQUEST['gglmps_additional_options'] ) ? 1 : 0,
				'basic'              => array(
					'alignment' => isset( $_REQUEST['gglmps_basic_alignment'] ) && in_array( $_REQUEST['gglmps_basic_alignment'], array( 'left', 'center', 'right' ) ) ? $_REQUEST['gglmps_basic_alignment'] : 'left',
					'map_type'  => isset( $_REQUEST['gglmps_basic_map_type'] ) && in_array( $_REQUEST['gglmps_basic_map_type'], array( 'roadmap', 'terrain', 'satellite', 'hybrid' ) ) ? $_REQUEST['gglmps_basic_map_type'] : 'roadmap',
					'tilt45'    => isset( $_REQUEST['gglmps_basic_tilt45'] ) ? 1 : 0,
					'zoom'      => isset( $_REQUEST['gglmps_basic_zoom'] ) ? intval( $_REQUEST['gglmps_basic_zoom'] ) : $this->options['basic']['zoom'],
					'width_unit' =>isset( $_REQUEST['gglmps_basic_width_unit'] ) && 'px' == $_REQUEST['gglmps_basic_width_unit'] ? 'px' : '%'
				),
				'controls'           => array(
					'map_type'            => isset( $_REQUEST['gglmps_control_map_type'] ) ? 1 : 0,
					'rotate'              => isset( $_REQUEST['gglmps_control_rotate'] ) ? 1 : 0,
					'zoom'                => isset( $_REQUEST['gglmps_control_zoom'] ) ? 1 : 0,
					'scale'               => isset( $_REQUEST['gglmps_control_scale'] ) ? 1 : 0
				),
				'markers' => array()
			);

			if ( 'px' == $this->map_data['basic']['width_unit'] && isset( $_REQUEST['gglmps_basic_width'] ) ) {
				if ( absint( $_REQUEST['gglmps_basic_width'] ) < 150 ) {
					$this->map_data['basic']['width'] = 150;
				} elseif ( absint( $_REQUEST['gglmps_basic_width'] ) > 1000 ) {
					$this->map_data['basic']['width'] = 1000;
				} else {
					$this->map_data['basic']['width'] = absint( $_REQUEST['gglmps_basic_width'] );
				}
			} else {
				$this->map_data['basic']['width'] = isset( $_REQUEST['gglmps_basic_width'] ) && absint( $_REQUEST['gglmps_basic_width'] ) < 100 ? absint( $_REQUEST['gglmps_basic_width'] ) : 100;
			}

			if ( isset( $_REQUEST['gglmps_basic_height'] ) ) {
				if ( absint( $_REQUEST['gglmps_basic_height'] ) < 150 ) {
					$this->map_data['basic']['height'] = 150;
				} elseif ( absint( $_REQUEST['gglmps_basic_height'] ) >  1000 ) {
					$this->map_data['basic']['height'] = 1000;
				} else {
					$this->map_data['basic']['height'] = absint( $_REQUEST['gglmps_basic_height'] );
				}
			}

			if ( isset( $_REQUEST['gglmps_list_marker_latlng'] ) && isset( $_REQUEST['gglmps_list_marker_location'] ) ) {
				$gglmps_marker_latlng = sanitize_text_field( $_REQUEST['gglmps_list_marker_latlng'] );
				$gglmps_marker_location = sanitize_text_field( $_REQUEST['gglmps_list_marker_location'] );
				$gglmps_marker_tooltip = sanitize_text_field( $_REQUEST['gglmps_list_marker_tooltip'] );
				foreach ( $gglmps_marker_location as $key => $value ) {
					$gglmps_marker_location[ $key ] = stripslashes( sanitize_text_field( $value ) );
					$gglmps_marker_latlng[ $key ] = stripslashes( sanitize_text_field( $gglmps_marker_latlng[ $key ] ) );
					$gglmps_marker_tooltip[ $key ] = stripslashes( sanitize_text_field( $gglmps_marker_tooltip[ $key ] ) );
				}
				$this->map_data['markers'] = array_map( null, $gglmps_marker_latlng, $gglmps_marker_location, $gglmps_marker_tooltip );
			}

			if ( empty( $this->gglmps_id ) ) {
				if ( count( $this->gglmps_maps ) == 0 ) {
					$this->gglmps_id = 1;
				} else {
					end( $this->gglmps_maps );
					$this->gglmps_id = key( $this->gglmps_maps ) + 1;
				}

				$this->gglmps_maps[ $this->gglmps_id ] = array(
					'title' => $gglmps_map_title,
					'data'  => $this->map_data,
					'date'  => date( 'Y/m/d' )
				);

				$message = __( 'Map created.', 'bws-google-maps' );	
			} else {
				$this->gglmps_maps[ $this->gglmps_id ] = array(
					'title' => $gglmps_map_title,
					'data'  => $this->map_data,
					'date'  => $this->gglmps_maps[ $this->gglmps_id ]['date']
				);
				$message = __( 'Map updated.', 'bws-google-maps' );
			}

			update_option( 'gglmps_maps', $this->gglmps_maps );				

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Displays the content of the "Settings" on the plugin settings page
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function display_content() {
			$save_results = $this->save_all_tabs_options();

			$title = ! empty( $this->gglmps_id ) ? $this->gglmps_maps[ $this->gglmps_id ]['title'] : ''; ?>
			<h1>
				<?php /* Add page name and add new button to page */
				if ( ! empty( $this->gglmps_id ) ) {
					echo __( 'Edit Map', 'bws-google-maps' ) . '<a class="page-title-action" href="' . admin_url( 'admin.php?page=gglmps_editor' ) . '">' . __( 'Add New', 'bws-google-maps' ) . '</a>';
				} else {
					_e( 'Add New Map', 'bws-google-maps' );
				} ?>
			</h1>
			<?php $this->display_messages( $save_results ); ?>
            <form class="bws_form" method="POST" action="admin.php?page=gglmps_editor&amp;gglmps_id=<?php echo $this->gglmps_id; ?>">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content" style="position: relative;">
                        	<div id="titlediv">
								<div id="titlewrap">
									<input name="gglmps_map_title" size="30" value="<?php echo esc_html( $title ); ?>" id="title" spellcheck="true" autocomplete="off" type="text" placeholder="<?php _e( 'Enter title here', 'bws-google-maps' ); ?>" />
								</div>
								<div class="inside"></div>
							</div>
							<?php $this->display_tabs(); ?>
                        </div><!-- #post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <div class="meta-box-sortables ui-sortable">
                                <div id="submitdiv" class="postbox">
                                    <h3 class="hndle"><?php _e( 'Publish', 'bestwebsoft' ); ?></h3>
                                    <div class="inside">
                                        <div class="submitbox" id="submitpost">
                                            <div id="major-publishing-actions">
                                                <div id="publishing-action">
                                                    <input type="hidden" name="<?php echo $this->prefix; ?>_form_submit" value="submit" />
                                                    <input id="bws-submit-button" type="submit" class="button button-primary button-large" value="<?php echo ( isset( $_GET['gglmps_id'] ) ) ? __( 'Update', 'bws-google-maps' ) : __( 'Publish', 'bws-google-maps' ); ?>" />
													<?php wp_nonce_field( $this->plugin_basename, 'bws_nonce_name' ); ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ( ! empty( $this->gglmps_id ) ) { ?>
									<div class="postbox">
										<h3 class="hndle">
											<?php _e( 'Map Shortcode', 'bws-google-maps' ); ?>
										</h3>
										<div class="inside">
											<?php _e( "Add Map to your posts, pages, custom post types or widgets by using the following shortcode:", 'bws-google-maps' ); ?>
											<?php bws_shortcode_output( '[bws_googlemaps id=' . $this->gglmps_id . ']' ); ?>
										</div>
									</div>
								<?php } ?>
                            </div>
                        </div>
                        <?php if ( ! $this->hide_pro_tabs ) { ?>
                        	<div id="postbox-container-2" class="postbox-container">
                				<div class="postbox">
									<h3 class="hndle">
										<?php _e( 'Map Preview', 'bws-google-maps' ); ?>
									</h3>
									<div class="inside">
										<div class="bws_pro_version_bloc">
						                    <div class="bws_pro_version_table_bloc">
						                        <button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'bws-google-maps' ); ?>"></button>
						                        <div class="bws_table_bg"></div>
						                        <img src="<?php echo plugins_url( 'images/map_preview_example.png', dirname(__FILE__) ); ?>">
						                    </div>
						                    <?php $this->bws_pro_block_links(); ?>
						                </div>
									</div>
								</div>													
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </form>
		<?php }

		/**
		 *
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Map Settings', 'bws-google-maps' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="gglmps_editor_table form-table">
				<tr class="gglmps_markers_wrap">
					<th><?php _e( 'Marker Location', 'bws-google-maps' ); ?></th>
					<td>
						<input id="gglmps_marker_location" type="text" placeholder="<?php _e( 'Enter location or coordinates', 'bws-google-maps' ); ?>" />
						<span class="bws_info">
							<?php _e( 'You should enter coordinates in decimal degrees with no spaces. Example coordinates:', 'bws-google-maps' ); ?> 41.40338,2.17403.
						</span>
						<input id="gglmps_marker_latlng" type="hidden" />
					</td>
				</tr>
				<tr class="gglmps_markers_wrap">
					<th><?php _e( 'Marker Tooltip', 'bws-google-maps' ); ?></th>
					<td>
						<textarea id="gglmps_marker_tooltip" placeholder="<?php _e( 'Enter tooltip', 'bws-google-maps' ); ?>"></textarea>
						<span class="bws_info"><?php _e( 'You can use HTML tags and attributes.', 'bws-google-maps' ); ?></span>
						<p>
							<input class="button-secondary" id="gglmps_marker_add" type="button" value="<?php _e( 'Add marker to list', 'bws-google-maps' ); ?>" />
							<input class="button-secondary" id="gglmps_marker_update" type="button" value="<?php _e( 'Update marker', 'bws-google-maps' ); ?>" />
							<input class="button-secondary" id="gglmps_marker_cancel" type="button" value="<?php _e( 'Cancel', 'bws-google-maps' ); ?>" />
						</p>
					</td>
				</tr>
				<tr class="gglmps_markers_wrap">
					<th><?php _e( 'Markers List', 'bws-google-maps' ); ?></th>
					<td>
						<ul id="gglmps_markers_container">
							<?php if ( count( $this->map_data['markers'] ) == 0 ) { ?>
								<li class="gglmps_no_markers">
									<?php _e( 'No markers', 'bws-google-maps' ); ?>
								</li>
							<?php } else {
								foreach ( $this->map_data['markers'] as $key => $marker ) { ?>
									<li class="gglmps_marker">
										<div class="gglmps_marker_control">
											<span class="gglmps_marker_delete"><?php _e( 'Delete', 'bws-google-maps' ); ?></span>
											<span class="gglmps_marker_edit"><?php _e( 'Edit', 'bws-google-maps' ); ?></span>
											<span class="gglmps_marker_latlng">[<?php echo stripcslashes( $marker[0] ); ?>]</span>
										</div>
										<div class="gglmps_marker_data">
											<div class="gglmps_marker_location"><?php echo stripcslashes( $marker[1] ); ?></div>
											<xmp class="gglmps_marker_tooltip"><?php echo html_entity_decode( stripcslashes( $marker[2] ) ); ?></xmp>
											<input class="gglmps_input_latlng" name="gglmps_list_marker_latlng[]" type="hidden" value="<?php echo $gglmps_marker[0]; ?>" />
											<textarea class="gglmps_textarea_location" name="gglmps_list_marker_location[]"><?php echo stripcslashes( $marker[1] ); ?></textarea>
											<textarea class="gglmps_textarea_tooltip" name="gglmps_list_marker_tooltip[]"><?php echo stripcslashes( $marker[2] ); ?></textarea>
										</div>
									</li>
								<?php }
							} ?>
						</ul>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Width', 'bws-google-maps' ); ?></th>
					<td>
						<input id="gglmps_basic_width" name="gglmps_basic_width" type="number" min="1" max="1000" value="<?php echo $this->map_data['basic']['width']; ?>" placeholder="<?php _e( 'Enter width', 'bws-google-maps' ); ?>">
						<select name="gglmps_basic_width_unit">
							<option value="px" <?php if ( isset( $this->map_data['basic']['width_unit'] ) && 'px' == $this->map_data['basic']['width_unit'] ) echo 'selected'; ?>><?php _e( 'px', 'bws-google-maps' ); ?></option>
							<option value="%" <?php if ( isset( $this->map_data['basic']['width_unit'] ) && '%' == $this->map_data['basic']['width_unit'] ) echo 'selected'; ?>>%</option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Height', 'bws-google-maps' ); ?></th>
					<td>
						<input id="gglmps_basic_height" name="gglmps_basic_height" type="number" min="150" max="1000" value="<?php echo $this->map_data['basic']['height']; ?>" placeholder="<?php _e( 'Enter height', 'bws-google-maps' ); ?>">
						<?php _e( 'px', 'bws-google-maps' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Alignment', 'bws-google-maps' ); ?></th>
					<td>
						<select id="gglmps_basic_alignment" name="gglmps_basic_alignment">
							<option value="left" <?php if ( $this->map_data['basic']['alignment'] == 'left' ) echo 'selected'; ?>><?php _e( 'Left', 'bws-google-maps' ); ?></option>
							<option value="center" <?php if ( $this->map_data['basic']['alignment'] == 'center' ) echo 'selected'; ?>><?php _e( 'Center', 'bws-google-maps' ); ?></option>
							<option value="right" <?php if ( $this->map_data['basic']['alignment'] == 'right' ) echo 'selected'; ?>><?php _e( 'Right', 'bws-google-maps' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Type', 'bws-google-maps' ); ?></th>
					<td>
						<select id="gglmps_basic_map_type" name="gglmps_basic_map_type">
							<option value="roadmap" <?php if ( $this->map_data['basic']['map_type'] == 'roadmap' ) echo 'selected'; ?>><?php _e( 'Roadmap', 'bws-google-maps' ); ?></option>
							<option value="terrain" <?php if ( $this->map_data['basic']['map_type'] == 'terrain' ) echo 'selected'; ?>><?php _e( 'Terrain', 'bws-google-maps' ); ?></option>
							<option value="satellite" <?php if ( $this->map_data['basic']['map_type'] == 'satellite' ) echo 'selected'; ?>><?php _e( 'Satellite', 'bws-google-maps' ); ?></option>
							<option value="hybrid" <?php if ( $this->map_data['basic']['map_type'] == 'hybrid' ) echo 'selected'; ?>><?php _e( 'Hybrid', 'bws-google-maps' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'View', 'bws-google-maps' ); ?>&nbsp;45&deg;</th>
					<td>
						<input id="gglmps_basic_tilt45" name="gglmps_basic_tilt45" type="checkbox" <?php if ( $this->map_data['basic']['tilt45'] == 1 ) echo 'checked="checked"'; ?> />
						<span class="bws_info"><?php _e( 'This option is only available for Satellite and Hybrid map types (if such snapshots are available).', 'bws-google-maps' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Zoom', 'bws-google-maps' ); ?></th>
					<td>
						<input id="gglmps_basic_zoom" name="gglmps_basic_zoom" type="number" min='0' max='21' value="<?php echo $this->map_data['basic']['zoom']; ?>">
						<div id="gglmps_zoom_wrap">
							<div id="gglmps_zoom_slider"></div>
							<span id="gglmps_zoom_value"></span>
						</div>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Controls Options', 'bws-google-maps' ); ?></th>
					<td>
						<label>
							<input name="gglmps_additional_options" class="bws_option_affect" data-affect-show=".gglmps_additional_options" type="checkbox" <?php checked( $this->map_data['additional_options'], 1 ); ?> />
							<span class="bws_info"><?php _e( 'Visibility and map action controls.', 'bws-google-maps' ); ?></span>
						</label>
						<fieldset class="gglmps_additional_options">
							<br>
							<label>
								<input name="gglmps_control_map_type" type="checkbox" <?php checked( $this->map_data['controls']['map_type'], 1 ); ?> />
								<?php _e( 'Type', 'bws-google-maps' ); ?>
							</label>
							<br>
							<label>
								<input name="gglmps_control_rotate" type="checkbox" <?php checked( $this->map_data['controls']['rotate'], 1 ); ?> />
								<?php _e( 'Rotate', 'bws-google-maps' ); ?>
								<span class="bws_info"><?php _e( 'This option is only available if View 45° option is checked.', 'bws-google-maps' ); ?></span>
							</label>
							<br>
							<label>
								<input name="gglmps_control_zoom" type="checkbox" <?php checked( $this->map_data['controls']['zoom'], 1 ); ?> />
								<?php _e( 'Zoom', 'bws-google-maps' ); ?>
							</label>
							<br>
							<label>
								<input name="gglmps_control_scale" type="checkbox" <?php checked( $this->map_data['controls']['scale'], 1 ); ?> />
								<?php _e( 'Scale', 'bws-google-maps' ); ?>
							</label>
						</fieldset>						
					</td>
				</tr>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
                <div class="bws_pro_version_bloc">
                    <div class="bws_pro_version_table_bloc">
                        <button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'bws-google-maps' ); ?>"></button>
                        <div class="bws_table_bg"></div>
                        <table class="form-table bws_pro_version">
                        	<tr>
								<th><?php _e( 'Style', 'bws-google-maps' ); ?></th>
								<td>
									<select disabled="disabled">
										<option>Midnight Commander (<?php _e( 'Default', 'bws-google-maps' ); ?>)</option>
									</select>
									<p class="bws_info"><?php _e( 'This option is only available for Roadmap, Terrain, and Hybrid map types.', 'bws-google-maps' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><?php _e( 'Zoom', 'bws-google-maps' ); ?></th>
								<td>
									<input disabled="disabled" name="gglmps_basic_auto_zoom" type="checkbox" />
									<label><?php _e( 'Auto', 'bws-google-maps' ); ?></label>
									<span class="bws_info"><?php _e( 'The map will be scaled to display all markers.', 'bws-google-maps' ); ?></span>
								</td>
							</tr>
							<tr>
								<th><?php _e( 'Controls Options', 'bws-google-maps' ); ?></th>
								<td>
									<fieldset>
										<input disabled="disabled" name="gglmps_control_street_view" type="checkbox" />
										<label><?php _e( 'Street View', 'bws-google-maps' ); ?></label>
										<br/>
										<input disabled="disabled" name="gglmps_control_map_draggable" type="checkbox" />
										<label><?php _e( 'Draggable', 'bws-google-maps' ); ?></label>
										<br/>
										<input disabled="disabled" name="gglmps_control_double_click" type="checkbox" />
										<label><?php _e( 'Double Click', 'bws-google-maps' ); ?></label>
										<br/>
										<input disabled="disabled" name="gglmps_control_scroll_wheel" type="checkbox" />
										<label><?php _e( 'Scroll Wheel', 'bws-google-maps' ); ?></label>
									</fieldset>
								</td>
							</tr>
                        </table>
                    </div>
                    <?php $this->bws_pro_block_links(); ?>
                </div>
            <?php }
		}
	}
}