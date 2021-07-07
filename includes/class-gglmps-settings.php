<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Gglmps_Settings_Tabs' ) ) {
	class Gglmps_Settings_Tabs extends Bws_Settings_Tabs {
		private $lang_codes;

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
			global $gglmps_options, $gglmps_plugin_info;

			$tabs = array(
				'settings' 		=> array( 'label' => __( 'Settings', 'bws-google-maps' ) ),
				'misc' 			=> array( 'label' => __( 'Misc', 'bws-google-maps' ) ),
				'custom_code' 	=> array( 'label' => __( 'Custom Code', 'bws-google-maps' ) ),
				'license'		=> array( 'label' => __( 'License Key', 'bws-google-maps' ) )
			);

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $gglmps_plugin_info,
				'prefix' 			 => 'gglmps',
				'default_options' 	 => gglmps_get_options_default(),
				'options' 			 => $gglmps_options,
				'tabs' 				 => $tabs,
				'doc_link'			 => 'https://bestwebsoft.com/documentation/maps/google-maps-by-bestwebsoft-how-to-use-instruction/',
				'wp_slug'			 => 'bws-google-maps',
				'link_key' 			 => '5ae35807d562bf6b5c67db88fefece60',
				'link_pn' 			 => '124'
			) );

			$this->lang_codes = array(
				'ar' => 'Arabic', 'eu' => 'Basque', 'bn' => 'Bengali', 'bg' => 'Bilgarian', 'ca' => 'Catalan', 'zh-CN' => 'Chinese (Simplified)', 'zh-TW' => 'Chinese (Traditional)',
				'hr' => 'Croatian', 'cs' => 'Czech', 'da' => 'Danish', 'nl' => 'Dutch', 'en' => 'English', 'en-AU' => 'English (Australian)', 'en-GB' => 'English (Great Britain)',
				'fa' => 'Farsi', 'fil' => 'Filipino', 'fi' => 'Finnish', 'fr' => 'French', 'gl' => 'Galician', 'de' => 'German', 'el' => 'Greek', 'gu' => 'Gujarati', 'iw' => 'Hebrew',
				'hi' => 'Hindi', 'hu' => 'Hungarian', 'id' => 'Indonesian', 'it' => 'Italian', 'ja' => 'Japanese', 'kn' => 'Kannada', 'ko' => 'Korean', 'lv' => 'Latvian',
				'lt' => 'Lithuanian', 'ml' => 'Malayalam', 'mr' => 'Marthi', 'no' => 'Norwegian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'pt-BR' => 'Portuguese (Brazil)',
				'pt-PT' => 'Portuguese (Portugal)', 'ro' => 'Romanian', 'ru' => 'Russian', 'sr' => 'Serbian', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'es' => 'Spanish', 'sv' => 'Swedish',
				'tl' => 'Tagalog', 'ta' => 'Tamil', 'te' => 'Telugu', 'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Ukrainian', 'vi' => 'Vietnamese'
			);

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
			<div id="gglmps_settings_notice" class="updated below-h2">
				<?php _e( 'These settings will be applied to newly added maps by default.', 'bws-google-maps' ); ?>
			</div>
		<?php }	

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {
			$message = $notice = $error = '';

			/* Takes all the changed settings on the plugin's admin page and saves them in array 'gglmps_options'. */
			$this->options['api_key'] = isset( $_REQUEST['gglmps_main_api_key'] ) ? trim( stripslashes( sanitize_text_field( $_REQUEST['gglmps_main_api_key'] ) ) ) : '';
			$this->options['language'] = ( isset( $_REQUEST['gglmps_main_language'] ) && array_key_exists( $_REQUEST['gglmps_main_language'], $this->lang_codes ) ) ? $_REQUEST['gglmps_main_language'] : 'en';
			$this->options['additional_options'] 	= isset( $_REQUEST['gglmps_additional_options'] ) ? 1 : 0;
			$this->options['basic'] 				= array(
				'alignment' => isset( $_REQUEST['gglmps_basic_alignment'] ) && in_array( $_REQUEST['gglmps_basic_alignment'], array( 'left', 'center', 'right' ) ) ? $_REQUEST['gglmps_basic_alignment'] : 'left',
				'map_type'  => isset( $_REQUEST['gglmps_basic_map_type'] ) && in_array( $_REQUEST['gglmps_basic_map_type'], array( 'roadmap', 'terrain', 'satellite', 'hybrid' ) ) ? $_REQUEST['gglmps_basic_map_type'] : 'roadmap',
				'tilt45'    => isset( $_REQUEST['gglmps_basic_tilt45'] ) ? 1 : 0,
				'zoom'      => isset( $_REQUEST['gglmps_basic_zoom'] ) ? intval( $_REQUEST['gglmps_basic_zoom'] ) : 3,
				'width_unit' => isset( $_REQUEST['gglmps_basic_width_unit'] ) && 'px' == $_REQUEST['gglmps_basic_width_unit'] ? 'px' : '%'
			);

			if ( 'px' == $this->options['basic']['width_unit'] && isset( $_REQUEST['gglmps_basic_width'] ) ) {
				if ( absint( $_REQUEST['gglmps_basic_width'] ) < 150 ) {
					$this->options['basic']['width'] = 150;
				} elseif ( absint( $_REQUEST['gglmps_basic_width'] ) > 1000 ) {
					$this->options['basic']['width'] = 1000;
				} else {
					$this->options['basic']['width'] = absint( $_REQUEST['gglmps_basic_width'] );
				}
			} else {
				$this->options['basic']['width'] = isset( $_REQUEST['gglmps_basic_width'] ) && absint( $_REQUEST['gglmps_basic_width'] ) < 100 ? absint( $_REQUEST['gglmps_basic_width'] ) : 100;
			}

			if ( isset( $_REQUEST['gglmps_basic_height'] ) ) {
				if ( absint( $_REQUEST['gglmps_basic_height'] ) < 150 ) {
					$this->options['basic']['height'] = 150;
				} elseif ( absint( $_REQUEST['gglmps_basic_height'] ) > 1000 ) {
					$this->options['basic']['height'] = 1000;
				} else {
					$this->options['basic']['height'] = absint( $_REQUEST['gglmps_basic_height'] );
				}
			}

			$this->options['controls'] = array(
				'map_type'            => isset( $_REQUEST['gglmps_control_map_type'] ) ? 1 : 0,
				'rotate'              => isset( $_REQUEST['gglmps_control_rotate'] ) ? 1 : 0,
				'zoom'                => isset( $_REQUEST['gglmps_control_zoom'] ) ? 1 : 0,
				'scale'               => isset( $_REQUEST['gglmps_control_scale'] ) ? 1 : 0
			);

			$message = __( 'Settings saved.', 'bws-google-maps' );
			update_option( 'gglmps_options', $this->options );

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 *
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Maps Settings', 'bws-google-maps' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr>
					<th><?php _e( 'API Key', 'bws-google-maps' ); ?></th>
					<td>
						<input id="gglmps_main_api_key" name="gglmps_main_api_key" type="text" maxlength='250' value="<?php echo $this->options['api_key']; ?>">
						<p class="bws_info">
							<?php printf(
								'%1$s <a href="https://developers.google.com/maps/documentation/javascript/usage#usage_limits" target="_blank">%2$s</a>, %3$s <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">%4$s</a>, %5$s.',
								__( 'Using an API key enables you to monitor your application Maps API usage, and ensures that Google can contact you about your application if necessary. If your application Maps API usage exceeds the', 'bws-google-maps' ),
								__( 'Usage Limits', 'bws-google-maps' ),
								__( 'you must load the Maps API using an API key in order to purchase additional quota. Click', 'bws-google-maps' ),
								__( 'here','bws-google-maps' ),
								__( 'to know how to get the API key','bws-google-maps' )
							); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Language', 'bws-google-maps' ); ?></th>
					<td>
						<select id="gglmps_main_language" name="gglmps_main_language">
							<?php foreach ( $this->lang_codes as $key => $lang ) {
								printf(
									'<option value="%1$s" %2$s>%3$s</option>',
									$key,
									$this->options['language'] == $key ? 'selected="selected"' : '',
									$lang
								);
							} ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Width', 'bws-google-maps' ); ?></th>
					<td>
						<input id="gglmps_basic_width" name="gglmps_basic_width" type="number" min="1" max="1000" value="<?php echo $this->options['basic']['width']; ?>" placeholder="<?php _e( 'Enter width', 'bws-google-maps' ); ?>">
						<select name="gglmps_basic_width_unit">
							<option value="px" <?php if ( 'px' == $this->options['basic']['width_unit'] ) echo 'selected'; ?>><?php _e( 'px', 'bws-google-maps' ); ?></option>
							<option value="%" <?php if ( '%' == $this->options['basic']['width_unit'] ) echo 'selected'; ?>>%</option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Height', 'bws-google-maps' ); ?></th>
					<td>
						<input id="gglmps_basic_height" name="gglmps_basic_height" type="number" min="150" max="1000" value="<?php echo $this->options['basic']['height']; ?>" placeholder="<?php _e( 'Enter height', 'bws-google-maps' ); ?>">
						<?php _e( 'px', 'bws-google-maps' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Alignment', 'bws-google-maps' ); ?></th>
					<td>
						<select id="gglmps_basic_alignment" name="gglmps_basic_alignment">
							<option value="left" <?php selected( $this->options['basic']['alignment'], 'left' ); ?>><?php _e( 'Left', 'bws-google-maps' ); ?></option>
							<option value="center" <?php selected( $this->options['basic']['alignment'], 'center' ); ?>><?php _e( 'Center', 'bws-google-maps' ); ?></option>
							<option value="right" <?php selected( $this->options['basic']['alignment'], 'right' ); ?>><?php _e( 'Right', 'bws-google-maps' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Type', 'bws-google-maps' ); ?></th>
					<td>
						<select id="gglmps_basic_map_type" name="gglmps_basic_map_type">
							<option value="roadmap" <?php selected( $this->options['basic']['map_type'], 'roadmap' ); ?>><?php _e( 'Roadmap', 'bws-google-maps' ); ?></option>
							<option value="terrain" <?php selected( $this->options['basic']['map_type'], 'terrain' ); ?>><?php _e( 'Terrain', 'bws-google-maps' ); ?></option>
							<option value="satellite" <?php selected( $this->options['basic']['map_type'], 'satellite' ); ?>><?php _e( 'Satellite', 'bws-google-maps' ); ?></option>
							<option value="hybrid" <?php selected( $this->options['basic']['map_type'], 'hybrid' ); ?>><?php _e( 'Hybrid', 'bws-google-maps' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'View', 'bws-google-maps' ); ?></th>
					<td>
						<input name="gglmps_basic_tilt45" type="checkbox" <?php checked( $this->options['basic']['tilt45'], 1 ); ?> />
						<span class="bws_info"><?php _e( 'This option is only available for Satellite and Hybrid map types (if such snapshots are available).', 'bws-google-maps' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Zoom', 'bws-google-maps' ); ?></th>
					<td>
						<input id="gglmps_basic_zoom" name="gglmps_basic_zoom" type="number" min='0' max='21' value="<?php echo $this->options['basic']['zoom']; ?>">
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
							<input name="gglmps_additional_options" class="bws_option_affect" data-affect-show=".gglmps_additional_options" type="checkbox" <?php checked( $this->options['additional_options'], 1 ); ?> />
							<span class="bws_info"><?php _e( 'Visibility and map action controls.', 'bws-google-maps' ); ?></span>
						</label>
						<fieldset class="gglmps_additional_options">
							<br>
							<label>
								<input name="gglmps_control_map_type" type="checkbox" <?php checked( $this->options['controls']['map_type'], 1 ); ?> />
								<?php _e( 'Type', 'bws-google-maps' ); ?>
							</label>
							<br>
							<label>
								<input name="gglmps_control_rotate" type="checkbox" <?php checked( $this->options['controls']['rotate'], 1 ); ?> />
								<?php _e( 'Rotate', 'bws-google-maps' ); ?>
								<span class="bws_info"><?php _e( 'This option is only available if View 45° option is checked.', 'bws-google-maps' ); ?></span>
							</label>
							<br>
							<label>
								<input name="gglmps_control_zoom" type="checkbox" <?php checked( $this->options['controls']['zoom'], 1 ); ?> />
								<?php _e( 'Zoom', 'bws-google-maps' ); ?>
							</label>
							<br>
							<label>
								<input name="gglmps_control_scale" type="checkbox" <?php checked( $this->options['controls']['scale'], 1 ); ?> />
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
								<th><?php _e( 'Zoom', 'bws-google-maps' ); ?></th>
								<td>
									<input <?php disabled( true ); ?> name="gglmps_basic_auto_zoom" type="checkbox" />
									<?php _e( 'Auto', 'bws-google-maps' ); ?>
									<p class="bws_info"><?php _e( 'The map will be scaled to display all markers.', 'bws-google-maps' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><?php _e( 'Controls Options', 'bws-google-maps' ); ?></th>
								<td>
									<fieldset>
										<label>
											<input <?php disabled( true ); ?> name="gglmps_control_street_view" type="checkbox" />
											<?php _e( 'Street View', 'bws-google-maps' ); ?>
										</label>
										<br>
										<label>
											<input <?php disabled( true ); ?> name="gglmps_control_map_draggable" type="checkbox" />
											<?php _e( 'Draggable', 'bws-google-maps' ); ?>
										</label>
										<br>
										<label>
											<input <?php disabled( true ); ?> name="gglmps_control_double_click" type="checkbox" />
											<?php _e( 'Double Click', 'bws-google-maps' ); ?>											
										</label>
										<br>
										<label>
											<input <?php disabled( true ); ?> name="gglmps_control_scroll_wheel" type="checkbox" />
											<?php _e( 'Scroll Wheel', 'bws-google-maps' ); ?>
										</label>
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
