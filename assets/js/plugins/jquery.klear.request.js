;(function($) {
	
	$.klear = $.klear || {};
	
	
	
	
	$.klear.request = function(params,successCallback,errorCallback,context) {
		
		
		var options = {
			file : 'index',
		};
		
		$.extend(options,params);

        
        var request_baseurl = '';
        
        var _parseResponse = function _parseResponse(response) {
        	
        	switch(response.responseType) {
        		case 'dispatch':
        			return _parseDispatchResponse(response);
        		case 'simple':
        			return _parseSimpleResponse(response);
        		default:
        			errorCallback.apply(context,["Unknown response type"]);
        		break;
        	}
        	
        };
        
        var _parseSimpleResponse = function _parseSimpleResponse(response) {
        	
        	if (!response.data) {
        		errorCallback.apply(context,["Unknown response format in Simple Response"]);
        		return;
        	}
        	successCallback.apply(context,[response.data]);
        	return;        	
        };
        
        var _parseDispatchResponse = function _parseDispatchResponse(response) {
        	
        	var responseCheck = ['baseurl', 'templates', 'scripts', 'css', 'data', 'plugin'];
    		for(var i=0; i<responseCheck.length; i++) {
    			if (response[responseCheck[i]] == undefined) {
    				errorCallback.apply(context,["Module registration error"]);
    				return;
    			}
   			}
    								
    		request_baseurl = response.baseurl;
    		
    		$.when(
    				_loadTemplates(response.templates),
    				_loadCss(response.css),
    				_loadScripts(response.scripts)
    				
    		).done( function(tmplReturn,scriptsReturn,cssReturn) {

    			var tryOuts = 0;
    			(function tryAgain() {
    					
    				if (typeof $.fn[response.plugin] == 'function' ) {
    					successCallback.apply(context,[response.plugin,response.data]);
    					return;
   					} else {
   						if (++tryOuts == 5) {
   							errorCallback.apply(context,['Module resistration error']);
   							return;
    					} else {
    						window.setTimeout(tryAgain,50);
    					}
    				}
    			})();
    		
    		}).fail( function( data ){
    			
    			errorCallback.apply(context,['Module resistration error',data]);
    		});	
        };
        
		var _errorResponse = function _errorResponse() {
			errorCallback.apply(context,arguments);
		};
		
		
		var _loadTemplates = function(templates) {
			var dfr = $.Deferred();
			var total = 0;
			for(var iden in templates) total++;
			var done = 0;
			var successCallback = function() {
				total--;
				done++;
				if (total == 0) {
					dfr.resolve(done);		
				}									
			};
			
			$.each(templates,function(tmplIden,tmplSrc) {
				
				if (undefined !== $.template[tmplIden]) {
					successCallback();
					return;
				}
				
				$.ajax({
					url: request_baseurl + tmplSrc,
					dataType:'text',
					type : 'get',
					success: function(r) {
						$.template(tmplIden, r);
						successCallback();
					},
					error : function(r) {
						dfr.reject($.translate("Error downloading template [%s].", tmplIden)); 
					}
				}); 
			});
			return dfr.promise();							
		};
		
		var _loadScripts = function(scripts) {
			var dfr = $.Deferred();
			var total = 0;
			for(var iden in scripts) total++;
			var done = 0;
			var isAjax = false;
			var _self = this;
			$.each(scripts, function(iden, _script) {
				if ($.klear.loadedScripts[iden]) {
					total--;
					return;
				}
				isAjax = true;
				$.ajax({
            			url: request_baseurl + _script,
            			dataType:'script',
            			type : 'get',
            			async: false,
            			success: function() {
            				$.klear.loadedScripts[iden] = true;
            				total--;
							done++;
							if (total == 0) {
								dfr.resolve(done);
							}
                        },
                        error : function(r) {
                            dfr.reject("Error downloading script ["+_script+"]"); 
            			}
				 }); 
			});
			if (!isAjax) {
				return dfr.resolve(0);
			} else {
				return dfr.promise();
			}
		};
		
		var _loadCss = function(css) {
			var total = $(css).length;
			var dfr = $.Deferred();
			for(var iden in css) {
				$.getStylesheet(request_baseurl + css[iden],iden);
				$("#" + iden).on("load",function() {
					total--;
					if (total == 0) {
						dfr.resolve(true);		
					}
				});
			}
			dfr.promise(true);							
		};
		
		$.ajax({
           	url:$.klear.baseurl + 'index/dispatch',
           	dataType:'json',
           	context : this,
           	data : options,
           	type : 'get',
           	success: _parseResponse,
           	error: _errorResponse
        });
		
		
	};
		
	
	
	
})(jQuery);