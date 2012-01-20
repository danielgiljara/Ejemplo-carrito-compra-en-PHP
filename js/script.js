/**
 * JavaScript feature for cart example.
 *
 * @author Daniel Gil Jara (danielgiljara@gmail.com)
 */

jQuery(function($){

  // Hide status messages after a period.
  setTimeout(function() {
    $('#messages div.status').slideUp('slow', function() {
      $(this).remove();
    });
  }, 4000);

  // Click update order button when 'enter' key is pressed
  // on a quantity field at checkout order form.
  $('td.quantity input').keydown(function(event) {
    if (event.keyCode == '13') {
      event.preventDefault();
      $('#update-order').click();
    }
  });

});
