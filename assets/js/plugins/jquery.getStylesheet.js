;(function($) {
	$.extend({

	getStylesheet: function(url, id, media) {
		var stylesheets = $("link[type='text/css']", jQuery("head"));
		var id = id || "rand" + (Math.floor(Math.random()*10000));
		var el = jQuery('link#'+id, jQuery('head'));
		if (el.length>0) {
			el.attr('href', url);
		} else {
			jQuery('<link />',{
		        href: url,
		        media: media || 'screen',
		        type: 'text/css',
		        rel: 'stylesheet',
		        id: id
		    }).appendTo('head');
		}		
	},
	removeStylesheet: function(id) {
		try {
			$('link#'+id).remove();
		} catch(e) {}
	}
});

})(jQuery);