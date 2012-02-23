;(function($) {
	
	var uiDialogClasses =
		'ui-dialog ' +
		'ui-widget ' +
		'ui-widget-content ' +
		'ui-corner-all ',
	sizeRelatedOptions = {
		buttons: true,
		height: true,
		maxHeight: true,
		maxWidth: true,
		minHeight: true,
		minWidth: true,
		width: true
	},
	resizableRelatedOptions = {
		maxHeight: true,
		maxWidth: true,
		minHeight: true,
		minWidth: true
	},
	// support for jQuery 1.3.2 - handle common attrFn methods for dialog
	attrFn = $.attrFn || {
		val: true,
		css: true,
		html: true,
		text: true,
		data: true,
		width: true,
		height: true,
		offset: true,
		click: true
	};
	
	$.widget("klear.moduleDialog", $.ui.dialog, {
		_superClass: $.ui.dialog.prototype,
		_getKlearPosition: function() {
			if (this.options.klearPosition) {
				return $(this.options.klearPosition); 
			}
			
			return document.body;
		},
		_makeDraggable : function() { 
		    this.uiDialog.draggable({
		        containment: this._getKlearPosition()
		    });
		    
		},
		_create: function() {
			this.originalTitle = this.element.attr('title');
			
			/*
			 * Klear Module Option
			 */
			this.options.isHidden = false;
			
			// #5742 - .attr() might return a DOMElement
			if ( typeof this.originalTitle !== "string" ) {
				this.originalTitle = "";
			}

			this.options.title = this.options.title || this.originalTitle;
			var self = this,
				options = self.options,

				title = options.title || '&#160;',
				titleId = $.ui.dialog.getTitleId(self.element),

				uiDialog = (self.uiDialog = $('<div></div>'))
					.appendTo(this._getKlearPosition())
					.hide()
					.addClass(uiDialogClasses + options.dialogClass)
					.css({
						zIndex: options.zIndex
					})
					// setting tabIndex makes the div focusable
					// setting outline to 0 prevents a border on focus in Mozilla
					.attr('tabIndex', -1).css('outline', 0).keydown(function(event) {
						if (options.closeOnEscape && !event.isDefaultPrevented() && event.keyCode &&
							event.keyCode === $.ui.keyCode.ESCAPE) {
							
							self.close(event);
							event.preventDefault();
						}
					})
					.attr({
						role: 'dialog',
						'aria-labelledby': titleId
					})
					.mousedown(function(event) {
						self.moveToTop(false, event);
					}),

				uiDialogContent = self.element
					.show()
					.removeAttr('title')
					.addClass(
						'ui-dialog-content ' +
						'ui-widget-content')
					.appendTo(uiDialog),

				uiDialogTitlebar = (self.uiDialogTitlebar = $('<div></div>'))
					.addClass(
						'ui-dialog-titlebar ' +
						'ui-widget-header ' +
						'ui-corner-all ' +
						'ui-helper-clearfix'
					)
					.prependTo(uiDialog),

				uiDialogTitlebarClose = $('<a href="#"></a>')
					.addClass(
						'ui-dialog-titlebar-close ' +
						'ui-corner-all'
					)
					.attr('role', 'button')
					.hover(
						function() {
							uiDialogTitlebarClose.addClass('ui-state-hover');
						},
						function() {
							uiDialogTitlebarClose.removeClass('ui-state-hover');
						}
					)
					.focus(function() {
						uiDialogTitlebarClose.addClass('ui-state-focus');
					})
					.blur(function() {
						uiDialogTitlebarClose.removeClass('ui-state-focus');
					})
					.click(function(event) {
						self.close(event);
						return false;
					})
					.appendTo(uiDialogTitlebar),

				uiDialogTitlebarCloseText = (self.uiDialogTitlebarCloseText = $('<span></span>'))
					.addClass(
						'ui-icon ' +
						'ui-icon-closethick'
					)
					.text(options.closeText)
					.appendTo(uiDialogTitlebarClose),

				uiDialogTitle = $('<span></span>')
					.addClass('ui-dialog-title')
					.attr('id', titleId)
					.html(title)
					.prependTo(uiDialogTitlebar);

			//handling of deprecated beforeclose (vs beforeClose) option
			//Ticket #4669 http://dev.jqueryui.com/ticket/4669
			//TODO: remove in 1.9pre
			if ($.isFunction(options.beforeclose) && !$.isFunction(options.beforeClose)) {
				options.beforeClose = options.beforeclose;
			}

			uiDialogTitlebar.find("*").add(uiDialogTitlebar).disableSelection();

			if (options.draggable && $.fn.draggable) {
				self._makeDraggable();
			}
			if (options.resizable && $.fn.resizable) {
				self._makeResizable();
			}

			self._createButtons(options.buttons);
			self._isOpen = false;

			if ($.fn.bgiframe) {
				uiDialog.bgiframe();
			}
		},
		open: function() {
			if (this._isOpen) { return; }

			var self = this,
				options = self.options,
				uiDialog = self.uiDialog;

			self.overlay = options.modal ? new $.ui.dialog.overlay(self) : null;
			
			self.overlay.$el.appendTo(this._getKlearPosition());
			
			self._size();
			
			self._position(options.position);
			
			uiDialog.show(options.show);
			self.moveToTop(true);

			// prevent tabbing out of modal dialogs
			if ( options.modal ) {
				uiDialog.bind( "keydown.ui-dialog", function( event ) {
					if ( event.keyCode !== $.ui.keyCode.TAB ) {
						return;
					}

					var tabbables = $(':tabbable', this),
						first = tabbables.filter(':first'),
						last  = tabbables.filter(':last');

					if (event.target === last[0] && !event.shiftKey) {
						first.focus(1);
						return false;
					} else if (event.target === first[0] && event.shiftKey) {
						last.focus(1);
						return false;
					}
				});
			}

			// set focus to the first tabbable element in the content area or the first button
			// if there are no tabbable elements, set focus on the dialog itself
			$(self.element.find(':tabbable').get().concat(
				uiDialog.find('.ui-dialog-buttonpane :tabbable').get().concat(
					uiDialog.get()))).eq(0).focus();

			self._isOpen = true;
			self._trigger('open');

			return self;
		},
		updateContent : function(content) {
			$(this.element).slideUp(function() {
				$(this).html(content).slideDown();
			});		
		},
		setAsLoading : function() {
			$(this.element).html('<br /><div class="loadingCircle"></div><div class="loadingCircle1"></div>');
		}
		
	});
	
	
	$.extend($.ui.dialog.overlay, {
		
		create: function(dialog) {
			
			
			if ( (dialog.widgetName == 'klearModule') && ($(dialog.element).moduleDialog("option","klearPosition")) ) {
				
				var container = $(dialog.element).moduleDialog("option","klearPosition");
			} else {
				
				var container = document;
				
			}
			if (this.instances.length === 0) {
				// prevent use of anchors and inputs
				// we use a setTimeout in case the overlay is created from an
				// event that we're going to be cancelling (see #2804)
				setTimeout(function() {
					return;
					// handle $(el).dialog().dialog('close') (see #4065)
					if ($.ui.dialog.overlay.instances.length) {
						$(container).bind($.ui.dialog.overlay.events, function(event) {

							if (!$(container).is(":visible")) return;
							// stop events if the z-index of the target is < the z-index of the overlay
							// we cannot return true when we don't want to cancel the event (#3523)
							if ($(event.target).zIndex() < $.ui.dialog.overlay.maxZ) {
								return false;
							}
						});
					}
				}, 1);

				// allow closing by pressing the escape key
				$(container).bind('keydown.dialog-overlay', function(event) {

					if (!$(container).is(":visible")) return;
					if (dialog.options.closeOnEscape && !event.isDefaultPrevented() && event.keyCode &&
						event.keyCode === $.ui.keyCode.ESCAPE) {
						
						dialog.close(event);
						event.preventDefault();
					}
				});

				// handle window resize
				$(window).bind('resize.dialog-overlay', $.ui.dialog.overlay.resize);
			}

			
			// COmpatibilidad con overlays en los tabs
			if (container == document) {
				container = document.body;
			}
			
			var $el = ( $('<div></div>').addClass('ui-widget-overlay'))
				.appendTo(container)
				.css({
					width: this.width(),
					height: this.height()
				});

		

			this.instances.push($el);
			return $el;
		}
	});

	
	
	
	
})(jQuery);
