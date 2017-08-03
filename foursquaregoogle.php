<?php
/*
Plugin Name: FourSquare + Google Maps
Plugin URI: http://system.damakhijau.com/
Description: Integrate google maps api with foursquare api to create an attractive and meaningful maps.
Author: Sanusi Yaakub
Version: 1.0
Author URI: http://system.damakhijau.com/
*/

$outputBuffer = ini_get('output_buffering');
if( empty( $outputBuffer ) ){
	ini_set( 'output_buffering', 'on' );
}

global $wpdb;
/* define table name */
define( 'FSQR_TABLE', $wpdb->prefix . '4sqrgo' );

/* this is the CSS for front page */
// add_action( 'wp_loaded', 'foursquaregoogle_map_css_front' );
function foursquaregoogle_map_css_front( $content ){
	if( has_shortcode( $content, '4sqrgo' ) ){
		wp_register_style( '4sqr-main-css', plugin_dir_url( __FILE__ ) . 'css/foursquaregoogle.css' );
		wp_enqueue_style( '4sqr-main-css' );
	}
	return $content;
}
add_filter( 'the_content', 'foursquaregoogle_map_css_front' );

/* shortcode to be placed on page */
function foursquaregoogle_map( $attrs ){
	
	global $wpdb;
	$settings = $wpdb->get_results( 'SELECT setting_name, setting_value FROM '. FSQR_TABLE );
	$shortcodeParam = array();
	foreach( $settings as $setting ){
		$shortcodeParam[$setting->setting_name] = $setting->setting_value;
	}
	
	$a = shortcode_atts( $shortcodeParam, $attrs );
	
	/******* the map ********/
	ob_start();
?>
<div style="position:relative; width:<?php echo $a['width']; ?>;height:<?php echo $a['height']; ?>;">
	<div id="fsqrgo" style="width:100%;height:100%;"></div>
	<input type="hidden" name="fsqr_lat" id="fsqr_lat" value="<?php echo $a['latitude']; ?>" />
	<input type="hidden" name="fsqr_lng" id="fsqr_lng" value="<?php echo $a['longitude']; ?>" />
	<input type="hidden" name="fsqr_location_id" id="fsqr_location_id" value="<?php echo $a['foursquare_id']; ?>" />
	<input type="hidden" name="fsqr_zoom_level" id="fsqr_zoom_level" value="<?php echo $a['zoom']; ?>" />
	<input type="hidden" name="fsqr_main_icon" id="fsqr_main_icon" value="<?php echo $a['icon']; ?>" />
	<input type="hidden" name="fsqr_control" id="fsqr_control" value="<?php echo $a['show_map_control']; ?>" />
</div>
<?php
	$mainmap = ob_get_clean();
	
	/******* the js ********/
	wp_enqueue_script( '4sqr-main-js', plugin_dir_url( __FILE__ ) . 'js/foursquaregoogle.js', array('jquery') );
	
	return $mainmap;
}
add_shortcode( '4sqrgo', 'foursquaregoogle_map' );

/* this is the CSS for admin page */
function foursquaregoogle_map_css(){
	wp_enqueue_style( plugin_dir_url( __FILE__ ) . 'css/foursquaregoogle.css' );
}
add_action( 'admin_head', 'foursquaregoogle_map_css' );

/* this is to add admin settings page */
function foursquare_plugin_menu() {
	add_options_page( 'FourSquare + Google Maps', 'FourSquare + Google Maps', 'manage_options', 'foursquare-google-maps', 'foursquare_plugin_options' );
}

function foursquare_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	global $wpdb;
	if( isset( $_POST['submit'] ) && !empty( $_POST['submit'] ) ){
		
		$height = $_POST['map_height'];
		$width = $_POST['map_width'];
		$icon = $_POST['marker_icon'];
		$zoom = (int)$_POST['zoom_level'];
		$show_map_control = (int)$_POST['show_map_control'];
		$latitude = (float)$_POST['latitude'];
		$longitude = (float)$_POST['longitude'];
		$foursquare_id = $_POST['foursquare_id'];
		
		$wpdb->update( FSQR_TABLE, array( 'setting_value' => $height ), array( 'setting_name' => 'height' ) );
		$wpdb->update( FSQR_TABLE, array( 'setting_value' => $width ), array( 'setting_name' => 'width' ) );
		$wpdb->update( FSQR_TABLE, array( 'setting_value' => $icon ), array( 'setting_name' => 'icon' ) );
		$wpdb->update( FSQR_TABLE, array( 'setting_value' => $zoom ), array( 'setting_name' => 'zoom' ) );
		$wpdb->update( FSQR_TABLE, array( 'setting_value' => $latitude ), array( 'setting_name' => 'latitude' ) );
		$wpdb->update( FSQR_TABLE, array( 'setting_value' => $longitude ), array( 'setting_name' => 'longitude' ) );
		$wpdb->update( FSQR_TABLE, array( 'setting_value' => $foursquare_id ), array( 'setting_name' => 'foursquare_id' ) );
		$wpdb->update( FSQR_TABLE, array( 'setting_value' => $show_map_control ), array( 'setting_name' => 'show_map_control' ) );
	}else{
		$settings = $wpdb->get_results( 'SELECT setting_name, setting_value FROM '. FSQR_TABLE );
		// var_dump( $settings );
		foreach( $settings as $setting ){
			${$setting->setting_name} = $setting->setting_value;
		}
	}
	
	ob_start();
?>
<div class="wrap">
	<h2>FourSquare + Google Maps Global Settings</h2>
	<form action="" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="map_height">Map Height</label></th>
					<td><input id="map_height" class="regular-text" type="text" value="<?php echo $height; ?>" name="map_height" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="map_width">Map Width</label></th>
					<td><input id="map_width" class="regular-text" type="text" value="<?php echo $width; ?>" name="map_width" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="marker_icon">Marker Icon</label></th>
					<td>
						<textarea name="marker_icon" id="marker_icon" cols="30" rows="3"><?php echo $icon; ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="zoom_level">Zoom Level</label></th>
					<td><input id="zoom_level" class="regular-text" type="text" value="<?php echo $zoom; ?>" name="zoom_level" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="show_map_control">Show Map Control</label></th>
					<td>
						<select name="show_map_control" id="show_map_control">
							<option value="0"<?php echo $show_map_control == 0 ? ' selected="selected"' : null; ?>>No</option>
							<option value="1"<?php echo $show_map_control == 1 ? ' selected="selected"' : null; ?>>Yes</option>
						</select>
						<p class="description">Sometimes it is better to turn the control off.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="latitude">Latitude</label></th>
					<td><input id="latitude" class="regular-text" type="text" value="<?php echo $latitude; ?>" name="latitude" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="longitude">Longitude</label></th>
					<td><input id="longitude" class="regular-text" type="text" value="<?php echo $longitude; ?>" name="longitude" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="foursquare_id">FourSquare Location ID</label></th>
					<td>
						<input id="foursquare_id" class="regular-text" type="text" value="<?php echo $foursquare_id; ?>" name="foursquare_id" />
						<p class="description">This value will be used to retrieve the tips for this location</p>
					</td>
				</tr>
				
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit" />
		</p>
	</form>
</div>
<?php
	$content = ob_get_clean();
	echo $content;
}
add_action( 'admin_menu', 'foursquare_plugin_menu' );
	
/* install/update */
global $jal_db_version;
$jal_db_version = "1.0";

function fsqr_install(){
	global $wpdb;
	global $jal_db_version;
	
	// this table is used to stored global variable of a map
	$sql = "CREATE TABLE ".FSQR_TABLE." (
		setting_name VARCHAR(30),
		setting_value TINYTEXT
	);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( "jal_db_version", $jal_db_version );
}
register_activation_hook( __FILE__, 'fsqr_install' );

function fsqr_install_data() {
	global $wpdb;

	$wpdb->insert( FSQR_TABLE, array( 'setting_name' => 'height', 'setting_value' => '300px' ) );
	$wpdb->insert( FSQR_TABLE, array( 'setting_name' => 'width', 'setting_value' => '300px' ) );
	$wpdb->insert( FSQR_TABLE, array( 'setting_name' => 'icon', 'setting_value' => plugin_dir_url( __FILE__ ) . 'img/marker_green_32.png' ) );
	$wpdb->insert( FSQR_TABLE, array( 'setting_name' => 'zoom', 'setting_value' => 14 ) );
	$wpdb->insert( FSQR_TABLE, array( 'setting_name' => 'latitude', 'setting_value' => 5.810632 ) );
	$wpdb->insert( FSQR_TABLE, array( 'setting_name' => 'longitude', 'setting_value' => 102.034195 ) );
	$wpdb->insert( FSQR_TABLE, array( 'setting_name' => 'foursquare_id', 'setting_value' => '52ff64fa498e27cc185cf9be' ) );
	$wpdb->insert( FSQR_TABLE, array( 'setting_name' => 'show_map_control', 'setting_value' => 1 ) );
}
register_activation_hook( __FILE__, 'fsqr_install_data' );

function fsqr_update_db_check() {
	global $jal_db_version;
	if( get_site_option( 'jal_db_version' ) != $jal_db_version ){
		fsqr_install();
	}
}
add_action( 'plugins_loaded', 'fsqr_update_db_check' );
?>