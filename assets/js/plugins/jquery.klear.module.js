;(function($) {
	
	$.loadedScripts = {};
		
	$.widget("klear.module", {
		options: {
			ui: null,
			container: null,
			mainEnl : null,
        	title : null,
            file : null,
            panel : null,
            tabIndex : null,
            baseurl : null,
            menuLink : null,
            screen : null,
            dispatchOptions : {},
            loadingSelector : null
		},
		
		_create: function(){
			// remember this instance
			$.klear.module.instances.push(this.element);
		},
		_getOtherInstances: function(){
			
			var element = this.element;

			return $.grep($.klear.module.instances, function(el){
				return el !== element;
			});
		},
		
		_init: function() {
			this.options.mainEnl = $("a:first",this.element);
			this.options.title = $("a:first",this.element).html();
			this.options.file = $("a:first",this.element).attr("href").replace(/\#tabs\-([^\_]+).*/,'$1');
			
			this.options.panel = this.options.ui.panel;
			this.options.tabIndex = this.options.ui.index;
			
			if ($('#target-' + this.options.file).length > 0) {
				this.options.menuLink = $('#target-' + this.options.file);
			}
			this.setAsloading();
			this._initTab();
		},
		
		reload : function() {
			this._initTab();
		},
		_initTab : function() {

			if (!this.options.menuLink) return;

			var _self = this;
		    	
			if ($("span.ui-silk",this.options.menuLink).length > 0) {
				var _mainEnl = this.options.mainEnl;
				var curClasses = $("span.ui-silk",this.options.menuLink.parent()).attr("class").split(' ');
			} else return;
			
			var $_icon = $("span.ui-silk",_self.element);

			if (curClasses) {
				$_icon.addClass(curClasses[(curClasses.length-1)])
			}
				
			$_icon.on('click',function() {
				$(_mainEnl).trigger("click");
			});
		
				
		},
		_setOption: function(key, value){
			
			this.options[key] = value;

			switch(key){
				case "title":
					this.options.mainEnl.html(value);
				break;
			}
		},
		destroy: function(){
			
			
			$(this.options.menuLink).removeClass("ui-state-highlight");
			
			// remove this instance from $.ui.mywidget.instances
			var element = this.element,
			position = $.inArray(element, $.klear.module.instances);
			// if this instance was found, splice it off
			
			if(position > -1){
				$.klear.module.instances.splice(position, 1);
			}
			

			// call the original destroy method since we overwrote it
			$.Widget.prototype.destroy.call( this );
		},
		_loadTemplates : function(templates) {
			
			var dfr = $.Deferred();
		
			var total = $(templates).length;
			var done = 0;
		
			var successCallback = function() {
				total--;
				done++;
				if (total == 0) {
					dfr.resolve(done);		
				}									
			};
		
			for (var tmplIden in templates) {
				var tmplSrc = templates[tmplIden];
				
				if (undefined !== $.template[tmplIden]) {
					successCallback();
					return;
				}

				$.ajax({
					url: this.options.baseurl + tmplSrc,
					dataType:'text',
					type : 'get',
					success: function(r) {
						$.template(tmplIden, r);
						successCallback();
					},
					error : function(r) {
						dfr.reject($.translate("Error descargando el template [%s]", tmplIden)); 
					}
				}); 
			}
			
			return dfr.promise();							
		},
		
		_loadScripts : function(scripts) {
			
			
			
			
			
			var dfr = $.Deferred();
			var total = 0;
			for(var iden in scripts) total++;
			var done = 0;
			var isAjax = false;

			var _self = this;

			$.each(scripts, function(iden, _script) {
				
			
			//for(var iden in scripts) {
				
				if ($.loadedScripts[iden]) {
					total--;
					return;
				}
				
				isAjax = true;

				//var _script = scripts[iden];
								
				$.ajax({
            			url: _self.options.baseurl + _script,
            			dataType:'script',
            			type : 'get',
            			async: false,
            			success: function() {
            				console.log(_script, iden);
            				$.loadedScripts[iden] = true;
            				total--;
							done++;
							console.log(total);
							if (total == 0) {
								
							//window.setTimeout(function(){
								
								dfr.resolve(done);
								
							//}, 1000);
								
							}
							
							
							
							
                        },
                        error : function(r) {
                            dfr.reject("Error descargando el script ["+_script+"]"); 
            			}
				 }); 
			 // }
			
			});
			
			if (!isAjax) {
				return dfr.resolve(0);
			} else {
				return dfr.promise();
			}
			
			
			
			
		},
		_loadCss : function(css) {
			
			var total = $(css).length;
			var dfr = $.Deferred();
			
			for(var iden in css) {
				$.getStylesheet(this.options.baseurl + css[iden],iden);
				$("#" + iden).on("load",function() {
					total--;
					if (total == 0) {
						dfr.resolve(true);		
					}
				});
			}
			
			dfr.promise(true);							
		},
		_parseDispatchResponse : function(response) {

			if ( (!response.baseurl) || (!response.templates) || (!response.scripts) || (!response.css) || (!response.data) || (!response.plugin) ) {
				alert("Formato de respuesta incorrecta.<br />Consulte con su administrador.");
				return;							
			}
			
					
			this.options.baseurl = response.baseurl;
			var self = this;
			
			$.when(
				this._loadScripts(response.scripts),
				this._loadTemplates(response.templates),
				this._loadCss(response.css)
			).done( function(tmplReturn,scriptsReturn,cssReturn) {
				
				console.log(response.plugin, $.fn[response.plugin]);
				
				window.setTimeout(function(){
					
					if (typeof $.fn[response.plugin] == 'function' ) {
						$(self.element)[response.plugin]({
							data: response.data
						});
					} else {
						
					}
					self.setAsloaded();
					
				}, 10);
				
				
				
				
			}).fail( function( data ){
				console.log(data);
				
		        self.dialog("Error registrando el módulo");				                    
		    });	
			
		},
		getPanel : function() {
			return this.options.panel;
		},
		getContainer : function() {
			return this.options.container;			
		},
		dispatch : function() {
			var dispatchData = {};
			dispatchData.file = this.options.file;
			
			$.extend(dispatchData,this.options.dispatchOptions);
			
            $.ajax({
               	url:$.baseurl + 'index/dispatch',
               	dataType:'json',
               	context : this,
               	data : dispatchData,
               	type : 'get',
               	success: this._parseDispatchResponse
            });
		},
		dialog : function(msg) {
			
			var _dialog = $("<div title='Aviso'>"+msg+"</div>");
			var self = this;
			_dialog.dialog({
				open : function(event,ui) {
					
				},
				position : 'center',
				draggable : false,
				resizable : false				
			});
			
		},
		highlightOn: function() {
			$(this.element).addClass("ui-state-highlight");
		},
		highlightOff : function() {
			$(this.element).removeClass("ui-state-highlight");
		},
		_loading : false,
		setAsloading : function() {
			this._loading = true;
			this.updateLoader();
		},
		setAsloaded : function() {
			this._loading = false;
			this.updateLoader();
		},
		updateLoader : function() {
			
			var _loadingItem = $(this.options.loadingSelector);
			
			if (this._loading) {
				
				_loadingItem.hide().appendTo(this.options.panel).css("z-index",'10000').fadeIn();
				$(this.options.ui.tab).addClass("ui-state-disabled");
			
			} else {
				$(this.options.ui.tab).removeClass("ui-state-disabled");
				_loadingItem.fadeOut(function() {
					$(this).appendTo(document.body);
					
				});				
			}
			
		}		

	});

	
	$.extend($.klear.module, {
		instances: []
	});

	
	$.widget.bridge("klearModule", $.klear.module);
	
})(jQuery);
