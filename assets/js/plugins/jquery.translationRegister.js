;(function reLoader() {
	
	if ((typeof(jQuery) == 'undefined') || (typeof(jQuery.klear) == 'undefined') ) {
		setTimeout(reLoader,100);
		return;
	}
	
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

})();
