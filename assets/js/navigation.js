/*
 * jQuery Klear
 */

;(function($) {

    /*
     * setting / getting $.klear Namespace
     */

    $.klear = $.klear || {};

    var __namespace__ = 'klear.navigation';

    $.klear.checkDeps = function(dependencies, callback) {

        if (typeof callback._numberOfTries == 'undefined') {
            callback._numberOfTries = 0;
        } else {
            callback._numberOfTries++;
        }

        if (!dependencies.length) {
            throw "Wrong dependencies parameter type.";
        }

        if (callback._numberOfTries > 10) {
            throw "JS Dependecy Timeout for: " + dependencies.join(', ');
        }

        var depLength = dependencies.length;
        for(var i=0; i < depLength; i++) {

            var segments = dependencies[i].split('.');
            var prev = window;
            for(var j=0; j < segments.length; j++) {
                if (typeof prev[segments[j]] == 'undefined') {
                    setTimeout(function() {callback($);}, 100 * (callback._numberOfTries + 1));
                    return false;
                } else {
                    prev = prev[segments[j]];
                }
            }
        }
        return true;
    };

    /*
     * Checking open in background Event:
     * control | middle click
     */

    $.klear.checkNoFocusEvent = function(e, $el, $link) {

        if(e.ctrlKey) {
            $el.data('noFocus', true);
            return;
        }
        if (typeof $link == 'undefined') return;
        if (e.which == null) {
                if (!e.button) return;
               /* IE case */
               var button= (e.button < 2) ? "LEFT" :
                         ((event.button == 4) ? "MIDDLE" : "RIGHT");
        } else {
               /* All others */
               var button= (e.which < 2) ? "LEFT" :
                         ((e.which == 2) ? "MIDDLE" : "RIGHT");
        }

        if (button == 'MIDDLE') {

            e.stopPropagation();
            e.preventDefault();

            var prevHref = $link.attr("href");
            $link.removeAttr("href");
            var $_link = $link;
            setTimeout(function() {
                $_link.attr("href",prevHref);
            },100);
            $el.data('noFocus', true);
        }

    };

    $.klear.klearDialog = function (msg, options) {
        $.extend(options, {
            icon: options.icon || 'ui-icon-info',
            state: options.state || 'default',
            text: msg || '',
        });
        var dialogSettings = {
            title: '<span class="ui-icon inline dialogTitle '+options.icon+' "></span>'+options.titleText + "",
            modal: true,
            resizable: false,
            close: function(ui) {
                $(this).remove();
            }
        };

        $.extend(dialogSettings, options);

        var dialogTemplate = dialogSettings.template ||
            '<div class="ui-widget"><div class="ui-state-${state} ui-corner-all inlineMessage"><p><span class="ui-icon ${icon} inlineMessage-icon"></span>{{html text}}</p></div></div>';
        var $parsedHtml = $.tmpl(dialogTemplate, dialogSettings);
        $parsedHtml.dialog(dialogSettings);
    };

    $.klear.klearMessage = function (msg, opts) {
        var options = {
            type: 'msg',
            icon: 'ui-icon-comment',
            titleText: 'Klear Message Window'
        };
        var opts = opts || {};
        $.extend(options, opts);
        $.klear.klearDialog(msg, options);
    };

    $.klear.klearWarn = function(msg, opts) {
        var options = {
            type: 'warn',
            icon: 'ui-icon-info',
            titleText: 'Klear Warning Window'
        };
        var opts = opts || {};
        $.extend(options, opts);
        $.klear.klearDialog(msg, options);
    };

    $.klear.klearError = function(msg, opts) {
        var options = {
            type: 'error',
            icon: 'ui-icon-alert',
            state: 'highlight',
            titleText: 'Klear Error Window'
        };
        var opts = opts || {}
        $.extend(options, opts);
        $.klear.klearDialog(msg, options);
    };



    /*
     * Hello Klear Server
     */

    $.klear.hello = function(option){
        var options = {
                controller: 'index',
                action: 'hello'
        };
        this.callback = this.callback || [];
        
        switch(option) {
            case 'rememberCallback':
                this.callback.push(arguments[1]);
                return;
            break;
            case 'setCallback':
                this.callback.push(arguments[1]);
            break;
            case 'options':
                // completamos las opciones de klear.request con las enviadas como segundo parámetro
                $.extend(options,arguments[1]);
            break;
        }
        var self = this;
        $.klear._doHelloSuccess = function(response) {
            if (response.success && response.success === true) {
                if (self.callback.length == 0) {
                    self.callback.push($.klear.menu);
                }
                do {
                    var callback = self.callback.shift();
                    if (typeof callback == 'function') {
                        setTimeout(callback, 350);	
                    }
                } while(self.callback.length > 0);
            }
        };

        $.klear._doHelloError = function(response) {
            console.log(response);
        };


        $.klear.request(options,
                        $.klear._doHelloSuccess,
                        $.klear._doHelloError,
                        this
        );
    };

    $.klear.menu = function(force, options) {
        var options = options || {};

        if (force === true) {
            this.loaded = false;
        }

        if (this.loaded) {
            return;
        }

        var $sidebar = $('#sidebar');
        var $headerbar = $('#headerbar');
        var $footerbar = $('#footerbar');
        var $infobar = $('#applicationInfo');

        var self = this;

        $.klear._doMenuSuccess = function(response) {

            var navMenus = response.data.navMenus;

            if (response.data.jqLocale) {
                $.datepicker.setDefaults($.datepicker.regional[response.data.jqLocale]);
            }

            $sidebar.empty();
            $sidebar.accordion("destroy");
            $headerbar.empty();
            $footerbar.empty();
            $infobar.empty();

            $.tmpl('klearSidebarMenu', navMenus.sidebar).appendTo($sidebar);

            $.tmpl('klearHeaderbarMenu', navMenus.headerbar).appendTo($headerbar);

            $.tmpl('klearFooterbarMenu', navMenus.footerbar).appendTo($footerbar);

            $.tmpl('klearInfoBar').appendTo($infobar);

            //Este template no lo queremos cacheado nunca
            $.template['klearInfoBar'] = undefined;

            $sidebar.stop().fadeIn();
            $headerbar.stop().fadeIn();
            $footerbar.stop().fadeIn();
            $infobar.stop().fadeIn();

            $("a",$sidebar).tooltip();
            $("a",$headerbar).tooltip();
            $("a",$footerbar).tooltip();

            if (localStorage.getItem('toogleMenu') == 'true') {
                $.klear.toggleMenu();
            }

            if (localStorage.getItem('toogleHeader') == 'true') {
                $.klear.toggleHeader();
            }

            $(document).trigger("kMenuLoaded");

            /*
             * Cargar
             */

            $.klear.request(
                {
                    controller: 'error',
                    action:'list'
                },
                function(response) {
                    $.klear.addErrors(response.data);
                    $(document).trigger("kErrorsLoaded");
                },
                function() {
                    console.error("errors.yaml not found!")
                }
            );

            $.klear.keepAlive();

            /*
             * JQ Decorartors
             */


            var sideBarOffset = $sidebar.offset();

            $sidebar.accordion({
                icons : {
                        header: "ui-icon-circle-arrow-e",
                        headerSelected: "ui-icon-circle-arrow-s"
                },
                collapsible: true,
                autoHeight: false
            });

            $sidebar.on('reposition', function() {
                if (!$(this).is(":visible")) {
                    return;
                }

                var _target = $(window).scrollTop();
                if (_target < sideBarOffset.top) {
                    _target = 0;
                } else {
                    _target -= sideBarOffset.top;
                }
                $(this).stop().animate({'marginTop': _target + 'px'}, 0, 'easeOutQuad');

            });

            $("li", $sidebar).on("mouseenter",function() {
                $(this).addClass("ui-state-highlight");
            }).on("mouseleave",function() {
                $(this).removeClass("ui-state-highlight");
            });

            self.loaded = true;

            /*
             *
             */


            $toolsBar = $( "#headerToolsbar" ),
            
            $toolsBar.html(navMenus.toolsbar).buttonset();
            
            $( "label", $toolsBar ).tooltip();

            $( ".pickableLang",  $toolsBar).off('change').on('change', function(){
                $.klear.language = $(this).val();
                $.klear.restart({'language': $(this).val()});
                $( "#langPicker", $toolsBar).trigger("change");
            });

            
            $( "#langPicker", $toolsBar).off('change').on('change', function(){
            	var $self = $(this);
                if ($(".pickableLanguage",$toolsBar).hasClass("expanded")) {
                	$(".pickableLanguage",$toolsBar).removeClass("expanded").css("display","inline").animate({width:'85px'});
                } else {
                	$(".pickableLanguage",$toolsBar).animate({width:'0'},function() {
                		$(this).addClass("expanded").css("display","none");
                	});
                	
                }
            });
            
            
            $( "#logout", $toolsBar ).off('change').on('change', function(){
                var $self = $(this);
                $.getJSON($self.data('url'),{json:true}, function(){
                    $sidebar.fadeOut('fast');
                    $infobar.fadeOut();
                    //custom klear events
                    $(document).trigger("kLogout");
                    $.klear.restart({}, true);
                });
            });

            $( "#tabsPersist", $toolsBar ).off('change').on('change', function(){
                var $self = $(this);
                if ($.klear.tabPersist.enabled()) {
                    $.klear.tabPersist.disable();
                } else {
                    $.klear.tabPersist.enable();
                }

                $self.trigger('update-icon');
            });

            $( "input#tabsPersist", $toolsBar ).off('update-icon').on('update-icon', function(){
                var $self = $(this).next('label');
                var $icon = $('.ui-icon', $self);
                if ($.klear.tabPersist.enabled()) {
                    $icon.removeClass('ui-icon-unlocked').addClass('ui-icon-locked');
                    $self.addClass('ui-state-active');
                } else {
                    $icon.removeClass('ui-icon-locked').addClass('ui-icon-unlocked');
                }
            });

            $( "#menuCollapse", $toolsBar ).off('change').on('change', function(){
                $.klear.toggleMenu();
            });

            $( "#menuCollapse", $toolsBar ).off('update-icon').on('update-icon', function(){
                var $self = $(this).next('label');
                var $icon = $('.ui-icon', $self);
                if ($.klear.isMenuCollapsed()) {
                    $icon.removeClass('ui-icon-triangle-1-w').addClass('ui-icon-triangle-1-e');
                    $self.addClass('ui-state-active');
                } else {
                    $icon.removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-w');
                }
            });
            

            $( "#headerCollapse", $toolsBar ).off('change').on('change', function(){
                $.klear.toggleHeader();
            });

            
            $( "#headerCollapse", $toolsBar ).off('update-icon').on('update-icon', function(){
                var $self = $(this).next('label');
                var $icon = $('.ui-icon', $self);
                if ($.klear.isHeaderCollapsed()) {
                	$icon.removeClass('ui-icon-triangle-1-n').addClass('ui-icon-triangle-1-s');
                    $self.addClass('ui-state-active');
                } else {
                	$icon.removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-n');
                }
            });

            
            $( "#superCollapse", $toolsBar ).off('change').on('change', function(){
                $.klear.toggleAll();
            });

            
            $( "#superCollapse", $toolsBar ).off('update-icon').on('update-icon', function(){
                var $self = $(this).next('label');
                var $icon = $('.ui-icon', $self);
                if ($.klear.isHeaderCollapsed()) {
                	$icon.removeClass('ui-icon-triangle-1-nw').addClass('ui-icon-triangle-1-se');
                    $self.addClass('ui-state-active');
                } else {
                	$icon.removeClass('ui-icon-triangle-1-se').addClass('ui-icon-triangle-1-nw');
                }
            });
            


//            $langBar.show();
            $toolsBar.show();


            //TODO: refactorizar tabPersist para que tenga consistencia con autoplay
            if ($.klear.tabPersist.enabled()) {
                $.klear.tabPersist
                            .loadAutoPlay()
                            .loadData()
                            .launch();
                $("#tabsPersist").trigger('update-icon');
            } else {
                // Autoplay es una forma de tabPersist
                $.klear.tabPersist
                            .loadAutoPlay()
                            .launch();
            }

        };

        $.klear._doMenuError = function(response) {
            console.log(response);
        };

        var settings = $.extend({
            controller: 'menu',
            action: 'index'
        },options);

        $(document).trigger("kMenuStartLoad");

        $.klear.request(
            settings,
            $.klear._doMenuSuccess,
            $.klear._doMenuSuccess,
            this
        );

        $.klear.tabPersist = {
            tabs: [],
            add : function( iden ) {
                for (var i in this.tabs ) {
                    if (this.tabs[i] == iden) return;
                }
                this.tabs.push(iden);
                this.save();
            },
            remove : function( iden ) {
                var tabs = [];
                for (var i in this.tabs ) {
                    if (this.tabs[i] != iden) {
                        tabs.push(this.tabs[i]);
                    }
                }
                this.tabs = tabs;
                this.save();
            },
            fix : function() {
            	  var auxTabs = {};
            	  var tabs = [];
                  for (var i in this.tabs ) {
                	  item = this.tabs[i];
                	  if (auxTabs[item] && auxTabs[item] === true) {
                		  continue;
                	  }
                	  auxTabs[item] = true;
                	  tabs.push(item);
                  }
                  this.tabs = tabs;
            },
            save : function() {
            	this.fix();
                var jsTabs = JSON.stringify(this.tabs);
                localStorage.setItem('tabPersist', jsTabs);
            },
            loadData : function(){
                var tabs = JSON.parse(localStorage.getItem('tabPersist'));
                for (var i in tabs ) {
                    this.tabs.push(tabs[i]);
                }
                return this;
            },
            loadAutoPlay: function() {
                if ($('a.subsection.autoplay:eq(0)').length == 1) {
                    this.tabs.push('#' + $('a.subsection.autoplay:eq(0)').attr("id"));
                }
                return this;
            },
            launch : function() {

                for (var i in this.tabs ) {
                    var menuButton = this.tabs[i].replace(/#tabs-/, '#target-');
                    $(menuButton).trigger('mouseup');
                }


                // Seleccionamos el tab del ultimo menú principal seleccionado
                if ($(menuButton).length>0) {

                    var _parent = $(menuButton).parents('div:eq(0)');

                    if (_parent.is(".ui-accordion-content")) {
                           var _idx = $("h2",$sidebar).index(_parent.prev('h2'));
                           if ($("#sidebar").accordion('option','active') != _idx) {
                               $("#sidebar").accordion('option','active', _idx);
                           }
                       }
                }


            },
            enable : function(){
                localStorage.setItem('tabPersistEnabled', '1');
            },
            disable : function(){
                localStorage.setItem('tabPersistEnabled', '0');
            },
            enabled : function() {
            	return localStorage.getItem('tabPersistEnabled') == '1';
            }
        };

        $sidebar.add($headerbar).add($footerbar);

        $('body').on("mouseup","a.subsection", function(e) {
            e.preventDefault();
            e.stopPropagation();

            var iden = $(this).attr("id").replace(/^target-/,'');

            $.klear.checkNoFocusEvent(e, $.klear.canvas, $(this));

            var tabTitle = $(this).text()!=""? $(this).text():$(this).parent().attr('title');

            if ($("#tabs-"+iden).length > 0) {
                ;
                //Actualizo su título, por si acaso soy una opción externa con un título "disfrazado"
                $.klear.canvas
                        .tabs('select', '#tabs-'+iden)
                        .find(".ui-tabs-selected:eq(0)")
                            .klearModule("updateTitle", tabTitle);

                return;
            }
            var idContent = "#tabs-" + iden;


            $.klear.canvas.tabs( "add", idContent, tabTitle);

            $.klear.tabPersist.add(idContent);

        }).on("click","a.subsection",function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

    };

    var $sidebar = $("#sidebar");
    // Fix para expendiente X menú apagado... :(
    $sidebar.on('mouseenter',function() {
    	$(this).animate({opacity:'1'});
    });
    
    $.klear.toggleMenu = function() {

        if ($sidebar.data("seized")) {
            $sidebar.animate({width: menuMeasures.getWidth() + 'px'});
            $(".textnode", $sidebar).stop().animate({opacity:'1', 'font-size': menuMeasures.getFontSize()});
            $("li",$sidebar).animate({padding:"0.5em"});
            
            $("#sidebar h2").removeClass('iconsidebar');
            $(".textnode").removeClass('compact');

            $sidebar.data("seized",false);
        } else {

            $(".textnode", $sidebar).animate({fontSize:'0em',opacity:'0'});
            $sidebar.animate({width:'50px'});
            $("li",$sidebar).animate({padding:"0em"});
            $sidebar.data("seized",true);
            
            $("#sidebar h2").addClass('iconsidebar');
            $(".textnode", $sidebar).addClass('compact');
        }

        localStorage.setItem('toogleMenu', $.klear.isMenuCollapsed());
        $( "#menuCollapse").trigger('update-icon');
        $( "#superCollapse").trigger('update-icon');
    };
    

    $.klear.toggleHeader = function() {
    	$appLogo = $("#applicationLogo");
    	
        if ($.klear.isHeaderCollapsed()) {
        	$("#header").fadeOut(function() {
        		$(this).removeClass("collapsedHeader").fadeIn();
        	});
        	
        	$appLogo.animate({opacity:'1', height:'100px'});
        	$("#applicationTools").animate({marginTop:'0px'});

        	$appLogo.data("seized",false);
            
        	if ($("#footer").hasClass("collapsedFooter")) {
        		$("#footer").fadeOut(function() {
        			$(this).removeClass("collapsedFooter").fadeIn();
        		});
        	}
        } else {
        	$("#header").fadeOut(function() {
        		$(this).addClass("collapsedHeader").fadeIn();
        	});
        	$appLogo.animate({opacity:'0', height:'0'});
        	$("#applicationTools").animate({marginTop:'-40px'});
        	$appLogo.data("seized",true);
        	
        	if ($("#footer").height() > '50') {
	        	$("#footer").fadeOut(function() {
	        		$(this).addClass("collapsedFooter").fadeIn();
	        	});
        	}
        }

        localStorage.setItem('toogleHeader', $.klear.isHeaderCollapsed());
        $( "#headerCollapse").trigger('update-icon');
        $( "#superCollapse").trigger('update-icon');
    };
    
    $.klear.toggleAll = function() {
    	
    	var initialState = $.klear.isMenuCollapsed();
    	$.klear.toggleMenu();
    	if (initialState == $.klear.isHeaderCollapsed()) {
    		$.klear.toggleHeader();	
    	}
    	
    }

    $.klear.isMenuCollapsed = function() {
        return $("#sidebar").data("seized") || false;
    };
    
    $.klear.isHeaderCollapsed = function() {
        return $("#applicationLogo").data("seized") || false;
    };


    var menuMeasures = {
               data : {
                   normal : {
                       fontsize : '1em',
                       width: 300
                   },
                   small  : {
                       fontsize : '0.70em',
                       width: 180
                   }
               },
               getFontSize : function() {
                   return this.data[this.current]['fontsize'];
               },
               getWidth : function() {
                   return this.data[this.current]['width'];
               },
               current : 'normal',
               loadSize : function() {

                   if ($(window).width() < 1030) {
                       menuMeasures.current = 'small';
                   } else {
                       menuMeasures.current = 'normal';
                   }
                   $sidebar.data("seized",! $sidebar.data("seized"));
                   $.klear.toggleMenu();
               }
    };

    $(window).on('resize', menuMeasures.loadSize);
    $(document).on("kMenuLoaded", menuMeasures.loadSize);


    $("body").on('click','a.toogleMenu',function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        $.klear.toggleMenu();
        $.klear.toggleHeader();
    });


    $.klear.login = function(option){

        switch(option) {
            case 'close':

                if (this.$loginForm) {
                    this.$loginForm.fadeOut(function() {
                        $(this).dialog("destroy").remove();
                    });
                    // klear custom events
                    $(document).trigger("kAuthSuccess");

                }

                return;
            break;
        }


        var self = this;

        $.klear._doLoginSuccess = function(response) {

            self.$loginForm = $.tmpl('klearForm', response.data);

            self.$loginForm.appendTo("#canvas").dialog({
                resizable: false,
                modal: true,
                draggable: false,
                stack: true,
                width:'45%',
                minHeigth:'350px',
                dialogClass: 'loginDialog',
                closeOnEscape: false,
                open : function(event, ui) {
                    $("p.submit input",self.$loginForm).button();
                    $("input:text:eq(0)",self.$loginForm).trigger("focusin").select();
                }
            });

            $("select",self.$loginForm).selectBoxIt({theme: "jqueryui"});
            $("input",self.$loginForm).removeAttr("disabled");
            $("input:submit",self.$loginForm).button();
            $("input:text:eq(0)",self.$loginForm).trigger("focusin").select();

            if ($("div.loginError",self.$loginForm).length > 0) {

                self.$loginForm.effect("shake",{times: 3},60);
            }

            $("form",self.$loginForm.parent()).on('submit',function(e) {

                e.preventDefault();
                e.stopPropagation();
                $.klear.hello('options',{
                            post: $(this).serialize(),
                            isLogin: true
                });

                $("input",self.$loginForm).attr("disabled","disabled");
            });


        };

        $.klear._doLoginError = function(response) {
            console.log(response);
        };


        $.klear.request(
            {
                controller: 'login',
                action: 'index'
            },
            $.klear._doLoginSuccess,
            $.klear._doLoginSuccess,
            this
        );

        return this;

    };

    $.klear.loadCanvas = function(){
        /*
         * TABS
         */

        var tabTemplate = "<li title='#{label}'><span class='ui-silk'></span>"+
            "<span class='ui-icon ui-icon-close'></span>" +
            "<span class='ui-icon ui-icon-arrowrefresh-1-w'></span>" +
            "<a href='#{href}'>#{label}</a></li>";

        $.klear.canvas.tabs({
            tabTemplate: tabTemplate,
            scrollable: true,
            add : function( event, ui ) {
                if ($(ui.tab).parents('ul').css('display') == 'none') {
                    $(ui.tab).parents('ul').fadeIn();
                }
                var backgroundTab = false;
                if ($(this).data('noFocus')===true) {
                    $(this).data('noFocus', false)
                    backgroundTab = true;
                }

                if (backgroundTab !== true) {
                    $.klear.canvas.tabs('select', ui.index);
                    $("html, body").animate({ scrollTop: 0 }, 600);
                }


                var $tabLi = $(ui.tab).parent("li");

                $tabLi.klearModule({
                    ui: ui,
                    container : $.klear.canvas
                }).tooltip();

                // Se invoca custom event para actualizar objeto klear.module (si fuera necesario);
                $.klear.canvas.trigger("tabspostadd",ui);

                $tabLi.klearModule("dispatch");

                if (backgroundTab !== true) {
                    //$tabLi.klearModule("highlightOn");
                }

                $("li",$.klear.canvas).each(function(idx,elem) {
                    $(elem).klearModule("option","tabIndex",idx);
                });


            },
            select : function(event, ui) {

                $("#tabsList li").each(function(idx,elem) {
                    $(elem).klearModule("highlightOff");
                });

                var $tabLi = $(ui.tab).parent("li");
                $tabLi
                    .klearModule("updateLoader");
                    //.klearModule("highlightOn");
            },
            show : function(event, ui) {
                $(ui.panel).trigger("focusin");
            },
            remove: function(event, ui) {

            	var iden = '#'  + $(ui.panel).attr("id");
                $.klear.tabPersist.remove(iden);
                $("li",$.klear.canvas).each(function(idx,elem) {
                    $(elem).klearModule("option","tabIndex",idx);
                });

                $.klear.canvas.tabs('select', $.klear.canvas.tabs('option', 'selected'));
            }
        }).find( ".ui-tabs-nav" ).sortable({ axis: "x" });
        /*
         * CLOSE
         */
        $( "#tabsList").on("click","span.ui-icon-close", function() {
            var $tab = $(this).parent("li");
            $tab.klearModule("close");
        });

        $( "#tabsList").on("click","span.ui-icon-arrowrefresh-1-w", function() {
            var $tab = $(this).parent("li");
            $tab.klearModule("reDispatch");
        });

    };


    $(document).on("keydown",function(e) {

        var ctrlAltActions = {
            87 : {
                key : 'w',
                action : function(selectedTab) {

                    $li = $("#tabsList li:eq("+selectedTab+")");
                    $li.klearModule('close');
                }
            },
            82 : {
                key : 'r',
                action : function(selectedTab) {
                    $li = $("#tabsList li:eq("+selectedTab+")");
                    $li.klearModule('reDispatch');
                }
            },
            34 : {
                key : 'rePag',
                action : function(selectedTab) {
                    selectedTab++;
                    if (selectedTab >= $("#tabsList li").length) {
                        selectedTab = 0;
                    }
                    $.klear.canvas.tabs('select', selectedTab);
                }
            },
            33 : {
                key : 'AvPag',
                action : function(selectedTab) {
                    selectedTab--;
                    selectedTab = selectedTab<0 ? $("#tabsList li").length-1 : selectedTab ;
                    $.klear.canvas.tabs('select', selectedTab);
                }
            },
            67 : { // c
                key: 'c',
                action: function(selectedTab) {
                    $.klear.cacheEnabled = !$.klear.cacheEnabled;
                    console.log($.klear.cacheEnabled? "Cache Habilitada":"Cache Deshabilitada");

                }
            },
            77 : {
                key: 'm',
                action: $.klear.toggleMenu
            },
            72 : {
                key: 'h',
                action: $.klear.toggleHeader
            },
            88 : {
                key: 'x',
                action: $.klear.toggleAll
            }

        };

        var altActions = {
            37 : { //Disable alt + back arrow shortcut on browser
                key: 'backArrow',
                action: function() {
                    return;
                }
            }
        };

        if (e.altKey && e.ctrlKey && ctrlAltActions[e.which]
            || e.altKey && altActions[e.which]
        ) {
            e.preventDefault();
            e.stopPropagation();

            var selectedTab = parseInt($.klear.canvas.tabs('option', 'selected'));

            if (e.altKey && e.ctrlKey) {
                // Ctrl + Alt + Key
                ctrlAltActions[e.which]['action'](selectedTab);
            } else {
                // Alt + Key
                altActions[e.which]['action'](selectedTab);
            }

            return;
        }
    });


    $.klear.removeTabs = function() {
        $.klear.canvas.tabs('destroy');
        $("#canvasWrapper").html($.klear.baseCanvas);
        $.klear.canvas = $("#canvas");
        $.klear.loadCanvas();
    };

    $.klear.restart = function(opts, removetabs) {
        if (removetabs === true) {
            $.klear.removeTabs();
        }
        $.klear.requestSearchTranslations();

        $.klear.menu(true, opts);
        $.klear.loadedTemplates = {};
        $.klear.requestReloadTranslations();

    };

    $.klear.start = function() {
        /*
         * Setting klear.baseurl value
         */
        $.klear.baseurl = $.klear.baseurl || $("base").attr("href");

        $.klear.language = $('html').attr('lang');


        $.klear.baseCanvas = $("#canvasWrapper").html();

        /*
         * Setting klear canvas MAIN container.
         */

        $.klear.canvas = $("#canvas");


        /*
         * Loading and binding main container
         */
        $.klear.loadCanvas();
        /*
         * Saying hello to server.
         *
         * - check user
         *
         */
        $.klear.hello();
        /*
         * Global Bindings
         */

        $.klear.keepAlive = function() {
            clearTimeout($.klear.keepAliveTimer);
            $.klear.keepAliveTimer = setTimeout(function() {
                $.klear.hello('setCallback',function() {
                    $.klear.keepAlive();
                });
            }, 300000);
        };

    };


})(jQuery);


/*
 * document ready Klear Launch
 */

;(function($) {

    $(document).ready(function() {

        var __namespace__ = 'klear.documentReady';

        $(document).on('contextmenu', 'a', function (e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        $.klear.start();

        
    	window.onbeforeunload = function (e) {
    		
    		if ($( "#tabsList li").length == 0) {
    			return;
    		}
    		
    		var _warnMsg = $.translate("Do you really want to leave?", __namespace__);
    		
    	    var e = e || window.event;

    	    if (e) {
    	        e.returnValue = _warnMsg;
    	    }

    	    return _warnMsg;
    	};
    	
    	
    	$(window).on("scroll", function() {
            $("#sidebar").trigger("reposition");

        });
    });

})(jQuery);
