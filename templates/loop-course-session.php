<?php
/**
 * The Template for outputting Course Session Archive items
 *
 * Override this template by copying it to yourtheme/sensei/loop-course-session.php
 *
 * @author   Open-DSI
 * @package  Course Session For Sensei
 * @category Templates
 * @version  1.0.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * This runs before the post type items in the loop-course-session.php template.
 *
 * @since 1.0.0
 */
do_action( 'css_loop_course_session_before' );
?>

	<section class="entry">

		<?php css_the_group_of_sessions_dates(); ?>

	</section>

	<section class="course-session-container" >

		<?php
		/**
		 * This runs before the course session items in the loop-course-session.php template.
		 *
		 * @since 1.0.0
		 *
		 * @hooked Sensei()->course-session->course_session_tag_archive_description - 11
		 * @hooked Sensei()->course-session->the_archive_header - 20
		 */
		do_action( 'css_loop_course_session_inside_before' );
		?>


		<?php
		// Loop through all course sessions.
		while ( have_posts() ) : the_post();

			css_load_template_part( 'content', 'course-session' );

		endwhile;
		?>

		<?php
		/**
		 * This runs inside the <ul> after the lesson items in the loop-course-session.php template.
		 *
		 * @since 1.0.0
		 */
		do_action( 'css_loop_course_session_inside_after' );
		?>

	</section>

<?php
/**
 * This runs after the lesson items <ul> in the loop-course-session.php template.
 *
 * @since 1.0.0
 */
do_action( 'css_loop_course_session_after' );
