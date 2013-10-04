;(function($) {
    $.extend({

        getStylesheet : function(url, id, media) {
            var stylesheets = $("link[type='text/css']", $("head:first"));

            var id = id || "rand" + (Math.floor(Math.random() * 10000));

            var el = $('link#' + id, $('head:first'));
            if (el.length > 0) {
                el.attr('href', url);
            } else {
                $('<link />', {
                    href : url,
                    media : media || 'screen',
                    type : 'text/css',
                    rel : 'stylesheet',
                    id : id
                }).appendTo('head:first');
            }
        },
        removeStylesheet : function(id) {
            try {
                $('link#' + id).remove();
            } catch (e) {
            }
        }
    });

})(jQuery);