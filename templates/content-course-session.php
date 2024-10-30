<?php
/**
 * Content-course-session.php template file
 *
 * responsible for content on archive like pages. Only shows the course session excerpt.
 *
 * For single course session content please see single-course-session.php
 *
 * @author   Open-DSI
 * @package  Course Session For Sensei
 * @category Templates
 * @version  1.0.0
 */

defined( 'ABSPATH' ) or exit;
?>

<article <?php post_class( get_the_ID() ); ?> >

	<section class="course-session-content">

		<?php
		/**
		 * css_content_lesson_before
		 * action that runs before the sensei {post_type} content. It runs inside the sensei
		 * content.php template. This applies to the specific post type that you've targeted.
		 *
		 * @since 1.9.0
		 * @param string $lesson_id
		 */
		do_action( 'css_content_lesson_before', get_the_ID() );
		?>

		<section class="entry">

			<?php
			/**
			 * Fires just before the post content in the content-course-session.php file.
			 *
			 * @since 1.0.0
			 *
			 * @hooked Sensei()->modules->module_archive_description -  11
			 * @hooked Sensei_Lesson::the_lesson_meta                -  20
			 *
			 * @param string $lesson_id
			 */
			do_action( 'css_content_lesson_inside_before', get_the_ID() );
			?>

			<header>

				<h2>

					<a href="<?php echo css_get_the_course_session_permalink(); ?>"
					   title="<?php echo esc_attr( css_get_the_course_session_title() ); ?>">

						<?php echo css_get_the_course_session_title(); ?>

					</a>

				</h2>

			</header>

			<?php css_the_course_session_date(); ?>

			<p class="course-session-excerpt">

				<?php the_excerpt(); ?>

			</p>

			<?php
			/**
			 * Fires just after the post content in the course-session-content.php file.
			 *
			 * @since 1.9.0
			 *
			 * @param string $lesson_id
			 */
			do_action( 'css_content_lesson_inside_after', get_the_ID() );
			?>

		</section> <!-- section .entry -->

		<?php
		/**
		 * This action runs after the sensei course session content. It runs inside the sensei
		 * course-session-content.php template.
		 *
		 * @since 1.9.0
		 * @param string $lesson_id
		 */
		do_action( 'css_content_lesson_after', get_the_ID() );
		?>

	</section> <!-- section .course-session-content -->

</article> <!-- article -->
