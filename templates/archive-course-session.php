<?php
/**
 * The Template for displaying course session archives, including the course session page template.
 * This template also handles the course session modules taxonomy and the lessons_tag taxonomy.
 *
 * Override this template by copying it to your_theme/course-session-for-sensei/archive-course-session.php
 *
 * @author   Open-DSI
 * @package  Course Session For Sensei
 * @category Templates
 * @version  1.0.0
 */

get_sensei_header();

/**
 * Action before course session archive loop. This action runs within the archive-course-session.php.
 *
 * It will be executed even if there are no posts on the archive page.
 */
do_action( 'css_archive_before_course_session_loop' ); ?>

<?php if ( css_get_the_group_of_sessions_title() ) : ?>

	<header>

		<h2>
			<span><?php _e( 'Group of Sessions', 'course-session-for-sensei' ); ?> :</span>
			<?php echo css_get_the_group_of_sessions_title(); ?>
		</h2>

	</header>

	<p><?php esc_html_e( css_get_the_group_of_sessions_description() ); ?></p>

<?php endif; ?>

<?php if ( have_posts() ) : ?>

	<?php css_load_template( 'loop-course-session.php' ); ?>

<?php else : ?>

	<p><?php _e( 'No course sessions found that match your selection.', 'course-session-for-sensei' ); ?></p>

<?php endif;

/**
 * Action after course session archive  loop on the archive-course-session.php template file
 * It will be executed even if there are no posts on the archive page.
 *
 * @since 1.0.0
 */
do_action( 'css_archive_after_course_session_loop' );

get_sensei_footer(); ?>
