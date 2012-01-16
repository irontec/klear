;(function($) {
	
	/*
	 * Klear Module definition
	 * 
	 * [jQuery UI]
	 * 
	 */ 
	
	/*
	 * Klear JQ Namespace
	 * 
	 */
	
	$.klear = $.klear || {};
	
	/*
	 * loaded Scripts Object
	 * 
	 */
	
	$.klear.loadedScripts = {};
	
	/*
	 * Klear Module definition
	 * 
	 * [jQuery UI Widget]
	 * 
	 */ 
	
	$.widget("klear.module", {
		
		/*
		 * Init / Create / Destroy Methods
		 * 
		 * [jQuery UI Widget] 
		 * 
		 */
		
		_create: function(){
			// remember this instance
			$.klear.module.instances.push(this.element);
		},
		
		_init: function() {
			// setting init options
			this._initOptions();
			
			this.setAsloading();
			
			this._initTab();
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
		
		/*
		 * Options Methods
		 * 
		 * [jQuery UI Widget]
		 * 
		 */
		
		_setOptions: function() {
			$.Widget.prototype._setOptions.apply(this, arguments);
		},
		
		_setOption: function(key, value) {
			$.Widget.prototype._setOption.apply(this, arguments); 
		},
		
		/*
		 * Helper Methods
		 * 
		 * [jQuery UI Widget] 
		 * 
		 */
		
		/*
		 * Helper method that 
		 * just calls option('disabled', false). 
		 * 
		 * Note that you'll want to handle this 
		 * by having an if (key === "disabled") block in your _setOption
		 */
		
		enable: function(key) {
			
		},
		
		disable: function(key) {
			
		},
		
		/*
		 * Callback Methods ???? //TODO ¿qué es esto?
		 * 
		 * [jQuery UI Widget]
		 * 
		 */
		
		_trigger: function() {
			
		},
		
		/*
		 * _getOtherInstances
		 * 
		 */
		
		_getOtherInstances: function(){
			var element = this.element;
			return $.grep($.klear.module.instances, function(el){
				return el !== element;
			});
		},
		
		/*
		 * Klear Module
		 * 
		 *   
		 * [jQuery UI Widget] 
		 * 
		 */		
		
		/*
		 * OPTIONS 
		 */
		
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
            loadingSelector : null,
            tabLock: false
		},
				
		/*
		 * _initOptions
		 */
		
		_initOptions: function() {
			this.options.mainEnl = $("a:first",this.element);
			this.options.title = $("a:first",this.element).html();
			this.options.file = $("a:first",this.element).attr("href").replace(/\#tabs\-([^\_]+).*/,'$1');
			this.options.panel = this.options.ui.panel;
			this.options.tabIndex = this.options.ui.index;
			if ($('#target-' + this.options.file).length > 0) {
				this.options.menuLink = $('#target-' + this.options.file);
			}
		},
		
		/*
		 * Init Tab
		 */
		
		_initTab : function() {

			if (!this.options.menuLink) return;

			var _self = this;
		    	
			if ($("span.ui-silk",this.options.menuLink).length > 0) {
				var _mainEnl = this.options.mainEnl;
				var curClasses = $("span.ui-silk",this.options.menuLink.parent()).attr("class").split(' ');
			} else return;
			
			var $_icon = $("span.ui-silk",_self.element);

			if (curClasses) {
				$_icon.addClass(curClasses[(curClasses.length-1)]);
			}
				
			$_icon.on('click',function() {
				$(_mainEnl).trigger("click");
			});
		
				
		},
		
		reload : function() {
			this._initTab();
		},

		/*
		 * Klear Module Dependency Loader
		 * 
		 * Templates
		 * Scripts
		 * Styles
		 * 
		 */
		
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
				if ($.klear.loadedScripts[iden]) {
					total--;
					return;
				}
				isAjax = true;
				$.ajax({
            			url: _self.options.baseurl + _script,
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
                            dfr.reject("Error descargando el script ["+_script+"]"); 
            			}
				 }); 
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
		
		/*
		 * Klear Module Dispatch Method
		 * 
		 * Module Launch Method
		 *  
		 */
		 
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
		
		_parseDispatchResponse : function(response) {
			var responseCheck = ['baseurl', 'templates', 'scripts', 'css', 'data', 'plugin'];
			for(var i=0; i<responseCheck.length; i++) {
				if (response[responseCheck[i]] == undefined) {
					
					/*this.dialogAlert(
						$.translate("Formato de respuesta incorrecta.<br />Consulte con su administrador."),
						{closeTab: true,tabInfo: true}
					);*/
					self.dialog("Error registrando el módulo");
					return;
				}
			}
								
			this.options.baseurl = response.baseurl;
			var self = this;
			
			$.when(
				this._loadTemplates(response.templates),
				this._loadCss(response.css),
				this._loadScripts(response.scripts)
			).done( function(tmplReturn,scriptsReturn,cssReturn) {

				// Javascript takes a bit executing. 
				// Wait and check until plugin is ready (3 tries)
				var tryOuts = 0;
				(function tryAgain() {
					
					if (typeof $.fn[response.plugin] == 'function' ) {
						self.setAsloaded();
						$(self.element)[response.plugin]({
							data: response.data
						});
						
					} else {
						
						console.log("trying..." + tryOuts);
						if (++tryOuts == 5) {
							// Mostrar error... algo pasa con el javascript :S
							alert("Error!");
						} else {
							window.setTimeout(tryAgain,50);
						}
					}
				})();
					
				
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
		
		/*
		 * close method
		 * 
		 * calls destroy and outer callback  
		 * 
		 */
		
		close: function(opts) {
			if (this.isLocked()) {
				this.showInlineWarn($.translate('This tab is locked'));
			} else {
				if (opts.callback && typeof opts.callback == "function") {
					this.destroy();
					opts.callback();
				}	
			}
		},
		
		/*
		 * TAB LOCK
		 */
		
		isLocked: function() {
			return this.options.tabLock;
		},
		
		lockTab: function() {
			this._setOption('tabLock', true);
		},
		
		unLockTab: function() {
			this._setOption('tabLock', false);
		},
		
		/*
		 * DECORATORS
		 * 
		 */
		
		inlineMessageTmpl: '<div class="ui-widget"><div class="ui-state-${state} ui-corner-all inlineMessage"><p><span class="ui-icon ${icon} inlineMessage-icon"></span>{{html text}}</p></div></div>',
		
		showInline: function (msg, options) {
			var $parsetHtml = $.tmpl(this.inlineMessageTmpl, {
				icon: options.icon? options.icon:'ui-icon-info',
				state: options.state? options.state:'highlight',
				text: msg
			});
			$parsetHtml.prependTo(this.options.panel);
			var _timeout = parseInt(options.timeout);
			if (options.timeout<=0) {
				
			} else {
				window.setTimeout(function(){
					$parsetHtml.fadeOut(function(){
						$parsetHtml.remove();
						if (options.fn && typeof options.fn == "function") {
							fn();
						}
					});
				}, _timeout);
			}
		},
		
		showInlineMessage: function (msg, fn, timeout) {
			this.showInline(msg, {
				type: 'msg',
				fn: fn || null,
				timeout: ((timeout==0)||(timeout))? timeout : 5000
			});
		},
		
		showInlineWarn: function(msg, fn, timeout) {
			this.showInline(msg, {
				type: 'warn',
				fn: fn || null,
				timeout: ((timeout==0)||(timeout))? timeout : 5000,
				icon: 'ui-icon-alert'
			});
		},
		
		showInlineError: function(msg, fn, timeout) {
			this.showInline(msg, {
				type: 'error',
				fn: fn || null,
				timeout: ((timeout==0)||(timeout))? timeout : 5000,
				state: 'error',
				icon: 'ui-icon-alert'
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
				
		/*
		 * Loading Methods
		 */
		
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
