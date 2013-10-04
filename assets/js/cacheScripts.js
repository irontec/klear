/*
 * Fichero compilado en klear.compiled.js, que se encarga de descargar en una petición los templates principales de klear y cachearlos.
 * Evita 4 peticiones HTTP
 */
;(function() {

    $.klear = $.klear || {};
    $.klear.baseurl = $.klear.baseurl || $("base").attr("href");
    $.klear.loadedTemplates = $.klear.loadedTemplates || {};

    $.ajax({
        url : $.klear.baseurl + '../klear/template/cache',
        dataType:'json',
        success: function(response) {

            if (!response.templates) {
                return;
            }

            for (var tmplIden in response.templates) {
                $.template(tmplIden, response.templates[tmplIden]);
                $.klear.loadedTemplates[tmplIden] = true;
            }
        }
    });
})();