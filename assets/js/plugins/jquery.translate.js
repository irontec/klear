;(function($) {
	$.extend({
		translate: function() {
			var _args = arguments;
			
			var _length = arguments.length;
			
			if (_length<=0) {
				return '0';
			}
			
			var _str = arguments[0].toString();
			
			var _namespace = null;
			
			if (_length>1) {
				if (typeof arguments[_length-1] == 'object') {
					_namespace = arguments[_length-1][0];
				}
			}

			var _strClean = _str.replace(/'/g, '').replace(/"/g, '');
			
			if ($.translations[_strClean]==undefined) {
				if ($.translationRegister!=undefined)
					$.translationRegister(_str, _namespace);
			} else {
				_str = $.translations[_strClean];
			}
			
			var _ll = _namespace==null? _length: _length-1; 
			
			for (var i=1; i<_ll; i++) {
				if (undefined != _args[i]) {
					_str = _str.replace(/%s/, _args[i]);
				}
			} 
			
			return _str;
		},
		addTranslation: function(obj){
			$.extend($.translations, obj);
		},
		translations: {}
	});

})(jQuery);
