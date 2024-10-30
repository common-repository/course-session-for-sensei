<?php
/**
 * The Template for displaying all single course session.
 *
 * Override this template by copying it to yourtheme/course-session-for-sensei/single-course/lesson-course-sessions.php
 *
 * @author   Open-DSI
 * @package  Course Session For Sensei
 * @category Templates
 * @version  1.0.0
 */

defined( 'ABSPATH' ) or exit;

while ( css_the_group_have_sessions() && css_is_the_course_session_next( 'lesson' ) ) :
	css_the_course_session_post(); ?>
	<li class="course-session">

		<?php

		/**
		 * Hook inside the single course post above the content
		 *
		 * @since 1.0.0
		 *
		 * @param integer $course_session_id
		 *
		 * @hooked Sensei()->frontend->sensei_course_session_start     -  10
		 * @hooked Sensei_Course::the_title                    -  10
		 * @hooked Sensei()->course->course_session_image              -  20
		 * @hooked Sensei_WC::course_session_in_cart_message           -  20
		 * @hooked Sensei_Course::the_course_session_enrolment_actions -  30
		 * @hooked Sensei()->message->send_message_link        -  35
		 * @hooked Sensei_Course::the_course_session_video             -  40
		 */
		do_action( 'css_single_course_session_inside_before', css_get_the_course_session_id() );

		?>

		<!--<span><?php _e( 'Course Session', 'course-session-for-sensei' ); ?></span>-->

		<a href="<?php echo css_get_the_course_session_permalink(); ?>"
		   title="<?php echo esc_attr( css_get_the_course_session_title() ); ?>" >

			<?php echo css_get_the_course_session_title(); ?>

		</a>

		<?php css_the_course_session_date(); ?>

		<?php

		/**
		 * Hook inside the single course post above the content
		 *
		 * @since 1.0.0
		 *
		 * @param integer $course_session_id
		 */
		do_action( 'css_single_course_session_inside_after', css_get_the_course_session_id() );

		?>
	</li><!-- .course-session -->

<?php endwhile; // css_the_group_have_sessions.
