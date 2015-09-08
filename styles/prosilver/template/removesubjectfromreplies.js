(function($) {  // Avoid conflicts with other libraries

'use strict';

$(function() {
	// hide subject from replies
	$('.postbody h3:not(.first)').hide();

	// hide subject from quickreply
	$('#qr_postform .fields1 > dl').hide();
});

})(jQuery); // Avoid conflicts with other libraries
