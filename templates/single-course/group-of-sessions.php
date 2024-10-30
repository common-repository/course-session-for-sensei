<?php
/**
 * The Template for displaying all single group of sessions.
 *
 * Override this template by copying it to yourtheme/sensei/single-group-of-sessions.php
 *
 * @author   Open-DSI
 * @package  Course Session For Sensei
 * @category Templates
 * @version  1.0.0
 */

defined( 'ABSPATH' ) or exit;

if ( css_the_course_has_group_of_sessions() ) : ?>
	<article class="group-of-sessions">

		<?php

		/**
		 * Hook inside the single course post above the content
		 *
		 * @since 1.0.0
		 *
		 * @param integer $group_of_sessions_id
		 *
		 * @hooked Sensei()->frontend->sensei_group_of_sessions_start     -  10
		 * @hooked Sensei_Course::the_title                    -  10
		 * @hooked Sensei()->course->group_of_sessions_image              -  20
		 * @hooked Sensei_WC::group_of_sessions_in_cart_message           -  20
		 * @hooked Sensei_Course::the_group_of_sessions_enrolment_actions -  30
		 * @hooked Sensei()->message->send_message_link        -  35
		 * @hooked Sensei_Course::the_group_of_sessions_video             -  40
		 */
		do_action( 'css_single_course_group_of_sessions_inside_before', css_get_the_group_of_sessions_id() );

		?>

		<header>

			<h2>

				<a href="<?php echo css_get_the_group_of_sessions_permalink(); ?>"
				   title="<?php echo esc_attr( css_get_the_group_of_sessions_title() ); ?>">

					<?php echo css_get_the_group_of_sessions_title(); ?>

				</a>

			</h2>

		</header>

		<section class="entry">

			<?php css_the_group_of_sessions_dates(); ?>

		</section>


		<?php

		/**
		 * Hook inside the single course post above the content
		 *
		 * @since 1.0.0
		 *
		 * @param integer $group_of_sessions_id
		 */
		do_action( 'css_single_course_group_of_sessions_inside_after', css_get_the_group_of_sessions_id() );

		?>
	</article><!-- .group-of-sessions -->

<?php endif; // css_the_course_has_group_of_sessions.
