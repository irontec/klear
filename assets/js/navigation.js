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

        switch(option) {
            case 'rememberCallback':
                this.callback = arguments[1];
                return;
            break;
            case 'setCallback':
                this.callback = arguments[1];
            break;
            case 'options':
                // completamos las opciones de klear.request con las enviadas como segundo parámetro
                $.extend(options,arguments[1]);
            break;
        }

        var self = this;
        $.klear._doHelloSuccess = function(response) {

            if (response.success && response.success === true) {
                if (self.callback) {
                    self.callback();
                    self.callback = null;
                } else {
                    $.klear.menu();
                }
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

        if (this.loaded && typeof force == 'undefined') {
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

            $sidebar.fadeIn();
            $headerbar.fadeIn();
            $footerbar.fadeIn();
            $infobar.fadeIn();

            $("a",$sidebar).tooltip();
            $("a",$headerbar).tooltip();
            $("a",$footerbar).tooltip();

            if (localStorage.getItem('toogleMenu') === true) {
                $.klear.toggleMenu();
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

            $sidebar.accordion({
                icons : {
                        header: "ui-icon-circle-arrow-e",
                        headerSelected: "ui-icon-circle-arrow-s"
                },
                collapsible: true,
                autoHeight: false
            });

            // collapsible : true, hace que active: 0 no sea activo :S
            // lanzamos evento a  mano
            setTimeout(function() {
            	if ($sidebar.accordion("option","active") === false) {
            		$("#sidebar").accordion('activate',0);
            	}
            },700);

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
            $langBar = $( "#headerLanguagebar" ),
            $langSelector = $( ".langSelector" );

            $langSelector.buttonset();
            $toolsBar.buttonset();

            $( "label", $toolsBar ).tooltip();

            $( "input",  $langSelector).off('change').on('change', function(){
                $.klear.language = $(this).val();
                $.klear.restart({'language': $(this).val()});
            });

            $( "input#logout", $toolsBar ).off('change').on('change', function(){
                var $self = $(this);
                $.getJSON($self.data('url'),{json:true}, function(){
                    $sidebar.fadeOut();
                    $infobar.fadeOut();
                    //custom klear events
                    $(document).trigger("kLogout");
                    $.klear.restart({}, true);
                });
            });

            $( "input#tabsPersist", $toolsBar ).off('change').on('change', function(){
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
                    $icon.removeClass('ui-icon-arrowthickstop-1-w').addClass('ui-icon-arrowthickstop-1-e');
                    $self.addClass('ui-state-active');
                } else {
                    $icon.removeClass('ui-icon-arrowthickstop-1-e').addClass('ui-icon-arrowthickstop-1-w');
                }
            });


            $langBar.show();
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
            save : function() {
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
                if ($(menuButton).length>0) {
                    $(menuButton).parents('.ui-accordion-content').prev('h2').click();
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

            if ($("#tabs-"+iden).length > 0) {
                $.klear.canvas.tabs('select', '#tabs-'+iden);
                return;
            }
            var idContent = "#tabs-" + iden;
            var title = $(this).text()!=""? $(this).text():$(this).parent().attr('title');

            $.klear.canvas.tabs( "add", idContent, title);

            $.klear.tabPersist.add(idContent);


        }).on("click","a.subsection",function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

    };

    var $sidebar = $("#sidebar");
    $.klear.toggleMenu = function() {

        if ($sidebar.data("seized")) {
            $sidebar.animate({width: menuMeasures.getWidth() + 'px'});
            $(".textnode", $sidebar).stop().animate({opacity:'1', 'font-size': menuMeasures.getFontSize()});
            $("li",$sidebar).animate({padding:"0.5em"});

            $sidebar.data("seized",false);
        } else {

            $(".textnode", $sidebar).animate({fontSize:'0em',opacity:'0'});
            $sidebar.animate({width:'50px'});
            $("li",$sidebar).animate({padding:"0em"});
            $sidebar.data("seized",true);
        }

        localStorage.setItem('toogleMenu', $.klear.isMenuCollapsed());
        $( "#menuCollapse").trigger('update-icon');


    };

    $.klear.isMenuCollapsed = function() {
        return $("#sidebar").data("seized");
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

            $("select",self.$loginForm).combobox();
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
            remove: function(event, ui) {

                $.klear.tabPersist.remove($(ui.tab).attr('href'));

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


        $(document).on("keydown",function(e) {

            var ctrlAltActions = {
                87 : {
                    key : 'w',
                    action : function(selectedTab) {
                        $.klear.canvas.tabs('remove', selectedTab);
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

    };

    $.klear.restart = function(opts, removetabs) {


        var removetabs = removetabs || false;
        if (removetabs == true) {
            $.klear.canvas.tabs('destroy');
            $("#canvasWrapper").html($.klear.baseCanvas);
            $.klear.canvas = $("#canvas");
            $.klear.loadCanvas();
        }
        $.klear.requestSearchTranslations();
        $.klear.menu(true, opts);
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

        $(window).on('beforeunload', function(e){
        	e.preventDefault();
        	return $.translate('Do you really want to leave?', __namespace__);
        });

    });

})(jQuery);
