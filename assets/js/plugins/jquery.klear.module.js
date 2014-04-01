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
     * loaded Templates Object
     *
     */

    $.klear.loadedTemplates = {};

    /*
     * Klear Module definition
     *
     * [jQuery UI Widget]
     *
     */

    var __namespace__ = 'klear.module';


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

            $.console.info("["+__namespace__+"] _init module");

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


            if (key === 'mainModuleLoaded' && value) {
                this.setMainLoaded();
                return;
            }

            if (key === 'addToBeLoadedFile') {

                this.totalToBeLoadedItems += value;
                this.updateTotalLoadingItems();
                return;
            }

            if (key === 'addLoadedFile') {
                this.totalLoadedItems++;
                this.updateCurrentLoadingItem();
                return;
            }


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
            filesUploading : {},
            panel : null,
            tabIndex : null,
            baseurl : null,
            menuLink : null,
            screen : null,
            dialog : null,
            dispatchOptions : {},
            tabLock: false,
            parentScreen: false,
            moduleDialog: null,
            PostDispatchMethod: null,
            PreDispatchMethod : null,
            shortcuts : {}
        },

        /*
         * _initOptions
         */

        _initOptions: function() {

            $.console.info("["+__namespace__+"] _initOptions");

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

            $.console.info("["+__namespace__+"] _initTab");

            if ((!this.options.menuLink)
                || ($("span.ui-silk",this.options.menuLink).length <= 0)) {
                return;
            }
            var _mainEnl = this.options.mainEnl;
            $("span.ui-silk",this.element)
                .attr("class",this._getTabIconClass())
                .on('click',function() {
                    $(_mainEnl).trigger("click");
            });
            var self = this;
            $(this.options.ui.tab).off('mouseup.closing');
            $(this.options.ui.tab).on('mouseup.closing',function(event) {
                if (event.which == null) {
                       /* IE case */
                       button= (event.button < 2) ? "LEFT" :
                                 ((event.button == 4) ? "MIDDLE" : "RIGHT");
                } else {
                       /* All others */
                       button= (event.which < 2) ? "LEFT" :
                                 ((event.which == 2) ? "MIDDLE" : "RIGHT");
                }

                if (button == 'MIDDLE') { //middle
                    // Parar el evento no sirve de nada, pero por si acaso.
                    event.stopPropagation();
                    event.preventDefault();

                    var prevHref = $(this).attr("href");
                    $(this).removeAttr("href");
                    var $self = $(this);
                    setTimeout(function() {
                        $self.attr("href",prevHref);
                    },100);
                    
                    $(self.element).klearModule("close");
                }

            });


        },

        reload : function() {

            $.console.info("["+__namespace__+"] reload");

            this._initTab();
        },

        /*
         * Klear Module Dispatch Method
         *
         * Module Launch Method
         *
         */

        dispatch : function() {

            $.console.info("["+__namespace__+"] dispatch");

            var dispatchData = {
                    file : this.options.file
            };

            $.extend(dispatchData,this.options.dispatchOptions);

            if (typeof this.options.PreDispatchMethod == 'function') {
                this.options.PreDispatchMethod.apply(this);
            }

            $.klear.request(dispatchData,this._parseDispatchResponse,this._errorResponse,this);

        },
        _errorResponse: function() {

            $.console.info("["+__namespace__+"] _errorResponse");

            this.setAsloaded();

            var title = '<span class="ui-silk inline dialogTitle '+this._getTabIconClass()+' "></span>';

            var message = [];

            if (arguments[0] && arguments[0].message != undefined) {
                message = arguments[0].message;
            } else {

                message = Array.prototype.join.call(arguments, '</em><br /><em>', [__namespace__]);
            }

            message = '<em>' + message + '</em>';

            var errorMessage = $.translate("Module registration error.")
                             + '<br /><br />'
                             + $.translate("Error: %s.", message);
            var dTitle = $.translate("Klear Module Error");
            dTitle = dTitle + ' - ' + title + '';
            this.showDialogError(
                errorMessage,
                {
                    title: dTitle,
                    closeTab: this.options.tabIndex
                }
            );
        },

        _parseDispatchResponse : function(response) {

            $.console.info("["+__namespace__+"] _parseDispatchResponse");

            this.setAsloaded();

            $(this.options.panel).html('');

            if (response.mainTemplate) {
                response.data.mainTemplate = response.mainTemplate;
            }

            var _self = this;


            $(this.element).on('moduleInitReady', function(){
                $.console.info("["+__namespace__+"] " + response.plugin + " is ready.");
                if (typeof _self.options.PostDispatchMethod == 'function') {
                    _self.options.PostDispatchMethod.apply(_self);
                }
            });

            $(this.element)[response.plugin]({
                data: response.data
            });
//
//            if (typeof this.options.PostDispatchMethod == 'function') {
//                this.options.PostDispatchMethod.apply(this);
//            }

        },

        reDispatch : function() {

            $.console.info("["+__namespace__+"] _reDispatch");

            // Invocamos reDispatch encadena, de padres a hijos.
            if (this.options.parentScreen) {
                $(this.options.parentScreen).klearModule("reDispatch");
            }

            this.setAsloading();
            this.dispatch();
        },

        getPanel : function() {
            return this.options.panel;
        },

        getContainer : function() {
            return this.options.container;
        },
        
        shortcut : function(keyCode)
        {
            if (this.options.shortcuts[keyCode]) {
                for(var i in this.options.shortcuts[keyCode]) {
                    if (typeof this.options.shortcuts[keyCode][i] == 'function') {
                        this.options.shortcuts[keyCode][i]();
                    }
                }
            }
        },
        registerShortcut : function(keyCode,fn)
        {
            if (!this.options.shortcuts[keyCode]) {
                this.options.shortcuts[keyCode] = [];
            }
            this.options.shortcuts[keyCode].push(fn);
        },
        
        /*
         * close method
         *
         * calls destroy and outer callback
         *
         */

        close: function(opts) {

            $.console.info("["+__namespace__+"] close");

            if (this.isLocked()) {
                $(this.options.container).tabs( "select", this.options.tabIndex );

                if ('function' == typeof this.options.tabLock) {

                    if (this.options.tabLock()) {

                        return;
                    }

                } else {
                    this.showInlineWarn($.translate("This tab is locked."));
                }
            }

            var uploadInProgress = false;
            for (var idx in this.options.filesUploading) {
                uploadInProgress = true;
                break;
            }

            if (uploadInProgress) {

                var self = this;

                this.showDialog(
                    $.translate("Upload in progress.") +
                    '<br />' +
                    $.translate("Close the screen?")
                    ,{
                    title : $.translate("Attention!"),
                    buttons :
                         [
                              {
                                text: $.translate("Cancel"),
                                click: function() {
                                    $(this).moduleDialog("close");
                              }
                            },
                            {
                                text: $.translate("Ignore changes and close"),
                                click: function() {
                                    self.unsetAllUploadsInProgress();
                                    self.close();
                                }
                            }
                        ]
                });

                return;
            }

            if (opts && opts.callback && typeof opts.callback == "function") {
                opts.callback();
            }

            var $parentLi = false;
            // Si la pestaña a cerrar, tiene padre
            if (this.options.tabIndex == parseInt($.klear.canvas.tabs('option', 'selected'))) {
                $parentLi = $(this.options.parentScreen);
            }
            $(this.options.container).tabs( "remove", this.options.tabIndex );


            // Después de cerrar (e invocar el handler en navigator), seleccionamos el padre
            if (false !== $parentLi) {
                $.klear.canvas.tabs('select',$parentLi.klearModule("option","tabIndex"));
            }
        },


        /*
         * blockTab
         */
        getModuleDialog : function() {

            return this.options.moduleDialog;
        },

        dialogMessageTmpl: '<div class="ui-widget"><div class="ui-state-${state} ui-corner-all inlineMessage"><p><span class="ui-icon ${icon} inlineMessage-icon"></span>{{html text}}</p></div></div>',

        showDialog: function (msg, options) {

            var defaults = {
                icon: options.icon || 'ui-icon-info',
                state: options.state || 'highlight',
                text: msg,
                resizable: options.resizable || false,
                buttons : options.buttons || null
            };

            var dialogTemplate = options.template || this.dialogMessageTmpl;

            var $parsedHtml = $.tmpl(dialogTemplate, defaults);
            var dialogType = options.dialogType || 'moduleDialog';
            var self = this;
            var iconClass = self._getTabIconClass();
            if (false === options.title) {
                var title = false;
            } else {
                var title =
                    '<span class="ui-icon  inlineMessage-icon dialogTitle '+defaults.icon+' "></span>'+options.title + ''
                    ||
                    '<span class="ui-silk inline dialogTitle '+iconClass+' "></span>'+this.options.title + '';
            }

            var width = options.width || 300;
            var height = options.height || 160;

            var closeTab = ((options.closeTab==0)||(options.closeTab))? options.closeTab.toString() : false;

            if (dialogType == 'moduleDialog') {

                this.options.moduleDialog = $parsedHtml;
                this.options.moduleDialog.moduleDialog({
                    position: {
                        my: 'center bottom',
                        at: 'center center',
                        collision: 'none'
                    },
                    buttons : defaults.buttons,
                    title: title,
                    modal:true,
                    width: width,
                    height: height,
                    resizable: defaults.resizable,
                    klearPosition: this.getPanel() ,
                    open: function(ui) {
                        $(self.options.ui.tab).addClass("ui-state-disabled");
                    },
                    close: function(ui) {
                        if ($(this).moduleDialog('option', 'isHidden')) {

                        } else {
                            $(self.options.ui.tab).removeClass("ui-state-disabled");
                            $(this).remove();
                            if (closeTab) {
                                self.close();
                            }
                        }
                    }
                });
            } else {
                $parsedHtml.dialog({
                    title: '<span class="ui-silk inline dialogTitle '+iconClass+' "></span>'+this.options.title + "",
                    modal: options.modal || false,
                    close: function(ui) {
                        $(this).remove();
                        if (closeTab) {
                            self.close();
                        }
                    }
                });
            }
        },

        showDialogMessage: function (msg, opts) {
            var options = {
                type: 'msg',
                dialogType: 'moduleDialog'
            };
            var opts = opts || {};
            $.extend(options, opts);
            this.showDialog(msg, options);
        },

        showDialogWarn: function(msg, opts) {
            var options = {
                type: 'warn',
                icon: 'ui-icon-alert',
                dialogType: 'moduleDialog'
            };
            var opts = opts || {};
            $.extend(options, opts);
            this.showDialog(msg, options);
        },

        showDialogError: function(msg, opts) {
            var options = {
                type: 'error',
                icon: 'ui-icon-alert',
                state: 'highlight',
                dialogType: 'moduleDialog'
            };
            var opts = opts || {};
            $.extend(options, opts);
            this.showDialog(msg, options);
        },

        /*
         * TAB LOCK
         */
        isLocked: function() {
            return this.options.tabLock !== false;
        },
        lockTab: function(callback) {
            if ('function' == typeof callback) {
                this._setOption('tabLock', callback);
            } else {
                this._setOption('tabLock', true);
            }
        },
        unLockTab: function() {
            this._setOption('tabLock', false);
        },
        setAsChanged : function(changeCallback) {

            this.element.addClass('changed');
            this.lockTab(changeCallback);
        },
        setAsUnChanged : function() {
            this.element.removeClass('changed');
            this.unLockTab();
        },
        setUploadInProgress: function (id) {
            this.options.filesUploading[id] = true;
        },
        unsetUploadInProgress: function (id) {
            if (this.options.filesUploading[id]) {
                delete this.options.filesUploading[id];
            }
        },
        unsetAllUploadsInProgress: function (id) {
            for (idx in this.options.filesUploading) {
                delete this.options.filesUploading[idx];
            }
        },
        /*
         * DECORATORS
         *
         */

        _getTabIconClass: function() {

            if (this.options.menuLink && $("span.ui-silk",this.options.menuLink).length > 0) {
                return $("span.ui-silk",this.options.menuLink).attr("class");
            }

            if (this.options.menuLink && $("span.ui-silk",this.options.menuLink.parent()).length > 0) {
                return $("span.ui-silk",this.options.menuLink.parent()).attr("class");
            }

            return '';
        },

        inlineMessageTmpl: '<div class="ui-widget"><div class="ui-state-${state} ui-corner-all inlineMessage"><p><span class="ui-icon ${icon} inlineMessage-icon"></span>{{html text}}</p></div></div>',

        showInline: function (msg, options) {
            var $parsetHtml = $.tmpl(this.inlineMessageTmpl, {
                icon: options.icon? options.icon:'ui-icon-info',
                state: options.state? options.state:'highlight',
                text: msg
            });
            $parsetHtml.prependTo(this.options.panel);
            var _timeout = parseInt(options.timeout);
            if (options.timeout>0) {
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

            $.console.info("["+__namespace__+"] setAsLoading");

            this._loading = true;
            this.updateLoader();
        },

        setAsloaded : function() {

            $.console.info("["+__namespace__+"] setAsloaded");

            this._loading = false;
            this.updateLoader();
        },

        loadingTmpl : ['<div id="loadingTemplate" class="loadingPanel ui-widget-content ui-corner-all">',
                       '<p>${loadingText}</p>',
                       '<p class="extra main">${loadingTextMain}<span class="ui-icon ui-icon-circle-check inline"></span></p>',
                       '<p class="extra">${loadingTextExtra}(<span class="current">0</span>/<span class="total">?</span>)<span class="ui-icon ui-icon-circle-check inline"></span></p>',
                       '</div>'],
        totalToBeLoadedItems : 0,
        totalLoadedItems : 0,
        updateTotalLoadingItems : function(total) {
            var _panel = $(this.options.panel);
            $(".loadingPanel",_panel).find(".total").html(this.totalToBeLoadedItems);
        },
        updateCurrentLoadingItem : function() {
            var _panel = $(this.options.panel);

            if ((100*this.totalLoadedItems)/this.totalToBeLoadedItems > 20) {
                var _opacity = (100*this.totalLoadedItems)/this.totalToBeLoadedItems / 100;
            } else {
                var _opacity = ".2";
            }

            $(".loadingPanel",_panel).find(".current").css("opacity",_opacity).html(this.totalLoadedItems);
        },
        setMainLoaded : function() {
            var _panel = $(this.options.panel);
            $(".loadingPanel",_panel).find("p.main").addClass("complete");

        },
        updateLoader : function() {

            var _panel = $(this.options.panel);
            if ($(".loadingPanel",_panel).length == 0) {

                var $parsetHtml = $.tmpl(this.loadingTmpl.join(''), {
                    loadingText: $.translate("Loading content"),
                    loadingTextMain: $.translate("Loading Main Module"),
                    loadingTextExtra: $.translate("Loading Secondary modules")
                });

                _loadingItem = $parsetHtml;

                _loadingItem
                    .removeAttr("id")
                    .spin({lines:8,length:18,width:4,radius:10,trail:100,speed:1.2})
                    .hide()
                    .appendTo(_panel);

            } else {
                var _loadingItem = $(".loadingPanel",_panel);
            }


            if (this._loading) {
                _loadingItem.show();
                this.showOverlay();
                $(this.options.ui.tab).addClass("ui-state-disabled");

            } else {
                $(this.options.ui.tab).removeClass("ui-state-disabled");
            }
        },
        _getOverlay : function() {
            var _panel = $(this.options.panel);
            if ($("div.overlay",_panel).length == 0) {
                return $("<div />")
                        .addClass("overlay")
                        .css({
                            opacity: '0.6',
                            width: _panel.width() + 'px',
                            height: _panel.height() + 'px'
                        })
                        .hide()
                        .appendTo(_panel);
            } else{
                return $("div.overlay",_panel);
            }
        },
        showOverlay : function() {
            this._getOverlay().show();

        },
        hideOverlay : function() {
            this._getOverlay().hide();
        },
        updateTitle : function(title) {
            if (title && title != '')  {
                $(this.options.ui.tab).html(title);
            }
        }

    });


    $.extend($.klear.module, {
        instances: []
    });


    $.widget.bridge("klearModule", $.klear.module);


})(jQuery);
