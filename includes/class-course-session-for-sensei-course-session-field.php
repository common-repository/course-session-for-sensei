<?php
/**
 * Course Session For Sensei Course Session Field
 * Where we order sessions inside a group and its course.
 *
 * Inspired by the Sensei LMS course modules ordering functionality.
 * @see class-sensei-modules.php
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course Session For Sensei Module Field class
 */
class Course_Session_For_Sensei_Course_Session_Field {

	/**
	 * Field (name).
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $field;

	public function __construct( $field ) {

		$this->field = $field;

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_filter( CSS_CPT_SLUG . '_custom_fields', array( $this, 'meta_boxes_content' ), 1 );

		// @link https://www.itsupportguides.com/knowledge-base/wordpress/wordpress-how-to-add-custom-column-to-custom-post-type-posts-list/
		add_filter( 'manage_' . CSS_CPT_SLUG . '_posts_columns', array( $this, 'add_custom_column' ) );

		add_action( 'manage_' . CSS_CPT_SLUG . '_posts_custom_column' , array( $this, 'add_custom_column_data' ), 10, 2 );

		add_filter( 'manage_edit-' . CSS_CPT_SLUG . '_sortable_columns', array( $this, 'add_custom_column_make_sortable' ) );

		add_action( 'load-edit.php', array( $this, 'add_custom_column_sort_request' ) );
	}


	/**
	 * Add meta boxes to the Session screen.
	 *
	 * @uses Admin API
	 */
	public function add_meta_boxes() {
		course_session_for_sensei()->admin_api->add_meta_box(
			CSS_CPT_SLUG . '-date',
			__( 'Date', 'course-session-for-sensei' ),
			array( CSS_CPT_SLUG ),
			'side'/*, $priority = 'default', $callback_args = null*/
		);
	}


	/**
	 * Meta boxes content
	 *
	 * @see Course_Session_For_Sensei_Admin_API::display_field
	 *
	 * @param array $fields Meta boxes fields.
	 *
	 * @return array Meta boxes fields.
	 */
	public function meta_boxes_content( $fields ) {
		$fields[] = array(
			'metabox' => CSS_CPT_SLUG . '-date',
			'id' => $this->field,
			'default' => '',
			'placeholder' => '',
			'type' => 'text',
			'class' => 'css-datepicker',
			'label' => '',
			'description' => '',
		);

		return $fields;
	}


	/**
	 * Add the custom column to the post type.
	 */
	public function add_custom_column( $columns ) {
		$columns[ $this->field ] = __( 'Course Session Date', 'course-session-for-sensei' );

		return $columns;
	}


	/**
	 * Add the data to the custom column.
	 */
	public function add_custom_column_data( $column, $post_id ) {
		switch ( $column ) {
			case $this->field :
				$date = get_post_meta( $post_id, $this->field, true );

				if ( empty( $date ) ) {

					echo '';

					break;
				}

				$fdate = date_i18n( get_option( 'date_format' ), mysql2date( 'U', $date ) );

				echo $fdate; // The data that is displayed in the column.

				break;
		}
	}


	/**
	 * Make the custom column sortable.
	 */
	public function add_custom_column_make_sortable( $columns ) {
		$columns[ $this->field ] = $this->field;

		return $columns;
	}


	/**
	 * Add custom column sort request to post list page.
	 */
	public function add_custom_column_sort_request() {
		add_filter( 'request', array( $this, 'add_custom_column_do_sortable' ) );
	}


	/**
	 * Handle the custom column sorting.
	 */
	public function add_custom_column_do_sortable( $vars ) {

		// Check if post type is being viewed.
		if ( isset( $vars['post_type'] ) && CSS_CPT_SLUG == $vars['post_type'] ) {

			// Check if sorting has been applied.
			if ( isset( $vars['orderby'] ) && $this->field == $vars['orderby'] ) {

				// Apply the sorting to the post list.
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => $this->field,
						'orderby' => 'meta_value',
					)
				);
			}
		}

		return $vars;
	}
}
