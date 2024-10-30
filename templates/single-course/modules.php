<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * List the Course Modules and Lesson in these modules
 *
 * Template is hooked into Single Course sensei_single_main_content. It will
 * only be shown if the course contains modules.
 *
 * All lessons shown here will not be included in the list of other lessons.
 *
 * @author 		Automattic
 * @package 	Sensei
 * @category    Templates
 * @version     1.9.0
 */

/**
 * Hook runs inside single-course/course-modules.php
 *
 * It runs before the modules are shown. This hook fires on the single course page. It will show
 * irrespective of irrespective the course has any modules or not.
 *
 * @since 1.8.0
 *
 */
do_action('sensei_single_course_modules_before' );

?>

<?php if ( sensei_have_modules() ) : ?>

	<?php while ( sensei_have_modules() ) : sensei_setup_module(); ?>
		<?php if ( sensei_module_has_lessons() ) : ?>

			<?php

			/**
			 * Hook runs inside single-course/course-modules.php
			 *
			 * It runs inside the if statement after the article tag opens just before the modules are shown. This hook will NOT fire if there
			 * are no modules to show.
			 *
			 * @since 1.9.0
			 * @since 1.9.7 Added the module ID to the parameters.
			 *
			 * @hooked Sensei()->modules->course_modules_title - 20
			 *
			 * @param int sensei_get_the_module_id() Module ID.
			 */
			do_action( 'css_single_course_modules_outside_before', sensei_get_the_module_id() );

			?>

			<article class="module">

				<?php

				/**
				 * Hook runs inside single-course/course-modules.php
				 *
				 * It runs inside the if statement after the article tag opens just before the modules are shown. This hook will NOT fire if there
				 * are no modules to show.
				 *
				 * @since 1.9.0
				 * @since 1.9.7 Added the module ID to the parameters.
				 *
				 * @hooked Sensei()->modules->course_modules_title - 20
				 *
				 * @param int sensei_get_the_module_id() Module ID.
				 */
				do_action( 'sensei_single_course_modules_inside_before', sensei_get_the_module_id() );

				?>

				<header>

					<h2>

						<?php if ( css_is_the_module_access_restricted() ) : ?>

							<?php sensei_the_module_title(); ?>

						<?php else : ?>

							<a href="<?php sensei_the_module_permalink(); ?>" title="<?php sensei_the_module_title_attribute(); ?>">

								<?php sensei_the_module_title(); ?>

							</a>

						<?php endif; ?>

					</h2>

				</header>

				<section class="entry">

					<p class="module-description"><?php sensei_the_module_description(); ?></p>

					<?php
					$module_status = sensei_get_the_module_status();

					if ( ! $module_status ) :
						?>

						<?php css_the_module_date_status(); ?>

					<?php else : // Module date comes inside the module status. ?>

						<?php echo $module_status; ?>

					<?php endif; ?>

					<?php if ( ! css_is_the_module_access_restricted() ) : ?>

						<section class="module-lessons">

							<header>

								<h3><?php _e( 'Lessons', 'woothemes-sensei' ); ?></h3>

							</header>

							<ul class="lessons-list" >

							<?php while ( sensei_module_has_lessons() ) : the_post(); ?>

								<?php do_action( 'css_single_course_module_lessons_before', get_the_ID() ); ?>

								<li class="<?php sensei_the_lesson_status_class(); ?>">

									<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >

										<?php the_title(); ?>

										<?php
										$course_id = Sensei()->lesson->get_course_id( get_the_ID() );

										if ( Sensei_Utils::is_preview_lesson( get_the_ID() ) && ! Sensei_Utils::user_started_course( $course_id, get_current_user_id() )  ) { ?>

											<span class="preview-label"><?php _e( 'Free Preview', 'woothemes-sensei' ); ?></span>

										<?php } ?>

									</a>

								</li>

								<?php do_action( 'css_single_course_module_lessons_after', get_the_ID() ); ?>

							<?php endwhile; ?>

							</ul>

						</section><!-- .module-lessons -->

					<?php endif; ?>

				</section>

				<?php

				/**
				 * Hook runs inside single-course/course-modules.php
				 *
				 * It runs inside the if statement before the closing article tag directly after the modules were shown.
				 * This hook will not trigger if there are no modules to show.
				 *
				 * @since 1.9.0
				 * @since 1.9.7 Added the module ID to the parameters.
				 *
				 * @param int sensei_get_the_module_id() Module ID.
				 */
				do_action( 'sensei_single_course_modules_inside_after', sensei_get_the_module_id() );

				?>

			</article>

			<?php

			/**
			 * Hook runs inside single-course/course-modules.php
			 *
			 * It runs inside the if statement after the article tag opens just before the modules are shown. This hook will NOT fire if there
			 * are no modules to show.
			 *
			 * @since 1.9.0
			 * @since 1.9.7 Added the module ID to the parameters.
			 *
			 * @hooked Sensei()->modules->course_modules_title - 20
			 *
			 * @param int sensei_get_the_module_id() Module ID.
			 */
			do_action( 'css_single_course_modules_outside_after', sensei_get_the_module_id() );

			?>

		<?php endif; //sensei_module_has_lessons  ?>

	<?php endwhile; // sensei_have_modules ?>

<?php endif; // sensei_have_modules ?>

<?php

/**
 * Hook runs inside single-course/course-modules.php
 *
 * It runs after the modules are shown. This hook fires on the single course page,but only if the course has modules.
 *
 * @since 1.8.0
 */
do_action( 'sensei_single_course_modules_after' );
