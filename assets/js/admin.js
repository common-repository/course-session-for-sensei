jQuery( document ).ready( function ( $ ) {
  cssDatepicker();
});

cssDatepicker = function() {
  jQuery('.css-datepicker').datepicker({
    dateFormat : 'yy-mm-dd',
    // http://stackoverflow.com/questions/17016598/ddg#17018763
    onSelect : function () {
      if ( this.id !== 'term-start_date' ) {
        return;
      }

      var dateEnd = jQuery('#term-end_date');

      // var startDate = jQuery(this).datepicker('getDate');
      var endDate = dateEnd.datepicker('getDate');

      // Add 30 days to selected date.
      // startDate.setDate(startDate.getDate() + 30);

      // sets dt2 maxDate to the last day of 30 days window
      // dateEnd.datepicker('option', 'maxDate', startDate);

      var minDate = jQuery(this).datepicker('getDate');

      if ( endDate < minDate ) {
        // minDate of dateEnd datepicker = dt1 selected day.
        dateEnd.datepicker('setDate', minDate);
      }

      // first day which can be selected in dt2 is selected date in dt1
      dateEnd.datepicker('option', 'minDate', minDate);

      //same for dt1
      // jQuery(this).datepicker('option', 'minDate', minDate);
    }
  });
};
