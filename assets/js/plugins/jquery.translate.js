;(function($) {
	$.extend({
		translate: function(_text) {
			var _args = arguments;

			var _length = arguments.length;

			if (_length<=0) {
				return '0';
			}

			var _namespace = null;

			if (_length>1) {
				if (typeof arguments[_length-1] == 'object') {
					_namespace = arguments[_length-1][0];
				}
			}

			var _cleanText = _text.replace(/'/g, '').replace(/"/g, '');

			if ($.translations[_cleanText]==undefined) {
				if ($.translationRegister!=undefined)
					$.translationRegister(_text, _namespace);
			} else {
				_text = $.translations[_cleanText];
			}

			var _ll = _namespace==null? _length: _length-1;

			for (var i=1; i<_ll; i++) {
				if (undefined != _args[i]) {
					_text = _text.replace(/%s/, _args[i]);
				}
			}

			return _text;
		},
		addTranslation: function(obj){
			$.extend($.translations, obj);
		},
		translations: {}
	});

})(jQuery);
