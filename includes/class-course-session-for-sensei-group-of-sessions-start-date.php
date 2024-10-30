<?php
/**
 * Course Session For Sensei Group of Sessions' Start_Date
 * Where we order sessions inside a group and its course.
 *
 * Inspired by the Sensei LMS course modules ordering functionality.
 * @see class-sensei-modules.php
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course Session For Sensei Group of Sessions' Start_Date class
 */
class Course_Session_For_Sensei_Group_Of_Sessions_Start_Date extends CSS_WP_Term_Meta_UI {

	/**
	 * @var string Metadata key
	 */
	public $meta_key = 'start_date';


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
			'singular'    => esc_html__( 'Start Date',  'course-session-for-sensei' ),
			'plural'      => esc_html__( 'Start Date', 'course-session-for-sensei' ),
			'description' => esc_html__(
				'Select the date this group of sessions starts on.',
				'course-session-for-sensei'
			),
		);

		// Call the parent and pass the file.
		parent::__construct( $file );
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
		?>
		<div>
			<input id="term-<?php echo esc_attr( $this->meta_key ); ?>"
				   name="term-<?php echo esc_attr( $this->meta_key ); ?>"
				   placeholder=""
				   value="<?php echo esc_attr( $value ); ?>"
				   class="css-datepicker"
				   type="text" />
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
	 * Return the formatted output for the column row
	 *
	 * @since 1.0.0
	 *
	 * @param string $meta
	 */
	protected function format_output( $meta = '' ) {

		echo esc_html( date_i18n( get_option( 'date_format' ), mysql2date( 'U', $meta ) ) );
	}
}
