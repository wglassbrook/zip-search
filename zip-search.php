<?php
/**
 *  @package   		Zip Search
 *  @author    		Wayne Glassbrook <wayne@ciesadesign.com>
 *  @license   		GPL-2.0+
 *  @link      		https://ciesadesign.com
 *  @copyright 		2024 Wayne Glassbrook/Ciesa Design
 *
 *	Plugin Name:	Zip Search
 *	Plugin URI: 	https://ciesadesign.com
 *	Description:	CSV to searchable data
 *	Version:		  1.3
 *	Author:			  Wayne Glassbrook/Ciesa Design
 *	Author URI:		https://ciesadesign.com
 *	License:		  GPLv2
 *  License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 *  
 */


 if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Define the upload directory for the CSV files
define('ZIP_SEARCH_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/zip-data');

// Activation Hook: Create directory if it doesn't exist
register_activation_hook(__FILE__, 'zip_search_create_directory');
function zip_search_create_directory() {
  if (!file_exists(ZIP_SEARCH_UPLOAD_DIR)) {
      wp_mkdir_p(ZIP_SEARCH_UPLOAD_DIR);
  }
}

// Include admin options page
require_once plugin_dir_path(__FILE__) . 'admin-options.php';

// Include Shortcodes
require_once plugin_dir_path(__FILE__) . 'shortcodes.php';

// Enqueue the CSS file for the plugin
function zip_search_enqueue_styles() {
  // Only load the CSS on pages where the shortcode is used
  if (is_admin() || has_shortcode(get_post()->post_content, 'zip-search')) {
      wp_enqueue_style('zip-search-styles', plugins_url('css/zip-search.css', __FILE__));
      wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css');
      wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
  }
}
add_action('wp_enqueue_scripts', 'zip_search_enqueue_styles');