<?php
/**
 * Course Session For Sensei Controller
 * Where we really
 * - register our taxonomy
 * - do the installation (Add default Course Sessions to database)
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course Session For Sensei Controller class
 */
class Course_Session_For_Sensei_Controller extends Course_Session_For_Sensei {

	/**
	 * The single instance of Course_Session_For_Sensei_Controller.
	 *
	 * @var    object
	 * @access private
	 * @since  1.0.0
	 */
	private static $_instance = null;

	/**
	 * Course Session CPT slug.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $cpt_slug = 'course-session';

	/**
	 * Course Session Order page slug.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $order_page_slug = 'course-session-order';

	/**
	 * Group of Sessions taxonomy slug.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $taxonomy_slug = 'group-of-sessions';

	/**
	 * Session Order page class object.
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $order;

	/**
	 * Group of sessions course term meta class object.
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $group_of_sessions_course;


	/**
	 * Constructor function.
	 *
	 * @link https://wordpress.stackexchange.com/questions/20043/inserting-taxonomy-terms-during-a-plugin-activation
	 *
	 * @access  public
	 * @since   1.0.0
	 *
	 * @param string $file    File pathname.
	 * @param string $version Version number.
	 * @return  void
	 */
	public function __construct( $file = '', $version = '1.0.0' ) {
		parent::__construct( $file, $version );

		register_activation_hook( $this->file, array( $this, 'installation' ) );

		// @link https://codex.wordpress.org/Function_Reference/register_activation_hook#Process_Flow
		if ( is_admin() &&
			get_option( 'CSS_Sensei_Plugin_Not_Activated' ) ) {

			delete_option( 'CSS_Sensei_Plugin_Not_Activated' );

			// Display warning to user: should activate Sensei plugin.
			add_action( 'admin_notices', array( $this, 'no_sensei_admin_notice__warning' ) );
		}

		if ( ! in_array(
			'course-session-for-sensei/course-session-for-sensei.php',
			apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
		) ) {
			// Sensei LMS plugin not activated, die.
			return;
		}

		$this->create_cpt();

		$this->create_taxonomy();

		$this->create_term_meta();

		/**
		 * Instantiate the Order class
		 */
		$this->order = new Course_Session_For_Sensei_Order( $this->order_page_slug );

		if ( ! is_admin() ) {
			/**
			 * Instantiate the Templates class
			 */
			$this->templates = new Course_Session_For_Sensei_Templates();

			/**
			 * Is Sensei returns true when is CSS
			 */
			add_filter( 'is_sensei', array( $this, 'is_sensei_filter' ), 10, 1 );
		} else {
			/**
			 * Instantiate the Admin class
			 */
			$this->admin = new Course_Session_For_Sensei_Admin();

			/**
			 * Instantiate the Course Session Field class
			 */
			$this->cpt_date_field = new Course_Session_For_Sensei_Course_Session_Field( 'css_date' );
		}
	}


	/**
	 * Main Course_Session_For_Sensei_Controller Instance
	 *
	 * Ensures only one instance of Course_Session_For_Sensei_Controller is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see course_session_for_sensei()
	 *
	 * @param string $file    File pathname.
	 * @param string $version Version number.
	 * @return Course_Session_For_Sensei_Controller instance
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	}


	/**
	 * Create our Course Sessions CPT.
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_post_type/
	 * @access  public
	 * @since 1.0.0
	 *
	 * @return object Post Type class object
	 */
	public function create_cpt() {

		// Register our post type.
		$cpt = parent::register_post_type(
			$this->cpt_slug,
			__( 'Course Sessions', 'course-session-for-sensei' ),
			__( 'Course Session', 'course-session-for-sensei' ),
			__(
				'Manage Session content and date for your classroom teaching or on-site training. Group them and link them to Sensei LMS courses. Finally, select where sessions will appear on the course page, in between modules or lessons.',
            	'course-session-for-sensei'
            ),
			array(
				'menu_position' => 52, // Sensei Courses menu is position 51.
				'menu_icon' => 'dashicons-calendar',
			)
		);

		// TODO: 'menu_position', 'show_in_nav_menus'?, 'rewrite' => 'slug'

		// Add slug to constant for easy access.
		define( 'CSS_CPT_SLUG', $this->cpt_slug );

		return $cpt;
	}

	/**
	 * Create our Group of Sessions taxonomy.
	 *
	 * @access  public
	 * @since 1.0.0
	 *
	 * @return object Taxonomy class object
	 */
	public function create_taxonomy() {

		// Register our taxonomy.
		$taxonomy = parent::register_taxonomy(
			$this->taxonomy_slug,
			__( 'Groups of Sessions', 'course-session-for-sensei' ),
			__( 'Group of Sessions', 'course-session-for-sensei' ),
			$this->cpt_slug,
			array(
				'hierarchical' => false,
			)
		);

		// Add slug to constant for easy access.
		define( 'CSS_TAXONOMY_SLUG', $this->taxonomy_slug );

		return $taxonomy;
	}

	/**
	 * Create term meta.
	 *
	 * @since 1.0.0
	 */
	public function create_term_meta() {
		// Load after plugins.
		add_action( 'plugins_loaded', array( $this, 'term_meta_load' ) );

		add_action( 'init', array( $this, 'term_meta_init' ), 88 );
	}


	/**
	 * Include the required files & dependencies
	 *
	 * @since 1.0.0
	 */
	public function term_meta_load() {

		// Classes.
		require_once $this->dir . '/includes/lib/class-wp-term-meta-ui.php';
		require_once $this->dir . '/includes/class-course-session-for-sensei-group-of-sessions-course.php';
		require_once $this->dir . '/includes/class-course-session-for-sensei-group-of-sessions-start-date.php';
		require_once $this->dir . '/includes/class-course-session-for-sensei-group-of-sessions-end-date.php';
		require_once $this->dir . '/includes/class-course-session-for-sensei-module-date.php';
		require_once $this->dir . '/includes/class-course-session-for-sensei-module-access.php';
	}


	/**
	 * Instantiate the main class
	 *
	 * @since 1.0.0
	 */
	public function term_meta_init() {
		$this->group_of_sessions_start_date = new Course_Session_For_Sensei_Group_Of_Sessions_Start_Date( $this->file );
		$this->group_of_sessions_end_date = new Course_Session_For_Sensei_Group_Of_Sessions_End_Date( $this->file );
		$this->group_of_sessions_course = new Course_Session_For_Sensei_Group_Of_Sessions_Course( $this->file );

		$this->module_date_access_init();
	}


	/**
	 * Instantiate the Module Date term meta class
	 * For each module's courses
	 * And the Module Access term meta class.
	 *
	 * @see Core_Modules::edit_module_fields()
	 */
	public function module_date_access_init() {

		if ( ! is_admin() ||
			! isset( $_REQUEST['taxonomy'] ) ||
			'module' !== $_REQUEST['taxonomy'] ||
			! isset( $_REQUEST['tag_ID'] ) ||
			empty( $_REQUEST['tag_ID'] ) ) {

			return;
		}

		// We are on a module edit page.
		// Get module courses.
		// @see Core_Modules::edit_module_fields()
		$module_id = $_REQUEST['tag_ID'];

		// Get module's existing courses.
		$args = array(
			'post_type' => 'course',
			'post_status' => array( 'publish', 'draft', 'future', 'private' ),
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'module',
					'field' => 'id',
					'terms' => $module_id,
				),
			),
		);

		$courses = get_posts( $args );

		// Build the defaults array.
		$module_courses = array();

		if ( isset( $courses ) && is_array( $courses ) ) {
			foreach ( $courses as $course ) {
				$module_courses[] = array(
					'id' => $course->ID,
					'title' => $course->post_title,
				);
			}
		}

		foreach ( $module_courses as $module_course ) {

			$this->{ 'module_date_course_' . $module_course['id'] } = new Course_Session_For_Sensei_Module_Date(
				$this->file,
				'date_course_' . $module_course['id'],
				$module_course['title']
			);

			$this->{ 'module_access_course_' . $module_course['id'] } = new Course_Session_For_Sensei_Module_Access(
				$this->file,
				'access_course_' . $module_course['id'],
				$module_course['title']
			);
		}
	}

	/**
	 * Installation. Runs on activation.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  bool false if Sensei plugin not activated.
	 */
	public function installation() {

		if ( ! $this->is_sensei_plugin_active() ) {
			// https://codex.wordpress.org/Function_Reference/register_activation_hook#Process_Flow
			add_option( 'CSS_Sensei_Plugin_Not_Activated', true );

			return false;
		}

		return true;
	}



	/**
	 * Is Sensei plugin active?
	 *
	 * @return bool
	 */
	public function is_sensei_plugin_active() {
		static $active = null;

		if ( ! is_null( $active ) ) {

			return $active;
		}

		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

		$active = in_array( 'woothemes-sensei/woothemes-sensei.php', $active_plugins ) ||
			in_array( 'sensei/woothemes-sensei.php', $active_plugins );

		return $active;
	}


	/**
	 * Display warning to user: should activate Sensei plugin.
	 *
	 * @access  public
	 * @since 1.0.0
	 */
	public function no_sensei_admin_notice__warning() {
		?>
		<div class="notice notice-warning">
			<p><strong><?php esc_html_e(
				'Course Session For Sensei',
				'course-session-for-sensei'
			); ?></strong>:
			<?php
			esc_html_e(
				'Please activate the Sensei plugin first and then reactivate the plugin.',
				'course-session-for-sensei'
			);
			?>
			</p>
		</div>
		<?php
	}


	/**
	 * Is Sensei returns true when is Course Session Management plugin
	 * @param $is_sensei
	 *
	 * @return bool
	 */
	public function is_sensei_filter( $is_sensei ) {

		if ( $is_sensei ) {

			return true;
		}

		return is_course_session_for_sensei();
	}
}
