<?php

/**
 * Term Meta UI Class
 *
 * This class is base helper to be extended by other plugins that may want to
 * provide a UI for term meta values. It hooks into several different WordPress
 * core actions & filters to add columns to list tables, add fields to forms,
 * and handle the sanitization & saving of values.
 *
 * @since 0.1.1
 * @version 0.1.9
 *
 * @package Plugins/Terms/Metadata/UI
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( function_exists( 'add_term_meta' ) && ! class_exists( 'CSS_WP_Term_Meta_UI' ) ) :
/**
 * Main WP Term Meta UI class
 *
 * @link https://make.wordpress.org/core/2013/07/28/potential-roadmap-for-taxonomy-meta-and-post-relationships/ Taxonomy Roadmap
 *
 * @since 0.1.0
 */
class CSS_WP_Term_Meta_UI {

	/**
	 * @var string Plugin version
	 */
	protected $version = '0.0.0';

	/**
	 * @var string Database version
	 */
	protected $db_version = 201601010001;

	/**
	 * @var string Database version
	 */
	protected $db_version_key = '';

	/**
	 * @var string Metadata key
	 */
	protected $meta_key = '';

	/**
	 * @var string No value
	 */
	protected $no_value = '&#8212;';

	/**
	 * @var array Array of labels
	 */
	protected $labels = array(
		'singular'   => '',
		'plural'     => '',
		'descrption' => ''
	);

	/**
	 * @var string File for plugin
	 */
	public $file = '';

	/**
	 * @var string URL to plugin
	 */
	public $url = '';

	/**
	 * @var string Path to plugin
	 */
	public $path = '';

	/**
	 * @var string Basename for plugin
	 */
	public $basename = '';

	/**
	 * @var array Which taxonomies are being targeted?
	 */
	public $taxonomies = array();

	/**
	 * @var bool Whether to use fancy UI
	 */
	public $fancy = false;

	/**
	 * @var bool Whether to show a column
	 */
	public $has_column = true;

	/**
	 * @var bool Whether to show fields
	 */
	public $has_fields = true;

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 0.1.0
	 */
	public function __construct( $file = '' ) {

		// Setup plugin
		$this->file       = $file;
		$this->url        = plugin_dir_url( $this->file );
		$this->path       = plugin_dir_path( $this->file );
		$this->basename   = plugin_basename( $this->file );
		$this->fancy      = apply_filters( "wp_fancy_term_{$this->meta_key}", true );

		// Only look for taxonomies if not already set
		if ( empty( $this->taxonomies ) ) {
			$this->taxonomies = $this->get_taxonomies();
		}

		// Maybe build db version key
		if ( empty( $this->db_version_key ) ) {
			$this->db_version_key = "wpdb_term_{$this->meta_key}_version";
		}

		// Register Meta
		$this->register_meta();

		// Queries
		add_action( 'create_term', array( $this, 'save_meta' ), 10, 2 );
		add_action( 'edit_term',   array( $this, 'save_meta' ), 10, 2 );

		// Term meta orderby
		add_filter( 'terms_clauses',     array( $this, 'terms_clauses'     ), 10, 3 );
		add_filter( 'get_terms_orderby', array( $this, 'get_terms_orderby' ), 10, 1 );

		// Always hook these in, for ajax actions
		foreach ( $this->taxonomies as $value ) {

			// Has column?
			if ( true === $this->has_column ) {
				add_filter( "manage_edit-{$value}_columns",          array( $this, 'add_column_header' ) );
				add_filter( "manage_{$value}_custom_column",         array( $this, 'add_column_value'  ), 10, 3 );
				add_filter( "manage_edit-{$value}_sortable_columns", array( $this, 'sortable_columns'  ) );
			}

			// Has fields?
			if ( true === $this->has_fields ) {
				add_action( "{$value}_add_form_fields",  array( $this, 'add_form_field'  ) );
				add_action( "{$value}_edit_form_fields", array( $this, 'edit_form_field' ) );
			}
		}

		// ajax actions
		add_action( "wp_ajax_{$this->meta_key}_terms", array( $this, 'ajax_update' ) );

		// Only blog admin screens
		if ( is_blog_admin() || doing_action( 'wp_ajax_inline_save_tax' ) ) {

			// Every admin page
			add_action( 'admin_init', array( $this, 'admin_init' ) );

			// Only add if taxonomy is supported
			if ( ! empty( $_REQUEST['taxonomy'] ) && in_array( $_REQUEST['taxonomy'], $this->taxonomies, true ) ) {
				add_action( 'load-edit-tags.php', array( $this, 'edit_tags_page' ) );
				add_action( 'load-term.php',      array( $this, 'term_page'      ) );
			}
		}

		// Pass ths object into an action
		do_action( "wp_term_meta_{$this->meta_key}_init", $this );
	}

	/**
	 * Register term meta, key, and callbacks
	 *
	 * @since 0.1.5
	 */
	public function register_meta() {
		register_meta(
			'term',
			$this->meta_key,
			array( $this, 'sanitize_callback' ),
			array( $this, 'auth_callback'     )
		);
	}

	/**
	 * Stub method for sanitizing meta data
	 *
	 * @since 0.1.5
	 *
	 * @param   mixed $data
	 * @return  mixed
	 */
	public function sanitize_callback( $data = '' ) {
		return $data;
	}

	/**
	 * Stub method for authorizing the saving of meta data
	 *
	 * @since 0.1.5
	 *
	 * @param  bool    $allowed
	 * @param  string  $meta_key
	 * @param  int     $post_id
	 * @param  int     $user_id
	 * @param  string  $cap
	 * @param  array   $caps
	 *
	 * @return boolean
	 */
	public function auth_callback( $allowed = false, $meta_key = '', $post_id = 0, $user_id = 0, $cap = '', $caps = array() ) {

		// Bail if incorrect meta key
		if ( $meta_key !== $this->meta_key ) {
			return $allowed;
		}

		return $allowed;
	}

	/**
	 * Administration area hooks
	 *
	 * @since 0.1.0
	 */
	public function admin_init() {

		// Check for DB update
		$this->maybe_upgrade_database();
	}

	/**
	 * Administration area hooks
	 *
	 * @since 0.1.0
	 */
	public function edit_tags_page() {
		add_action( 'admin_head-edit-tags.php',          array( $this, 'help_tabs'       ) );
		add_action( 'admin_head-edit-tags.php',          array( $this, 'admin_head'      ) );
		add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'enqueue_scripts' ) );
		add_action( 'quick_edit_custom_box',             array( $this, 'quick_edit_meta' ), 10, 3 );
	}

	/**
	 * Administration area hooks
	 *
	 * @since 0.1.9
	 */
	public function term_page() {
		add_action( 'admin_head-term.php',          array( $this, 'admin_head'      ) );
		add_action( 'admin_print_scripts-term.php', array( $this, 'enqueue_scripts' ) );
	}

	/** Get Terms *************************************************************/

	/**
	 * Filter `get_terms_args` and tweak for meta_query orderby's
	 *
	 * @since 0.1.5
	 *
	 * @param  string  $orderby
	 * @param  array   $args
	 * @param  array   $taxonomies
	 */
	public function get_terms_orderby( $orderby = '' ) {

		// Ordering by meta key
		if ( ! empty( $_REQUEST['orderby'] ) && ( $this->meta_key === $_REQUEST['orderby'] ) ) {
			$orderby = 'meta_value';
		}

		return $orderby;
	}

	/**
	 * Filter get_terms() and maybe order by meta data
	 *
	 * @since 0.1.5
	 *
	 * @param  array  $clauses
	 * @param  array  $taxonomies
	 * @param  array  $args
	 */
	public function terms_clauses( $clauses = array(), $taxonomies = array(), $args = array() ) {
		global $wpdb;

		// Default allowed keys & primary key
		$allowed_keys = array( $this->meta_key );

		// Set allowed keys
		$allowed_keys[] = 'meta_value';
		$allowed_keys[] = 'meta_value_num';

		// Tweak orderby
		$orderby = isset( $args[ 'orderby' ] )
			? $args[ 'orderby' ]
			: '';

		// Bail if no orderby or allowed_keys
		if ( ! in_array( $orderby, $allowed_keys, true ) ) {
			return $clauses;
		}

		// Join term meta data
		$clauses['join'] .= " INNER JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id";

		// Maybe order by term meta
		switch ( $args[ 'orderby' ] ) {
			case $this->meta_key :
			case 'meta_value' :
				if ( ! empty( $this->key_type ) ) {
					$clauses['orderby'] = "ORDER BY CAST(tm.meta_value AS tm)";
				} else {
					$clauses['orderby'] = "ORDER BY tm.meta_value";
				}
				$clauses['fields'] .= ', tm.*';
				$clauses['where']  .= " AND tm.meta_key = '{$this->meta_key}'";
				break;
			case 'meta_value_num':
				$clauses['orderby'] = "ORDER BY tm.meta_value+0";
				$clauses['fields'] .= ', tm.*';
				$clauses['where']  .= " AND tm.meta_key = '{$this->meta_key}'";
				break;
		}

		// Return maybe modified clauses
		return $clauses;
	}

	/** Assets ****************************************************************/

	/**
	 * Enqueue quick-edit JS
	 *
	 * @since 0.1.0
	 */
	public function enqueue_scripts() { }

	/**
	 * Add help tabs for this metadata
	 *
	 * @since 0.1.0
	 */
	public function help_tabs() { }

	/**
	 * Add help tabs for this metadata
	 *
	 * @since 0.1.2
	 */
	public function admin_head() { }

	/**
	 * Quick edit ajax updating
	 *
	 * @since 0.1.1
	 */
	public function ajax_update() {}

	/**
	 * Return the taxonomies used by this plugin
	 *
	 * @since 0.1.0
	 *
	 * @param array $args
	 * @return array
	 */
	private function get_taxonomies( $args = array() ) {

		// The filter key/tag
		$tag = "wp_term_{$this->meta_key}_get_taxonomies";

		/**
		 * Allow filtering of affected taxonomies
		 *
		 * @since 0.1.3
		 */
		$defaults = apply_filters( $tag, array(
			'show_ui' => true
		) );

		// Parse arguments
		$r = wp_parse_args( $args, $defaults );

		// Get & return the taxonomies
		return get_taxonomies( $r );
	}

	/** Columns ***************************************************************/

	/**
	 * Add the "meta_key" column to taxonomy terms list-tables
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_column_header( $columns = array() ) {
		$columns[ $this->meta_key ] = $this->labels['singular'];

		return $columns;
	}

	/**
	 * Output the value for the custom column
	 *
	 * @since 0.1.0
	 *
	 * @param string $empty
	 * @param string $custom_column
	 * @param int    $term_id
	 *
	 * @return mixed
	 */
	public function add_column_value( $empty = '', $custom_column = '', $term_id = 0 ) {

		// Bail if no taxonomy passed or not on the `meta_key` column
		if ( empty( $_REQUEST['taxonomy'] ) || ( $this->meta_key !== $custom_column ) || ! empty( $empty ) ) {
			return;
		}

		// Get the metadata
		$meta   = $this->get_meta( $term_id );
		$retval = $this->no_value;

		// Output HTML element if not empty
		if ( ! empty( $meta ) ) {
			$retval = $this->format_output( $meta );
		}

		echo $retval;
	}

	/**
	 * Allow sorting by this `meta_key`
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function sortable_columns( $columns = array() ) {
		$columns[ $this->meta_key ] = $this->meta_key;
		return $columns;
	}

	/**
	 * Add `meta_key` to term when updating
	 *
	 * @since 0.1.0
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

		$this->set_meta( $term_id, $taxonomy, $meta );
	}

	/**
	 * Set `meta_key` of a specific term
	 *
	 * @since 0.1.0
	 *
	 * @param  int     $term_id
	 * @param  string  $taxonomy
	 * @param  string  $meta
	 * @param  bool    $clean_cache
	 */
	public function set_meta( $term_id = 0, $taxonomy = '', $meta = '', $clean_cache = false ) {

		// No meta_key, so delete
		if ( empty( $meta ) ) {
			delete_term_meta( $term_id, $this->meta_key );

		// Update meta_key value
		} else {
			update_term_meta( $term_id, $this->meta_key, $meta );
		}

		// Maybe clean the term cache
		if ( true === $clean_cache ) {
			clean_term_cache( $term_id, $taxonomy );
		}
	}

	/**
	 * Return the `meta_key` of a term
	 *
	 * @since 0.1.0
	 *
	 * @param int $term_id
	 */
	public function get_meta( $term_id = 0 ) {
		return get_term_meta( $term_id, $this->meta_key, true );
	}

	/** Markup ****************************************************************/

	/**
	 * Output the form field for this metadata when adding a new term
	 *
	 * @since 0.1.0
	 */
	public function add_form_field() {
		?>

		<div class="form-field term-<?php echo esc_attr( $this->meta_key ); ?>-wrap">
			<label for="term-<?php echo esc_attr( $this->meta_key ); ?>">
				<?php echo esc_html( $this->labels['singular'] ); ?>
			</label>

			<?php $this->form_field(); ?>

			<?php if ( ! empty( $this->labels['description'] ) ) : ?>

				<p class="description">
					<?php echo esc_html( $this->labels['description'] ); ?>
				</p>

			<?php endif; ?>

		</div>

		<?php
	}

	/**
	 * Output the form field when editing an existing term
	 *
	 * @since 0.1.0
	 *
	 * @param object $term
	 */
	public function edit_form_field( $term = false ) {
		?>

		<tr class="form-field term-<?php echo esc_attr( $this->meta_key ); ?>-wrap">
			<th scope="row" valign="top">
				<label for="term-<?php echo esc_attr( $this->meta_key ); ?>">
					<?php echo esc_html( $this->labels['singular'] ); ?>
				</label>
			</th>
			<td>
				<?php $this->form_field( $term ); ?>

				<?php if ( ! empty( $this->labels['description'] ) ) : ?>

					<p class="description">
						<?php echo esc_html( $this->labels['description'] ); ?>
					</p>

				<?php endif; ?>

			</td>
		</tr>

		<?php
	}

	/**
	 * Output the quick-edit field
	 *
	 * @since 0.1.0
	 *
	 * @param  $term
	 */
	public function quick_edit_meta( $column_name = '', $screen = '', $name = '' ) {

		// Bail if not the meta_key column on the `edit-tags` screen for a visible taxonomy
		if ( ( $this->meta_key !== $column_name ) || ( 'edit-tags' !== $screen ) || ! in_array( $name, $this->taxonomies ) ) {
			return false;
		} ?>

		<fieldset>
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php echo esc_html( $this->labels['singular'] ); ?></span>
					<span class="input-text-wrap">

						<?php $this->quick_edit_form_field(); ?>

					</span>
				</label>
			</div>
		</fieldset>

		<?php
	}

	/**
	 * Output the form field
	 *
	 * @since 0.1.0
	 *
	 * @param  $term
	 */
	protected function form_field( $term = '' ) {

		// Get the meta value
		$value = isset( $term->term_id )
			?  $this->get_meta( $term->term_id )
			: ''; ?>

		<input type="text" name="term-<?php echo esc_attr( $this->meta_key ); ?>" id="term-<?php echo esc_attr( $this->meta_key ); ?>" value="<?php echo esc_attr( $value ); ?>">

		<?php
	}

	/**
	 * Output the form field
	 *
	 * @since 0.1.0
	 *
	 * @param  $term
	 */
	protected function quick_edit_form_field() {
		?>

		<input type="text" class="ptitle" name="term-<?php echo esc_attr( $this->meta_key ); ?>" value="">

		<?php
	}

	/** Database Alters *******************************************************/

	/**
	 * Should a database update occur
	 *
	 * Runs on `init`
	 *
	 * @since 0.1.0
	 */
	protected function maybe_upgrade_database() {

		// Check DB for version
		$db_version = get_option( $this->db_version_key );

		// Needs
		if ( $db_version < $this->db_version ) {
			$this->upgrade_database( $db_version );
		}
	}

	/**
	 * Upgrade the database as needed, based on version comparisons
	 *
	 * @since 0.1.0
	 *
	 * @param  int  $old_version
	 */
	private function upgrade_database( $old_version = 0 ) {
		update_option( $this->db_version_key, $this->db_version );
	}
}
endif;
