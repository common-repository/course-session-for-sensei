<?php
/**
 * Course Session For Sensei Order
 * Where we order sessions inside a group and its course.
 *
 * Inspired by the Sensei LMS course modules ordering functionality.
 * @see class-sensei-modules.php
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course Session For Sensei Order class
 */
class Course_Session_For_Sensei_Order {

	/**
	 * Order page slug.
	 *
	 * @var     string
	 * @access  private
	 * @since   1.0.0
	 */
	private $page_slug;

	/**
	 * Constructor function.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct( $page_slug ) {

		$this->page_slug = $page_slug;

		if ( is_admin() ) {
			// Handle ordering.
			add_action( 'admin_menu', array( $this, 'register_admin_menu_items' ), 30 );

			// Admin styling
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );

			add_filter(
				'sensei_module_admin_script_page_white_lists',
				array( $this, 'admin_enqueue_sensei_modules_scripts' )
			);

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 25 , 2 );
		}
	}


	/**
	 * Register admin screen for ordering
	 *
	 * @since 1.0.0
	 * @copyright WooCommerce
	 * @see class-sensei-modules.php
	 *
	 * @return void
	 */
	public function register_admin_menu_items() {
		// Register new admin page for ordering.
		add_submenu_page(
			'edit.php?post_type=' . CSS_CPT_SLUG,
			__( 'Session Order', 'course-session-for-sensei' ),
			__( 'Session Order', 'course-session-for-sensei' ),
			'edit_lessons',
			$this->page_slug,
			array( $this, 'screen' )
		);
	}


	/**
	 * Display Order screen
	 *
	 * @since 1.0.0
	 * @copyright WooCommerce
	 * @see class-sensei-modules.php
	 *
	 * @return void
	 */
	public function screen() {
		?>
		<div id="<?php echo esc_attr( $this->page_slug ); ?>"
			 class="wrap <?php echo esc_attr( $this->page_slug ); ?>">
		<h2>
			<?php _e( 'Order Sessions', 'course-session-for-sensei' ); ?>
		</h2>
		<?php

		$html = '';

		if ( isset( $_POST[ $this->page_slug ] ) &&
			0 < strlen( $_POST[ $this->page_slug ] ) ) {

			$ordered = $this->save_course_session_order(
				esc_attr( $_POST[ $this->page_slug ] ),
				esc_attr( $_POST['group_id'] )
			);

			if ( $ordered ) {
				$html .= '<div class="updated fade">' . "\n";
				$html .= '<p>' . __(
					'The session order has been saved for this group.',
					'course-session-for-sensei'
				) . '</p>' . "\n";
				$html .= '</div>' . "\n";
			}
		}

		$groups = $this->get_groups();

		$html .= '<form action="' . admin_url( 'edit.php' ) . '" method="get">' . "\n";

		$html .= '<input type="hidden" name="post_type"
			value="' . CSS_CPT_SLUG . '" />' . "\n";

		$html .= '<input type="hidden" name="page" value="' . esc_attr( $this->page_slug ) . '" />' . "\n";

		$html .= '<select id="session-order-group" name="group_id">' . "\n";

		$html .= '<option value="">' . __( 'Select a group of sessions', 'course-session-for-sensei' ) .
			'</option>' . "\n";

		$group_id = 0;

		if ( isset( $_GET['group_id'] ) &&
			intval( $_GET['group_id'] ) > 0 ) {
			$group_id = intval( $_GET['group_id'] );
		}

		foreach ( $groups as $group ) {
			// var_dump( $group );
			$html .= '<option value="' . esc_attr( intval( $group->term_id ) ) . '" ' .
				selected( $group->term_id, $group_id, false ) . '>' .
				$group->name . '</option>' . "\n";
		}

		$html .= '</select>' . "\n";

		$html .= '<input type="submit" class="button-primary session-order-select-group-submit"
			value="' . __( 'Select', 'course-session-for-sensei' ) . '" />' . "\n";

		$html .= '</form>' . "\n";

		$html .= $this->get_group_sessions_screen( $group_id );

		echo $html;

		?>
		</div>
		<?php
	}


	/**
	 * Group of sessions screen.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $group_id Group of sessions ID.
	 *
	 * @return string HTML for Group sessions screen.
	 */
	public function get_group_sessions_screen( $group_id ) {

		if ( ! $group_id ) {

			return '';
		}

		$course_items = $this->get_group_course_items( $group_id );
		// $course_items = $this->append_teacher_name_to_session( $course_items, array( 'session' ), array() );

		// var_dump( $course_items );

		if ( ! $course_items ) {

			return '';
		}

		$order = $this->get_course_session_order( $group_id );

		$order_string = '';

		if ( $order ) {
			$order_string = implode( ',', $order );
		}

		$html = '';

		// Course title.
		$course_id = get_term_meta( $group_id, 'course', true );

		$course = get_post( $course_id );

		$html .= '<h3>' . esc_html( $course->post_title ) . '</h3>';

		$html .= '<form id="editgrouping" method="post" action="" class="validate">' . "\n";
		$html .= '<ul class="sortable-' . CSS_CPT_SLUG . '-list">' . "\n";
		$count = 0;

		foreach ( $course_items as $key => $course_item ) {
			// var_dump( $course_item->ID ); echo '<br />';

			$count++;
			$class = '';

			if ( 1 == $count ) {
				$class .= ' first';
			}

			if ( count( $course_items ) == $count ) {
				$class .= ' last';
			}

			if ( 0 != $count % 2 ) {
				$class .= ' alternate';
			}

			$is_term = false;

			if ( 0 === strpos( $course_item->ID, 'lesson' ) ) {
				/**
				 * jQuery UI sortable
				 *
				 * @link https://jqueryui.com/sortable/#items
				 */
				$class .= ' ui-state-disabled lesson';
			} elseif ( 0 === strpos( $course_item->ID, 'module' ) ) {
				$is_term = true;

				/**
				 * jQuery UI sortable
				 *
				 * @link https://jqueryui.com/sortable/#items
				 */
				$class .= ' ui-state-disabled module';
			} else {
				$class .= ' ' . CSS_CPT_SLUG;

				// Before lesson?
				for ( $i = $key, $max = count( $course_items ); $i < $max; $i++ ) {

					if ( 0 === strpos( $course_items[ $i ]->ID, 'lesson' ) ) {

						$class .= ' before-lesson';

						break;
					} elseif ( 0 === strpos( $course_items[ $i ]->ID, 'module' ) ) {

						break;
					}
				}
			}

			$html .= '<li class="' . esc_attr( $class ) . '">
				<span rel="' . esc_attr( $course_item->ID ) . '" style="width: 100%;"> ' .
				( $is_term ? $course_item->name : $course_item->post_title ) .
				'</span></li>' . "\n";
		}

		$html .= '</ul>' . "\n";

		$html .= '<input type="hidden" name="' . CSS_CPT_SLUG . '-order"
			value="' . $order_string . '" />' . "\n";

		$html .= '<input type="hidden" name="group_id" value="' . $group_id . '" />' . "\n";

		$html .= '<input type="submit" class="button-primary"
				value="' . __( 'Save session order', 'course-session-for-sensei' ) . '" />' . "\n";

		$html .= '<a href="' . admin_url(
			'term.php?taxonomy=' . CSS_TAXONOMY_SLUG .
			'&tag_ID=' . $group_id . '&action=edit'
		) . '" class="button-secondary">' .
		__( 'Edit group of sessions', 'course-session-for-sensei' ) . '</a>' . "\n";

		return $html;
	}


	/**
	 * Save session order for group
	 *
	 * @since 1.0.0
	 * @copyright WooCommerce
	 * @see class-sensei-modules.php
	 *
	 * @todo Resave trigger when updating course lessons or modules to keep consistency!
	 *
	 * @param  string  $order_string Comma-separated string of session IDs.
	 * @param  integer $group_id    ID of group of sessions.
	 * @return boolean              True on success, false on failure.
	 */
	public function save_course_session_order( $order_string = '', $group_id = 0 ) {
		if ( $order_string &&
			$group_id ) {

			$order = explode( ',', $order_string );

			// Use the module or lesson after as course session order key.
			$order_formatted = array();

			for ( $i = 0, $max = count( $order ); $i < $max; $i++ ) {
				if ( ! is_numeric( $order[ $i ] ) ) {

					// Skip item, is module or lesson.
					continue;
				}

				if ( isset( $order[ $i + 1 ] ) ) {

					// Item is course session, use next item as order key.
					$order_key = $order[ $i + 1 ];
				} else {

					// Course session is last, use 999999 as key.
					$order_key = 999999;
				}

				$order_formatted[ $order_key ] = $order[ $i ];
			}

			update_term_meta( intval( $group_id ), '_session_order', $order_formatted );

			return true;
		}

		return false;
	}


	/**
	 * Get groups of sessions
	 *
	 * @return array Ordered array of group of sessions term objects.
	 */
	public function get_groups() {
		$groups_args = array(
			'taxonomy' => CSS_TAXONOMY_SLUG,
		);

		$groups = get_terms( $groups_args );

		if ( ! empty( $groups ) &&
			! is_wp_error( $groups ) ) {
			return $groups;
		} else {

			return array();
		}
	}


	/**
	 * Get session order for group
	 *
	 * @since 1.0.0
	 * @copyright WooCommerce
	 * @see class-sensei-modules.php
	 *
	 * @param  integer $group_id Group of sessions ID.
	 * @return mixed             Session order on success, false if no session order has been saved.
	 */
	static public function get_course_session_order( $group_id = 0 ) {

		static $orders = array();

		if ( $group_id ) {

			if ( isset( $orders[ $group_id ] ) ) {

				return $orders[ $group_id ];
			}

			$orders[ $group_id ] = get_term_meta( intval( $group_id ), '_session_order', true );

			return $orders[ $group_id ];
		}

		return false;
	}


	/**
	 * Is Course Session before Module or Lesson (in Order)?
	 *
	 * @static
	 *
	 * @since 1.2.2 Fix Unordered Course Session display.
	 *
	 * @param int    $group_id            Group of Sessions ID.
	 * @param int    $course_session_id   Course Session ID.
	 * @param int    $module_or_lesson_id Module or Lesson ID.
	 * @param string $type                Type: 'module' or 'lesson'.
	 *
	 * @return bool
	 */
	static public function is_course_session_before_module_or_lesson( $group_id, $course_session_id, $module_or_lesson_id, $type ) {

		static $course_session_done = array();

		if ( ! $group_id ||
			! $course_session_id ||
			! $module_or_lesson_id ||
			! in_array( $type, array( 'lesson', 'module' ) ) ) {

			return false;
		}

		$order = self::get_course_session_order( $group_id );

		if ( ! $order ) {

			return false;
		}

		$module_or_lesson_key = $type . $module_or_lesson_id;

		$order_key = array_search( $course_session_id, $order );

		while ( is_numeric( $order_key ) && $order_key > 0 ) {

			if ( 999999 === $order_key ) {

				// Session is last item.
				return false;
			}

			// We have another course session next.
			$course_session_id = $order_key;

			$order_key = array_search( $course_session_id, $order );
		}

		if ( false === $order_key ||
			( ! empty( $course_session_done[ $course_session_id ] ) || $order_key !== 0 ) &&
			$order_key !== $module_or_lesson_key ) {

			return false;
		}

		$course_session_done[ $course_session_id ] = true;

		return true;
	}


	/**
	 * Is Course Session last (in Order)?
	 * Can have multiple course sessions stacked after last module.
	 * Last course session has 999999 as order key.
	 *
	 * @static
	 *
	 * @param int    $group_id            Group of Sessions ID.
	 * @param int    $course_session_id   Course Session ID.
	 *
	 * @return bool
	 */
	static public function is_course_session_last( $group_id, $course_session_id ) {

		if ( ! $group_id ||
			! $course_session_id ) {

			return false;
		}

		$order = self::get_course_session_order( $group_id );

		if ( ! $order ) {

			return false;
		}

		$order_key = array_search( $course_session_id, $order );

		while ( is_numeric( $order_key ) ) {

			if ( 999999 === $order_key ) {

				// Session is last item.
				return true;
			}

			// We have another course session next.
			$course_session_id = $order_key;

			$order_key = array_search( $course_session_id, $order );
		}

		return false;
	}


	/**
	 * Get ordered array of all course items (sessions, modules & lessons) in a group
	 *
	 * @since 1.0.0
	 * @copyright WooCommerce
	 * @see class-sensei-modules.php
	 *
	 * @uses Sensei()->modules->get_course_modules()
	 *
	 * @param  integer $group_id ID of a group of sessions.
	 * @return array             Ordered array of course items objects.
	 */
	public function get_group_course_items( $group_id = 0 ) {

		$group_id = intval( $group_id );

		if ( empty( $group_id ) ) {
			return array();
		}

		$course_id = get_term_meta( $group_id, 'course', true );

		$modules = Sensei()->modules->get_course_modules( $course_id );

		$course_items = array();

		// Append modules to course items.
		if ( count( $modules ) > 0 ) {

			foreach ( $modules as $module ) {
				$module_items = array();

				$module_id = $module->term_id;

				// Add ID to module, using the 'module' prefix.
				$module->ID = 'module' . $module_id;

				$module_items[] = $module;

				$lessons = Sensei()->modules->get_lessons( $course_id, $module_id );

				$lesson_items = array();

				// Append lessons to course items.
				if ( count( $lessons ) > 0 ) {

					foreach ( $lessons as $lesson ) {
						// Add 'lesson' prefix to ID.
						$lesson->ID = 'lesson' . $lesson->ID;

						// Add module ID.
						$lesson->module_id = $module_id;

						$lesson_items[] = $lesson;
					}
				}

				$course_items = array_merge( $course_items, $module_items, $lesson_items );
			}

			// var_dump( $course_items );
		}

		$sessions = $this->get_group_sessions( $group_id );

		return $this->get_ordered_course_items( $group_id, $sessions, $course_items );
	}


	/**
	 * Get Sessions for Group
	 *
	 * @param int $group_id Group of Sessions ID.
	 *
	 * @return array Sessions.
	 */
	public function get_group_sessions( $group_id ) {
		// Get sessions for group.
		$session_args = array(
			'posts_per_page'   => -1,
			'orderby'          => 'date',
			'order'            => 'ASC',
			'post_type'        => CSS_CPT_SLUG,
			'post_status'      => 'publish',
			'tax_query' => array(
				array(
					'taxonomy' => CSS_TAXONOMY_SLUG,
					'field'    => 'term_id',
					'terms'    => absint( $group_id ),
				),
			),
			// 'suppress_filters' => true,
		);

		$sessions = get_posts( $session_args );

		return $sessions;
	}


	/**
	 * Get ordered course items (sessions, modules and eventually lessons)
	 *
	 * @param int   $group_id     Group of Sessions ID.
	 * @param array $sessions     Sessions.
	 * @param array $course_items Course Items.
	 *
	 * @return array
	 */
	public function get_ordered_course_items( $group_id, $sessions, $course_items ) {

		// Get custom session order for course.
		$order = $this->get_course_session_order( $group_id );

		if ( ! $order ) {
			return array_merge( $sessions, $course_items );
		}

		// Sort by custom order.
		$unordered_sessions = array();
		$sessions_add_tmp = array();
		$sessions_add_tmp_old = array();

		$ordered_course_items = $course_items;

		while ( ! empty( $sessions ) ) {
			foreach ( $sessions as $session ) {

				$next_item_key = array_search( $session->ID, $order );

				if ( false === $next_item_key ) {

					$unordered_sessions[] = $session;

					continue;
				}

				// Course Session before Lesson or Module, find it!
				$ordered_course_items_ids = array_map(
					function ( $ordered_course_item ) {
						return $ordered_course_item->ID;
					},
					$ordered_course_items
				);

				$item_status = get_post_status( $next_item_key );

				if ( is_numeric( $next_item_key ) &&
					( ! $item_status || 'trash' === $item_status ) &&
					array_search( $next_item_key, $order ) ) {

					// Order, remove Session fix: Jump non existing Session.
					$next_item_key = array_search( $next_item_key, $order );
				}

				if ( 999999 === $next_item_key ) {
					// Last session.
					$order_key = 999999;
				} else {
					$order_key = array_search( $next_item_key, $ordered_course_items_ids );

					if ( false === $order_key ) {

						// Move session to tmp / remaining array to retry on next loop.
						$sessions_add_tmp[ $next_item_key ] = $session;

						continue;
					}
				}

				// Add session to course items.
				$ordered_course_items = array_merge(
					array_slice(
						$ordered_course_items,
						0,
						$order_key,
						true
					),
					array( $session ),
					array_slice(
						$ordered_course_items,
						$order_key,
						count( $ordered_course_items ),
						true
					)
				);
			}

			if ( $sessions_add_tmp_old === $sessions_add_tmp ) {

				// Nothing has changed, add remaining sessions to unordered array and break.
				$unordered_sessions = array_merge( $unordered_sessions, $sessions_add_tmp );

				break;
			}

			// Start again with remaining sessions.
			$sessions = $sessions_add_tmp;

			// To check if nothing has changed after next loop.
			$sessions_add_tmp_old = $sessions_add_tmp;

			$sessions_add_tmp = array();
		}

		// Prepend sessions that have not yet been ordered.
		if ( count( $unordered_sessions ) > 0 ) {

			$ordered_course_items = array_merge( $unordered_sessions, $ordered_course_items );
		}

		return $ordered_course_items;
	}


	/**
	 * Add the teacher name next to sessions. Only works in Admin for Admin users.
	 * This will not add name to terms belonging to admin user.
	 *
	 * Hooked into 'get_terms'
	 *
	 * @copyright WooCommerce
	 * @see class-sensei-modules.php
	 *
	 * @param $terms
	 * @param $taxonomies
	 * @param $args
	 *
	 * @return array
	 */
	public function append_teacher_name_to_session( $terms, $taxonomies, $args ) {

		// Only for admin users on the session taxonomy.
		if ( empty( $terms ) ||
			! current_user_can( 'manage_options' ) ||
			! in_array( CSS_CPT_SLUG, $taxonomies ) ||
			! is_admin() ) {

			return $terms;
		}

		// In certain cases the array is passed in as reference to the parent term_id => parent_id
		// In other cases we explicitly require ids (as in 'tt_ids' or 'ids')
		// simply return this as wp doesn't need an array of stdObject Term.
		if ( isset( $args['fields'] ) &&
			in_array( $args['fields'], array( 'id=>parent', 'tt_ids', 'ids' ) ) ) {

			return $terms;
		}

		$users_terms = array();

		// Loop through and update all terms adding the author name.
		foreach ( $terms as $index => $term ) {

			if ( is_numeric( $term ) ) {
				// The term id was given, get the term object.
				$term = get_term( $term, 'module' );
			}

			$author = Sensei_Core_Modules::get_term_author( $term->slug );

			if ( ! user_can( $author, 'manage_options' ) &&
				isset( $term->name ) ) {

				$term->name = $term->name . ' (' . $author->display_name . ') ';
			}

			// Add the term to the teachers terms.
			$users_terms[] = $term;

		}

		return $users_terms;
	}


	/**
	 * Load admin CSS
	 *
	 * @since 1.0.0
	 *
	 * @copyright WooCommerce
	 * @see class-sensei-modules.php
	 *
	 * @return void
	 */
	public function admin_enqueue_styles() {

		wp_register_style(
			CSS_CPT_SLUG . '-sortable',
			course_session_for_sensei()->assets_url . 'css/admin.css',
			'',
			course_session_for_sensei()->_version
		);

		wp_enqueue_style( CSS_CPT_SLUG . '-sortable' );
	}


	/**
	 * Load Sensei modules admin Javascript
	 * Add Order Sessions page to white list so order scripts are loaded.
	 *
	 * @since 1.0.0
	 *
	 * @see class-sensei-modules.php
	 *
	 * @return array
	 */
	public function admin_enqueue_sensei_modules_scripts( $script_on_pages_white_list ) {

		$script_on_pages_white_list[] = 'course-session_page_' . $this->page_slug;

		return $script_on_pages_white_list;
	}


	/**
	 * Load admin Javascript
	 *
	 * @since 1.0.0
	 *
	 * @copyright WooCommerce
	 * @see class-sensei-modules.php
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {

		/**
		 * Filter the page hooks where modules admin script can be loaded on.
		 *
		 * @param array $white_listed_pages
		 */
		$script_on_pages_white_list = array(
			// 'edit-tags.php',
			'course-session_page_' . $this->page_slug,
			// 'post-new.php',
			// 'post.php',
			'term.php',
		);

		if ( ! in_array( $hook, $script_on_pages_white_list ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script(
			CSS_CPT_SLUG . '-admin',
			esc_url( course_session_for_sensei()->assets_url ) . 'js/admin-order' . $suffix . '.js',
			array( 'jquery', 'sensei-chosen', 'sensei-chosen-ajax', 'jquery-ui-sortable', 'sensei-core-select2' ),
			course_session_for_sensei()->_version,
			true
		);

		wp_dequeue_script( 'module-admin' );

		// Localized session data.
		$localize_course_sessions_admin = array(
			'search_courses_nonce' => wp_create_nonce( 'search-courses' ),
			'selectPlaceholder'    => __( 'Search for courses', 'woothemes-sensei' ),
		);

		wp_localize_script(
			CSS_CPT_SLUG . '-admin',
			'courseSessionsAdmin',
			$localize_course_sessions_admin
		);
	}
}

