<?php
/**
 * Plugin Name: Course Session For Sensei
 * Version: 1.2.6
 * Plugin URI: https://git.open-dsi.fr/wordpress-plugin/course-session-for-sensei
 * Description: This is your starter template for your next WordPress plugin.
 * Author: Open-DSI
 * Author URI: https://www.open-dsi.fr/
 * Requires at least: 4.9
 * Tested up to: 5.0.3
 *
 * Text Domain: course-session-for-sensei
 * Domain Path: /lang/
 *
 * @package Course Session For Sensei
 * @author Open-DSI
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Load plugin class files.
require_once 'includes/class-course-session-for-sensei.php';
// Extends main object.
require_once 'includes/class-course-session-for-sensei-controller.php';
// require_once 'includes/class-course-session-for-sensei-settings.php';
// Order Sessions in a Group.
require_once 'includes/class-course-session-for-sensei-order.php';
// Templates.
require_once 'includes/class-course-session-for-sensei-templates.php';
// Admin.
require_once 'includes/class-course-session-for-sensei-admin.php';
// Course Session Fields.
require_once 'includes/class-course-session-for-sensei-course-session-field.php';

// Load plugin libraries.
require_once 'includes/lib/class-course-session-for-sensei-admin-api.php';
require_once 'includes/lib/class-course-session-for-sensei-post-type.php';
require_once 'includes/lib/class-course-session-for-sensei-taxonomy.php';

// Load plugin functions.
require_once 'includes/functions.php';
require_once 'includes/functions-group-of-sessions.php';
require_once 'includes/functions-course-sessions.php';

/**
 * Returns the main instance of Course_Session_For_Sensei_Controller to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Course_Session_For_Sensei_Controller
 */
function course_session_for_sensei() {
	$instance = Course_Session_For_Sensei_Controller::instance( __FILE__, '1.2.6' );

	/*if ( is_null( $instance->settings ) ) {
		$instance->settings = Course_Session_For_Sensei_Settings::instance( $instance );
	}*/

	return $instance;
}

course_session_for_sensei();
