<?php
/**
 * Course Session For Sensei Module Date
 * Where we add the Date field for each course of the Module.
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course Session For Sensei Module Date class
 */
class Course_Session_For_Sensei_Module_Date extends CSS_WP_Term_Meta_UI {

	/**
	 * @var string Metadata key
	 */
	public $meta_key = 'date';


	/**
	 * @var array Which taxonomies are being targeted?
	 */
	public $taxonomies = array();

	/**
	 * @var string Course Title.
	 */
	public $course_title = array();

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 1.0.0
	 */
	public function __construct( $file = '', $meta_key = '', $course_title = '' ) {

		$this->taxonomies[] = 'module';

		$this->course_title = $course_title;

		// Has fields & course title?
		if ( true === $this->has_fields && $course_title ) {
			add_action( "module_edit_form_fields", array( $this, 'edit_course_header' ) );
		}

		// Translators: %s is course title.
		$label = __( 'Date',  'course-session-for-sensei' );

		// Setup the labels.
		$this->labels = array(
			'singular'    => esc_html( $label ),
			'plural'      => esc_html( $label ),
			'description' => esc_html__(
				'Select the module opening date.',
				'course-session-for-sensei'
			),
		);

		if ( $meta_key ) {

			$this->meta_key = $meta_key;
		}

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


	/**
	 * Output the form field when editing an existing term
	 *
	 * @since 0.1.0
	 *
	 * @param object $term
	 */
	public function edit_course_header( $term = false ) {
		?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label>
					<?php esc_html_e( 'Course', 'woothemes-sensei' ); ?>
				</label>
			</th>
			<td>
				<?php echo esc_html( $this->course_title ); ?>
			</td>
		</tr>

		<?php
	}
}
