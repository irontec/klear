;(function($) {
	$.extend({
		translate: function() {
			var _args = arguments;
			
			var _length = arguments.length;
			
			if (_length<=0) {
				return '0';
			}
			
			var _str = arguments[0].toString();
			
			for (var i=1; i<_length; i++) {
				if (undefined != _args[i]) {
					_str = _str.replace(/%s/, _args[i]);
				}
			} 
			
			return _str;
		}
	});

})(jQuery);
