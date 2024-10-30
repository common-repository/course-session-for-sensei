<?php
/**
 * Course Session For Sensei Group of Sessions' Course
 * Where we order sessions inside a group and its course.
 *
 * Inspired by the Sensei LMS course modules ordering functionality.
 * @see class-sensei-modules.php
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course Session For Sensei Group of Sessions' Course class
 */
class Course_Session_For_Sensei_Group_Of_Sessions_Course extends CSS_WP_Term_Meta_UI {

	/**
	 * @var string Metadata key
	 */
	public $meta_key = 'course';


	/**
	 * @var string Extra column key
	 */
	public $extra_column_key = 'order-sessions';


	/**
	 * @var array Which taxonomies are being targeted?
	 */
	public $taxonomies = array();

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 1.0.0
	 */
	public function __construct( $file = '' ) {

		$this->taxonomies[] = CSS_TAXONOMY_SLUG;

		// Setup the labels.
		$this->labels = array(
			'singular'    => esc_html__( 'Course',  'woothemes-sensei' ),
			'plural'      => esc_html__( 'Courses', 'woothemes-sensei' ),
			'description' => esc_html__(
				'Select the course that this group of sessions will belong to.',
				'course-session-for-sensei'
			),
		);

		// Call the parent and pass the file.
		parent::__construct( $file );

		// Always hook these in, for ajax actions.
		foreach ( $this->taxonomies as $value ) {

			add_filter( "manage_edit-{$value}_columns",  array( $this, 'remove_description_column_header' ) );
			add_filter( "manage_edit-{$value}_columns",  array( $this, 'add_extra_column_header' ) );
			add_filter( "manage_{$value}_custom_column", array( $this, 'add_extra_column_value'  ), 10, 3 );
		}
	}


	/**
	 * Output the form field
	 *
	 * @since 1.0.0
	 *
	 * @param  $term
	 */
	protected function form_field( $term = '' ) {

		$term_id = ! empty( $term->term_id )
			? $term->term_id
			: 0;

		// Get the meta value.
		$value = $this->get_meta( $term_id );

		$courses = $this->get_courses();
		?>
		<div>
			<select name="term-<?php echo esc_attr( $this->meta_key ); ?>"
					id="term-<?php echo esc_attr( $this->meta_key ); ?>"
					required="required">
				<?php foreach ( $courses as $course ) { ?>
					<option value="<?php echo esc_attr( $course['id'] ); ?>"
						<?php selected( $course['id'], $value, true ); ?>>
						<?php echo esc_html( $course['details'] ); ?>
					</option>
				<?php } ?>
			</select>
		</div>
		<?php
		if ( ! $term_id ) {
			// Duplicate on creation, only.
			$this->duplicate_course_form_field( $term );
		}
	}


	/**
	 * Output the duplicate course form field
	 *
	 * @since 1.0.0
	 */
	protected function duplicate_course_form_field() {
		?>
		<div>
			<label title="<?php esc_attr_e( 'Duplicate this course with its lessons', 'woothemes-sensei' ); ?>">
				<input type="checkbox"
					   name="term-<?php echo esc_attr( $this->meta_key ); ?>-duplicate"
					   id="term-<?php echo esc_attr( $this->meta_key ); ?>-duplicate"
					   value="1" />
				<?php _e( 'Duplicate (with lessons)', 'woothemes-sensei' ); ?>
			</label>
		</div>
		<?php
	}


	/**
	 * Do not output the quick-edit field
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name
	 * @param string $screen
	 * @param string $name
	 *
	 * @return bool False, always.
	 */
	public function quick_edit_meta( $column_name = '', $screen = '', $name = '' ) {
		return false;
	}


	/**
	 * Get Sensei Courses
	 * formatted for the Group of Sessions' Course select input.
	 *
	 * @return array Sensei courses.
	 */
	public function get_courses() {
		// Get existing courses.
		$args = array(
			'post_type' => 'course',
			'post_status' => array( 'publish', 'draft', 'future', 'private' ),
			'posts_per_page' => -1,
		);

		$courses = get_posts( $args );

		// Build the defaults array.
		$group_of_sessions_courses = array();

		if ( isset( $courses ) &&
			is_array( $courses ) ) {
			foreach ( $courses as $course ) {
				$group_of_sessions_courses[] = array(
					'id' => $course->ID,
					'details' => $course->post_title,
				);
			}
		}

		return $group_of_sessions_courses;
	}


	/**
	 * Add `meta_key` to term when updating
	 *
	 * @since 1.0.0
	 *
	 * @uses CSS_Sensei_Admin::duplicate_course()
	 *
	 * @param  int     $term_id
	 * @param  string  $taxonomy
	 */
	public function save_meta( $term_id = 0, $taxonomy = '' ) {

		// Get the term being posted
		$term_key = 'term-' . $this->meta_key;

		// Bail if not updating meta_key
		$meta = ! empty( $_POST[ $term_key ] )
			? $_POST[ $term_key ]
			: '';

		$term_duplicate_key = $term_key . '-duplicate';

		if ( $meta &&
			! empty( $_POST[ $term_duplicate_key ] ) ) {

			// Duplicate post and get new post ID.
			$meta = course_session_for_sensei()->admin->duplicate_course( $_POST[ $term_key ] );
		}

		// Call the parent and save the meta.
		$this->set_meta( $term_id, $taxonomy, $meta );
	}


	/**
	 * Return the formatted output for the column row
	 *
	 * @since 1.0.0
	 *
	 * @param string $meta
	 */
	protected function format_output( $meta = '' ) {

		$course_id = $meta;

		$course = get_post( $course_id, ARRAY_A );

		if ( ! $course ) {

			echo esc_html( $course_id );
		} else {

			$edit_course_url = get_edit_post_link( $course_id );

			?>
			<a href="<?php echo esc_url( $edit_course_url ); ?>">
				<?php echo esc_html( $course['post_title'] ); ?>
			</a>
			<?php
		}
	}


	/**
	 * Remove the "description" column from taxonomy terms list-tables
	 *
	 * @since 1.0.0
	 *
	 * @see add_column_value(), CSS_WP_Term_Meta_UI
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function remove_description_column_header( $columns = array() ) {
		unset( $columns['description'] );

		return $columns;
	}


	/**
	 * Add the extra "key" column to taxonomy terms list-tables
	 *
	 * @since 1.0.0
	 *
	 * @see add_column_value(), CSS_WP_Term_Meta_UI
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_extra_column_header( $columns = array() ) {
		$columns[ $this->extra_column_key ] = __( 'Session Order', 'course-session-for-sensei' );

		return $columns;
	}


	/**
	 * Output the value for the extra column
	 *
	 * @since 1.0.0
	 *
	 * @see add_column_value(), CSS_WP_Term_Meta_UI
	 *
	 * @param string $empty
	 * @param string $custom_column
	 * @param int    $term_id
	 */
	public function add_extra_column_value( $empty = '', $custom_column = '', $term_id = 0 ) {

		// Bail if no taxonomy passed or not on the `extra_column_key` column.
		if ( empty( $_REQUEST['taxonomy'] ) || ( $this->extra_column_key !== $custom_column ) || ! empty( $empty ) ) {
			return;
		}

		// Get the metadata.
		// $meta   = $this->get_meta( $term_id );
		$retval = $this->no_value;

		// Output HTML element if not empty.
		if ( ! empty( $term_id ) ) {
			$retval = $this->format_extra_column_output( $term_id );
		}

		echo $retval;
	}


	/**
	 * Return the formatted output for the extra column row
	 *
	 * @since 1.0.0
	 *
	 * @param string $meta
	 */
	protected function format_extra_column_output( $term_id = 0 ) {

		$edit_group_url = admin_url(
			'edit.php?post_type=' . CSS_CPT_SLUG .
			'&page=' . course_session_for_sensei()->order_page_slug .
			'&group_id=' . $term_id
		);
		?>
		<a href="<?php echo esc_url( $edit_group_url ); ?>" class="button-secondary">
			<?php esc_html_e( 'Order', 'course-session-for-sensei' ); ?>
		</a>
		<?php
	}
}
