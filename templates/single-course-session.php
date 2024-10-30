<?php
/**
 * The Template for displaying all single course sessions.
 *
 * Override this template by copying it to yourtheme/course-session-for-sensei/single-course-session.php
 *
 * @author   Open-DSI
 * @package  Course Session For Sensei
 * @category Templates
 * @version  1.0.0
 */

get_sensei_header();
the_post();
?>

	<article <?php post_class( array( 'course-session', 'post' ) ); ?>>

		<?php

		/**
		 * Hook inside the single lesson above the content
		 *
		 * @since 1.0.0
		 *
		 * @param integer $course_session_id
		 *
		 * @hooked deprecated_course_session_image_hook - 10
		 * @hooked deprecate_sensei_course_session_single_title - 15
		 * @hooked Sensei_Lesson::course_session_image() -  17
		 * @hooked deprecate_course_session_single_main_content_hook - 20
		 */
		do_action( 'css_single_course_session_content_inside_before', get_the_ID() );

		?>

		<header>

			<h2>
				<span><?php _e( 'Course Session', 'course-session-for-sensei' ); ?> :</span>
				<?php the_title(); ?>
			</h2>

		</header>

		<section class="entry fix">

			<?php css_the_course_session_date(); ?>

			<?php

			if ( css_can_user_view_course_session() ) {

				the_content();

			} else {
				?>

				<p> <?php the_excerpt(); ?> </p>

				<?php
			}

			?>

		</section>

		<?php

		/**
		 * Hook inside the single lesson template after the content
		 *
		 * @since 1.0.0
		 *
		 * @param integer $course_session_id
		 *
		 * @hooked Sensei()->frontend->sensei_breadcrumb   - 30
		 */
		do_action( 'css_single_course_session_content_inside_after', get_the_ID() );

		?>

	</article><!-- .post -->

<?php get_sensei_footer(); ?>
