<?php
/*
Plugin Name: Google Maps by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/bws-google-maps/
Description: Add customized Google maps to WordPress posts, pages and widgets.
Author: BestWebSoft
Text Domain: bws-google-maps
Domain Path: /languages
Version: 1.3.6
Author URI: https://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2017 BestWebSoft  ( https://support.bestwebsoft.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
* Function to display admin menu.
*/
if ( ! function_exists( 'gglmps_admin_menu' ) ) {
	function gglmps_admin_menu() {
		global $submenu;
		bws_general_menu();
		$settings = add_submenu_page( 'bws_panel', __( 'Google Maps Settings', 'bws-google-maps' ), 'Google Maps', 'manage_options', 'bws-google-maps.php', 'gglmps_settings_page' );
		$hook = add_menu_page( 'Google Maps', 'Google Maps', 'edit_posts', 'gglmps_manager', 'gglmps_manager_page', 'dashicons-location', '54.1' );
		$gglmps_manager = add_submenu_page( 'gglmps_manager', __( 'Google Maps Editor', 'bws-google-maps' ), __( 'Add New', 'bws-google-maps' ), 'manage_options', 'gglmps_editor', 'gglmps_editor_page' );
		
		add_action( "load-$hook", 'gglmps_screen_options' );
		add_action( 'load-' . $settings, 'gglmps_add_tabs' );
		add_action( 'load-' . $gglmps_manager, 'gglmps_add_tabs' );
		
		if ( isset( $submenu['gglmps_manager'] ) )
			$submenu['gglmps_manager'][] = array( __( 'Settings', 'bws-google-maps' ), 'manage_options', admin_url( 'admin.php?page=bws-google-maps.php' ) );
	}
}

if ( ! function_exists( 'gglmps_plugins_loaded' ) ) {
	function gglmps_plugins_loaded() {
		/* Internationalization. */
		load_plugin_textdomain( 'bws-google-maps', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/*
* Function to add localization to the plugin.
*/
if ( ! function_exists ( 'gglmps_init' ) ) {
	function gglmps_init() {
		global $gglmps_plugin_info;
		
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		
		if ( empty( $gglmps_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$gglmps_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version  */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $gglmps_plugin_info, '3.8' );

		if ( ! is_admin() || isset( $_GET['page'] ) && ( $_GET['page'] == 'bws-google-maps.php' || $_GET['page'] == 'gglmps_manager' || $_GET['page'] == 'gglmps_editor' ) ) {
			gglmps_default_options();
		}
	}
}

/*
* Function to add plugin version.
*/
if ( ! function_exists ( 'gglmps_admin_init' ) ) {
	function gglmps_admin_init() {
		global $bws_plugin_info, $gglmps_plugin_info, $bws_shortcode_list;

		if ( empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '124', 'version' => $gglmps_plugin_info['Version'] );
		/* add Google Maps to global $bws_shortcode_list  */
		$bws_shortcode_list['gglmps'] = array( 'name' => 'Google Maps', 'js_function' => 'gglmps_shortcode_init' );
	}
}

/*
* Function to set up default options.
*/
if ( ! function_exists ( 'gglmps_default_options' ) ) {
	function gglmps_default_options() {
		global $gglmps_options, $gglmps_default_options, $gglmps_maps, $gglmps_plugin_info;

		$gglmps_default_options = array(
			'plugin_option_version'		=> $gglmps_plugin_info['Version'],
			'display_settings_notice'	=> 1,
			'suggest_feature_banner'	=> 1,
			'first_install'				=>	strtotime( "now" ),
			'api_key'              	 	=> '',
			'language'              	=> 'en',
			'additional_options'    	=> 0,
			'basic'                 	=> array(
				'width'			=> 100,
				'width_unit'	=> '%',
				'height'    	=> 300,
				'alignment' 	=> 'left',
				'map_type'  	=> 'roadmap',
				'tilt45'    	=> 1,
				'zoom'      	=> 3
			),
			'controls'              	=> array(
				'map_type'            => 1,
				'rotate'              => 1,
				'zoom'                => 1,
				'scale'               => 1
			)			
		);
		if ( ! get_option( 'gglmps_options' ) )
			add_option( 'gglmps_options', $gglmps_default_options );

		$gglmps_options = get_option( 'gglmps_options' );

		if ( ! get_option( 'gglmps_maps' ) )
			add_option( 'gglmps_maps', array() );

		$gglmps_maps = get_option( 'gglmps_maps' );

		if ( ! isset( $gglmps_options['plugin_option_version'] ) || $gglmps_options['plugin_option_version'] != $gglmps_plugin_info['Version'] ) {
			/**
			* @since 1.3.4
			* @todo remove after 01.02.2017
			*/
			if ( ! isset( $gglmps_options['basic']['width_unit'] ) )
				$gglmps_options['basic']['width_unit'] = 'px';
			$gglmps_default_options['display_settings_notice'] = 0;
			/* end @todo */

			/* show pro features */
			$gglmps_options['hide_premium_options'] = array();
			$gglmps_options = array_merge( $gglmps_default_options, $gglmps_options );
			$gglmps_options['plugin_option_version'] = $gglmps_plugin_info['Version'];
			update_option( 'gglmps_options', $gglmps_options );
		}
	}
}


/*
* Function to display plugin main settings page.
*/
if ( ! function_exists( 'gglmps_settings_page' ) ) {
	function gglmps_settings_page() {
		global $gglmps_options, $gglmps_default_options, $gglmps_plugin_info, $wp_version;
		$plugin_basename = plugin_basename( __FILE__ );
		$gglmps_lang_codes = array(
			'ar' => 'Arabic', 'eu' => 'Basque', 'bn' => 'Bengali', 'bg' => 'Bilgarian', 'ca' => 'Catalan', 'zh-CN' => 'Chinese (Simplified)', 'zh-TW' => 'Chinese (Traditional)',
			'hr' => 'Croatian', 'cs' => 'Czech', 'da' => 'Danish', 'nl' => 'Dutch', 'en' => 'English', 'en-AU' => 'English (Australian)', 'en-GB' => 'English (Great Britain)',
			'fa' => 'Farsi', 'fil' => 'Filipino', 'fi' => 'Finnish', 'fr' => 'French', 'gl' => 'Galician', 'de' => 'German', 'el' => 'Greek', 'gu' => 'Gujarati', 'iw' => 'Hebrew',
			'hi' => 'Hindi', 'hu' => 'Hungarian', 'id' => 'Indonesian', 'it' => 'Italian', 'ja' => 'Japanese', 'kn' => 'Kannada', 'ko' => 'Korean', 'lv' => 'Latvian',
			'lt' => 'Lithuanian', 'ml' => 'Malayalam', 'mr' => 'Marthi', 'no' => 'Norwegian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'pt-BR' => 'Portuguese (Brazil)',
			'pt-PT' => 'Portuguese (Portugal)', 'ro' => 'Romanian', 'ru' => 'Russian', 'sr' => 'Serbian', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'es' => 'Spanish', 'sv' => 'Swedish',
			'tl' => 'Tagalog', 'ta' => 'Tamil', 'te' => 'Telugu', 'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Ukrainian', 'vi' => 'Vietnamese'
		);
		$error = $message = "";

		if ( isset( $_REQUEST['gglmps_form_submit'] ) && check_admin_referer( $plugin_basename ) ) {
			if ( isset( $_POST['bws_hide_premium_options'] ) ) {
				$hide_result = bws_hide_premium_options( $gglmps_options );
				$gglmps_options = $hide_result['options'];
			}

			$gglmps_options['api_key'] 				= isset( $_REQUEST['gglmps_main_api_key'] ) ? trim( stripslashes( esc_html( $_REQUEST['gglmps_main_api_key'] ) ) ) : $gglmps_default_options['api_key'];
			$gglmps_options['language' ] 			= isset( $_REQUEST['gglmps_main_language'] ) ? $_REQUEST['gglmps_main_language'] : $gglmps_default_options['language'];
			$gglmps_options['additional_options'] 	= isset( $_REQUEST['gglmps_settings_additional_options'] ) ? 1 : 0;
			$gglmps_options['basic'] 				= array(
				'alignment' => isset( $_REQUEST['gglmps_basic_alignment'] ) ? $_REQUEST['gglmps_basic_alignment'] : $gglmps_default_options['basic']['alignment'],
				'map_type'  => isset( $_REQUEST['gglmps_basic_map_type'] ) ? $_REQUEST['gglmps_basic_map_type'] : $gglmps_default_options['basic']['map_type'],
				'tilt45'    => isset( $_REQUEST['gglmps_basic_tilt45'] ) ? 1 : 0,
				'zoom'      => isset( $_REQUEST['gglmps_basic_zoom'] ) && is_numeric( intval( $_REQUEST['gglmps_basic_zoom'] ) ) ? intval( $_REQUEST['gglmps_basic_zoom'] ) : $gglmps_default_options['basic']['zoom']
			);

			$gglmps_options['basic']['width_unit'] = ( 'px' == $_REQUEST['gglmps_basic_width_unit'] ) ? 'px' : '%';

			if ( 'px' == $gglmps_options['basic']['width_unit'] ) 
				$gglmps_options['basic']['width'] = isset( $_REQUEST['gglmps_basic_width'] ) && intval( $_REQUEST['gglmps_basic_width'] ) > 150 ? intval( $_REQUEST['gglmps_basic_width'] ) : 150;
			else
				$gglmps_options['basic']['width'] = isset( $_REQUEST['gglmps_basic_width'] ) && intval( $_REQUEST['gglmps_basic_width'] ) < 100 ? intval( $_REQUEST['gglmps_basic_width'] ) : 100;

			$gglmps_options['basic']['height'] = isset( $_REQUEST['gglmps_basic_height'] ) && intval( $_REQUEST['gglmps_basic_height'] ) > 150 ? intval( $_REQUEST['gglmps_basic_height'] ) : 150;

			$gglmps_options['controls'] 			= array(
					'map_type'            => isset( $_REQUEST['gglmps_control_map_type'] ) ? 1 : 0,
					'rotate'              => isset( $_REQUEST['gglmps_control_rotate'] ) ? 1 : 0,
					'zoom'                => isset( $_REQUEST['gglmps_control_zoom'] ) ? 1 : 0,
					'scale'               => isset( $_REQUEST['gglmps_control_scale'] ) ? 1 : 0
				);

			$message = __( 'Settings saved.', 'bws-google-maps' );
			update_option( 'gglmps_options', $gglmps_options );
		}

		$bws_hide_premium_options_check = bws_hide_premium_options_check( $gglmps_options );

		/* Add restore function */
		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$gglmps_options = $gglmps_default_options;
			update_option( 'gglmps_options', $gglmps_options );
			$message = __( 'All plugin settings were restored.', 'bws-google-maps' );
		}

		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename, 'gglmps_options' );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
			elseif ( ! empty( $go_pro_result['message'] ) )
				$message = $go_pro_result['message'];
		} ?>
		<div id="gglmps_settings_wrap" class="wrap">
			<h1 class="gglmps_settings_title"><?php _e( 'Google Maps Settings', 'bws-google-maps' ); ?></h1>
			<ul class="subsubsub gglmps_how_to_use">
				<li><a href="https://docs.google.com/document/d/1sY7nLypbL7Mv_F95eQ_xFkdRs_VjG8h2kBpuRg_IbxY/" target="_blank"><?php _e( 'How to Use Step-by-step Instruction', 'bws-google-maps' ); ?></a></li>
			</ul>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=bws-google-maps.php">
					<?php _e( 'Basic', 'bws-google-maps' ); ?>
				</a>
				<a class="nav-tab<?php if ( isset( $_GET['action'] ) && 'appearance' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=bws-google-maps.php&amp;action=appearance">
					<?php _e( 'Appearance', 'bws-google-maps' ); ?>
				</a>
				<a class="nav-tab <?php if ( isset( $_GET['action'] ) && 'custom_code' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=bws-google-maps.php&amp;action=custom_code">
					<?php _e( 'Custom code', 'bws-google-maps' ); ?>
				</a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=bws-google-maps.php&amp;action=go_pro">
					<?php _e( 'Go PRO', 'bws-google-maps' ); ?>
				</a>
			</h2>
			<noscript>
				<div class="error below-h2">
					<p>
						<?php printf(
							'<strong>%1$s</strong> %2$s.',
							__( 'WARNING:', 'bws-google-maps' ),
							__( 'Google Maps only works with JavaScript enabled', 'bws-google-maps' )
						); ?>
					</p>
				</div><!-- .error -->
			</noscript><!-- noscript -->
			<div class="updated fade below-h2"<?php if ( '' == $message || "" != $error ) echo " style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error below-h2" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>
			<?php if ( ! empty( $hide_result['message'] ) ) { ?>
				<div class="updated fade below-h2"><p><strong><?php echo $hide_result['message']; ?></strong></p></div>
			<?php }
			bws_show_settings_notice();
			if ( ! isset( $_GET['action'] ) ) {
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} else { ?>
					<div id="gglmps_settings_notice" class="updated below-h2">
						<?php _e( 'These settings are used as default when you create a new map.', 'bws-google-maps' ); ?>
						<p><?php printf(
							'%1$s <a href="admin.php?page=gglmps_editor">%2$s</a> %3$s <a href="admin.php?page=gglmps_manager">%4$s</a> %5$s',
							__( 'In the', 'bws-google-maps' ),
							__( 'Google Maps editor', 'bws-google-maps' ),
							__( 'you can create a new map and in the', 'bws-google-maps' ),
							__( 'Google Maps manager', 'bws-google-maps' ),
							__( 'you can find maps that have been previously saved.', 'bws-google-maps' )
						 ); ?></p>
						<?php printf( 
							__( 'Please add the map by clicking on %s button', 'bws-google-maps' ), 
							'<span class="bws_code"><span class="bwsicons bwsicons-shortcode"></span></span>'
						); ?>
						<div class="bws_help_box dashicons dashicons-editor-help">
							<div class="bws_hidden_help_text" style="min-width: 180px;">
								<?php printf( 
									__( "You can add the map to your content by clicking on %s button in the content edit block using the Visual mode. If the button isn't displayed, please use the shortcode %s, where * stands for map ID", 'bws-google-maps' ), 
									'<span class="bws_code"><span class="bwsicons bwsicons-shortcode"></span></span>',
									'<span class="bws_code">[bws_googlemaps id=*]</span>'
								); ?>
							</div>
						</div>
					</div><!-- #gglmps_settings_notice -->
					<form id="gglmps_settings_form" class="bws_form" name="gglmps_settings_form" method="post" action="admin.php?page=bws-google-maps.php">
						<table class="gglmps_settings_table form-table">
							<tbody>
								<tr valign="middle">
									<th><?php _e( 'API Key', 'bws-google-maps' ); ?></th>
									<td>
										<div style="max-width: 600px;">
											<input id="gglmps_main_api_key" name="gglmps_main_api_key" type="text" maxlength='250' value="<?php echo $gglmps_options['api_key']; ?>">
											<span class="gglmps_settings_tooltip">
												<?php printf(
													'%1$s <a href="https://developers.google.com/maps/documentation/javascript/usage#usage_limits" target="_blank">%2$s</a>, %3$s <a href="https://developers.google.com/maps/documentation/javascript/tutorial#api_key" target="_blank">%4$s</a>.',
													__( 'Using an API key enables you to monitor your application Maps API usage, and ensures that Google can contact you about your application if necessary. If your application Maps API usage exceeds the', 'bws-google-maps' ),
													__( 'Usage Limits', 'bws-google-maps' ),
													__( 'you must load the Maps API using an API key in order to purchase additional quota. How to create a API key you can find', 'bws-google-maps' ),
													__( 'here', 'bws-google-maps' )
												); ?>
											</span>
										</div>
									</td>
								</tr>
								<tr valign="middle">
									<th><?php _e( 'Language', 'bws-google-maps' ); ?></th>
									<td>
										<select id="gglmps_main_language" name="gglmps_main_language">
											<?php foreach ( $gglmps_lang_codes as $key => $lang ) {
												printf(
													'<option value="%1$s" %2$s>%3$s</option>',
													$key,
													$gglmps_options['language'] == $key ? 'selected="selected"' : '',
													$lang
												);
											} ?>
										</select>
									</td>
								</tr>
								<tr valign="middle">
									<th><?php _e( 'Width', 'bws-google-maps' ); ?></th>
									<td>
										<div class="gglmps_inline">
											<input id="gglmps_basic_width" name="gglmps_basic_width" type="number" min="1" max="1000" value="<?php echo $gglmps_options['basic']['width']; ?>" placeholder="<?php _e( 'Enter width', 'bws-google-maps' ); ?>">
											<select name="gglmps_basic_width_unit">
												<option value="px" <?php if ( 'px' == $gglmps_options['basic']['width_unit'] ) echo 'selected'; ?>><?php _e( 'px', 'bws-google-maps' ); ?></option>
												<option value="%" <?php if ( '%' == $gglmps_options['basic']['width_unit'] ) echo 'selected'; ?>>%</option>
											</select>
										</div>
									</td>
								</tr>
								<tr valign="middle">
									<th><?php _e( 'Height', 'bws-google-maps' ); ?></th>
									<td>
										<div class="gglmps_inline">
											<input id="gglmps_basic_height" name="gglmps_basic_height" type="number" min="150" max="1000" value="<?php echo $gglmps_options['basic']['height']; ?>" placeholder="<?php _e( 'Enter height', 'bws-google-maps' ); ?>">
											<?php _e( 'px', 'bws-google-maps' ); ?>
										</div>
									</td>
								</tr>
								<tr valign="middle">
									<th><label for="gglmps_basic_alignment"><?php _e( 'Alignment', 'bws-google-maps' ); ?></label></th>
									<td>
										<select id="gglmps_basic_alignment" name="gglmps_basic_alignment">
											<option value="left" <?php if ( $gglmps_options['basic']['alignment'] == 'left' ) echo 'selected'; ?>><?php _e( 'Left', 'bws-google-maps' ); ?></option>
											<option value="center" <?php if ( $gglmps_options['basic']['alignment'] == 'center' ) echo 'selected'; ?>><?php _e( 'Center', 'bws-google-maps' ); ?></option>
											<option value="right" <?php if ( $gglmps_options['basic']['alignment'] == 'right' ) echo 'selected'; ?>><?php _e( 'Right', 'bws-google-maps' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="middle">
									<th><label for="gglmps_basic_map_type"><?php _e( 'Type', 'bws-google-maps' ); ?></label></th>
									<td>
										<select id="gglmps_basic_map_type" name="gglmps_basic_map_type">
											<option value="roadmap" <?php if ( $gglmps_options['basic']['map_type'] == 'roadmap' ) echo 'selected'; ?>><?php _e( 'Roadmap', 'bws-google-maps' ); ?></option>
											<option value="terrain" <?php if ( $gglmps_options['basic']['map_type'] == 'terrain' ) echo 'selected'; ?>><?php _e( 'Terrain', 'bws-google-maps' ); ?></option>
											<option value="satellite" <?php if ( $gglmps_options['basic']['map_type'] == 'satellite' ) echo 'selected'; ?>><?php _e( 'Satellite', 'bws-google-maps' ); ?></option>
											<option value="hybrid" <?php if ( $gglmps_options['basic']['map_type'] == 'hybrid' ) echo 'selected'; ?>><?php _e( 'Hybrid', 'bws-google-maps' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="middle">
									<th><label for="gglmps_basic_tilt45"><?php _e( 'View', 'bws-google-maps' ); ?>&nbsp;45&deg;</label></th>
									<td>
										<input id="gglmps_basic_tilt45" name="gglmps_basic_tilt45" type="checkbox" <?php if ( $gglmps_options['basic']['tilt45'] == 1 ) echo 'checked="checked"'; ?> />
										<span class="gglmps_settings_tooltip"><?php _e( 'This option is only available for the types of map Satellite and Hybrid (if such snapshots are available).', 'bws-google-maps' ); ?></span>
									</td>
								</tr>
								<tr valign="middle">
									<th><label for="gglmps_basic_auto_zoom"><?php _e( 'Zoom', 'bws-google-maps' ); ?></label></th>
									<td>
										<div id="gglmps_zoom_wrap">
											<div id="gglmps_zoom_slider"></div>
											<span id="gglmps_zoom_value"></span>
										</div>
										<input id="gglmps_basic_zoom" name="gglmps_basic_zoom" type="number" min='0' max='21' value="<?php echo $gglmps_options['basic']['zoom']; ?>">
									</td>
								</tr>
								<tr valign="middle">
									<th>
										<input id="gglmps_settings_additional_options" name="gglmps_settings_additional_options" type="checkbox" <?php if ( $gglmps_options['additional_options'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_settings_additional_options"><?php _e( 'Controls options', 'bws-google-maps' ); ?></label>
									</th>
									<td>
										<span class="gglmps_settings_tooltip"><?php _e( 'Visibility and actions controls of the map.', 'bws-google-maps' ); ?></span>
									</td>
								</tr>
								<tr class="gglmps_settings_additional_options" valign="middle">
									<th>&nbsp;</th>
									<td>
										<p class="gglmps_settings_additional_option">
											<input id="gglmps_control_map_type" name="gglmps_control_map_type" type="checkbox" <?php if ( $gglmps_options['controls']['map_type'] == 1 ) echo 'checked="checked"'; ?> />
											<label for="gglmps_control_map_type"><?php _e( 'Type', 'bws-google-maps' ); ?></label>
										</p>
										<p class="gglmps_settings_additional_option">
											<input id="gglmps_control_rotate" name="gglmps_control_rotate" type="checkbox" <?php if ( $gglmps_options['controls']['rotate'] == 1 ) echo 'checked="checked"'; ?> />
											<label for="gglmps_control_rotate"><?php _e( 'Rotate', 'bws-google-maps' ); ?></label>
											<span class="gglmps_settings_tooltip"><?php _e( 'This option is only available if View 45° option is checked', 'bws-google-maps' ); ?></span>
										</p>
										<p class="gglmps_settings_additional_option">
											<input id="gglmps_control_zoom" name="gglmps_control_zoom" type="checkbox" <?php if ( $gglmps_options['controls']['zoom'] == 1 ) echo 'checked="checked"'; ?> />
											<label for="gglmps_control_zoom"><?php _e( 'Zoom', 'bws-google-maps' ); ?></label>
										</p>
										<p class="gglmps_settings_additional_option">
											<input id="gglmps_control_scale" name="gglmps_control_scale" type="checkbox" <?php if ( $gglmps_options['controls']['scale'] == 1 ) echo 'checked="checked"'; ?> />
											<label for="gglmps_control_scale"><?php _e( 'Scale', 'bws-google-maps' ); ?></label>
										</p>
									</td>
								</tr>
							</tbody>
						</table><!-- .gglmps_settings_table -->
						<?php if ( ! $bws_hide_premium_options_check ) { ?>
							<div class="bws_pro_version_bloc">
								<div class="bws_pro_version_table_bloc">	
									<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'bws-google-maps' ); ?>"></button>
									<div class="bws_table_bg"></div>
									<table class="form-table bws_pro_version">
										<tr valign="middle">
											<th><?php _e( 'Zoom', 'bws-google-maps' ); ?></th>
											<td>
												<p class="gglmps-zoom-container">
													<input disabled="disabled" name="gglmps_basic_auto_zoom" type="checkbox" />
													<label><?php _e( 'Auto', 'bws-google-maps' ); ?></label>
													<span class="gglmps_settings_tooltip"><?php _e( 'The map will be scaled to display all markers.', 'bws-google-maps' ); ?></span>
												</p>
											</td>
										</tr>
										<tr valign="middle">
											<th><?php _e( 'Controls options', 'bws-google-maps' ); ?></th>
											<td>
												<p class="gglmps_settings_additional_option">
												<input disabled="disabled" name="gglmps_control_street_view" type="checkbox" />
													<label><?php _e( 'Street View', 'bws-google-maps' ); ?></label>
												</p>
												<p class="gglmps_settings_additional_option">
													<input disabled="disabled" name="gglmps_control_map_draggable" type="checkbox" />
													<label><?php _e( 'Draggable', 'bws-google-maps' ); ?></label>
												</p>
												<p class="gglmps_settings_additional_option">
													<input disabled="disabled" name="gglmps_control_double_click" type="checkbox" />
													<label><?php _e( 'Double Click', 'bws-google-maps' ); ?></label>
												</p>
												<p class="gglmps_settings_additional_option">
													<input disabled="disabled" name="gglmps_control_scroll_wheel" type="checkbox" />
													<label><?php _e( 'Scroll Wheel', 'bws-google-maps' ); ?></label>
												</p>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row" colspan="2">
												* <?php _e( 'If you upgrade to Pro version all your settings will be saved.', 'bws-google-maps' ); ?>
											</th>
										</tr>
									</table>
								</div>
								<div class="bws_pro_version_tooltip">
									<a class="bws_button" target="_blank" href="https://bestwebsoft.com/products/wordpress/plugins/bws-google-maps/?k=f546edd672c2e16f8359dcb48f9d2fff&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( 'Learn More', 'bws-google-maps' ); ?></a>
									<div class="clear"></div>
								</div>
							</div>
						<?php } ?>
						<p>
							<?php wp_nonce_field( $plugin_basename ); ?>
							<input type="hidden" name="gglmps_form_submit" value="submit" />
							<input class="button-primary" id="bws-submit-button" name="gglmps_settings_submit" type="submit" value="<?php _e( 'Save Settings', 'bws-google-maps' ) ?>" />
						</p>
					</form><!-- #gglmps_settings_form -->
					<?php bws_form_restore_default_settings( $plugin_basename );
				}
			} elseif ( 'appearance' == $_GET['action'] ) { ?>				
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<div class="bws_table_bg" style="top: 0;"></div>
						<div id="gglmps_appearance_block" style="margin: 10px;">
							<div id="gglmps_settings_notice" class="updated below-h2">
								<?php _e( 'Install required styles in order to apply them to certain maps on Google Maps Editor page', 'bws-google-maps' ); ?>
							</div><!-- #gglmps_settings_notice -->	
							<table class="gglmps_settings_table form-table">
								<tbody>
									<tr>
										<th><?php _e( 'Snazzymaps API Key', 'bws-google-maps' ); ?></th>
										<td>
											<div class="gglmps-snazzymaps-api">
												<input type="text" id="gglmps_snazzymaps_api_key_value" disabled="disabled" size="36" value="">
												<input type="submit" class="button-primary" id="gglmps_snazzymaps_key_submit" disabled="disabled" value="Save">
											</div>
											<p>
												<span class="gglmps_settings_tooltip">
													<?php printf(
														'%1$s. <a>%2$s</a> %3$s <a>%4$s</a>.',
														__( 'You can use your own API key which allows using favorite styles from  your Snazzymaps account', 'bws-google-maps' ),
														__( 'Personal API key', 'bws-google-maps' ),
														__( 'is available after', 'bws-google-maps' ),
														__( 'registration', 'bws-google-maps' )
													); ?>
												</span>
											</p>
										</td>
									</tr>
								</tbody>
							</table>
							<div id="gglmps-snazzymaps_browse">
								<div class="gglmps-styles">
									<div class="gglmps-slyle-list">
										<div class="wp-filter">
											<form id="gglmps-styles-navigation">
												<ul class="filter-links">
													<li>
														<a href="#" class="current"><?php _ex( 'All', 'view all styles', 'bws-google-maps' ); ?></a>
													</li>
													<li>
														<a href="#"><?php _ex( 'Installed', 'view all installed styles', 'bws-google-maps' ); ?></a>
													</li>
													<li>
														<a href="#"><?php _e( 'Favorites', 'bws-google-maps' ); ?></a>
													</li>
												</ul>
												<select class="gglmps-color gglmps-filter" disabled="disabled">
													<option value="" selected="">
														<?php _e( 'Filter by color', 'bws-google-maps' ); ?>
													</option>
												</select>
												<select class="gglmps-tag gglmps-filter" disabled="disabled">
													<option value="" selected="">
														<?php _e( 'Filter by Tag', 'bws-google-maps' ); ?>
													</option>
												</select>
												<input class="wp-filter-search gglmps-filter" id="gglmps-search" disabled="disabled" type="text" placeholder="Search..." value="">
												<select class="gglmps-orderby gglmps-filter" disabled="disabled">
													<option value="" selected="">
														<?php _e( 'Sort by', 'bws-google-maps' ); ?>...
													</option>
												</select>
												<input type="submit" class="button button-primary" disabled="disabled" value="<?php _e( 'Apply', 'bws-google-maps' ); ?>">
											</form>
										</div>
										<hr>
										<div class="tablenav top">
											<div class="tablenav-pages">
												<span class="displaying-num">
													5486 <?php _e( 'items', 'bws-google-maps' ); ?>
												</span>
												<span class="pagination-links">
													<span class="tablenav-pages-navspan">«</span>
													<span class="tablenav-pages-navspan">‹</span>
													<span id="table-paging" class="paging-input">
														<?php _e( 'Page', 'bws-google-maps' ); ?> 1 <?php _e( 'of', 'bws-google-maps' ); ?> <span class="total-pages">458</span>
													</span>
													<a href="" class="next-page">›</a>
													<a href="" class="last-page">»</a>
												</span><!-- .pagination-links -->
											</div><!-- .tablenav-pages -->
											<br class="clear">
										</div><!-- .tablenav-top -->
										<div class="clear"></div>
										<div class="theme-browser content-filterable rendered">
											<div class="themes">
												<?php $themes = array(
													array( 
														'name' 			=> 'Midnight Commander',
														'is-installed' 	=> 1,
														'default'		=> 1
													),
													array( 
														'name' 			=> 'Unsaturated Browns',
														'is-installed' 	=> 1,
														'default'		=> 0
													),
													array( 
														'name' 			=> 'Bentley',
														'is-installed' 	=> 0,
														'default'		=> 0
													),
													array( 
														'name' 			=> 'Blue Essence',
														'is-installed' 	=> 0,
														'default'		=> 0
													),
													array( 
														'name' 			=> 'Nature',
														'is-installed' 	=> 0,
														'default'		=> 0
													),
													array( 
														'name' 			=> 'Just Retro',
														'is-installed' 	=> 0,
														'default'		=> 0
													),
													array( 
														'name' 			=> 'İnturlam Style',
														'is-installed' 	=> 0,
														'default'		=> 0
													),
													array( 
														'name' 			=> 'Sin City',
														'is-installed' 	=> 1,
														'default'		=> 0
													)
												);
												foreach ( $themes as $key => $value ) { ?>
													<div class="theme<?php if ( 1 == $value['default'] ) echo ' active is-installed'; ?>">
														<span class="gglmps-style">
															<div class="theme-screenshot">
																<img src="<?php echo plugins_url( 'images/style-' . $key . '.png', __FILE__ ); ?>">
															</div>
														</span>
														<h2 class="theme-name">
															<?php if ( 1 == $value['default'] ) { ?>
																<span class="active"><?php _e( 'Default', 'bws-google-maps' ); ?>:</span> 
															<?php }
															echo $value['name']; ?>						
														</h2>
														<div class="theme-actions">
															<?php if ( 0 == $value['default'] ) { ?>
																<button disabled="disabled" class="button"><?php _e( 'Set as default', 'bws-google-maps' ); ?></button>
															<?php }
															if ( 1 == $value['is-installed'] ) { ?>
																<button disabled="disabled" class="button button-remove" value=""><?php _e( 'Delete', 'bws-google-maps' ); ?></button>
															<?php } else { ?>
																<button disabled="disabled" class="button button-primary"><?php _e( 'Install', 'bws-google-maps' ); ?></button>
															<?php } ?>
														</div>
														<?php if ( 1 == $value['is-installed'] ) { 
															if ( $wp_version < '4.6' ) { ?>
																<div class="theme-installed"><?php _ex( 'Already Installed', 'style is installed', 'bws-google-maps' ); ?></div>
															<?php } else { ?>
																<div class="notice notice-success notice-alt inline"><p><?php _e( 'Installed', 'bws-google-maps' ); ?></p></div>
															<?php }
														} ?>
													</div><!-- .theme -->
												<?php } ?>
												<div class="clear"></div>
											</div><!-- .themes -->
										</div><!-- .theme-browser -->
										<div class="tablenav bottom">
											<div class="tablenav-pages">
												<span class="displaying-num">
													5486 <?php _e( 'items', 'bws-google-maps' ); ?>
												</span>
												<span class="pagination-links">
													<span class="tablenav-pages-navspan">«</span>
													<span class="tablenav-pages-navspan">‹</span>
													<span id="table-paging" class="paging-input">
														<?php _e( 'Page', 'bws-google-maps' ); ?> 1 <?php _e( 'of', 'bws-google-maps' ); ?> <span class="total-pages">458</span>
													</span>
													<a href="#" class="next-page">›</a>
													<a href="#" class="last-page">»</a>
												</span><!-- .pagination-links -->
											</div><!-- .tablenav-pages -->
											<br class="clear">
										</div><!-- .tablenav-bottom -->
									</div><!-- .style-list -->
									<hr>
								</div><!-- .styles -->
							</div><!-- #gglmps-snazzymaps_browse -->
						</div><!-- #gglmps_appearance_block -->
					</div>
					<div class="bws_pro_version_tooltip">
						<a class="bws_button" target="_blank" href="https://bestwebsoft.com/products/wordpress/plugins/bws-google-maps/?k=f546edd672c2e16f8359dcb48f9d2fff&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( 'Learn More', 'bws-google-maps' ); ?></a>
						<div class="clear"></div>
					</div>
				</div>
			<?php } elseif ( 'custom_code' == $_GET['action'] ) {
				bws_custom_code_tab();
			} elseif ( 'go_pro' == $_GET['action'] ) { 
				bws_go_pro_tab_show( $bws_hide_premium_options_check, $gglmps_plugin_info, $plugin_basename, 'bws-google-maps.php', 'bws-google-maps-pro.php', 'bws-google-maps-pro/bws-google-maps-pro.php', 'bws-google-maps', 'f546edd672c2e16f8359dcb48f9d2fff', '124', isset( $go_pro_result['pro_plugin_is_activated'] ) ); 
			} 
			bws_plugin_reviews_block( $gglmps_plugin_info['Name'], 'bws-google-maps' ); ?>
		</div><!-- #gglmps_settings_wrap -->
	<?php }
}

/*
* Function to display plugin manager page.
*/
if ( ! function_exists( 'gglmps_manager_page' ) ) {
	function gglmps_manager_page() {
		global $gglmps_maps;
		$gglmps_manager = new Gglmps_Manager();
		if ( $gglmps_manager->current_action() ) {
			$gglmps_manager_action = $gglmps_manager->current_action();
		} else {
			$gglmps_manager_action = isset( $_REQUEST['gglmps_manager_action'] ) ? $_REQUEST['gglmps_manager_action'] : '';
		}
		$gglmps_manager_mapid = isset( $_REQUEST['gglmps_manager_mapid'] ) ? $_REQUEST['gglmps_manager_mapid'] : 0;
		switch ( $gglmps_manager_action ) {
			case 'delete':
				$gglmps_mapids = is_array( $gglmps_manager_mapid ) ? $gglmps_manager_mapid : array( $gglmps_manager_mapid );
				foreach ( $gglmps_mapids as $gglmps_mapid ) {
					if ( isset( $gglmps_maps[ $gglmps_mapid ] ) ) {
						$gglmps_maps[ $gglmps_mapid ] = NULL;
					}
				}
				update_option( 'gglmps_maps', $gglmps_maps );
				break;
			default:
				break;
		}
		krsort( $gglmps_maps );
		$gglmps_result = array();
		foreach ( $gglmps_maps as $key => $gglmps_map ) {
			if ( isset( $gglmps_map ) ) {
				$gglmps_result[ $key ] = array(
					'gglmps-id' => $key,
					'title'     => sprintf( '<a class="row-title" href="admin.php?page=gglmps_editor&gglmps_editor_action=edit&gglmps_editor_mapid=%1$d">%2$s</a>', $key, $gglmps_map['title'] ),
					'shortcode' => sprintf( '[bws_googlemaps id=%d]', $key ),
					'date'      => $gglmps_map['date']
				);
			}
		}
		$gglmps_manager->gglmps_table_data = $gglmps_result;
		$gglmps_manager->prepare_items(); ?>
		<div class="wrap">
			<h1 class="gglmps_manager_title">
				<?php _e( 'Google Maps', 'bws-google-maps' ); ?>
				<a class="add-new-h2" href="admin.php?page=gglmps_editor"><?php _e( 'Add New', 'bws-google-maps' )?></a>
			</h1>
			<noscript>
				<div class="error">
					<p>
						<?php printf(
							'<strong>%1$s</strong> %2$s.',
							__( 'WARNING:', 'bws-google-maps' ),
							__( 'Google Maps only works with JavaScript enabled', 'bws-google-maps' )
						); ?>
					</p>
				</div><!-- .error -->
			</noscript><!-- noscript -->
			<form method="get">
				<?php $gglmps_manager->display(); ?>
				<input type="hidden" name="page" value="gglmps_manager"/>
			</form>
		</div><!-- .wrap -->
	<?php }
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) )
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/*
* Built-in WP class WP_List_Table.
*/
if ( class_exists( 'WP_List_Table' ) && ! class_exists( 'Gglmps_Manager' ) ) {
	class Gglmps_Manager extends WP_List_Table {
		public $gglmps_table_data;

		/*
		* Constructor of class.
		*/
		function __construct() {
			global $status, $page;
				parent::__construct( array(
					'singular'  => __( 'map', 'bws-google-maps' ),
					'plural'    => __( 'maps', 'bws-google-maps' ),
					'ajax'      => false
				)
			);
		}

		/*
		* Function to label the columns.
		*/
		function get_columns() {
			$columns = array(
				'cb'        => '<input type="checkbox" />',				
				'title'     => __( 'Title', 'bws-google-maps' ),
				'shortcode' => __( 'Shortcode', 'bws-google-maps' ),
				'date'      => __( 'Date', 'bws-google-maps' ),
				'gglmps-id'	=> __( 'ID', 'bws-google-maps' )
			);
			return $columns;
		}

		/*
		* Function to display data in columns.
		*/
		function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'gglmps-id':
				case 'title':
				case 'shortcode':
				case 'date':
					return $item[ $column_name ];
				default:
					return print_r( $item, true );
			}
		}

		/*
		* Function to add checkboxes in the column to the items.
		*/
		function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="gglmps_manager_mapid[]" value="%d" />', $item['gglmps-id'] );
		}

		/*
		* Function to add advanced menus for items.
		*/
		function column_title( $item ) {
			$gglmps_manager_paged = isset( $_GET['paged'] ) ? '&paged=' . $_GET['paged'] : '';
			$actions = array(
				'edit'   => sprintf( '<a href="admin.php?page=gglmps_editor&gglmps_editor_action=%1$s&gglmps_editor_mapid=%2$d">%3$s</a>', 'edit', $item['gglmps-id'],  __( 'Edit', 'bws-google-maps' ) ),
				'delete' => sprintf( '<a href="admin.php?page=gglmps_manager&gglmps_manager_action=%1$s&gglmps_manager_mapid=%2$d%3$s">%4$s</a>', 'delete', $item['gglmps-id'], $gglmps_manager_paged, __( 'Delete', 'bws-google-maps' ) )
			);
			return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
		}

		/*
		* Function to display message if items not found.
		*/
		function no_items() {
			printf('<i>%s</i>', __( 'Maps not found.', 'bws-google-maps' ) );
		}

		/*
		* Function for prepare items to display.
		*/
		function prepare_items() {
			$this->_column_headers = array(
				$this->get_columns(),
				array(),
				array()
			);
			$user = get_current_user_id();
			$screen = get_current_screen();
			$option = $screen->get_option('per_page', 'option');
			$per_page = get_user_meta($user, $option, true);
			if ( empty ( $per_page ) || $per_page < 1 ) {
				$per_page = $screen->get_option( 'per_page', 'default' );
			}
			$current_page = $this->get_pagenum();
			$total_items = count( $this->gglmps_table_data );
			$this->items = array_slice( $this->gglmps_table_data, ( ( $current_page - 1 ) * $per_page ), $per_page );
			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page
			) );
		}

		/*
		* Function to add support for group actions.
		*/
		function get_bulk_actions() {
			$actions = array(
				'delete' => __( 'Delete', 'bws-google-maps' )
			);
			return $actions;
		}
	}
}

/*
* Function to display plugin editor page.
*/
if ( ! function_exists( 'gglmps_editor_page' ) ) {
	function gglmps_editor_page() {
		global $gglmps_options, $gglmps_maps, $gglmps_plugin_info, $wp_version;
		$gglmps_editor_submit = array(
			'add'    => __( 'Save Map', 'bws-google-maps' ),
			'edit'   => __( 'Update Map', 'bws-google-maps' )
		);
		$gglmps_editor_action = isset( $_REQUEST['gglmps_editor_action'] ) ? $_REQUEST['gglmps_editor_action'] : 'new';
		$gglmps_editor_mapid = isset( $_REQUEST['gglmps_editor_mapid'] ) ? $_REQUEST['gglmps_editor_mapid'] : '';
		$gglmps_editor_shortcode = 0;
		$gglmps_editor_status = 0;
		
		switch ( $gglmps_editor_action ) {
			case 'new':
				$gglmps_map_title = '';
				$gglmps_map_data = array(
					'additional_options' => $gglmps_options['additional_options'],
					'basic'              => array(
						'width'			=> $gglmps_options['basic']['width'],
						'width_unit'	=> $gglmps_options['basic']['width_unit'],
						'height'		=> $gglmps_options['basic']['height'],
						'alignment'		=> $gglmps_options['basic']['alignment'],
						'map_type'		=> $gglmps_options['basic']['map_type'],
						'tilt45'		=> $gglmps_options['basic']['tilt45'],
						'zoom'			=> $gglmps_options['basic']['zoom']
					),
					'controls'           => array(
						'map_type'            => $gglmps_options['controls']['map_type'],
						'rotate'              => $gglmps_options['controls']['rotate'],
						'zoom'                => $gglmps_options['controls']['zoom'],
						'scale'               => $gglmps_options['controls']['scale']
					),
					'markers' => array()
				);
				$gglmps_editor_action = 'add';
				$gglmps_editor_form_action = 'admin.php?page=gglmps_editor&noheader=true';
				break;
			case 'add':
			case 'edit':
				if ( isset( $_REQUEST['gglmps_editor_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ) ) ) {
 					$gglmps_map_title = ! empty( $_REQUEST['gglmps_map_title'] ) ? trim( stripslashes( esc_html( $_REQUEST['gglmps_map_title'] ) ) ) : __( 'No title', 'bws-google-maps' );
					$gglmps_map_data = array(
						'additional_options' => isset( $_REQUEST['gglmps_editor_additional_options'] ) ? 1 : 0,
						'basic'              => array(
							'alignment' => isset( $_REQUEST['gglmps_basic_alignment'] ) ? $_REQUEST['gglmps_basic_alignment'] : 'left',
							'map_type'  => isset( $_REQUEST['gglmps_basic_map_type'] ) ? $_REQUEST['gglmps_basic_map_type'] : 'roadmap',
							'tilt45'    => isset( $_REQUEST['gglmps_basic_tilt45'] ) ? 1 : 0,
							'zoom'      => isset( $_REQUEST['gglmps_basic_zoom'] ) && is_numeric( intval( $_REQUEST['gglmps_basic_zoom'] ) ) ? intval( $_REQUEST['gglmps_basic_zoom'] ) : $gglmps_options['basic']['zoom']
						),
						'controls'           => array(
							'map_type'            => isset( $_REQUEST['gglmps_control_map_type'] ) ? 1 : 0,
							'rotate'              => isset( $_REQUEST['gglmps_control_rotate'] ) ? 1 : 0,
							'zoom'                => isset( $_REQUEST['gglmps_control_zoom'] ) ? 1 : 0,
							'scale'               => isset( $_REQUEST['gglmps_control_scale'] ) ? 1 : 0
						),
						'markers' => array()
					);

					$gglmps_map_data['basic']['width_unit'] = ( isset( $_REQUEST['gglmps_basic_width_unit'] ) && 'px' == $_REQUEST['gglmps_basic_width_unit'] ) ? 'px' : '%';
					if ( 'px' == $gglmps_map_data['basic']['width_unit'] ) 
						$gglmps_map_data['basic']['width'] = isset( $_REQUEST['gglmps_basic_width'] ) && intval( $_REQUEST['gglmps_basic_width'] ) > 150 ? intval( $_REQUEST['gglmps_basic_width'] ) : 150;
					else
						$gglmps_map_data['basic']['width'] = isset( $_REQUEST['gglmps_basic_width'] ) && intval( $_REQUEST['gglmps_basic_width'] ) < 100 ? intval( $_REQUEST['gglmps_basic_width'] ) : 100;

					$gglmps_map_data['basic']['height'] = isset( $_REQUEST['gglmps_basic_height'] ) && intval( $_REQUEST['gglmps_basic_height'] ) > 150 ? intval( $_REQUEST['gglmps_basic_height'] ) : 150;

					if ( isset( $_REQUEST['gglmps_list_marker_latlng'] ) && isset( $_REQUEST['gglmps_list_marker_location'] ) ) {
						$gglmps_marker_latlng = $_REQUEST['gglmps_list_marker_latlng'];
						$gglmps_marker_location = $_REQUEST['gglmps_list_marker_location'];
						$gglmps_marker_tooltip = $_REQUEST['gglmps_list_marker_tooltip'];
						foreach ( $gglmps_marker_location as $key => $value ) {
                            $gglmps_marker_location[ $key ] = stripslashes( esc_html( $value ) );
                            $gglmps_marker_latlng[ $key ] = stripslashes( esc_html( $gglmps_marker_latlng[ $key ] ) );
                            $gglmps_marker_tooltip[ $key ] = stripslashes( esc_html( $gglmps_marker_tooltip[ $key ] ) );
                        }
						$gglmps_map_data['markers'] = array_map( null, $gglmps_marker_latlng, $gglmps_marker_location, $gglmps_marker_tooltip );
					}

					if ( 'add' == $gglmps_editor_action ) {
						if ( count( $gglmps_maps ) == 0 ) {
							$gglmps_editor_mapid = 1;
						} else {
							end( $gglmps_maps );
							$gglmps_editor_mapid = key( $gglmps_maps ) + 1;
						}

						$gglmps_maps[ $gglmps_editor_mapid ] = array(
							'title' => $gglmps_map_title,
							'data'  => $gglmps_map_data,
							'date'  => date( 'Y/m/d' )
						);
						update_option( 'gglmps_maps', $gglmps_maps );
						header( 'Location: admin.php?page=gglmps_editor&gglmps_editor_action=edit&gglmps_editor_mapid=' . $gglmps_editor_mapid );
						exit;
					} else {
						if ( isset( $gglmps_maps[ $gglmps_editor_mapid ] ) ) {
							$gglmps_maps[ $gglmps_editor_mapid ] = array(
								'title' => $gglmps_map_title,
								'data'  => $gglmps_map_data,
								'date'  => $gglmps_maps[ $gglmps_editor_mapid ]['date']
							);
							update_option( 'gglmps_maps', $gglmps_maps );
							$gglmps_editor_status = 1;
						} else {
							wp_die(
								sprintf(
									'<div class="error"><p>%1$s <strong>ID#%2$s</strong> %3$s <a href="admin.php?page=gglmps_manager">%4$s</a> %5$s <a href="admin.php?page=gglmps_editor">%6$s</a>.</p></div>',
									__( 'Map with', 'bws-google-maps' ),
									$gglmps_editor_mapid,
									__( 'not found! You can return to the', 'bws-google-maps' ),
									__( 'Google Maps manager', 'bws-google-maps' ),
									__( 'or create new map in the', 'bws-google-maps' ),
									__( 'Google Maps editor', 'bws-google-maps' )
								)
							);
						}
					}
				}
				if ( 'edit' == $gglmps_editor_action ) {
					if ( isset( $gglmps_maps[ $gglmps_editor_mapid ] ) ) {
						$gglmps_map_title = $gglmps_maps[ $gglmps_editor_mapid ]['title'];
						$gglmps_map_data = $gglmps_maps[ $gglmps_editor_mapid ]['data'];
						$gglmps_editor_shortcode = 1;
					} else {
						wp_die(
							sprintf(
								'<div class="error"><p>%1$s <strong>ID#%2$s</strong> %3$s <a href="admin.php?page=gglmps_manager">%4$s</a> %5$s <a href="admin.php?page=gglmps_editor">%6$s</a>.</p></div>',
								__( 'Map with', 'bws-google-maps' ),
								$gglmps_editor_mapid,
								__( 'not found! You can return to the', 'bws-google-maps' ),
								__( 'Google Maps manager', 'bws-google-maps' ),
								__( 'or create new map in the', 'bws-google-maps' ),
								__( 'Google Maps editor', 'bws-google-maps' )
							)
						);
					}
					$gglmps_editor_action = 'edit';
					$gglmps_editor_form_action = 'admin.php?page=gglmps_editor&gglmps_editor_action=edit&gglmps_editor_mapid=' . $gglmps_editor_mapid;
				}
				break;
			default: ?>
				<script type="text/javascript">
					document.location.href="admin.php?page=gglmps_manager";
				</script>
				<?php exit;
				break;
		} ?>
		<div id="gglmps_editor_wrap" class="wrap">
			<h1 class="gglmps_editor_title"><?php _e( 'Google Maps Editor', 'bws-google-maps' ); ?></h1>
			<noscript>
				<div class="error">
					<p>
						<?php printf(
							'<strong>%1$s</strong> %2$s.',
							__( 'WARNING:', 'bws-google-maps' ),
							__( 'Google Maps only works with JavaScript enabled', 'bws-google-maps' )
						); ?>
					</p>
				</div><!-- .error -->
			</noscript><!-- noscript -->
			<?php if ( $gglmps_editor_status == 1 ) { ?>
				<div class="updated">
					<p>
						<?php _e( 'Map has been updated.', 'bws-google-maps' ); ?>
					</p>
				</div><!-- .updated -->
			<?php }
			if ( $gglmps_editor_shortcode == 1 ) { ?>
				<div id="gglmps_editor_notice" class="updated">
					<?php printf( 
						__( 'To insert this map use %s button', 'bws-google-maps' ), 
						'<span class="bws_code"><span class="bwsicons bwsicons-shortcode"></span></span>'
					); ?>
					<div class="bws_help_box dashicons dashicons-editor-help">
						<div class="bws_hidden_help_text" style="min-width: 180px;">
							<?php printf( 
								__( "You can add the map to your content by clicking on %s button in the content edit block using the Visual mode. If the button isn't displayed, please use the shortcode %s", 'bws-google-maps' ), 
								'<span class="bws_code"><span class="bwsicons bwsicons-shortcode"></span></span>',
								'<span class="bws_code">[bws_googlemaps id=' . $gglmps_editor_mapid . ']</span>'
							); ?>
						</div>
					</div>
				</div><!-- #gglmps_editor_notice -->
			<?php } ?>			
			<div id="gglmps_editor_settings">
				<form id="gglmps_editor_form" name="gglmps_editor_form" method="post" action="<?php echo $gglmps_editor_form_action; ?>">
					<table class="gglmps_editor_table form-table">
						<tbody>
							<tr valign="middle">
								<th><label for="gglmps_map_title"><?php _e( 'Map Title', 'bws-google-maps' ); ?></label></th>
								<td>
									<input id="gglmps_map_title" name="gglmps_map_title" type="text" maxlength="64" value="<?php echo $gglmps_map_title; ?>" placeholder="<?php _e( 'Enter title', 'bws-google-maps' ); ?>" />
								</td>
							</tr>
							<tr class="gglmps_markers_wrap" valign="middle">
								<th><label for="gglmps_marker_location"><?php _e( 'Marker Location', 'bws-google-maps' ); ?></label></th>
								<td>
									<input id="gglmps_marker_location" type="text" placeholder="<?php _e( 'Enter location or coordinates', 'bws-google-maps' ); ?>" />
									<span class="gglmps_editor_tooltip">
										<?php _e( 'You should enter coordinates in decimal degrees with no spaces. Example coordinates:', 'bws-google-maps' ); ?> 41.40338,2.17403.
									</span>
									<input id="gglmps_marker_latlng" type="hidden" />
								</td>
							</tr>
							<tr class="gglmps_markers_wrap" valign="middle">
								<th><label for="gglmps_marker_tooltip"><?php _e( 'Marker Tooltip', 'bws-google-maps' ); ?></label></th>
								<td>
									<textarea id="gglmps_marker_tooltip" placeholder="<?php _e( 'Enter tooltip', 'bws-google-maps' ); ?>"></textarea>
									<span class="gglmps_editor_tooltip"><?php _e( 'You can use HTML tags and attributes.', 'bws-google-maps' ); ?></span>
									<p>
										<input class="button-secondary" id="gglmps_marker_add" type="button" value="<?php _e( 'Add marker to list', 'bws-google-maps' ); ?>" />
										<input class="button-secondary" id="gglmps_marker_update" type="button" value="<?php _e( 'Update marker', 'bws-google-maps' ); ?>" />
										<input class="button-secondary" id="gglmps_marker_cancel" type="button" value="<?php _e( 'Cancel', 'bws-google-maps' ); ?>" />
									</p>
								</td>
							</tr>
							<tr class="gglmps_markers_wrap" valign="middle">
								<th><?php _e( 'Markers List', 'bws-google-maps' ); ?></th>
								<td>
									<ul id="gglmps_markers_container">
										<?php if ( count( $gglmps_map_data['markers'] ) == 0 ) { ?>
											<li class="gglmps_no_markers">
												<?php _e( 'No markers', 'bws-google-maps' ); ?>
											</li>
										<?php } else {
											foreach ( $gglmps_map_data['markers'] as $key => $gglmps_marker ) { ?>
												<li class="gglmps_marker">
													<div class="gglmps_marker_control">
														<span class="gglmps_marker_delete"><?php _e( 'Delete', 'bws-google-maps' ); ?></span>
														<span class="gglmps_marker_edit"><?php _e( 'Edit', 'bws-google-maps' ); ?></span>
														<span class="gglmps_marker_latlng">[<?php echo stripcslashes( $gglmps_marker[0] ); ?>]</span>
													</div>
													<div class="gglmps_marker_data">
														<div class="gglmps_marker_location"><?php echo stripcslashes( $gglmps_marker[1] ); ?></div>
														<xmp class="gglmps_marker_tooltip"><?php echo html_entity_decode( stripcslashes( $gglmps_marker[2] ) ); ?></xmp>
														<input class="gglmps_input_latlng" name="gglmps_list_marker_latlng[]" type="hidden" value="<?php echo $gglmps_marker[0]; ?>" />
														<textarea class="gglmps_textarea_location" name="gglmps_list_marker_location[]"><?php echo stripcslashes( $gglmps_marker[1] ); ?></textarea>
														<textarea class="gglmps_textarea_tooltip" name="gglmps_list_marker_tooltip[]"><?php echo stripcslashes( $gglmps_marker[2] ); ?></textarea>
													</div>
												</li>
											<?php }
										} ?>
									</ul>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_width"><?php _e( 'Width', 'bws-google-maps' ); ?></label></th>
								<td>
									<div class="gglmps_inline">
										<input id="gglmps_basic_width" name="gglmps_basic_width" type="number" min="1" max="10000" value="<?php echo $gglmps_map_data['basic']['width']; ?>" placeholder="<?php _e( 'Enter width', 'bws-google-maps' ); ?>">
										<select name="gglmps_basic_width_unit">
											<option value="px" <?php if ( isset( $gglmps_map_data['basic']['width_unit'] ) && 'px' == $gglmps_map_data['basic']['width_unit'] ) echo 'selected'; ?>><?php _e( 'px', 'bws-google-maps' ); ?></option>
											<option value="%" <?php if ( isset( $gglmps_map_data['basic']['width_unit'] ) && '%' == $gglmps_map_data['basic']['width_unit'] ) echo 'selected'; ?>>%</option>
										</select>
									</div>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_height"><?php _e( 'Height', 'bws-google-maps' ); ?></label></th>
								<td>
									<div class="gglmps_inline">
										<input id="gglmps_basic_height" name="gglmps_basic_height" type="number" min="150" max="10000" value="<?php echo $gglmps_map_data['basic']['height']; ?>" placeholder="<?php _e( 'Enter height', 'bws-google-maps' ); ?>">
										<?php _e( 'px', 'bws-google-maps' ); ?>
									</div>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_alignment"><?php _e( 'Alignment', 'bws-google-maps' ); ?></label></th>
								<td>
									<select id="gglmps_basic_alignment" name="gglmps_basic_alignment">
										<option value="left" <?php if ( $gglmps_map_data['basic']['alignment'] == 'left' ) echo 'selected'; ?>><?php _e( 'Left', 'bws-google-maps' ); ?></option>
										<option value="center" <?php if ( $gglmps_map_data['basic']['alignment'] == 'center' ) echo 'selected'; ?>><?php _e( 'Center', 'bws-google-maps' ); ?></option>
										<option value="right" <?php if ( $gglmps_map_data['basic']['alignment'] == 'right' ) echo 'selected'; ?>><?php _e( 'Right', 'bws-google-maps' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_map_type"><?php _e( 'Type', 'bws-google-maps' ); ?></label></th>
								<td>
									<select id="gglmps_basic_map_type" name="gglmps_basic_map_type">
										<option value="roadmap" <?php if ( $gglmps_map_data['basic']['map_type'] == 'roadmap' ) echo 'selected'; ?>><?php _e( 'Roadmap', 'bws-google-maps' ); ?></option>
										<option value="terrain" <?php if ( $gglmps_map_data['basic']['map_type'] == 'terrain' ) echo 'selected'; ?>><?php _e( 'Terrain', 'bws-google-maps' ); ?></option>
										<option value="satellite" <?php if ( $gglmps_map_data['basic']['map_type'] == 'satellite' ) echo 'selected'; ?>><?php _e( 'Satellite', 'bws-google-maps' ); ?></option>
										<option value="hybrid" <?php if ( $gglmps_map_data['basic']['map_type'] == 'hybrid' ) echo 'selected'; ?>><?php _e( 'Hybrid', 'bws-google-maps' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_tilt45"><?php _e( 'View', 'bws-google-maps' ); ?>&nbsp;45&deg;</label></th>
								<td>
									<input id="gglmps_basic_tilt45" name="gglmps_basic_tilt45" type="checkbox" <?php if ( $gglmps_map_data['basic']['tilt45'] == 1 ) echo 'checked="checked"'; ?> />
									<span class="gglmps_editor_tooltip"><?php _e( 'This option is only available for the types of map Satellite and Hybrid (if such snapshots are available).', 'bws-google-maps' ); ?></span>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_auto_zoom"><?php _e( 'Zoom', 'bws-google-maps' ); ?></label></th>
								<td>
									<div id="gglmps_zoom_wrap">
										<div id="gglmps_zoom_slider"></div>
										<span id="gglmps_zoom_value"></span>
									</div>
									<input id="gglmps_basic_zoom" name="gglmps_basic_zoom" type="number" min='0' max='21' value="<?php echo $gglmps_map_data['basic']['zoom']; ?>">
								</td>
							</tr>
							<tr valign="middle">
								<th>
									<input id="gglmps_editor_additional_options" name="gglmps_editor_additional_options" type="checkbox" <?php if ( $gglmps_map_data['additional_options'] == 1 ) echo 'checked="checked"'; ?> />
									<label for="gglmps_editor_additional_options"><?php _e( 'Controls options', 'bws-google-maps' ); ?></label>
								</th>
								<td>
									<span class="gglmps_editor_tooltip"><?php _e( 'Visibility and actions controls of the map.', 'bws-google-maps' ); ?></span>
								</td>
							</tr>
							<tr class="gglmps_editor_additional_options" valign="middle">
								<th>&nbsp;</th>
								<td>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_map_type" name="gglmps_control_map_type" type="checkbox" <?php if ( $gglmps_map_data['controls']['map_type'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_map_type"><?php _e( 'Type', 'bws-google-maps' ); ?></label>
									</p>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_rotate" name="gglmps_control_rotate" type="checkbox" <?php if ( $gglmps_map_data['controls']['rotate'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_rotate"><?php _e( 'Rotate', 'bws-google-maps' ); ?></label>
										<span class="gglmps_settings_tooltip"><?php _e( 'This option is only available if View 45° option is checked', 'bws-google-maps' ); ?></span>
									</p>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_zoom" name="gglmps_control_zoom" type="checkbox" <?php if ( $gglmps_map_data['controls']['zoom'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_zoom"><?php _e( 'Zoom', 'bws-google-maps' ); ?></label>
									</p>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_scale" name="gglmps_control_scale" type="checkbox" <?php if ( $gglmps_map_data['controls']['scale'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_scale"><?php _e( 'Scale', 'bws-google-maps' ); ?></label>
									</p>
								</td>
							</tr>
						</tbody>
					</table> <!-- .gglmps_editor_table -->
					<?php if ( ! bws_hide_premium_options_check( $gglmps_options ) ) { ?>
						<div class="bws_pro_version_bloc">
							<div class="bws_pro_version_table_bloc">
								<div class="bws_table_bg"></div>
								<table class="form-table bws_pro_version">
									<tr valign="middle">
										<th><label for="gglmps_snazzymaps_style"><?php _e( 'Style', 'bws-google-maps' ); ?></label></th>
										<td>
											<select id="gglmps_snazzymaps_style" disabled="disabled">
												<option>Midnight Commander (<?php _e( 'Default', 'bws-google-maps' ); ?>)</option>
											</select><br>
											<span class="gglmps_editor_tooltip"><?php _e( 'This option is only available for the types of map Roadmap, Terrain and Hybrid.', 'bws-google-maps' ); ?></span>
										</td>
									</tr>
									<tr valign="middle">
										<th>
											<label><?php _e( 'Zoom', 'bws-google-maps' ); ?></label>
										</th>
										<td>
											<p class="gglmps-zoom-container">
												<input disabled="disabled" name="gglmps_basic_auto_zoom" type="checkbox" />
												<label><?php _e( 'Auto', 'bws-google-maps' ); ?></label>
												<span class="gglmps_settings_tooltip"><?php _e( 'The map will be scaled to display all markers.', 'bws-google-maps' ); ?></span>
											</p>
										</td>
									</tr>
									<tr valign="middle">
										<th><?php _e( 'Controls options', 'bws-google-maps' ); ?></th>
										<td>
											<p class="gglmps_settings_additional_option">
											<input disabled="disabled" name="gglmps_control_street_view" type="checkbox" />
												<label><?php _e( 'Street View', 'bws-google-maps' ); ?></label>
											</p>
											<p class="gglmps_settings_additional_option">
												<input disabled="disabled" name="gglmps_control_map_draggable" type="checkbox" />
												<label><?php _e( 'Draggable', 'bws-google-maps' ); ?></label>
											</p>
											<p class="gglmps_settings_additional_option">
												<input disabled="disabled" name="gglmps_control_double_click" type="checkbox" />
												<label><?php _e( 'Double Click', 'bws-google-maps' ); ?></label>
											</p>
											<p class="gglmps_settings_additional_option">
												<input disabled="disabled" name="gglmps_control_scroll_wheel" type="checkbox" />
												<label><?php _e( 'Scroll Wheel', 'bws-google-maps' ); ?></label>
											</p>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row" colspan="2">
											* <?php _e( 'If you upgrade to Pro version all your settings will be saved.', 'bws-google-maps' ); ?>
										</th>
									</tr>
								</table>
							</div>
							<div class="bws_pro_version_tooltip">
								<a class="bws_button" target="_blank" href="https://bestwebsoft.com/products/wordpress/plugins/bws-google-maps/?k=f546edd672c2e16f8359dcb48f9d2fff&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( 'Learn More', 'bws-google-maps' ); ?></a>
								<div class="clear"></div>
							</div>
						</div>
					<?php } ?>
					<p>
						<input id="gglmps_editor_action" name="gglmps_editor_action" type="hidden" value="<?php echo $gglmps_editor_action; ?>" />
						<input id="gglmps_editor_mapid" name="gglmps_editor_mapid" type="hidden" value="<?php echo $gglmps_editor_mapid; ?>" />
						<?php wp_nonce_field( plugin_basename( __FILE__ ) ); ?>
						<input class="button-primary" id="gglmps_editor_submit" name="gglmps_editor_submit" type="submit" value="<?php echo $gglmps_editor_submit[ $gglmps_editor_action ]; ?>" />
					</p>
				</form><!-- #gglmps_editor_form -->
			</div><!-- #gglmps_editor_settings -->
			<div id="gglmps_editor_preview">
				<div class="bws_pro_version_bloc bws_pro_version_bloc_mini">
					<div class="bws_pro_version_table_bloc">
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr valign="middle">
								<th>
									<img src="<?php echo plugins_url( 'images/map_preview_example.png', __FILE__ ); ?>">
								</th>
							</tr>
						</table>
					</div>
					<div class="bws_pro_version_tooltip">
						<a class="bws_button" target="_blank" href="https://bestwebsoft.com/products/wordpress/plugins/bws-google-maps/?k=f546edd672c2e16f8359dcb48f9d2fff&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( 'Learn More', 'bws-google-maps' ); ?></a>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div><!-- #gglmps_editor_wrap -->
	<?php }
}

/*
* Function to display table screen options.
*/
if ( ! function_exists ( 'gglmps_screen_options' ) ) {
	function gglmps_screen_options() {
		gglmps_add_tabs();
		$args = array(
			'label'   => __( 'Map(s)', 'bws-google-maps' ),
			'default' => 20,
			'option'  => 'gglmps_maps_per_page'
		);
		add_screen_option( 'per_page', $args );
	}
}

/* add help tab  */
if ( ! function_exists( 'gglmps_add_tabs' ) ) {
	function gglmps_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id'		=> 'gglmps',
			'section'	=> '200538659'
		);
		bws_help_tab( $screen, $args );
	}
}

/*
* Function to add script and styles to the admin panel.
*/
if ( ! function_exists( 'gglmps_admin_head' ) ) {
	function gglmps_admin_head() {
		global $gglmps_options;
		wp_enqueue_style( 'gglmps_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'gglmps_editor' ) {
			$gglmps_api_key = ! empty( $gglmps_options['api_key'] ) ? sprintf( '&key=%s', $gglmps_options['api_key'] ) : '';
			$gglmps_language = sprintf( '&language=%s', $gglmps_options['language'] );
			$gglmps_api = sprintf(
				'https://maps.googleapis.com/maps/api/js?sensor=false&libraries=places%1$s%2$s',
				$gglmps_api_key,
				$gglmps_language
			);
			wp_enqueue_script( 'gglmps_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'gglmps_api', $gglmps_api );
			wp_enqueue_script( 'gglmps_editor_script', plugins_url( 'js/editor.js', __FILE__ ), array( 'jquery-ui-slider', 'jquery-touch-punch' ) );
			$gglmps_translation_array = array(
				'deleteMarker'   => __( 'Delete', 'bws-google-maps' ),
				'editMarker'     => __( 'Edit', 'bws-google-maps' ),
				'noMarkers'      => __( 'No markers', 'bws-google-maps' ),
				'getCoordinates' => __( 'Get coordinates', 'bws-google-maps' )
			);
			wp_localize_script( 'gglmps_editor_script', 'gglmps_translation', $gglmps_translation_array );
		}
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'bws-google-maps.php' ) {
			wp_enqueue_script( 'gglmps_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'gglmps_settings_script', plugins_url( 'js/settings.js', __FILE__ ), array( 'jquery-ui-slider', 'jquery-touch-punch' ) );

			if ( isset( $_GET['action'] ) && 'custom_code' == $_GET['action'] )
				bws_plugins_include_codemirror();
		}
		if ( isset( $_GET['action'] ) && 'appearance' == $_GET['action'] ) {
			wp_enqueue_style( 'gglmps_appearance_stylesheet', plugins_url( 'css/appearance-style.css', __FILE__ ) );
		}
	}
}

/*
* Function to set up table screen options.
*/
if ( ! function_exists ( 'gglmps_set_screen_options' ) ) {
	function gglmps_set_screen_options( $status, $option, $value ) {
		if ( $option == 'gglmps_maps_per_page' ) {
			return $value;
		}
		return $status;
	}
}

/*
* Function to add meta tag to the front-end.
*/
if ( ! function_exists( 'gglmps_head' ) ) {
	function gglmps_head() { ?>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<?php }
}

/*
* Function to add script and styles to the front-end.
*/
if ( ! function_exists( 'gglmps_frontend_head' ) ) {
	function gglmps_frontend_head() {
		wp_enqueue_style( 'gglmps_style', plugins_url( 'css/gglmps.css', __FILE__ ) );
	}
}

if ( ! function_exists( 'gglmps_front_end_scripts' ) ) {
	function gglmps_front_end_scripts() {
		global $gglmps_options;	

		if ( wp_script_is( 'gglmps_script', 'registered' ) && ! wp_script_is( 'gglmps_script', 'enqueued' ) ) {

			if ( empty( $gglmps_options ) )
				$gglmps_options = get_option( 'gglmps_options' );

			$api_key = ! empty( $gglmps_options['api_key'] ) ? sprintf( '&key=%s', $gglmps_options['api_key'] ) : '';
			$language = sprintf( '&language=%s', $gglmps_options['language'] );
			$api = sprintf(
				'https://maps.googleapis.com/maps/api/js?sensor=false%1$s%2$s',
				$api_key,
				$language
			);
			wp_enqueue_script( 'gglmps_api', $api );
			wp_enqueue_script( 'gglmps_script' );
		}
	}
}

/*
* Function to display Google Maps.
*/
if ( ! function_exists( 'gglmps_shortcode' ) ) {
	function gglmps_shortcode( $atts ) {
		global $gglmps_maps, $gglmps_count;
		if ( empty( $gglmps_count ) )
			$gglmps_count = 1;

		if ( $gglmps_count > 1 )
			return;

		if ( ! isset( $atts['id'] ) ) {
			$gglmps_count++;
			return sprintf(
				'<div class="gglmps_map_error">[Google Maps: %s]</div>',
				__( 'You have not specified map ID', 'bws-google-maps' )
			);
		}

		if ( isset( $gglmps_maps[ $atts['id'] ] ) ) {
			$gglmps_mapid = uniqid('gglmps_map_');
			$gglmps_map_data = $gglmps_maps[ $atts['id'] ]['data'];
			$gglmps_map_width = $gglmps_map_data['basic']['width'];
			$gglmps_map_width .= isset( $gglmps_map_data['basic']['width_unit'] ) ? $gglmps_map_data['basic']['width_unit'] : 'px';
			$gglmps_map_height = $gglmps_map_data['basic']['height'] . 'px';
			$gglmps_map_markers = array();

			switch ( $gglmps_map_data['basic']['alignment'] ) {
				case 'right':
					$gglmps_map_alignment = 'float: right;';
					break;
				case 'center':
					$gglmps_map_alignment = 'margin: 0 auto;';
					break;
				case 'left':
				default:
					$gglmps_map_alignment = 'float: left;';
					break;
			}

			if ( count( $gglmps_map_data['markers'] ) ) {				
				foreach ( $gglmps_map_data['markers'] as $key => $gglmps_marker ) {
					$gglmps_map_markers[ $key ] = array( 
						'latlng' => $gglmps_marker[0],
						'location' => $gglmps_marker[1],
						'tooltip' => preg_replace( "|<script.*?>.*?</script>|", "", html_entity_decode( $gglmps_marker[2] ) )
					);
				}
			}
			$gglmps_count++;

			wp_register_script( 'gglmps_script', plugins_url( 'js/gglmps.js' , __FILE__ ), array( 'jquery' ), false, true );

			return sprintf(
				'<div class="gglmps_container_map">
					<div id="%1$s" class="gglmps_map" style="%2$s width: %3$s; height: %4$s;" data-basic="%7$s" data-controls="%8$s" data-markers="%9$s">
						<noscript>
							<p class="gglmps_no_script">
								[Google Maps: %5$s <a href="https://support.google.com/answer/23852" target="_blank">%6$s</a>]
							</p>
						</noscript>
					</div>
				</div>',
				 $gglmps_mapid,
				 $gglmps_map_alignment,
				 $gglmps_map_width,
				 $gglmps_map_height,
				 __( 'Please, enable JavaScript!', 'bws-google-maps' ),
				 __( 'HELP', 'bws-google-maps' ),
				htmlspecialchars( json_encode( $gglmps_map_data['basic'] ) ),
				htmlspecialchars( json_encode( $gglmps_map_data['controls'] ) ),
				htmlspecialchars( json_encode( $gglmps_map_markers ) )
			);
		} else {
			$gglmps_count++;
			return sprintf(
				'<div class="gglmps_map_error">[Google Maps: %1$s ID#%2$d %3$s]</div>',
				__( 'Map with', 'bws-google-maps' ),
				$atts['id'],
				__( 'not found', 'bws-google-maps' )
			);
		}
	}
}

/* add shortcode content  */
if ( ! function_exists( 'gglmps_shortcode_button_content' ) ) {
	function gglmps_shortcode_button_content( $content ) {
		global $wp_version; ?>
		<div id="gglmps" style="display:none;">
			<fieldset>
				<label>					
					<?php $gglmps_maps = get_option( 'gglmps_maps' );
					if ( ! empty( $gglmps_maps ) ) {
						$result = '<select name="gglmps_list" id="gglmps_shortcode_list">';
						foreach ( $gglmps_maps as $key => $value ) { 
							if ( ! empty( $value ) ) {
								if ( ! isset( $map_first ) )
									$map_first = $key;
								$result .= '<option value="' . $key . '"><h2>' . $value['title'] . '</h2></option>';
							}
							}
						$result .= '</select> 
						<span class="title">' . __( 'Google Map', 'bws-google-maps' ) . '</span>';
					}
					if ( ! isset( $map_first ) ) { ?>
						<span class="title"><?php _e( 'Maps not found.', 'bws-google-maps' ); ?></span>						
					<?php } else
						echo $result; ?>
				</label>
			</fieldset>
			<?php if ( ! empty( $map_first ) ) { ?>
				<input class="bws_default_shortcode" type="hidden" name="default" value="[bws_googlemaps id=<?php echo $map_first; ?>]" />
			<?php } ?>
			<script type="text/javascript">
				function gglmps_shortcode_init() {
					(function($) {	
						<?php if ( $wp_version < '3.9' ) { ?>	
							var current_object = '#TB_ajaxContent';
						<?php } else { ?>
							var current_object = '.mce-reset';
						<?php } ?>			

						$( current_object + ' #gglmps_shortcode_list' ).on( 'change', function() {
							var map = $( current_object + ' #gglmps_shortcode_list option:selected' ).val();
							var shortcode = '[bws_googlemaps id=' + map + ']';

							$( current_object + ' #bws_shortcode_display' ).text( shortcode );
						});	         
					})(jQuery);
				}
			</script>
			<div class="clear"></div>
		</div>
	<?php }
}

/*
* Function to add action links to the plugin menu.
*/
if ( ! function_exists ( 'gglmps_plugin_action_links' ) ) {
	function gglmps_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row */
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=bws-google-maps.php">' . __( 'Settings', 'bws-google-maps' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/*
* Function to add links to the plugin description on the plugins page.
*/
if ( ! function_exists ( 'gglmps_register_action_links' ) ) {
	function gglmps_register_action_links( $links, $file ) {
		if ( $file == plugin_basename( __FILE__ ) ) {
			if ( ! is_network_admin() )
				$links[] = sprintf( '<a href="admin.php?page=bws-google-maps.php">%s</a>', __( 'Settings', 'bws-google-maps' ) );
			$links[] = sprintf( '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538659" target="_blank">%s</a>', __( 'FAQ', 'bws-google-maps' ) );
			$links[] = sprintf( '<a href="https://support.bestwebsoft.com">%s</a>', __( 'Support', 'bws-google-maps' ) );
		}
		return $links;
	}
}

if ( ! function_exists ( 'gglmps_plugin_banner' ) ) {
	function gglmps_plugin_banner() {
		global $hook_suffix, $gglmps_plugin_info;
		if ( 'plugins.php' == $hook_suffix ) {
			global $gglmps_options;
			if ( empty( $gglmps_options ) )
				$gglmps_options = get_option( 'gglmps_options' );
			
			if ( isset( $gglmps_options['first_install'] ) && strtotime( '-1 week' ) > $gglmps_options['first_install'] )
				bws_plugin_banner( $gglmps_plugin_info, 'gglmps', 'bws-google-maps', 'f546edd672c2e16f8359dcb48f9d2fff', '124', '//ps.w.org/bws-google-maps/assets/icon-128x128.png' );
		
			bws_plugin_banner_to_settings( $gglmps_plugin_info, 'gglmps_options', 'bws-google-maps', 'admin.php?page=bws-google-maps.php', 'admin.php?page=gglmps_editor' );
		}

		if ( isset( $_GET['page'] ) && 'bws-google-maps.php' == $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $gglmps_plugin_info, 'gglmps_options', 'bws-google-maps' );
		}
	}
}

/*
* Function to uninstall Google Maps.
*/
if ( ! function_exists( 'gglmps_uninstall' ) ) {
	function gglmps_uninstall() {
		global $wpdb;

		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$all_plugins = get_plugins();

		if ( ! array_key_exists( 'bws-google-maps-pro/bws-google-maps-pro.php', $all_plugins ) ) {
			if ( is_multisite() ) {
				$old_blog = $wpdb->blogid;
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					delete_option( 'gglmps_options' );
					delete_option( 'gglmps_maps' );
				}
				switch_to_blog( $old_blog );
			} else {
				delete_option( 'gglmps_options' );
				delete_option( 'gglmps_maps' );
			}
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

/* Displaying admin menu */
add_action( 'admin_menu', 'gglmps_admin_menu' );
/* Initialization */
add_action( 'plugins_loaded', 'gglmps_plugins_loaded' );
add_action( 'init', 'gglmps_init' );
add_action( 'admin_init', 'gglmps_admin_init' );
/* Adding scripts and styles in the admin panel */
add_action( 'admin_enqueue_scripts', 'gglmps_admin_head' );
/* Adding support for pagination in the maps manager */
add_filter( 'set-screen-option', 'gglmps_set_screen_options', 10, 3 );
/* Adding meta tag, scripts and styles on the frontend */
add_action( 'wp_head', 'gglmps_head' );
add_action( 'wp_enqueue_scripts', 'gglmps_frontend_head' );
add_action( 'wp_footer', 'gglmps_front_end_scripts' );
/* Adding a plugin support shortcode */
add_shortcode( 'bws_googlemaps', 'gglmps_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );
/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'gglmps_shortcode_button_content' );
/* Adding additional links on the plugins page */
add_filter( 'plugin_action_links', 'gglmps_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'gglmps_register_action_links', 10, 2 );

add_action( 'admin_notices', 'gglmps_plugin_banner' );
/* Uninstall plugin */
register_uninstall_hook( __FILE__, 'gglmps_uninstall' );