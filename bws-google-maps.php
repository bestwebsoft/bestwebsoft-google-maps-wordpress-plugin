<?php
/*
Plugin Name: Maps by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/bws-google-maps/
Description: Add customized Google maps to WordPress posts, pages and widgets.
Author: BestWebSoft
Text Domain: bws-google-maps
Domain Path: /languages
Version: 1.4.2
Author URI: https://bestwebsoft.com/
License: GPLv2 or later
*/

/* © Copyright 2021 BestWebSoft ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

/*
* Function to display admin menu.
*/
if ( ! function_exists( 'gglmps_admin_menu' ) ) {
	function gglmps_admin_menu() {
		global $submenu, $gglmps_plugin_info, $wp_version;

		$hook = add_menu_page( 'Maps', 'Maps', 'edit_posts', 'gglmps_manager', 'gglmps_manager_page', 'dashicons-location', '54.1' );
		
		$gglmps_manager = add_submenu_page( 'gglmps_manager', __( 'Maps Editor', 'bws-google-maps' ), __( 'Add New', 'bws-google-maps' ), 'manage_options', 'gglmps_editor', 'gglmps_editor_page' );

		$appearance = add_submenu_page( 'gglmps_manager',  __( 'Maps Appearance', 'bws-google-maps' ), __( 'Appearance', 'bws-google-maps' ), 'manage_options', 'bws-google-maps-appearance.php', 'gglmps_appearance_page' );

		$settings = add_submenu_page( 'gglmps_manager',  __( 'Maps Settings', 'bws-google-maps' ), __( 'Settings', 'bws-google-maps' ), 'manage_options', 'bws-google-maps.php', 'gglmps_settings_page' );

		add_submenu_page( 'gglmps_manager', 'BWS Panel', 'BWS Panel', 'manage_options', 'gglmps-bws-panel', 'bws_add_menu_render' );

		/* Add "Go Pro" submenu link */
		if ( isset( $submenu['gglmps_manager'] ) ) {
			$submenu['gglmps_manager'][] = array(
				'<span style="color:#d86463"> ' . __( 'Upgrade to Pro', 'bws-google-maps' ) . '</span>',
				'manage_options',
				'https://bestwebsoft.com/products/wordpress/plugins/bws-google-maps/?k=5ae35807d562bf6b5c67db88fefece60&pn=124&v=' . $gglmps_plugin_info["Version"] . '&wp_v=' . $wp_version );
		}

		add_action( "load-$hook", 'gglmps_screen_options' );
		add_action( 'load-' . $settings, 'gglmps_add_tabs' );
		add_action( 'load-' . $gglmps_manager, 'gglmps_add_tabs' );
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

		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $gglmps_plugin_info, '4.5' );

		if ( ! is_admin() || isset( $_GET['page'] ) && ( $_GET['page'] == 'bws-google-maps.php' || $_GET['page'] == 'gglmps_manager' || $_GET['page'] == 'gglmps_editor' ) ) {
			gglmps_get_options();
		}
	}
}

/*
* Function to add plugin version.
*/
if ( ! function_exists ( 'gglmps_admin_init' ) ) {
	function gglmps_admin_init() {
		global $pagenow, $bws_plugin_info, $bws_shortcode_list, $gglmps_plugin_info, $gglmps_options;

		if ( empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '124', 'version' => $gglmps_plugin_info['Version'] );

		/* add Maps to global $bws_shortcode_list */
		$bws_shortcode_list['gglmps'] = array( 'name' => 'Maps', 'js_function' => 'gglmps_shortcode_init' );

		if ( 'plugins.php' == $pagenow ) {
			if ( function_exists( 'bws_plugin_banner_go_pro' ) ) {
				gglmps_get_options();
				bws_plugin_banner_go_pro( $gglmps_options, $gglmps_plugin_info, 'gglmps', 'bws-google-maps', 'f546edd672c2e16f8359dcb48f9d2fff', '124', 'bws-google-maps' );
			}
		}
	}
}

/*
* Function to set up default options.
*/
if ( ! function_exists ( 'gglmps_get_options' ) ) {
	function gglmps_get_options() {
		global $gglmps_options, $gglmps_maps, $gglmps_plugin_info;

		if ( ! get_option( 'gglmps_options' ) ) {
			$options_default = gglmps_get_options_default();
			add_option( 'gglmps_options', $options_default );
		}

		$gglmps_options = get_option( 'gglmps_options' );

		if ( ! get_option( 'gglmps_maps' ) )
			add_option( 'gglmps_maps', array() );
		$gglmps_maps = get_option( 'gglmps_maps' );

		if ( ! isset( $gglmps_options['plugin_option_version'] ) || $gglmps_options['plugin_option_version'] != $gglmps_plugin_info['Version'] ) {
			$options_default = gglmps_get_options_default();
			$gglmps_options = array_merge( $options_default, $gglmps_options );
			$gglmps_options['plugin_option_version'] = $gglmps_plugin_info["Version"];
			/* show pro features */
			$gglmps_options['hide_premium_options'] = array();

			update_option( 'gglmps_options', $gglmps_options );
		}
	}
}

if ( ! function_exists( 'gglmps_get_options_default' ) ) {
	function gglmps_get_options_default() {
		global $gglmps_plugin_info;

		$default_options = array(
			'plugin_option_version'		=> $gglmps_plugin_info['Version'],
			'display_settings_notice'	=> 1,
			'suggest_feature_banner'	=> 1,
			/* end deneral options */
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

		return $default_options;
	}
}

/**
 * Activation plugin function
 */
if ( ! function_exists( 'gglmps_plugin_activate' ) ) {
	function gglmps_plugin_activate( $networkwide = '' ) {
		/* Activation function for network, check if it is a network activation - if so, run the activation function for each blog id */
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'gglmps_delete_options' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'gglmps_delete_options' );
		}
	}
}

/*
* Function to display plugin main settings page.
*/
if ( ! function_exists( 'gglmps_settings_page' ) ) {
	function gglmps_settings_page() {
		if ( ! class_exists( 'Bws_Settings_Tabs' ) )
            require_once( dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-gglmps-settings.php' );
		$page = new Gglmps_Settings_Tabs( plugin_basename( __FILE__ ) );
		if ( method_exists( $page, 'add_request_feature' ) ) {
			$page->add_request_feature(); 
		} ?>    
		<div class="wrap">
			<h1>Maps <?php _e( 'Settings', 'bws-google-maps' ); ?></h1>			
			<?php $page->display_content(); ?>
		</div>
	<?php }
}

if ( ! function_exists( 'gglmps_appearance_page' ) ) {
	function gglmps_appearance_page() {
		global $gglmps_plugin_info, $wp_version, $gglmps_options;  

		if ( empty( $gglmps_options ) )
			$gglmps_options = get_option( 'gglmps_options' );

		$bws_hide_premium_options_check = bws_hide_premium_options_check( $gglmps_options );?>
		<div class="wrap">
			<h1><?php _e( 'Maps Appearance', 'bws-google-maps' ); ?></h1>
			<?php if ( ! $bws_hide_premium_options_check ) : ?>			
			<div class="bws_pro_version_bloc">
				<div class="bws_pro_version_table_bloc">
					<div class="bws_table_bg" style="top: 0;"></div>
					<div id="gglmps_appearance_block" style="margin: 10px;">
						<div id="gglmps_settings_notice" class="updated below-h2">
							<?php _e( 'Install required styles in order to apply them to certain maps on Maps Editor page', 'bws-google-maps' ); ?>.
						</div><!-- #gglmps_settings_notice -->
						<table class="gglmps_settings_table form-table">
							<tbody>
								<tr>
									<th><?php _e( 'Snazzymaps API Key', 'bws-google-maps' ); ?></th>
									<td>
										<div class="gglmps-snazzymaps-api">
											<input type="text" id="gglmps_snazzymaps_api_key_value" disabled="disabled" size="36" value="">
											<input type="submit" class="button-primary" id="gglmps_snazzymaps_key_submit" disabled="disabled" value="<?php _e( 'Save', 'bws-google-maps' ) ?>">
										</div>
										<p class="bws_info">
											<?php printf(
												'%1$s. <a>%2$s</a> %3$s <a>%4$s</a>.',
												__( 'You can use your own API key which allows using favorite styles from your Snazzymaps account', 'bws-google-maps' ),
												__( 'Personal API key', 'bws-google-maps' ),
												__( 'is available after', 'bws-google-maps' ),
												__( 'registration', 'bws-google-maps' )
											); ?>
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
											<input class="wp-filter-search gglmps-filter" id="gglmps-search" disabled="disabled" type="text" placeholder="<?php _e( 'Search...', 'bws-google-maps' ); ?>" value="">
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
													'name'			=> 'Midnight Commander',
													'is-installed'	=> 1,
													'default'		=> 1
												),
												array(
													'name'			=> 'Unsaturated Browns',
													'is-installed'	=> 1,
													'default'		=> 0
												),
												array(
													'name'			=> 'Bentley',
													'is-installed'	=> 0,
													'default'		=> 0
												),
												array(
													'name' 			=> 'Blue Essence',
													'is-installed' 	=> 0,
													'default'		=> 0
												),
												array(
													'name'			=> 'Nature',
													'is-installed'	=> 0,
													'default'		=> 0
												),
												array(
													'name'			=> 'Just Retro',
													'is-installed'	=> 0,
													'default'		=> 0
												),
												array(
													'name'			=> 'İnturlam Style',
													'is-installed'	=> 0,
													'default'		=> 0
												),
												array(
													'name'			=> 'Sin City',
													'is-installed'	=> 1,
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
													<div class="theme-id-container">
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
	                <a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/bws-google-maps/?k=5ae35807d562bf6b5c67db88fefece60&amp;pn=124&amp;v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Maps"><?php _e( 'Upgrade to Pro', 'bws-google-maps' ); ?></a>
					<div class="clear"></div>
	            </div>
			</div>
			<?php else : ?>
			<p>
				<?php _e( 'This tab contains Pro options only.', 'bws-google-maps' );
				echo ' ' . sprintf(
					__( '%sChange the settings%s to view the Pro options.', 'bws-google-maps' ),
					'<a href="admin.php?page=bws-google-maps.php&bws_active_tab=misc">',
					'</a>'
					); ?>
			</p>
			<?php endif; ?>
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
		$message = "";

		$message = "";

		if ( $gglmps_manager->current_action() ) {
			$gglmps_manager_action = $gglmps_manager->current_action();
		} else {
			$gglmps_manager_action = isset( $_REQUEST['gglmps_manager_action'] ) ? $_REQUEST['gglmps_manager_action'] : '';
		}

		switch ( $gglmps_manager_action ) {
			case 'delete':
				if ( check_admin_referer( plugin_basename( __FILE__ ), 'gglmps_nonce_name' ) ) {
					$gglmps_manager_mapid = isset( $_REQUEST['gglmps_manager_mapid'] ) ? $_REQUEST['gglmps_manager_mapid'] : array();
					$gglmps_mapids = is_array( $gglmps_manager_mapid ) ? $gglmps_manager_mapid : array( $gglmps_manager_mapid );
					foreach ( $gglmps_mapids as $gglmps_mapid ) {
						if ( isset( $gglmps_maps[ $gglmps_mapid ] ) ) {
							$gglmps_maps[ $gglmps_mapid ] = NULL;
						}
					}
					update_option( 'gglmps_maps', $gglmps_maps );
					$message = sprintf( __( '%s maps deleted.', 'bws-google-maps' ), count( $gglmps_mapids ) );
				}
				break;
			case 'delete_single':
				if ( isset( $_GET['gglmps_manager_mapid'] ) && check_admin_referer( 'gglmps_delete_' . $_GET['gglmps_manager_mapid'] ) ) {
					if ( isset( $gglmps_maps[ $_GET['gglmps_manager_mapid'] ] ) ) {
						$gglmps_maps[ $_GET['gglmps_manager_mapid'] ] = NULL;
					}
					update_option( 'gglmps_maps', $gglmps_maps );
					$message = __( 'Map deleted.', 'bws-google-maps' );
				}
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
					'title'     => sprintf( '<a class="row-title" href="admin.php?page=gglmps_editor&gglmps_id=%1$d">%2$s</a>', $key, $gglmps_map['title'] ),
					'shortcode' => sprintf( '[bws_googlemaps id=%d]', $key ),
					'date'      => $gglmps_map['date']
				);
			}
		}
		$gglmps_manager->gglmps_table_data = $gglmps_result;
		$gglmps_manager->prepare_items(); ?>
		<div class="wrap">
			<?php printf(
				'<h1> %s <a class="add-new-h2" href="%s" >%s</a></h1>',
				esc_html__( 'Maps', 'bws-google-maps' ),
				esc_url( admin_url( 'admin.php?page=gglmps_editor' ) ),
				esc_html__( 'Add New', 'bws-google-maps' )
			); ?>
			<noscript>
				<div class="error">
					<p>
						<?php printf(
							'<strong>%1$s</strong> %2$s.',
							__( 'WARNING:', 'bws-google-maps' ),
							__( 'Maps only works with JavaScript enabled.', 'bws-google-maps' )
						); ?>
					</p>
				</div><!-- .error -->
			</noscript><!-- noscript -->
			<div class="updated fade below-h2" <?php if ( $message == "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
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

			$actions = array(
				'edit'		=> sprintf( '<a href="?page=gglmps_editor&gglmps_id=%d">%s</a>', $item['gglmps-id'], __( 'Edit', 'bws-google-maps' ) ),
				'delete'	=> '<a href="' . esc_url( wp_nonce_url( sprintf( '?page=%s&gglmps_manager_action=delete_single&gglmps_manager_mapid=%d', $_REQUEST['page'], $item['gglmps-id'] ), 'gglmps_delete_' . $item['gglmps-id'] ) ) . '">' . __( 'Delete', 'bws-google-maps' ) . '</a>'
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

/**
 * Function will render new slider page.
 */
if ( ! function_exists( 'gglmps_editor_page' ) ) {
	function gglmps_editor_page() {
		if ( ! class_exists( 'Bws_Settings_Tabs' ) )
            require_once( dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-gglmps-add-new.php' );
		$page = new Gglmps_Single_Tabs( plugin_basename( __FILE__ ) ); ?>
		<div class="wrap">
			<?php $page->display_content(); ?>
		</div>
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

/* add help tab */
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
				'https://maps.googleapis.com/maps/api/js?libraries=places%1$s%2$s',
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

			bws_enqueue_settings_scripts();
		} else if ( isset( $_GET['page'] ) && $_GET['page'] == 'bws-google-maps.php' ) {
			wp_enqueue_script( 'gglmps_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'gglmps_settings_script', plugins_url( 'js/settings.js', __FILE__ ), array( 'jquery-ui-slider', 'jquery-touch-punch' ) );

			bws_enqueue_settings_scripts();
			bws_plugins_include_codemirror();
				
		} else if ( isset( $_GET['page'] ) && $_GET['page'] == 'bws-google-maps-appearance.php' ) {
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

			$api_key = ! empty( $gglmps_options['api_key'] ) ? sprintf( 'key=%s&', $gglmps_options['api_key'] ) : '';
			$language = sprintf( 'language=%s', $gglmps_options['language'] );
			$api = sprintf(
				'https://maps.googleapis.com/maps/api/js?%1$s%2$s',
				$api_key,
				$language
			);
			wp_enqueue_script( 'gglmps_api', $api );
			wp_enqueue_script( 'gglmps_script' );
		}
	}
}

/*
* Function to display Maps.
*/
if ( ! function_exists( 'gglmps_shortcode' ) ) {
	function gglmps_shortcode( $atts ) {
		global $gglmps_maps;

		if ( ! isset( $atts['id'] ) ) {
			return sprintf(
				'<div class="gglmps_map_error">[Maps: %s]</div>',
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

			if ( ! wp_script_is( 'gglmps_script', 'registered' ) )
				wp_register_script( 'gglmps_script', plugins_url( 'js/gglmps.js' , __FILE__ ), array( 'jquery' ), false, true );

			return sprintf(
				'<div class="gglmps_container_map">
					<div id="%1$s" class="gglmps_map" style="%2$s width: %3$s; height: %4$s;" data-basic="%7$s" data-controls="%8$s" data-markers="%9$s">
						<noscript>
							<p class="gglmps_no_script">
								[Maps: %5$s <a href="https://support.google.com/answer/23852" target="_blank">%6$s</a>]
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
			return sprintf(
				'<div class="gglmps_map_error">[Maps: %1$s ID#%2$d %3$s]</div>',
				__( 'Map with', 'bws-google-maps' ),
				$atts['id'],
				__( 'not found', 'bws-google-maps' )
			);
		}
	}
}

/* add shortcode content */
if ( ! function_exists( 'gglmps_shortcode_button_content' ) ) {
	function gglmps_shortcode_button_content( $content ) { ?>
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
						<span class="title">' . __( 'Map', 'bws-google-maps' ) . '</span>';
					}
					if ( ! isset( $map_first ) ) { ?>
						<span class="title"><?php _e( 'Maps not found.', 'bws-google-maps' ); ?></span>
					<?php } else
						echo $result; ?>
				</label>
			</fieldset>
			<?php if ( ! empty( $map_first ) ) { ?>
				<input class="bws_default_shortcode" type="hidden" name="default" value="[bws_googlemaps id=<?php echo $map_first; ?>]" />
			<?php }
			$script = "function gglmps_shortcode_init() {
					(function($) {
						$( '.mce-reset #gglmps_shortcode_list' ).on( 'change', function() {
							var map = $( '.mce-reset #gglmps_shortcode_list option:selected' ).val();
							var shortcode = '[bws_googlemaps id=' + map + ']';

							$( '.mce-reset #bws_shortcode_display' ).text( shortcode );
						});
					})(jQuery);
				}";
			wp_register_script( 'gglmps_shortcode', '' );
			wp_enqueue_script( 'gglmps_shortcode' );
			wp_add_inline_script( 'gglmps_shortcode', sprintf( $script ) ); ?>
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
			bws_plugin_banner_to_settings( $gglmps_plugin_info, 'gglmps_options', 'bws-google-maps', 'admin.php?page=bws-google-maps.php', 'admin.php?page=gglmps_editor' );
		}

		if ( isset( $_GET['page'] ) && 'bws-google-maps.php' == $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $gglmps_plugin_info, 'gglmps_options', 'bws-google-maps' );
		}
	}
}

/*
* Function to uninstall plugin.
*/
if ( ! function_exists( 'gglmps_delete_options' ) ) {
	function gglmps_delete_options() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$all_plugins = get_plugins();

		if ( ! array_key_exists( 'bws-google-maps-pro/bws-google-maps-pro.php', $all_plugins ) ) {
			global $wpdb;
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				$old_blog = $wpdb->blogid;
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					delete_option( 'gglmps_options' );
					delete_option( 'gglmps_maps' );
				}
				switch_to_blog( $old_blog );
				delete_site_option( 'gglmps_options' );

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


register_activation_hook( __FILE__, 'gglmps_plugin_activate' );
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
