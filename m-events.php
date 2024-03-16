<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://github.com/muhammad-07
 * @since             1.0.0
 * @package           M_Events
 *
 * @wordpress-plugin
 * Plugin Name:       M-Events
 * Plugin URI:        https://https://artcloud.fi
 * Description:       Recurring/Repeating events management
 * Version:           1.0.0
 * Author:            Muhammad
 * Author URI:        https://https://github.com/muhammad-07/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       m-events
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'M_EVENTS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-m-events-activator.php
 */
function activate_m_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-m-events-activator.php';
	M_Events_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-m-events-deactivator.php
 */
function deactivate_m_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-m-events-deactivator.php';
	M_Events_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_m_events' );
register_deactivation_hook( __FILE__, 'deactivate_m_events' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
// require plugin_dir_path( __FILE__ ) . 'includes/class-m-events.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
// function run_m_events() {

// 	$plugin = new M_Events();
// 	$plugin->run();
require_once('vendor/Carbon/autoload.php');
// }
include_once 'm-event-functions.php';
include_once 'm-event-metaboxes.php';
// run_m_events();
