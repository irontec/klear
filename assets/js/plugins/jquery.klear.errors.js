(function($) {
	
	$.klear = $.klear || {};

	$.klear.errors = {};
	
	$.klear.addErrors = function(errors) {
		
		$.extend($.klear.errors, errors);
	};

	
	$.klear.fetchErrorByCode = function(code) {
		
		
		if (typeof $.klear.errors[code] != 'undefined') {
			return $.klear.errors[code];
		}
		
		return false;		
		
	};

})(jQuery);
