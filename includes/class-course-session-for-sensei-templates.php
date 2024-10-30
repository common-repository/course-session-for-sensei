<?php
/**
 * Course Session For Sensei Templates
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course Session For Sensei Templates class
 */
class Course_Session_For_Sensei_Templates {

	/**
	 * Course_Session_For_Sensei_Templates constructor.
	 */
	public function __construct() {

		// Add group of sessions to the single course template.
		add_action(
			'sensei_single_course_content_inside_after',
			array( $this, 'load_course_group_of_sessions_content_template' ),
			5
		);

		// Add course sessions to the single course template.
		add_action(
			'sensei_single_course_modules_before',
			array( $this, 'load_course_sessions_content_template_no_module' ),
			15
		);

		// Add course sessions to the single course's modules template.
		add_action(
			'css_single_course_modules_outside_before',
			array( $this, 'load_course_sessions_content_template_before_module' ),
			5
		);

		// Add last course sessions after the single course's last module template.
		add_action(
			'css_single_course_modules_outside_after',
			array( $this, 'load_course_sessions_content_template_after_module' ),
			5
		);

		add_action(
			'css_single_course_module_lessons_before',
			array( $this, 'load_course_sessions_content_template_before_lesson' ),
			5
		);

		add_filter(
			'sensei_locate_template',
			array( $this, 'override_single_course_modules_template' ),
			10,
			2
		);

		// Load our templates.
		add_filter(
			'template_include',
			array( $this, 'template_loader' )
		);

		// Alter the main WP_Query.
		add_action(
			'pre_get_posts',
			array( $this, 'alter_query' )
		);

		// Add course session navigation links to Sensei pagination (Sensei footer).
		add_action(
			'sensei_pagination',
			array( $this, 'course_session_navigation_links' ),
			11
		);

		add_action(
			'sensei_pagination',
			array( $this, 'css_breadcrumb_output' ),
			80
		);

		// Add Module date to status.
		add_filter( 'sensei_the_module_status_html', array( $this, 'the_module_status_html_filter' ), 10, 3 );

		// Remove the restricted module links.
		// add_filter( 'sensei_the_module_permalink', array( $this, 'the_module_permalink_filter' ), 90, 3 );

		// Remove the restricted module navigation links.
		// add_action( 'wp_head', array( $this, 'remove_module_navigation_links' ) );

		// Filter terms: remove restricted modules.
		add_filter( 'get_object_terms', array( $this, 'get_object_terms_filter' ), 90, 4 );

		// Filter requested WP query: 404 for restricted modules and their lessons.
		add_filter( 'request', array( $this, 'request_filter' ), 90, 1 );

		// Add Future User Courses tab.
		add_action(
			'sensei_before_active_user_courses',
			array( $this, 'future_user_courses_tab' )
		);
	}


	/**
	 * Override Single Course Modules template
	 * Get the one in theme if exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Template.
	 * @param string $template_name Template name.
	 *
	 * @return string Template.
	 */
	public function override_single_course_modules_template( $template, $template_name ) {

		if ( 'single-course/modules.php' === $template_name ) {

			$template_path = 'course-session-for-sensei/';

			// Look within passed path within the theme - this is priority.
			$template_overridden = locate_template(
				array(
					$template_path . $template_name,
					$template_name,
				)
			);

			if ( ! $template_overridden ) {

				$default_path = course_session_for_sensei()->dir . '/templates/';

				$template_overridden = $default_path . $template_name;

				// return nothing for file that do not exist
				if ( ! file_exists( $template_overridden ) ) {
					$template_overridden = '';
				}
			}

			if ( $template_overridden ) {

				return $template_overridden;
			}
		}

		return $template;
	}


	/**
	 * Display the single course group of sessions content
	 * this will only show if the course has group of sessions.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $course_id Course ID.
	 */
	public function load_course_group_of_sessions_content_template( $course_id ) {

		// Only show group of sessions on the course that has group of sessions.
		if ( ! $course_id ||
			! is_singular( 'course' ) ||
			! css_the_course_has_group_of_sessions() ) {

			return;
		}

		self::get_template( 'single-course/group-of-sessions.php' );
	}


	/**
	 * Display the single course course sessions content
	 * this will only show for groups with sessions.
	 *
	 * @since 1.3.0
	 */
	public function load_course_sessions_content_template_no_module() {

		// Only show sessions for groups with sessions.
		if ( ! css_the_group_have_sessions() ) {

			return;
		}

		if ( sensei_have_modules() ) {

			// Check if modules have lessons.
			if ( sensei_module_has_lessons() ) {

				return;
			}
		}

		self::get_template( 'single-course/module-course-sessions.php' );
	}


	/**
	 * Display the single course course sessions content
	 * this will only show for groups with sessions.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $module_id Module ID.
	 */
	public function load_course_sessions_content_template_before_module( $module_id ) {

		// Only show sessions for groups with sessions.
		if ( ! $module_id ||
			! css_the_group_have_sessions() ||
			! css_is_the_course_session_next( 'module' ) ) {

			return;
		}

		self::get_template( 'single-course/module-course-sessions.php' );
	}


	/**
	 * Display the single course course sessions content
	 * this will only show for groups with sessions.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $module_id Module ID.
	 */
	public function load_course_sessions_content_template_after_module( $module_id ) {

		// Only show sessions for groups with sessions.
		if ( ! $module_id ||
			! css_the_group_have_sessions() ||
			! css_is_the_module_last() ) {

			return;
		}

		self::get_template( 'single-course/module-course-sessions.php' );
	}


	/**
	 * Display the single course course sessions content
	 * this will only show if the group has sessions.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $lesson_id Lesson ID.
	 */
	public function load_course_sessions_content_template_before_lesson( $lesson_id ) {

		// Only show sessions on the group that has sessions.
		if ( ! $lesson_id ||
			! css_the_group_have_sessions() ) {

			return;
		}

		self::get_template( 'single-course/lesson-course-sessions.php' );
	}


	/**
	 * Get template
	 *
	 * @since 1.0.0
	 *
	 * @uses Sensei_Templates::get_template()
	 *
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments.
	 */
	static function get_template( $template_name, $args = array() ) {

		// Use our plugin templates folder and name to override templates in theme.
		Sensei_Templates::get_template(
			$template_name,
			$args,
			'course-session-for-sensei/',
			course_session_for_sensei()->dir . '/templates/'
		);
	}


	/**
	 * Template loader
	 *
	 * @param string $template WordPress theme template.
	 *
	 * @return string|void Our custom (taxonomy or CPT) template if applies.
	 */
	static public function template_loader( $template ) {
		$file = '';

		if ( is_tax( CSS_TAXONOMY_SLUG ) ) {

			// Add group of sessions archive template.
			$file = 'archive-course-session.php';

			// Do not return true for is_sensei, will enable header and description display.
			// add_filter( 'is_sensei', '__return_true' );

		} elseif ( is_singular( CSS_CPT_SLUG ) ) {

			// Add single course session template.
			$file = 'single-course-session.php';

			// Return true for is_sensei, will disable post pagination.
			add_filter( 'is_sensei', '__return_true' );
		}

		// If file is present set it to be loaded otherwise continue with the initial template given by WP.
		if ( $file ) {

			$template = locate_template( $file );

			if ( ! $template ) {
				$template = self::get_template( $file );
			}
		}

		return $template;
	}


	/**
	 * Load the template files from within templates/ or the theme if overridden within the theme.
	 *
	 * @since 1.0.0
	 * @param string $slug
	 * @param string $name default: ''
	 *
	 * @return void
	 */
	public static function get_part( $slug, $name = '' ) {

		$template = '';
		$plugin_template_url = 'course-session-for-sensei/';
		$plugin_template_path = course_session_for_sensei()->dir . '/templates/';

		// Look in yourtheme/slug-name.php and yourtheme/course-session-for-sensei/slug-name.php.
		if ( $name ) {

			$template = locate_template( array( "{$slug}-{$name}.php", "{$plugin_template_url}{$slug}-{$name}.php" ) );
		}

		// Get default slug-name.php.
		if ( ! $template && $name && file_exists( $plugin_template_path . "{$slug}-{$name}.php" ) ) {

			$template = $plugin_template_path . "{$slug}-{$name}.php";
		}

		// If template file doesn't exist, look in yourtheme/slug.php
		// and yourtheme/course-session-for-sensei/slug.php.
		if ( ! $template ) {

			$template = locate_template( array( "{$slug}.php", "{$plugin_template_url}{$slug}.php" ) );
		}

		if ( $template ) {

			load_template( $template, false );
		}
	}


	/**
	 * Alter the main WP_Query.
	 *
	 * @param object $query WP_Query.
	 */
	public function alter_query( $query ) {

		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( is_tax( CSS_TAXONOMY_SLUG ) ) {
			// Course Sessions archive page: only query course sessions in group and order.
			$query->set( 'post__in', css_get_course_session_order( get_queried_object_id() ) );
			$query->set( 'orderby', 'post__in' );
		}
	}


	/**
	 * Display course session navigation links on single course session page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function course_session_navigation_links() {
		if ( ! is_singular( CSS_CPT_SLUG ) ||
			! isset( $_GET['group_id'] ) ||
			intval( $_GET['group_id'] ) < 1 ) {

			return;
		}

		$group_id = intval( $_GET['group_id'] );

		// Group ID in URL, check it has our course session.
		if ( ! has_term( $group_id, CSS_TAXONOMY_SLUG ) ) {

			return;
		}

		$queried_course_session_id = get_queried_object_id();

		$course_session_order = css_get_course_session_order( $group_id );

		$prev_course_session_id = 0;
		$next_course_session_id = 0;
		$on_current = false;

		foreach ( $course_session_order as $course_session_id ) {
			if ( $on_current ) {
				$next_course_session_id = $course_session_id;
				break;
			}

			if ( $course_session_id == $queried_course_session_id ) {
				$on_current = true;
			} else {
				$prev_course_session_id = $course_session_id;
			}
		}

		?>
		<div id="post-entries" class="post-entries course-session-navigation fix">
			<?php
			if ( $next_course_session_id ) :
				$course_session_link = add_query_arg(
					'group_id',
					$group_id,
					get_post_permalink( $next_course_session_id )
				);
				?>
				<div class="nav-next fr">
					<a href="<?php echo esc_url( $course_session_link ); ?>"
					   title="<?php esc_attr_e( 'Next course session', 'course-session-for-sensei' ); ?>">
						<?php echo get_the_title( $next_course_session_id ); ?>
						<span class="meta-nav"></span>
					</a>
				</div>
			<?php endif; ?>
			<?php
			if ( $prev_course_session_id ) :
				$course_session_link = add_query_arg(
					'group_id',
					$group_id,
					get_post_permalink( $prev_course_session_id )
				);
				?>
				<div class="nav-prev fl">
					<a href="<?php echo esc_url( $course_session_link ); ?>"
					   title="<?php esc_attr_e( 'Previous course session', 'course-session-for-sensei' ); ?>">
						<span class="meta-nav"></span>
						<?php echo get_the_title( $prev_course_session_id ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}


	/**
	 * Outputs the breadcrumb trail on course session pages
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function css_breadcrumb_output() {

		// Only output on course session and taxonomy (group) pages.
		if ( ! is_tax( CSS_TAXONOMY_SLUG ) &&
			! is_singular( CSS_CPT_SLUG ) ) {
			return;
		}

		$breadcrumb_prefix = __( 'Back to: ', 'woothemes-sensei' );

		$separator = apply_filters( 'css_breadcrumb_separator', '&gt;' );

		$html = '<section class="css-breadcrumb sensei-breadcrumb">' . $breadcrumb_prefix;

		$course_id = css_get_the_group_of_sessions_course_id();

		if ( ! $course_id ) {
			return;
		}

		// Back to course.
		$html .= '<a href="' . esc_url( get_permalink( $course_id ) ) . '"
			title="' . __( 'Back to the course', 'course-session-for-sensei' ) . '">' .
			get_the_title( $course_id ) .
			'</a>';

		// Back to group.
		if ( is_singular( CSS_CPT_SLUG ) &&
			css_get_the_course_session_group_id() ) {

			$html .= ' ' . $separator .
				' <a href="' . esc_url( css_get_the_group_of_sessions_permalink() ) . '"
				title="' . __( 'Back to the group of sessions', 'course-session-for-sensei' ) . '">' .
				css_get_the_group_of_sessions_title() .
				'</a>';
		}

		// Allow other plugins to filter html.
		$html = apply_filters( 'css_breadcrumb_output', $html, $separator );
		$html .= '</section>';

		echo $html;
	}

	/**
	 * Filter the module status HTML
	 * Add Module Date.
	 *
	 * @param string $module_status_html Module status HTML.
	 * @param int    $module_term_id     Module term Id.
	 * @param int    $course_id          Course ID.
	 *
	 * @return string
	 */
	public function the_module_status_html_filter( $module_status_html, $module_term_id, $course_id ) {

		$date = css_get_the_module_date();

		if ( empty( $date ) ) {

			return $module_status_html;
		}

		$fdate = date_i18n( get_option( 'date_format' ), mysql2date( 'U', $date ) );

		ob_start();
		?>
		<span class="date">
			&mdash;
			<?php echo $fdate; ?>
		</span>
		<?php

		$fdate_html = ob_get_clean();

		// Insert after first occurrence of </p>.
		$module_status_html_with_date = substr_replace(
			$module_status_html,
			$fdate_html,
			strpos( $module_status_html, '</p>' ),
			0
		);

		return $module_status_html_with_date;
	}


	/**
	 * Filter the module permalink.
	 * No link when module access is restricted.
	 *
	 * @param string $module_url     Module URL.
	 * @param int    $module_term_id Module term ID.
	 * @param int    $course_id      Course ID.
	 *
	 * @deprecated See single-course/modules.php custom template.
	 *
	 * @return string
	 */
	public function the_module_permalink_filter( $module_url, $module_term_id  ,$course_id ) {

		if ( css_is_the_module_access_restricted() ) {

			// No link when module access is restricted.
			return '';
		}

		return $module_url;
	}


	/**
	 * Remove module navigation links
	 * On module archive page.
	 *
	 * there is no other alternative as for now
	 * the pagination function does not allow for filtering.
	 *
	 * @deprecated See get_object_terms_filter below.
	 */
	public function remove_module_navigation_links() {

		remove_action( 'sensei_pagination', array( Sensei()->modules, 'module_navigation_links'), 11 );
	}


	/**
	 * Get object terms filter
	 * Remove restricted access modules,
	 * unless we are on single course template modules loop.
	 *
	 * @since WordPress 4.9!
	 *
	 * @param array $terms      Terms.
	 * @param array $object_ids Object IDs.
	 * @param array $taxonomies Taxonomies.
	 * @param array $args       Query arguments.
	 *
	 * @return array Filtered object terms.
	 */
	public function get_object_terms_filter( $terms, $object_ids, $taxonomies, $args ) {

		global $css_restricted_module_terms_filter_disabled;

		if ( ! empty( $css_restricted_module_terms_filter_disabled ) ) {

			// Temporarily disable restricted module terms filter.
			// Re-enable.
			$css_restricted_module_terms_filter_disabled = false;

			return $terms;
		}

		if ( is_admin() ||
			! in_array( 'module', $taxonomies ) ) {

			return $terms;
		}

		if ( sensei_all_access() ||
			user_can( get_current_user_id(), 'edit_courses' ) ) {

			// User has Sensei all access or can edit courses.
			return $terms;
		}

		if ( doing_action( 'sensei_single_course_modules_before' ) ||
			doing_action( 'sensei_single_course_lessons_before' ) ||
			doing_filter( 'request' ) ) {

			// We need to keep restricted modules on single course template modules loop and on request vars filter.
			// @see Sensei_Core_Modules::setup_single_course_module_loop()
			return $terms;
		}

		$filtered_terms = array();

		// Check if any of the modules in the course is restricted.
		foreach	( (array) $terms as $term ) {

			if ( is_int( $term ) ) {

				$term_id = $term;
			} else {

				if ( 'module' !== $term->taxonomy ) {

					$filtered_terms[] = $term;

					continue;
				}

				$term_id = $term->term_id;
			}

			foreach ( (array) $object_ids as $course_id ) {
				if ( css_is_module_access_restricted( $course_id, $term_id ) ) {

					if ( 1 === count( $object_ids ) ||
						isset( $term->object_id ) &&
						$term->object_id === $course_id ) {

						// Object ID is the course ID
						// and module access is restricted.
						continue 2;
					}
				}
			}

			$filtered_terms[] = $term;
		}

		// var_dump($filtered_terms);

		return $filtered_terms;
	}


	/**
	 * Filter Requested WP Query variables
	 * Send back 404 if requested page is a restricted module
	 * or a lesson belonging to a restricted module.
	 *
	 * @see wp-query.php
	 *
	 * @param array $query_vars Requested WP Query variables.
	 *
	 * @return array Filtered Requested WP Query variables.
	 */
	public function request_filter( $query_vars ) {

		if ( empty( $query_vars['module'] ) &&
			empty( $query_vars['lesson'] ) ) {

			// Not on a module or lesson page.
			return $query_vars;
		}

		if ( sensei_all_access() ||
			user_can( get_current_user_id(), 'edit_courses' ) ) {

			// User has Sensei all access or can edit courses.
			return $query_vars;
		}

		if ( ! empty( $query_vars['module'] ) ) {

			if ( ! isset( $_REQUEST['course_id'] ) ) {

				// Not our concern anymore.
				return $query_vars;
			}

			$term = get_term_by( 'slug', $query_vars['module'], 'module' );

			if ( ! $term ||
				is_wp_error( $term ) ) {

				// Not our concern anymore.
				return $query_vars;
			}

			$course_id = absint( $_REQUEST['course_id'] );

			$module_term_id = $term->term_id;
		} elseif ( ! empty( $query_vars['lesson'] ) ) {

			$lesson = get_page_by_path( $query_vars['lesson'], OBJECT, 'lesson' );

			if ( empty( $lesson ) ) {

				// Not our concern anymore.
				return $query_vars;
			}

			$course_id = Sensei()->lesson->get_course_id( $lesson->ID );

			$module_term = Sensei()->modules->get_lesson_module( $lesson->ID );

			$module_term_id = $module_term->term_id;
		}

		$restricted = css_is_module_access_restricted( $course_id, $module_term_id );

		if ( $restricted &&
			empty( $query_vars['error'] ) ) {

			// Module access is restricted, send 404 error.
			$query_vars['error'] = '404';
		}

		return $query_vars;
	}


	/**
	 * Adds Future courses tab
	 * to the My Courses page.
	 *
	 * Moves future course (based on group of sessions' start date)
	 * using jQuery and existing jQuery UI tabs.
	 *
	 * @since 1.1.0
	 *
	 * @see Sensei_Course::load_user_courses_content()
	 */
	public function future_user_courses_tab() {

		$user_id = get_current_user_id();

		$course_statuses = Sensei_Utils::sensei_check_for_activity(
			array(
				'user_id' => $user_id,
				'type' => 'sensei_course_status',
			),
			true
		);

		// User may only be on 1 Course.
		if ( ! is_array( $course_statuses ) ) {
			$course_statuses = array( $course_statuses );
		}

		$future_classes = array();

		foreach( $course_statuses as $course_status ) {
			if ( ! Sensei_Utils::user_completed_course( $course_status, $user_id ) ) {
				$active_id = $course_status->comment_post_ID;

				// Determine if course is future:
				// Has session?
				$group_of_sessions = css_get_course_group_of_sessions( $active_id );

				if ( $group_of_sessions ) {

					$start_date = get_term_meta( $group_of_sessions->term_id, 'start_date', true );

					if ( $start_date &&
						$start_date > date( 'Y-m-d' ) ) {

						$future_classes[] = '.post-' . $active_id;
					}
				}
			}
		}

		?>
		<script>
		jQuery(document).ready(function() {
		  // Add tab.
          jQuery("div#my-courses ul").prepend( "<li><a href='#future-courses'>" +
			  <?php echo json_encode( __( 'Future courses', 'course-session-for-sensei' ) ); ?> + "</a></li>" );

          <?php if ( $future_classes ) : ?>
			  // Move future courses in right tab.
			  jQuery(<?php echo json_encode( implode( ',', $future_classes ) ); ?>).appendTo('#future-courses');
		  <?php endif; ?>
		});
		</script>
		<div id="future-courses">
		</div>
		<?php
	}
}
