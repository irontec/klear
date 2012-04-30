;(function($) {
	$.extend({
		translationRegister: function(_str, _namespace) {
			$.klear.request(
					{
						controller: 'index',
						action: 'registertranslation',
						namespace: 'javascript/'+_namespace,
						str: _str
					},
					function(){},
					function(){}
			);
			
		}
	});

})(jQuery);
