<?php
/**
 * Course Session For Sensei Admin
 * Where we duplicate courses.
 *
 * Inspired by the Sensei LMS Admin.
 * @see class-sensei-admin.php
 *
 * @package Course Session For Sensei
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course Session For Sensei Admin class
 */
class Course_Session_For_Sensei_Admin {
	/**
	 * Duplicate course
	 *
	 * @param int $post_id Post ID.
	 * @param boolean $with_lessons Include lessons or not.
	 *
	 * @return int New post ID.
	 */
	public function duplicate_course( $post_id, $with_lessons = true ) {

		// Duplicate course.
		$post = get_post( $post_id );

		if ( ! is_wp_error( $post ) ) {
			$new_post = $this->duplicate_post( $post );

			if ( $with_lessons ) {
				$this->duplicate_course_lessons( $post_id, $new_post->ID );
			}

			if ( $new_post && ! is_wp_error( $new_post ) ) {

				return $new_post->ID;
			}
		}

		return $post_id;
	}


	/**
	 * Duplicate post
	 * We copied the original Sensei_Admin function here to access it the way we want.
	 * Private functions...
	 *
	 * @param  object  $post          Post to be duplicated.
	 * @param  string  $suffix        Suffix for duplicated post title.
	 * @param  boolean $ignore_course Ignore lesson course when duplicating.
	 * @return object                 Duplicate post object.
	 */
	private function duplicate_post( $post, $suffix = null, $ignore_course = false ) {

		$new_post = array();

		foreach ( $post as $k => $v ) {
			if ( ! in_array( $k, array( 'ID', 'post_status', 'post_date', 'post_date_gmt', 'post_name', 'post_modified', 'post_modified_gmt', 'guid', 'comment_count' ) ) ) {
				$new_post[ $k ] = $v;
			}
		}

		$new_post['post_title']       .= empty( $suffix ) ? __( '(Duplicate)', 'woothemes-sensei' ) : $suffix;
		$new_post['post_date']         = current_time( 'mysql' );
		$new_post['post_date_gmt']     = get_gmt_from_date( $new_post['post_date'] );
		$new_post['post_modified']     = $new_post['post_date'];
		$new_post['post_modified_gmt'] = $new_post['post_date_gmt'];

		switch ( $post->post_type ) {
			case 'course':
				$new_post['post_status'] = 'draft';
				break;
			case 'lesson':
				$new_post['post_status'] = 'draft';
				break;
			case 'quiz':
				$new_post['post_status'] = 'publish';
				break;
			case 'question':
				$new_post['post_status'] = 'publish';
				break;
		}

		// As per wp_update_post() we need to escape the data from the db.
		$new_post = wp_slash( $new_post );

		$new_post_id = wp_insert_post( $new_post );

		if ( ! is_wp_error( $new_post_id ) ) {

			$post_meta = get_post_custom( $post->ID );

			if ( $post_meta && count( $post_meta ) > 0 ) {

				$ignore_meta = array( '_quiz_lesson', '_quiz_id', '_lesson_quiz' );

				if ( $ignore_course ) {
					$ignore_meta[] = '_lesson_course';
				}

				foreach ( $post_meta as $key => $meta ) {
					foreach ( $meta as $value ) {
						$value = maybe_unserialize( $value );

						if ( ! in_array( $key, $ignore_meta ) ) {
							add_post_meta( $new_post_id, $key, $value );
						}
					}
				}
			}

			add_post_meta( $new_post_id, '_duplicate', $post->ID );

			$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );

			foreach ( $taxonomies as $slug => $tax ) {
				$terms = get_the_terms( $post->ID, $slug );

				if ( isset( $terms ) && is_array( $terms ) && 0 < count( $terms ) ) {
					foreach ( $terms as $term ) {
						wp_set_object_terms( $new_post_id, $term->term_id, $slug, true );
					}
				}
			}

			$new_post = get_post( $new_post_id );

			return $new_post;
		}

		return false;
	}

	/**
	 * Duplicate lessons inside a course
	 *
	 * @param  integer $old_course_id ID of original course.
	 * @param  integer $new_course_id ID of duplicated course.
	 *
	 * @return void
	 */
	private function duplicate_course_lessons( $old_course_id, $new_course_id ) {
		$lesson_args = array(
			'post_type' => 'lesson',
			'posts_per_page' => -1,
			'meta_key' => '_lesson_course',
			'meta_value' => $old_course_id,
			'suppress_filters' => 0,
		);

		$lessons = get_posts( $lesson_args );

		foreach ( $lessons as $lesson ) {
			$new_lesson = $this->duplicate_post( $lesson, '', true );
			add_post_meta( $new_lesson->ID, '_lesson_course', $new_course_id );

			$this->duplicate_lesson_quizzes( $lesson->ID, $new_lesson->ID );
		}
	}


	/**
	 * Duplicate quizzes inside lessons
	 *
	 * @param  integer $old_lesson_id ID of original lesson.
	 * @param  integer $new_lesson_id ID of duplicate lesson.
	 *
	 * @return void
	 */
	private function duplicate_lesson_quizzes( $old_lesson_id, $new_lesson_id ) {

		$old_quiz_id = Sensei()->lesson->lesson_quizzes( $old_lesson_id );
		$old_quiz_questions = Sensei()->lesson->lesson_quiz_questions( $old_quiz_id );

		// Duplicate the generic wp post information.
		$new_quiz = $this->duplicate_post( get_post( $old_quiz_id ), '' );

		// Update the new lesson data.
		add_post_meta( $new_lesson_id, '_lesson_quiz', $new_quiz->ID );

		//update the new quiz data
		add_post_meta( $new_quiz->ID, '_quiz_lesson', $new_lesson_id );
		wp_update_post(
			array(
				'ID' => $new_quiz->ID,
				'post_parent' => $new_lesson_id,
			)
		);

		foreach ( $old_quiz_questions as $question ) {

			// Copy the question order over to the new quiz.
			$old_question_order = get_post_meta( $question->ID, '_quiz_question_order' . $old_quiz_id, true );
			$new_question_order = str_ireplace( $old_quiz_id, $new_quiz->ID , $old_question_order );
			add_post_meta( $question->ID, '_quiz_question_order' . $new_quiz->ID, $new_question_order );

			// Add question to quiz.
			add_post_meta( $question->ID, '_quiz_id', $new_quiz->ID, false );
		}
	}
}
