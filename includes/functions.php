<?php
/**
 * Course Session For Sensei Functions
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * This is a wrapper function for Course_Session_For_Sensei_Templates::get_template
 * It helps simplify templates for designers by removing the class::function call.
 *
 * @param string $template_name the name of the template.
 *              If it is in a sub directory please supply the directory name as well e.g. globals/wrapper-end.php
 *
 * @since 1.0.0
 */
function css_load_template( $template_name ) {

	Course_Session_For_Sensei_Templates::get_template( $template_name );
}


/**
 * This is a wrapper function for Sensei_Templates::get_part
 * It helps simplify templates for designers by removing the class::function call.
 *
 * @param string $slug the first part to the template file name
 * @param string $name the name of the template.
 * @since 1.0.0
 */
function css_load_template_part( $slug, $name ) {

	Course_Session_For_Sensei_Templates::get_part( $slug, $name );
}


/**
 * Is Course Session Management plugin?
 * Hooked by is_sensei() to return true for is Sensei when is CSS.
 *
 * @see Course_Session_For_Sensei_Controller::is_sensei_filter()
 *
 * @return bool True on Single course session or Group of sessions pages.
 */
function is_course_session_for_sensei() {

	$is_course_session_for_sensei = false;

	if ( is_post_type_archive( CSS_CPT_SLUG ) || is_singular( CSS_CPT_SLUG ) || is_tax( CSS_TAXONOMY_SLUG ) ) {

		$is_course_session_for_sensei = true;
	}

	return apply_filters( 'is_course_session_for_sensei', $is_course_session_for_sensei );
}



/**
 * Display the Module Date Status
 * Reuse the Sensei status markup.
 */
function css_the_module_date_status() {

	$date = css_get_the_module_date();

	if ( empty( $date ) ) {

		echo '';

		return;
	}

	$fdate = date_i18n( get_option( 'date_format' ), mysql2date( 'U', $date ) );

	?>
	<p class="status module-status date <?php echo css_is_the_module_access_restricted( false ) ? 'restricted' : ''; ?>">
		<?php echo $fdate; ?>
	</p>
	<?php
}


/**
 * Get the Module Date
 */
function css_get_the_module_date() {

	global $sensei_modules_loop;

	$module_term_id = $sensei_modules_loop['current_module']->term_id;
	$course_id = $sensei_modules_loop['course_id'];

	return css_get_module_date( $course_id, $module_term_id );
}


/**
 * Get Module Date
 */
function css_get_module_date( $course_id, $module_term_id ) {

	// Field name.
	$name = 'date_course_' . $course_id;

	$date = '';

	if ( $module_term_id ) {

		$date = get_term_meta( $module_term_id, $name, true );
	}

	if ( empty( $date ) ) {

		return '';
	}

	return $date;
}


/**
 * Is the module access restricted?
 * Check if module is restricted AND today is before opening date.
 *
 * @param bool $check_user_permissions Check user permissions, defaults to true, optional.
 *
 * @return bool
 */
function css_is_the_module_access_restricted( $check_user_permissions = true ) {

	global $sensei_modules_loop;

	$module_term_id = $sensei_modules_loop['current_module']->term_id;
	$course_id = $sensei_modules_loop['course_id'];

	// Check if module is restricted AND today is before opening date.
	return css_is_module_access_restricted( $course_id, $module_term_id, $check_user_permissions );
}


/**
 * Is module access restricted?
 * Check if module is restricted AND today is before opening date.
 * Allow if user has Sensei all access or can edit courses.
 *
 * @param int  $course_id              Course ID.
 * @param int  $module_term_id         Module Term ID.
 * @param bool $check_user_permissions Check user permissions, defaults to true, optional.
 *
 * @return bool
 */
function css_is_module_access_restricted( $course_id, $module_term_id, $check_user_permissions = true ) {

	if ( $check_user_permissions &&
		( sensei_all_access() ||
		user_can( get_current_user_id(), 'edit_courses' ) ) ) {

		// User has Sensei all access or can edit courses.
		return false;
	}

	// Field name.
	$name = 'access_course_' . $course_id;

	$access = '';

	if ( $module_term_id ) {

		$access = get_term_meta( $module_term_id, $name, true );
	}

	if ( empty( $access ) ) {

		// Module is not restricted.
		return false;
	}

	$date = css_get_module_date( $course_id, $module_term_id );

	// Check if today is before opening date.
	return date( 'Y-m-d' ) < $date;
}

/**
 * Temporarily disable restricted module terms filter.
 */
function css_temporarily_disable_restricted_module_terms_filter() {

	global $css_restricted_module_terms_filter_disabled;

	$css_restricted_module_terms_filter_disabled = true;
}
