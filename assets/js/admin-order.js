/**
 * Admin Order Javascript
 *
 * @since 1.0.0
 * @copyright WooCommerce
 */

/**
 * Get the url query parameter by name
 *
 * Credit: http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
 *
 * @param name
 * @returns {string}
 */
function getParameterByName(name) {
  name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
  return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

jQuery( document ).ready( function ( e ) {

  /**
   * Add select to the course-sessions select boxes
   */
  // course-session order screen
  jQuery( '#course-session-order-course' ).select2({width:"resolve"});
  // lesson edit screen course-sessions selection
  jQuery( 'select#lesson-course-session-options' ).select2({width:"resolve"});

  /**
   * Sortable functionality
   */
  jQuery( '.sortable-course-session-list' ).sortable({

    // Exclude items (but keep as drop targets).
    cancel: ".ui-state-disabled",
  });
  jQuery( '.sortable-tab-list' ).disableSelection();

  jQuery( '.sortable-course-session-list' ).bind( 'sortstop', function ( e, ui ) {
    var orderString = '';

    jQuery( this ).find( 'li' ).each( function ( i, e ) {
      if ( i > 0 ) { orderString += ','; }
      orderString += jQuery( this ).find( 'span' ).attr( 'rel' );

      jQuery( this ).removeClass( 'alternate' );
      jQuery( this ).removeClass( 'first' );
      jQuery( this ).removeClass( 'last' );
      jQuery( this ).removeClass( 'before-lesson' );
      if( i == 0 ) {
        jQuery( this ).addClass( 'first alternate' );
      } else {
        var r = ( i % 2 );
        if( 0 == r ) {
          jQuery( this ).addClass( 'alternate' );
        }
      }

      // Add .before-lesson CSS class to .course-session where appropriate.
      if ( jQuery( this ).hasClass( 'course-session' ) ) {

        var nextEl = jQuery( this ).next( 'li' );

        while ( nextEl.hasClass( 'course-session' ) ) {

          nextEl = nextEl.next( 'li' );
        }

        if ( nextEl.hasClass( 'lesson' ) ) {

          jQuery( this ).addClass( 'before-lesson' );
        }
      }

    });

    jQuery( 'input[name="course-session-order"]' ).attr( 'value', orderString );
  });


  /**
   * Searching for courses on the course-sessions admin edit screen
   */
  /*jQuery('select.ajax_chosen_select_courses').select2({
    minimumInputLength: 2,
    placeholder: course-sessionsAdmin.selectplaceholder,
    width:'300px',
    multiple: true,
    ajax: {
      // in wp-admin ajaxurl is supplied by WordPress and is available globaly
      url: ajaxurl,
      dataType: 'json',
      cache: true,
      data: function (params) { // page is the one-based page number tracked by Select2
        return {
          term: params.term, //search term
          page: params.page || 1,
          action: 'sensei_json_search_courses',
          security: 	course-sessionsAdmin.search_courses_nonce,
          default: ''
        };
      },
      processResults: function (courses, page) {

        var validCourses = [];
        jQuery.each( courses, function (i, val) {
          if( ! jQuery.isEmptyObject( val )  ){
            validcourse = { id: i , text: val  };
            validCourses.push( validcourse );
          }
        });
        // wrap the users inside results for select 2 usage
        return {
          results: validCourses,
          page: page
        };
      }
    }
  }); // end select2*/



  /*jQuery( '#sensei-course-session-add-toggle').on( 'click', function( e ){

    var hidden = 'wp-hidden-child';
    var addBlock = jQuery(this).parent().next( 'p#sensei-course-session-add');
    var courseSessionInput = addBlock.children('#newcourse-session');
    if( addBlock.hasClass( hidden ) ){

      addBlock.removeClass(hidden);
      courseSessionInput.val('');
      courseSessionInput.focus();
      return;
    }else{

      addBlock.addClass(hidden);

    }
  });*/

  /*jQuery( '#sensei-course-session-add-submit').on( 'click', function( e ){

    // setup the fields
    var courseId = getParameterByName('post');
    var courseSessionInput = jQuery(this).parent().children( '#newcourse-session' );
    var nonceField = jQuery(this).parent().children( '#add_course-session_nonce' );
    var termListContainer = jQuery( '#course-session_course_mb #taxonomy-course-session #course-session-all ul#course-sessionchecklist' );

    // get the new term value
    var newTerm = courseSessionInput.val();
    var security = nonceField.val();

    if( _.isEmpty( newTerm ) || _.isEmpty( security ) ){

      courseSessionInput.focus();
      return;
    }

    var newTermData = {
      newTerm : newTerm,
      security: security,
      action: 'sensei_add_new_course-session_term',
      course_id: courseId
    };

    jQuery.post( ajaxurl, newTermData, function(response) {

      if( response.success ){

        var termId = response.data.termId;
        var termName = response.data.termName;

        // make sure the return values are valid
        if( ! ( parseInt( termId ) > 0 ) || _.isEmpty( termName ) ){
          courseSessionInput.focus();
          return;
        }

        // setup the new list item
        var li = '<li id="course-session-' + termId + '">';
        li += '<label class="selectit">';
        li += '<input value="' + termId +  '" type="checkbox" checked="checked" name="tax_input[course-session][]" id="in-course-session-' + termId + '">';
        li += termName;
        li += '</label></li>';

        // ad the list item
        termListContainer.prepend( li );

        // clear the input
        courseSessionInput.val('');
        courseSessionInput.focus();

        return;

      }else if( typeof response.data.errors != 'undefined'
        &&  typeof response.data.errors.term_exists != 'undefined' ){

        var termId = response.data.term.id;

        // find term with id and just make sure it is
        var termCheckBox = termListContainer.find( '#course-session-' + termId  + ' input');

        // checked also move the focus of the user there
        termCheckBox.prop( 'checked', 'checked' );

        // then empty the field that was added
        termCheckBox.focus();
        courseSessionInput.val('');

      }else{

        console.log( response );

      }
    });
  });*/
});
