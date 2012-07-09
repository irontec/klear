(function() {
	
	var _base = document.getElementsByTagName('base')[0].getAttribute('href');

	var _baseScripts = ['base!js/plugins/jquery.cookie.js',
	 'base!js/plugins/jquery.scrollabletab.js',
 	 'base!js/plugins/jquery.ui.tooltip.js',
 	 'base!js/plugins/jquery.ui.combobox.js',
 	 'base!js/scripts/spin.min.js',
 	 'base!js/plugins/jquery.getStylesheet.js',
 	 'base!js/plugins/jquery.translate.js',
 	 'base!js/translation/jquery.klear.translation.js',
 	 'base!../default/js/translation/jquery.default.translation.js',
 	 'base!js/plugins/jquery.klear.request.js',
 	 'base!js/plugins/jquery.klear.module.js',
 	 'base!js/plugins/jquery.klear.module.dialog.js',
 	 'base!js/plugins/jquery.klear.errors.js',
 	 'base!js/navigation.js'];
	
	var _baseScripts2 = [
	                    'base!js/klear.compiled.js',
	                    'base!js/translation/jquery.klear.translation.js',
	                    'base!../default/js/translation/jquery.default.translation.js',
	                	'base!js/navigation.js'	                	 
	                    ];
	var _scripts = [];
	
	yepnope.addPrefix('local', function(resourceObj) {  
	    resourceObj.url =  _base + resourceObj.url;  
	    return resourceObj;  
	});  
	
	yepnope([
	  {
		  load: {
			  'jquery.min.js': 'timeout=1000!//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
			  'jquery.tmpl.min.js': 'timeout=1000!//ajax.aspnetcdn.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js',
			  'jquery-ui.min.js': 'timeout=1000!//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js',
			  'jquery-ui-i18n.min.js': 'timeout=1000!//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/i18n/jquery-ui-i18n.min.js'
		  },
		  complete: function() {
			  if (!window.jQuery) {
				  _scripts.push('base!js/libs/jquery.min.js');
			  }
			  
			  if (!window.jQuery.template) {
				  _scripts.push('base!js/libs/jquery.tmpl.min.js');
			  }
			  
			  if (!window.jQuery.ui) {
				  _scripts.push('base!js/libs/jquery-ui.min.js');
				  _scripts.push('base!js/libs/jquery-ui-i18n.min.js');
			  }
		  }
	},
	{
		load:_scripts.concat(_baseScripts)
	}]);
})();
