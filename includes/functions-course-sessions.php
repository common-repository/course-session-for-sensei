<?php
/**
 * Course Session For Sensei Functions
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Ordered Course Sessions
 *
 * @since 1.0.0
 *
 * @uses get_terms()
 *
 * @param integer $group_id Group of Sessions ID.
 *
 * @return bool|array False if no group of sessions ID, array WP_Post objects.
 */
function css_get_course_sessions( $group_id ) {
	static $course_sessions = array();

	if ( empty( $group_id ) ) {

		return false;
	}

	if ( isset( $course_sessions[ $group_id ] ) ) {

		return $course_sessions[ $group_id ];
	}

	/**
	 * @link https://developer.wordpress.org/reference/classes/wp_term_query/__construct/#source
	 */
	$args = array(
		'post_type'      => CSS_CPT_SLUG,
		'orderby'        => 'post__in',
		'post__in'       => css_get_course_session_order( $group_id ),
		'posts_per_page' => 100,
	);

	$course_sessions[ $group_id ] = get_posts( $args )[0];

	return $course_sessions[ $group_id ];
}


/**
 * Get Course Session Order wrapper
 *
 * @uses Course_Session_For_Sensei_Order::get_course_session_order()
 *
 * @since 1.0.0
 *
 * @param int $group_id Group of Sessions ID.
 *
 * @return mixed Course Session Order.
 */
function css_get_course_session_order( $group_id ) {

	return Course_Session_For_Sensei_Order::get_course_session_order( $group_id );
}


/**
 * Get (the) Course Session
 *
 * @since 1.0.0
 *
 * @uses css_get_course_sessions()
 *
 * @param int $course_session_id Course Session ID.
 *
 * @return WP_Post False if not on a single course / course session or group of sessions page.
 */
function css_get_course_session( $course_session_id = 0 ) {

	if ( $course_session_id ) {

		return get_post( $course_session_id );
	}

	if ( CSS_CPT_SLUG === get_post_type( get_the_ID() ) ) {

		return get_post( get_the_ID() );
	}

	if ( ! is_singular( array( 'course', CSS_CPT_SLUG ) ) &&
		! is_tax( array( 'module', CSS_TAXONOMY_SLUG ) ) ) {

		return null;
	}

	$the_course_sessions_query = css_get_the_course_sessions_query();

	$the_course_session = $the_course_sessions_query->post;

	return $the_course_session;
}


/**
 * Get the Course Session ID
 *
 * @since 1.0.0
 *
 * @uses css_get_the_course_session()
 *
 * @return int 0 else Course Session ID.
 */
function css_get_the_course_session_id() {

	if ( CSS_CPT_SLUG === get_post_type( get_the_ID() ) ) {

		return get_the_ID();
	}

	$the_course_session = css_get_course_session();

	if ( ! $the_course_session ) {

		return 0;
	}

	return $the_course_session->ID;
}


/**
 * Get the Session Group of Sessions ID
 *
 * @since 1.0.0
 *
 * @uses css_get_the_group_of_sessions_id()
 *
 * @return int 0 else Group of Sessions ID.
 */
function css_get_the_course_session_group_id() {

	// TODO if outside Group of Sessions context?? REQUIRE $_GET['group_id'] Can be multiple...
	return css_get_the_group_of_sessions_id();
}


/**
 * The next course session post in the course session loop
 *
 * @since 1.0.0
 */
function css_the_course_session_post() {

	if ( ! css_the_group_have_sessions() ) {

		$course_sessions_query = css_get_the_course_sessions_query();

		$course_sessions_query->reset_postdata();
	}

	if ( is_singular( CSS_CPT_SLUG ) ) {

		// If querying Course Sessions, no problem.
		the_post();

	} else {

		$course_sessions_query = css_get_the_course_sessions_query();

		$course_sessions_query->next_post();
	}
}


/**
 * Is the course session coming next in the course items loop?
 *
 * @since 1.0.0
 *
 * @uses Course_Session_For_Sensei_Order::is_course_session_before_module_or_lesson
 * @uses Course_Session_For_Sensei_Order::is_course_session_last
 * @uses css_is_the_module_last()
 * @uses css_is_singular_lesson()
 *
 * @param string $type Type: 'module' or 'lesson'.
 *
 * @return bool
 */
function css_is_the_course_session_next( $type ) {

	if ( ! css_the_group_have_sessions() ) {

		return false;
	}

	if ( 'lesson' === $type ) {

		$the_module_or_lesson_id = get_the_ID();
	} else {
		$the_module_or_lesson_id = sensei_get_the_module_id();
	}

	$course_sessions_query = css_get_the_course_sessions_query();

	$next_course_session_id = $course_sessions_query->posts[ $course_sessions_query->current_post + 1 ]->ID;

	$is_before_module_or_lesson = Course_Session_For_Sensei_Order::is_course_session_before_module_or_lesson(
		css_get_the_group_of_sessions_id(),
		$next_course_session_id,
		$the_module_or_lesson_id,
		$type
	);

	if ( $is_before_module_or_lesson ) {

		return true;

	} elseif ( 'lesson' === $type ) {

		return false;
	}

	if ( ! css_is_the_module_last() ) {

		return false;
	}

	// Display last course session AFTER the last module content.
	if ( false === strpos( current_action(), 'after' ) ) {

		// We are in the BEFORE module content action.
		return false;
	}

	return Course_Session_For_Sensei_Order::is_course_session_last(
		css_get_the_group_of_sessions_id(),
		$next_course_session_id
	);
}


/**
 * Is the module last?
 * Helper function.
 *
 * @return bool
 */
function css_is_the_module_last() {

	global $sensei_modules_loop;

	$current_module = $sensei_modules_loop['current'];

	// Check the current item compared to the total number of modules.
	if ( $current_module + 1 === $sensei_modules_loop[ 'total' ] ) {

		return true;
	}

	if ( ( $current_module + 2 ) === $sensei_modules_loop[ 'total' ] ) {

		if ( ! Sensei()->modules->get_lessons_query( $sensei_modules_loop['course_id'], $sensei_modules_loop[ $current_module ]->term_id ) ) {

			// Last module is empty, so current module is actually last!
			return true;
		}
	}

	return false;
}


/**
 * Is singular lesson?
 * Helper function.
 *
 * is_singular( 'lesson' ) is false but is_tax( 'modules' ) is true.
 * while inside the course module's lessons loop...
 * Use this simple workaround.
 *
 * @return bool
 */
function css_is_singular_lesson() {

	return ( 'lesson' === get_post_type( get_the_ID() ) );
}


/**
 * Check if the current group has any course_sessions.
 * This relies on the global $cs_query. Which will be setup for each group
 * by sensei_the_module(). This function must only be used withing the module course_sessions loop.
 *
 * If the loop has not been initiated this function will check if the first
 * module has course_sessions.
 *
 * @return bool
 */
function css_the_group_have_sessions() {

	static $css_the_group_sessions_loop_has_ended = false;

	if ( is_singular( CSS_CPT_SLUG ) ) {

		// If querying Course Sessions, no problem.
		return have_posts();

	} else {

		// Check first if course have any group?
		if ( ! css_the_course_has_group_of_sessions() ) {

			return false;
		}

		// If querying a course or a group of sessions.
		// If the loop has not been initiated check the group has sessions.
		$course_sessions_query = css_get_the_course_sessions_query();

		// Setup the global cs-query only if the course_sessions.
		if ( $course_sessions_query &&
			$course_sessions_query->have_posts() &&
			! $css_the_group_sessions_loop_has_ended ) {

			return true;
		}

		$css_the_group_sessions_loop_has_ended = true;

		// Default to false if the first module doesn't have posts.
		return false;
	}
}


/**
 * Returns all course_sessions for the current Group of Sessions
 *
 * @uses css_get_course_sessions_query()
 *
 * @return array|WP_Query
 */
function css_get_the_course_sessions_query() {
	$group_id = css_get_the_group_of_sessions_id();

	return css_get_course_sessions_query( $group_id );
}


/**
 * Returns all course_sessions for the given Group of Sessions ID
 *
 * @since 1.0.0
 *
 * @param int $group_id Group of Sessions ID.
 * @return array|WP_Query $query
 */
function css_get_course_sessions_query( $group_id = 0 ) {

	static $query = array();

	if ( empty( $group_id ) ) {
		return array();
	}

	$post__in = css_get_course_session_order( $group_id );

	if ( ! $post__in ) {

		return array();
	}

	if ( isset( $query[ $group_id ] ) ) {

		return $query[ $group_id ];
	}

	$args = array(
		'post_type'      => CSS_CPT_SLUG,
		'orderby'        => 'post__in',
		'post__in'       => $post__in,
		'posts_per_page' => 100,
	);

	$query[ $group_id ] = new WP_Query( $args );

	return $query[ $group_id ];
}


/**
 * Returns a permalink to the course session.
 *
 * @since 1.0.0
 *
 * This function should only be used inside templates.
 *
 * @return string
 */
function css_get_the_course_session_permalink() {

	$group_id = css_get_the_course_session_group_id();

	// Add Group of Sessions ID to URL.
	$url = add_query_arg(
		'group_id',
		$group_id,
		get_the_permalink( css_get_the_course_session_id() )
	);

	/**
	 * Filter the course session permalink url.
	 * This fires within the css_get_the_course_session_permalink function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $course_session_url
	 * @param int    $course_session_id
	 * @param string $group_id
	 */
	return esc_url_raw(
		apply_filters(
			'css_get_the_course_session_permalink',
			$url,
			css_get_the_course_session_id(),
			$group_id
		)
	);
}


/**
 * Returns the current course session title.
 * This must be used within the templates.
 *
 * @since 1.0.0
 *
 * @return string
 */
function css_get_the_course_session_title() {

	$group_id = css_get_the_course_session_group_id();

	$course_session = css_get_course_session();

	if ( ! $course_session ) {

		return '';
	}

	$course_session_title = $course_session->post_title;
	$course_session_id = $course_session->ID;

	/**
	 * Filter the course session title.
	 *
	 * This fires within the css_the_course_session_title function.
	 *
	 * @since 1.0.0
	 *
	 * @param $course_session_title
	 * @param $course_session_id
	 * @param $group_id
	 */
	return apply_filters(
		'css_the_course_session_title',
		$course_session_title,
		$course_session_id,
		$group_id
	);
}


/**
 * Template function to determine if the current user can
 * access the current course session content being viewed.
 *
 * This function checks in the following order
 * - if the current user has all access based on their permissions
 * - If the access permission setting is enabled for this site, if not the user has access
 *
 * @since 1.0.0
 *
 * @param int $course_session_id Course Session ID.
 * @param int $user_id           User ID.
 * @return bool
 */
function css_can_user_view_course_session( $course_session_id = 0, $user_id = 0 ) {

	if ( empty( $course_session_id ) ) {

		$course_session_id = get_the_ID();
	}

	if ( empty( $user_id ) ) {

		$user_id = get_current_user_id();
	}

	$course_session_course_id = css_get_the_group_of_sessions_course_id();

	$user_taking_course = Sensei_Utils::user_started_course( $course_session_course_id, $user_id );

	$user_can_access_course_session =  false;

	if ( is_user_logged_in() && $user_taking_course ) {

		$user_can_access_course_session =  true;
	}
	
	$access_permission = false;

	if ( ! Sensei()->settings->get( 'access_permission' ) || sensei_all_access() ) {

		$access_permission = true;
	}

	$can_user_view_course_session = $access_permission || $user_can_access_course_session;

	/**
	 * Filter the can user view course session function
	 *
	 * @since 1.0.0
	 *
	 * @param bool $can_user_view_course_session
	 * @param string $course_session_id
	 * @param string $user_id
	 */
	return apply_filters(
		'css_can_user_view_course_session',
		$can_user_view_course_session,
		$course_session_id,
		$user_id
	);
}


/**
 * Display the Course Session Date
 * Reuse the Sensei status markup.
 */
function css_the_course_session_date() {

	$course_session_id = css_get_the_course_session_id();

	$date = get_post_meta( $course_session_id, 'css_date', true );

	if ( empty( $date ) ) {

		echo '';

		return;
	}

	$fdate = date_i18n( get_option( 'date_format' ), mysql2date( 'U', $date ) );

	?>
	<p class="status course-session-status date">
		<?php echo $fdate; ?>
	</p>
	<?php
}
