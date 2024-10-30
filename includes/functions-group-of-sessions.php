<?php
/**
 * Course Session For Sensei Functions
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Course Group of Sessions
 *
 * @since 1.0.0
 *
 * @uses get_terms()
 *
 * @param integer $course_id Course ID.
 *
 * @return bool|mixed False if no course ID, WP_Term object.
 */
function css_get_course_group_of_sessions( $course_id ) {
	static $group_of_sessions = array();

	if ( empty( $course_id ) ) {

		return false;
	}

	if ( isset( $group_of_sessions[ $course_id ] ) ) {

		return $group_of_sessions[ $course_id ];
	}

	/**
	 * @link https://developer.wordpress.org/reference/classes/wp_term_query/__construct/#source
	 */
	$group_of_sessions_arg = array(
		'taxonomy'   => CSS_TAXONOMY_SLUG,
		'hide_empty' => false,
		'meta_key'   => 'course',
		'meta_value' => $course_id,
	);

	$terms = get_terms( $group_of_sessions_arg );

	if ( ! $terms ) {

		// No group of sessions.
		return false;
	}

	$group_of_sessions[ $course_id ] = $terms[0];

	return $group_of_sessions[ $course_id ];
}



/**
 * Get the Course Group of Sessions
 *
 * @since 1.0.0
 *
 * @uses css_get_course_group_of_sessions()
 *
 * @return bool|mixed False if not on a single course / course session or group of sessions page, WP_Term object.
 */
function css_get_the_group_of_sessions() {

	static $the_group_of_sessions;

	if ( is_a( $the_group_of_sessions, 'WP_Term' ) ) {

		return $the_group_of_sessions;
	}

	if ( ! is_singular( array( 'course', CSS_CPT_SLUG ) ) &&
		! is_tax( CSS_TAXONOMY_SLUG ) ) {

		return false;
	}

	if ( is_singular( 'course' ) ) {
		$course_id = get_the_ID();

		$the_group_of_sessions = css_get_course_group_of_sessions( $course_id );
	} elseif ( is_singular( CSS_CPT_SLUG ) ) {

		// Can be multiple, do it anyway.
		if ( isset( $_GET['group_id'] ) &&
			intval( $_GET['group_id'] ) > 0 ) {

			$term_id = intval( $_GET['group_id'] );

			// Group ID in URL, check it has our course session.
			if ( has_term( $term_id, CSS_TAXONOMY_SLUG ) ) {

				$the_group_of_sessions = get_term( $term_id, CSS_TAXONOMY_SLUG );
			}
		}

	} elseif ( is_tax( CSS_TAXONOMY_SLUG ) ) {

		$term_id = get_queried_object_id();

		$the_group_of_sessions = get_term( $term_id, CSS_TAXONOMY_SLUG );
	}

	return $the_group_of_sessions;
}


/**
 * Get the Group of Sessions ID
 *
 * @since 1.0.0
 *
 * @uses css_get_the_group_of_sessions()
 *
 * @return int 0 else Group of Sessions ID.
 */
function css_get_the_group_of_sessions_id() {

	$the_group_of_sessions = css_get_the_group_of_sessions();

	if ( ! $the_group_of_sessions ||
		! is_a( $the_group_of_sessions, 'WP_Term' ) ) {

		return 0;
	}

	return $the_group_of_sessions->term_id;
}


/**
 * Get the Group of Sessions Course ID
 *
 * @since 1.0.0
 *
 * @uses css_get_the_group_of_sessions()
 *
 * @return int 0 else Group of Sessions Course ID.
 */
function css_get_the_group_of_sessions_course_id() {

	$the_group_of_sessions_id = css_get_the_group_of_sessions_id();

	if ( ! $the_group_of_sessions_id ) {

		return 0;
	}

	return get_term_meta( $the_group_of_sessions_id, 'course', true );
}


/**
 * Has the Course a Group of Sessions?
 *
 * @since 1.0.0
 *
 * @uses css_get_the_course_group_of_sessions_id()
 *
 * @return bool False if the course has no group of sessions, else true.
 */
function css_the_course_has_group_of_sessions() {

	return (bool) css_get_the_group_of_sessions_id();
}


/**
 * Returns a permalink to the group of sessions.
 *
 * @since 1.0.0
 *
 * This function should only be used inside templates.
 *
 * @return string
 */
function css_get_the_group_of_sessions_permalink() {

	$course_id = css_get_the_group_of_sessions_course_id();

	// Add Course ID to URL.
	$group_of_sessions_url = add_query_arg(
		'course_id',
		$course_id,
		get_term_link( css_get_the_group_of_sessions_id(), CSS_TAXONOMY_SLUG )
	);

	/**
	 * Filter the group of sessions permalink url.
	 * This fires within the css_get_the_group_of_sessions_permalink function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $group_of_sessions_url
	 * @param int    $group_of_sessions_id
	 * @param string $course_id
	 */
	return esc_url_raw(
		apply_filters(
			'css_get_the_group_of_sessions_permalink',
			$group_of_sessions_url,
			css_get_the_group_of_sessions_id(),
			$course_id
		)
	);
}


/**
 * Returns the current group of sessions name.
 * This must be used within the templates.
 *
 * @since 1.0.0
 *
 * @return string
 */
function css_get_the_group_of_sessions_title() {

	$course_id = css_get_the_group_of_sessions_course_id();

	$group_of_sessions = css_get_the_group_of_sessions();

	if ( ! $group_of_sessions ) {

		return '';
	}

	$group_of_sessions_title = $group_of_sessions->name;
	$group_of_sessions_id = $group_of_sessions->term_id;

	/**
	 * Filter the group of sessions title.
	 *
	 * This fires within the css_the_group_of_sessions_title function.
	 *
	 * @since 1.0.0
	 *
	 * @param $group_of_sessions_title
	 * @param $group_of_sessions_id
	 * @param $course_id
	 */
	return apply_filters(
		'css_the_group_of_sessions_title',
		$group_of_sessions_title,
		$group_of_sessions_id,
		$course_id
	);
}


/**
 * Returns the current group of sessions description.
 * This must be used within the templates.
 *
 * @since 1.0.0
 *
 * @return string
 */
function css_get_the_group_of_sessions_description() {

	$group_of_sessions = css_get_the_group_of_sessions();

	if ( ! $group_of_sessions ) {

		return '';
	}

	$group_of_sessions_description = $group_of_sessions->description;

	return $group_of_sessions_description;
}


/**
 * Display the Group of Sessions Dates
 * Reuse the Sensei status markup.
 */
function css_the_group_of_sessions_dates() {

	$group_of_sessions_id = css_get_the_group_of_sessions_id();

	$start_date = get_term_meta( $group_of_sessions_id, 'start_date', true );

	$end_date = get_term_meta( $group_of_sessions_id, 'end_date', true );

	if ( empty( $start_date ) &&
		empty( $end_date ) ) {

		echo '';

		return;
	}

	// Localize dates.
	$fstart_date = date_i18n( get_option( 'date_format' ), mysql2date( 'U', $start_date ) );

	$fend_date = date_i18n( get_option( 'date_format' ), mysql2date( 'U', $end_date ) );

	if ( $fstart_date && $fend_date ) {
		$fdates = sprintf(
			// Translators: %1$s is localized start date, %2$s is localized end date.
			__( '%1$s &mdash; %2$s', 'course-sessions-for-sensei' ),
			$fstart_date,
			$fend_date
		);
	} else {

		$fdates = $fstart_date ? $fstart_date : $fend_date;
	}
	?>
	<p class="status group-of-sessions-status dates">
		<?php echo $fdates; ?>
	</p>
	<?php
}
