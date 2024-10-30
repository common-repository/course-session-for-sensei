<?php
/**
 * Course Session For Sensei Module Access
 * Where we add the Access field for each course of the Module.
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course Session For Sensei Module Access class
 */
class Course_Session_For_Sensei_Module_Access extends CSS_WP_Term_Meta_UI {

	/**
	 * @var string Metadata key
	 */
	public $meta_key = 'access';


	/**
	 * @var array Which taxonomies are being targeted?
	 */
	public $taxonomies = array();

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 1.0.0
	 */
	public function __construct( $file = '', $meta_key = '', $course_title = '' ) {

		$this->taxonomies[] = 'module';

		// Translators: %s is course title.
		$label = __( 'Access',  'course-session-for-sensei' );

		// Setup the labels.
		$this->labels = array(
			'singular'    => esc_html( $label ),
			'plural'      => esc_html( $label ),
			'description' => esc_html__(
				'Restrict access before the module opening date.',
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
		<fieldset>
			<label>
				<input id="term-<?php echo esc_attr( $this->meta_key ); ?>-allow"
					name="term-<?php echo esc_attr( $this->meta_key ); ?>"
					placeholder=""
					value=""
					<?php checked( '', $value ); ?>
					type="radio" />
				<?php esc_attr_e( 'Allow', 'course-session-for-sensei' ); ?>
			</label>
			<br />
			<label>
				<input id="term-<?php echo esc_attr( $this->meta_key ); ?>-restrict"
					name="term-<?php echo esc_attr( $this->meta_key ); ?>"
					placeholder=""
					value="1"
					<?php checked( '1', $value ); ?>
					type="radio" />
				<?php esc_attr_e( 'Restrict', 'course-session-for-sensei' ); ?>
			</label>
		</fieldset>
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
