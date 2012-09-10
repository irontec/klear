var console = window.console || { log : function() {}};

(function() {

	var _base = document.getElementsByTagName('base')[0].getAttribute('href');

	
	var _loadIndicator = document.createElement("div"); 
	var _loader = document.createElement("div");
	_loader.setAttribute("class","initialLoader");
	_loader.appendChild(_loadIndicator);
	
	_loader.curPercent = 0;
	_loader.target = 0;
	_loader.total = 0;
	
	(function lazyLoader() {
		if (!document.getElementsByTagName('body')[0]) {
			setTimeout(lazyLoader,10);
			return;
		}
	
		document.getElementsByTagName('body')[0].appendChild(_loader);
		
		_loader.interval = setInterval(function() {
			
			if (_loader.total == 0) {
				return;
			}
			
			var _percentTarget = parseInt((100*_loader.target)/_loader.total);
			_loader.firstChild.innerHTML = _loader.curPercent + '%';
			
			if (_percentTarget >= _loader.curPercent) {
				_loader.curPercent++;
				
				_loader.firstChild.style.width = _loader.curPercent + '%';
				
				
				if (_loader.curPercent == 100) {
					clearInterval(_loader.interval);
					// Ya tenemos jQuery
					$(_loader).fadeOut("slow",function() {
						$(this).remove();
					});
					
				}
			}
		},5);
	
	})();
	
	
	
	
	
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

	// El total de cargas serán los "base" + los 4 principales
	_loader.total = _baseScripts.length + 4;
	
	yepnope.addPrefix('local', function(resourceObj) {
	    resourceObj.url =  _base + resourceObj.url;
	    return resourceObj;
	});

	yepnope([
	  {
		  load: {
			  'jquery.min.js': 'timeout=1000!//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
			  'jquery.tmpl.min.js': 'timeout=1000!//ajax.aspnetcdn.com/ajax/jquery.templates/beta1/jquery.tmpl.js',
			  'jquery-ui.min.js': 'timeout=1000!//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js',
			  'jquery-ui-i18n.min.js': 'timeout=1000!//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/i18n/jquery-ui-i18n.min.js'
		  },
		  complete: function() {
			  if ( (!window.jQuery) || (!window.jQuery.ui) || (!window.jQuery.tmpl) ) {
				  _scripts.push('base!js/libs/jquery.min.js');
				  _scripts.push('base!js/libs/jquery.tmpl.min.js');
				  _scripts.push('base!js/libs/jquery-ui.min.js');
				  _scripts.push('base!js/libs/jquery-ui-i18n.min.js');
			  } else {
				  _loader.target += 4;
			  }
			  
			  yepnope([{
				  load:_scripts.concat(_baseScripts),
				  callback : function() {
					  _loader.target++;
				  }
			  }]);
		  }
	}
	]);
	
})();
