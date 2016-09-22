/*
 * jQuery Klear
 */
;
(function($) {

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
        for (var i = 0; i < depLength; i++) {
            var segments = dependencies[i].split('.');
            var prev = window;
            for (var j = 0; j < segments.length; j++) {
                if (typeof prev[segments[j]] == 'undefined') {
                    setTimeout(function() {
                        callback($);
                    }, 100 * (callback._numberOfTries + 1));
                    return false;
                } else {
                    prev = prev[segments[j]];
                }
            }
        }
        return true;
    };

    /*
     * Checking open in background Event: control | middle click
     */

    $.klear.checkNoFocusEvent = function(e, $el, $link) {
        
        if (e.ctrlKey) {
            $el.data('noFocus', true);
            return;
        }

        if (typeof $link == 'undefined') {
            return;
        }

        var button;
        if (e.which == null) {
            if (!e.button) {
                return;
            }
            /* IE case */
            button = (e.button < 2) ? "LEFT" : ((event.button == 4) ? "MIDDLE"
                    : "RIGHT");
        } else {
            /* All others */
            button = (e.which < 2) ? "LEFT" : ((e.which == 2) ? "MIDDLE"
                    : "RIGHT");
        }

        if (button == 'MIDDLE') {
            e.stopPropagation();
            e.preventDefault();

            var prevHref = $link.attr("href");
            $link.removeAttr("href");
            var $_link = $link;
            setTimeout(function() {
                $_link.attr("href", prevHref);
            }, 100);
            $el.data('noFocus', true);
        }
    };

    $.klear.klearDialog = function(msg, options) {

        $.extend(options, {
            icon : options.icon || 'ui-icon-info',
            state : options.state || 'default',
            text : msg || ''
        });

        var dialogSettings = {
            title : '<span class="ui-icon inline dialogTitle ' + options.icon
                    + ' "></span>' + options.titleText,
            modal : true,
            resizable : false,
            close : function(ui) {
                $(this).remove();
            }
        };

        $.extend(dialogSettings, options);

        var dialogTemplate = dialogSettings.template
                || '<div class="ui-widget"><div class="ui-state-${state} ui-corner-all inlineMessage"><p><span class="ui-icon ${icon} inlineMessage-icon"></span>{{html text}}</p></div></div>';
        var $parsedHtml = $.tmpl(dialogTemplate, dialogSettings);
        $parsedHtml.dialog(dialogSettings);
        return $parsedHtml;
    };

    $.klear.klearMessage = function(msg, opts) {
        var options = {
            type : 'msg',
            icon : 'ui-icon-comment',
            titleText : 'Klear Message Window'
        };
        opts = (typeof opts == 'undefined') ? {} : opts;
        $.extend(options, opts);
        $.klear.klearDialog(msg, options);
    };

    $.klear.klearWarn = function(msg, opts) {
        var options = {
            type : 'warn',
            icon : 'ui-icon-info',
            titleText : 'Klear Warning Window'
        };
        opts = (typeof opts == 'undefined') ? {} : opts;
        $.extend(options, opts);
        $.klear.klearDialog(msg, options);
    };

    $.klear.klearError = function(msg, opts) {
        var options = {
            type : 'error',
            icon : 'ui-icon-alert',
            state : 'highlight',
            titleText : 'Klear Error Window'
        };
        opts = (typeof opts == 'undefined') ? {} : opts;
        $.extend(options, opts);
        $.klear.klearDialog(msg, options);
    };

    /*
     * Hello Klear Server
     */

    $.klear.hello = function(option) {

        var options = {
            controller : 'index',
            action : 'hello'
        };

        this.callback = this.callback || [];

        switch (option) {
        case 'rememberCallback':
            this.callback.push(arguments[1]);
            return;
            break;
        case 'setCallback':
            this.callback.push(arguments[1]);
            break;
        case 'options':
            // completamos las opciones de klear.request con las enviadas como
            // segundo parámetro
            $.extend(options, arguments[1]);
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
                } while (self.callback.length > 0);
            }
        };

        $.klear._doHelloError = function(response) {
            $.console.log(response);
        };

        $.klear.request(options, $.klear._doHelloSuccess,
                $.klear._doHelloError, this);
    };

    $.klear.menu = function(force, options) {

        options = (typeof options == 'undefined') ? {} : options;

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
                if (response.data.jqLocale == 'en') {
                    $.datepicker.setDefaults($.datepicker.regional['']);
                } else {
                    $.datepicker
                            .setDefaults($.datepicker.regional[response.data.jqLocale]);
                }
            }

            $sidebar.empty();
            $sidebar.accordion("destroy");
            $headerbar.empty();
            $footerbar.empty();
            $infobar.empty();

            $.tmpl('klearSidebarMenu', navMenus.sidebar).appendTo($sidebar);
            $.tmpl('klearHeaderbarMenu', navMenus.headerbar).appendTo(
                    $headerbar);
            $.tmpl('klearFooterbarMenu', navMenus.footerbar).appendTo(
                    $footerbar);
            $.tmpl('klearInfoBar').appendTo($infobar);

            // Este template no lo queremos cacheado nunca
            $.template['klearInfoBar'] = undefined;

            $sidebar.stop().fadeIn();
            $headerbar.stop().fadeIn();
            $footerbar.stop().fadeIn();
            $infobar.stop().fadeIn();

            $("a", $sidebar).tooltip();
            $("a", $headerbar).tooltip();
            $("a", $footerbar).tooltip();

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

            $.klear.request({
                controller : 'error',
                action : 'list'
            }, function(response) {
                $.klear.addErrors(response.data);
                $(document).trigger("kErrorsLoaded");
            }, function() {
                console.error("errors.yaml not found!");
            });

            $.klear.keepAlive();

            /*
             * JQ Decorartors
             */
            var sideBarOffset = $sidebar.offset();

            $sidebar.accordion({
                icons : {
                    header : "ui-icon-circle-arrow-e",
                    headerSelected : "ui-icon-circle-arrow-s"
                },
                collapsible : true,
                autoHeight : false
            });

            $sidebar.on('reposition', function() {
                
                if (!$(this).is(":visible") || $(window).width() <= '680') {
                    return;
                }

                var _target = $(window).scrollTop();
                if (_target < sideBarOffset.top) {
                    _target = 0;
                    $(this).css({
                        opacity : "1"
                    });
                } else {
                    _target -= sideBarOffset.top;
                }

               if (!response.data.disableFixed) {
                   $(this).stop().animate({
                       'marginTop' : _target + 'px'
                   }, 0, 'easeOutQuad');

                   /*
                    * Hacemos que la barra de tabs, esté siempre arriba Corregimos
                    * en 7px para que se ajuste al marco superior.
                    */
                   _target -= 7;
                   if (_target > 0) {
                       $("#tabsList").css("position", "absolute");
                   } else {
                       _target = 0;
                   }
                   $("#tabsList").stop().animate({
                       'marginTop' : _target + 'px'
                   }, 0, 'easeOutQuad', function() {
                       if (_target <= 0) {
                           $(this).css({
                               position : "relative",
                               opacity : "1"
                           });
                       }
                   });

                   $("#tabsListNavArrows").stop().animate({
                       'marginTop' : _target + 'px'
                   }, 0, 'easeOutQuad', function() {
                   });
               }
            });

            $("li", $sidebar).on("mouseenter", function() {
                $(this).addClass("ui-state-highlight");
            }).on("mouseleave", function() {
                $(this).removeClass("ui-state-highlight");
            });

            self.loaded = true;
            $toolsBar = $("#headerToolsbar");
            $toolsBar.html(navMenus.toolsbar).buttonset();
            $("label", $toolsBar).tooltip();

            $(".pickableLang", $toolsBar).off('change').on('change',
                    function() {
                        $.klear.language = $(this).val();
                        $.klear.restart({
                            'language' : $(this).val()
                        });
                        $("#langPicker", $toolsBar).trigger("change");
                    });

            $("#langPicker", $toolsBar).off('change').on(
                    'change',
                    function() {
                        if ($(".pickableLanguage", $toolsBar).hasClass(
                                "expanded")) {
                            $(".pickableLanguage", $toolsBar).removeClass(
                                    "expanded").css("display", "inline")
                                    .animate({
                                        width : '85px'
                                    });
                        } else {
                            $(".pickableLanguage", $toolsBar).animate(
                                    {
                                        width : '0'
                                    },
                                    function() {
                                        $(this).addClass("expanded").css(
                                                "display", "none");
                                    });
                        }
                    });

            $("#logout", $toolsBar).off('change').on('change', function() {
                var $self = $(this);
                $.getJSON($self.data('url'), {
                    json : true
                }, function() {
                    $sidebar.fadeOut('fast');
                    $infobar.fadeOut();
                    // custom klear events
                    $(document).trigger("kLogout");
                    $.klear.restart({}, true);
                });
            });

            $("#tabsPersist", $toolsBar).off('change').on('change', function() {
                var $self = $(this);
                if ($.klear.tabPersist.enabled()) {
                    $.klear.tabPersist.disable();
                } else {
                    $.klear.tabPersist.enable();
                }
                $self.trigger('update-icon');
            });

            $("input#tabsPersist", $toolsBar).off('update-icon').on(
                    'update-icon',
                    function() {
                        var $self = $(this).next('label');
                        var $icon = $('.ui-icon', $self);
                        if ($.klear.tabPersist.enabled()) {
                            $icon.removeClass('ui-icon-unlocked').addClass(
                                    'ui-icon-locked');
                            $self.addClass('ui-state-active');
                        } else {
                            $icon.removeClass('ui-icon-locked').addClass(
                                    'ui-icon-unlocked');
                        }
                    });

            $("#hideTabLabelNames", $toolsBar).off('change').on('change',
                    function() {
                        var $self = $(this);
                        if ($.klear.tabNamesFunction.areVisible()) {
                            $.klear.tabNamesFunction.hide();
                        } else {
                            $.klear.tabNamesFunction.show();
                        }
                        $self.trigger('update-icon');
                    });

            $("#hideTabLabelNames", $toolsBar).off('update-icon').on(
                    'update-icon',
                    function() {
                        var $self = $(this).next('label');
                        var $icon = $('.ui-icon', $self);
                        if ($.klear.tabNamesFunction.areVisible()) {
                            $icon.removeClass('ui-icon-arrowstop-1-e')
                                    .addClass('ui-icon-arrowstop-1-w');
                        } else {
                            $icon.removeClass('ui-icon-arrowstop-1-w')
                                    .addClass('ui-icon-arrowstop-1-e');
                        }
                        $self.removeClass('ui-state-active');
                    });

            $.klear.tabNamesFunction.loadSavedState();

            $("#menuCollapse", $toolsBar).off('change').on('change',
                    function() {
                        $.klear.toggleMenu();
                    });

            $("#menuCollapse", $toolsBar).off('update-icon').on(
                    'update-icon',
                    function() {
                        var $self = $(this).next('label');
                        var $icon = $('.ui-icon', $self);
                        if ($.klear.isMenuCollapsed()) {
                            $icon.removeClass('ui-icon-triangle-1-w').addClass(
                                    'ui-icon-triangle-1-e');
                            $self.addClass('ui-state-active');
                        } else {
                            $icon.removeClass('ui-icon-triangle-1-e').addClass(
                                    'ui-icon-triangle-1-w');
                        }
                    });

            $("#headerCollapse", $toolsBar).off('change').on('change',
                    function() {
                        $.klear.toggleHeader();
                    });

            $("#headerCollapse", $toolsBar).off('update-icon').on(
                    'update-icon',
                    function() {
                        var $self = $(this).next('label');
                        var $icon = $('.ui-icon', $self);
                        if ($.klear.isHeaderCollapsed()) {
                            $icon.removeClass('ui-icon-triangle-1-n').addClass(
                                    'ui-icon-triangle-1-s');
                            $self.addClass('ui-state-active');
                        } else {
                            $icon.removeClass('ui-icon-triangle-1-s').addClass(
                                    'ui-icon-triangle-1-n');
                        }
                    });

            $("#superCollapse", $toolsBar).off('change').on('change',
                    function() {
                        $.klear.toggleAll();
                    });

            $("#superCollapse", $toolsBar).off('update-icon').on(
                    'update-icon',
                    function() {
                        var $self = $(this).next('label');
                        var $icon = $('.ui-icon', $self);
                        if ($.klear.isHeaderCollapsed()) {
                            $icon.removeClass('ui-icon-triangle-1-nw')
                                    .addClass('ui-icon-triangle-1-se');
                            $self.addClass('ui-state-active');
                        } else {
                            $icon.removeClass('ui-icon-triangle-1-se')
                                    .addClass('ui-icon-triangle-1-nw');
                        }
                    });

            $("#themeRoller", $toolsBar).off('change').on('change', function() {
                var $self = $(this);
                $self.button('widget').removeClass('ui-state-active');
                if (!$("#themeRollerSelector").hasClass("active")) {
                    $("#themeRollerSelector").show('fast', function() {
                        $(this).selectBoxIt({
                            theme : "jqueryui",
                            autoWidth : true,
                            viewport : $(window)
                        });
                    });
                    $("#themeRollerSelector").addClass("active");
                }

                if ($("#themeRollerSelector").hasClass("open")) {
                    $("#themeRollerSelector").removeClass("open");
                    $("#themeRollerSelectorSelectBoxItContainer").fadeOut();
                } else {
                    $("#themeRollerSelector").addClass("open");
                    $("#themeRollerSelectorSelectBoxItContainer").fadeIn();
                }
            });

            $("#themeRollerSelector", $toolsBar).off('change').on('change',
                    function() {
                        $("#currentTheme")[0].href = $(this).val();
                        $.klear.request({
                            controller : 'index',
                            action : 'hello',
                            theme : $("option:selected", $(this)).text()
                        }, function(response) {
                        }, function() {
                        });
                    });

            $("#generalHelp", $toolsBar).off('change').on('change', function() {
                var $self = $(this);
                $self.button('widget').removeClass('ui-state-active');
                $.klear.toggleHelpDialog();
            });

            $toolsBar.show();
            
            if (!options.isRestart) {
                $.klear.tabPersist.loadData().launch();
            }
            
            if (options.openedSection) {
            
                $("#sidebar").accordion('option','active', options.openedSection)
            }
            
            $(document).trigger('kMenuSuccess');
        };

        $.klear._doMenuError = function(response) {
            $.console.log(response);
        };

        var settings = $.extend({
            controller : 'menu',
            action : 'index'
        }, options);

        $(document).trigger("kMenuStartLoad");

        $.klear.request(settings, $.klear._doMenuSuccess,
                $.klear._doMenuSuccess, this);

        $.klear.tabPersist = {
            tabs : [],
            add : function(iden) {
                for ( var i in this.tabs) {
                    if (this.tabs[i] == iden)
                        return;
                }
                this.tabs.push(iden);
                this.save();
            },
            remove : function(iden) {
                var tabs = [];
                for ( var i in this.tabs) {
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
                for ( var i in this.tabs) {
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
            loadData : function() {

                if (this.enabled()) {
                    var tabs = JSON.parse(localStorage.getItem('tabPersist'));
                    for ( var i in tabs) {
                        this.add(tabs[i]);
                    }
                    $("#tabsPersist").trigger('update-icon');
                } else {
                    if ($('a.subsection.autoplay:eq(0)').length == 1) {
                        this.add('#'
                                + $('a.subsection.autoplay:eq(0)').attr("id"));
                    }
                }
                return this;

            },
            launch : function() {

                var menuButton = null;
                for ( var i in this.tabs) {
                    menuButton = this.tabs[i].replace(/#tabs-/, '#target-');
                    $(menuButton).trigger('mouseup', true);
                }

                // Seleccionamos el tab del ultimo menú principal seleccionado
                if ($(menuButton).length > 0) {
                    var _parent = $(menuButton).parents('div:eq(0)');
                    if (_parent.is(".ui-accordion-content")) {
                        var _idx = $("h2", $sidebar).index(_parent.prev('h2'));
                        if ($("#sidebar").accordion('option', 'active') != _idx) {
                            $("#sidebar").accordion('option', 'active', _idx);
                        }
                    }
                }
            },
            enable : function() {
                localStorage.setItem('tabPersistEnabled', '1');
            },
            disable : function() {
                localStorage.setItem('tabPersistEnabled', '0');
            },
            enabled : function() {
                return localStorage.getItem('tabPersistEnabled') == '1';
            }
        };

        $sidebar.add($headerbar).add($footerbar);

        $('body').on("mousedown", "a.subsection", function(e) {
            $(this).data("pressed", true);
        }).on("mouseout", "a.subsection", function(e) {
            $(this).data("pressed", false);
        }).on(
                "mouseup",
                "a.subsection",
                function(e, force) {
                    e.preventDefault();
                    e.stopPropagation();
                    if ($(this).data("pressed") !== true && force !== true) {
                        return;
                    }

                    var iden = $(this).attr("id").replace(/^target-/, '');
                    $.klear.checkNoFocusEvent(e, $.klear.canvas, $(this));
                    var tabTitle = $(this).text() != "" ? $(this).text() : $(
                            this).parent().attr('title');

                    if ($("#tabs-" + iden).length > 0) {
                        // Actualizo su título, por si acaso soy una opción
                        // externa con un título "disfrazado"
                        $.klear.canvas.tabs('select', '#tabs-' + iden).find(
                                ".ui-tabs-selected:eq(0)").klearModule(
                                "updateTitle", tabTitle);
                        return;
                    }
                    var idContent = "#tabs-" + iden;
                    $.klear.canvas.tabs("add", idContent, tabTitle);
                    $.klear.tabPersist.add('#' + $(this).attr("id"));

                }).on("click", "a.subsection", function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
    };

    var $sidebar = $("#sidebar");
    // Fix para expendiente X menú apagado... :(
    $sidebar.on('mouseenter', function() {
        $(this).animate({
            opacity : '1'
        });
    });

    $.klear.toggleMenu = function() {

        if ($sidebar.data("seized")) {

            $sidebar.animate({
                width : menuMeasures.getWidth() + 'px'
            });
            $(".textnode", $sidebar).stop().animate({
                opacity : '1',
                'font-size' : menuMeasures.getFontSize()
            });
            $("li", $sidebar).animate({
                padding : "0.5em"
            });
            $("#sidebar h2").removeClass('iconsidebar');
            $(".textnode").removeClass('compact');
            $sidebar.data("seized", false);

        } else {

            $(".textnode", $sidebar).animate({
                fontSize : '0em',
                opacity : '0'
            });
            $sidebar.animate({
                width : '50px'
            });
            $("li", $sidebar).animate({
                padding : "0em"
            });
            $sidebar.data("seized", true);
            $("#sidebar h2").addClass('iconsidebar');
            $(".textnode", $sidebar).addClass('compact');
        }

        localStorage.setItem('toogleMenu', $.klear.isMenuCollapsed());
        $("#menuCollapse").trigger('update-icon');
        $("#superCollapse").trigger('update-icon');
    };

    $.klear.toggleHeader = function() {

        $appLogo = $("#applicationLogo");

        if ($.klear.isHeaderCollapsed()) {

            $("#header").fadeOut(function() {
                $(this).removeClass("collapsedHeader").fadeIn();
            });

            $appLogo.animate({
                opacity : '1',
                height : '100px'
            });
            $("#applicationTools").animate({
                marginTop : '0px'
            });
            $appLogo.data("seized", false);

            if ($("#footer").hasClass("collapsedFooter")) {
                $("#footer").fadeOut(function() {
                    $(this).removeClass("collapsedFooter").fadeIn();
                });
            }

        } else {

            $("#header").fadeOut(function() {
                $(this).addClass("collapsedHeader").fadeIn();
            });
            $appLogo.animate({
                opacity : '0',
                height : '0'
            });
            $("#applicationTools").animate({
                marginTop : '-40px'
            });
            $appLogo.data("seized", true);

            if ($("#footer").height() > '50') {
                $("#footer").fadeOut(function() {
                    $(this).addClass("collapsedFooter").fadeIn();
                });
            }
        }

        localStorage.setItem('toogleHeader', $.klear.isHeaderCollapsed());
        $("#headerCollapse").trigger('update-icon');
        $("#superCollapse").trigger('update-icon');
    };

    $.klear.toggleAll = function() {
        var initialState = $.klear.isMenuCollapsed();
        $.klear.toggleMenu();
        if (initialState == $.klear.isHeaderCollapsed()) {
            $.klear.toggleHeader();
        }
    };

    $.klear.isMenuCollapsed = function() {
        return $("#sidebar").data("seized") || false;
    };

    $.klear.isHeaderCollapsed = function() {
        return $("#applicationLogo").data("seized") || false;
    };

    $.klear.tabNamesFunction = {
        loadSavedState : function() {
            if (localStorage.getItem('hideTabNames') == 'true') {
                $.klear.tabNamesFunction.hide();
            } else {
                $.klear.tabNamesFunction.show();
            }
            $("#hideTabLabelNames").trigger("update-icon");
        },
        areVisible : function() {
            return !$("#tabsList").hasClass("hideLabelNames");
        },
        show : function() {
            localStorage.setItem('hideTabNames', false);
            $("#tabsList").removeClass("hideLabelNames");
        },
        hide : function() {
            localStorage.setItem('hideTabNames', true);
            $("#tabsList").addClass("hideLabelNames");

        }
    };

    $.klear.helpDialog = {};

    $.klear.toggleHelpDialog = function() {

        if ($.klear.helpDialog.dialogObj
                && $.klear.helpDialog.dialogObj.dialog('isOpen') === true) {
            $.klear.helpDialog.dialogObj.dialog('close');
            return;
        }

        if (!$.klear.helpDialog.fixedHelpList) {
            var generalHelpLi = [];
            for ( var keyNum in $.klear.ctrlAltActions) {
                if ($.klear.ctrlAltActions[keyNum]['title'] != undefined) {
                    var title = $.klear.ctrlAltActions[keyNum]['title'];
                    var key = $.klear.ctrlAltActions[keyNum]['key'];
                    generalHelpLi.push('<li>Ctrl+Alt+<strong>' + key
                            + '</strong>: ' + title + '');
                }
            }
            $.klear.helpDialog.fixedHelpList = '<ul>' + generalHelpLi.join(' ')
                    + '</ul>';
        }

        var openedScreensHelp = "";

        $("#tabsList li").each(
                function() {

                    var $self = $(this);
                    var currentScreenShortCuts = $self
                            .klearModule('getShortcuts');
                    var title = $self.klearModule('getTitle');
                    var panel = $self.klearModule('getPanel');
                    var screenHelpLi = [];
                    for ( var chartCode in currentScreenShortCuts) {
                        var chartString = String.fromCharCode(chartCode);
                        screenHelpLi.push('<li>Ctrl+Alt+<strong>'
                                + chartString
                                + '</strong>: '
                                + $("[data-shortcut=" + chartString + "]",
                                        panel).text() + '');
                    }
                    if (screenHelpLi.length <= 0) {
                        return;
                    }
                    var helpUl = '<ul>' + screenHelpLi.join(' ') + '</ul>';
                    openedScreensHelp += '<div class="inlineDialogHelp" >'
                            + title + ':' + helpUl + '</div>';
                });

        var $primaryToolsBar = $("label.primary", $toolsBar);
        var $tmp = $("<div />");
        var $tmpUl = $("<ul />", {
            'class' : 'toolsBarHelp'
        });

        $primaryToolsBar.each(function() {
            var $self = $(this);
            var $tmpLi = $("<li />");
            $tmpLi.append($self.find(".ui-button-text").html());
            $tmpLi.append($self.attr('title'));
            $tmpUl.append($tmpLi);
        });

        $tmp.append($tmpUl);
        $.klear.helpDialog.dialogObj = $.klear
                .klearDialog(
                        "",
                        {
                            width : '450',
                            title : '<span class="ui-icon inline dialogTitle ui-icon-info "></span>'
                                    + $.translate("Global help") + "",
                            type : 'msg',
                            icon : 'ui-icon-comment',
                            template : '<div class="ui-widget">'
                                    + '<div class="ui-state-default ui-corner-all inlineMessage inlineDialogHelpBox">'
                                    + '<p class="dialogTitle">'
                                    + $
                                            .translate("Shortcuts information Ctrl+Alt+<em>[KEY]</em>")
                                    + '</p>'
                                    + ((openedScreensHelp != "") ? openedScreensHelp
                                            : '')
                                    + '<div class="inlineDialogHelp" ><p class="dialogSubtitle">'
                                    + $.translate("Klear")
                                    + ':</p>'
                                    + $.klear.helpDialog.fixedHelpList
                                    + '</div>'
                                    + '</div>'
                                    + '<div class="ui-state-default ui-corner-all inlineMessage inlineDialogHelpBox">'
                                    + '<p class="dialogTitle">'
                                    + $.translate("Toolsbar information")
                                    + '</p>'
                                    + '<div class="inlineDialogHelp" >'
                                    + $tmp.html() + '</div>' + '</div>'
                                    + '</div>',
                            close : function() {
                                $.klear.helpDialog.dialogObj.dialog('destroy');
                                delete $.klear.helpDialog.dialogObj;
                            }
                        });
    };

    var menuMeasures = {
        data : {
            normal : {
                fontsize : '1em',
                width : 300
            },
            small : {
                fontsize : '0.70em',
                width : 180
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
            $sidebar.data("seized", !$sidebar.data("seized"));
            $.klear.toggleMenu();
        }
    };

    if ($("#applicationInfo").attr("data-rememberScroll") == "true") {
        $(window).on("scroll", function(){
            $(".ui-tabs-selected").data("scrollposition", $(window).scrollTop());
        });
    }
    
    $(window).on('resize', menuMeasures.loadSize);
    $(document).on("kMenuLoaded", menuMeasures.loadSize);
    
    $("body").on('click', 'a.toogleMenu', function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        $.klear.toggleMenu();
        $.klear.toggleHeader();
    });

    $.klear.login = function(option) {
        switch (option) {
        case 'close':
            if (this.$loginForm && $(this.$loginForm).is(":visible")) {
                this.$loginForm.fadeOut(function() {
                    $(this).remove();
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

            self.$loginForm.appendTo("#canvas").dialog(
                    {
                        resizable : false,
                        modal : true,
                        draggable : false,
                        stack : true,
                        width : '45%',
                        minHeigth : '350px',
                        dialogClass : 'loginDialog',
                        closeOnEscape : false,
                        open : function(event, ui) {
                            $("p.submit input", self.$loginForm).button();
                            $("input:text:eq(0)", self.$loginForm).trigger(
                                    "focusin").select();
                        }
                    });

            setTimeout(function() {
                $loginToolsBar = $("#loginToolsbar", self.$loginForm);
                $loginToolsBar.buttonset();
                $("#loginToolsbar", self.$loginForm).fadeIn();
                var tempIntCounter = 0;
                var tempInt = null;
                tempInt = setInterval(function() {
                    tempIntCounter++;
                    $("#langPickerLogin").button('widget').toggleClass(
                            'ui-state-highlight');
                    if (tempIntCounter == 6) {
                        clearInterval(tempInt);
                    }
                }, 200);
                $("#langPickerLogin").siblings('label[for=langPickerLogin]')
                        .addClass('ui-corner-right');
                $(".pickableLang", $loginToolsBar).off('change').on('change',
                        function() {
                            $.klear.language = $(this).val();
                            $.klear.restart({
                                'language' : $(this).val()
                            });
                        });
                $("#langPickerLogin", $loginToolsBar).off('change').on(
                        'change',
                        function() {
                            if ($(".pickableLanguage", $loginToolsBar)
                                    .hasClass("expanded")) {
                                $(".pickableLanguage", $loginToolsBar)
                                        .removeClass("expanded").css("display",
                                                "inline").animate({
                                            width : '85px'
                                        });
                            } else {
                                $(".pickableLanguage", $loginToolsBar).animate(
                                        {
                                            width : '0'
                                        },
                                        function() {
                                            $(this).addClass("expanded").css(
                                                    "display", "none");
                                        });
                            }
                        });
            }, 1000);

            $("select", self.$loginForm).selectBoxIt({
                theme : "jqueryui"
            });
            $("input", self.$loginForm).removeAttr("disabled");
            $("input:submit", self.$loginForm).button();
            $("input:text:eq(0)", self.$loginForm).trigger("focusin").select();

            if ($("div.loginError", self.$loginForm).length > 0) {
                self.$loginForm.effect("shake", {
                    times : 3
                }, 60);
            }

            $("form", self.$loginForm.parent()).on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $.klear.hello('options', {
                    post : $(this).serialize(),
                    isLogin : true
                });

                $("input", self.$loginForm).attr("disabled", "disabled");
            });
        };

        $.klear._doLoginError = function(response) {
            $.console.log(response);
        };

        $.klear.request({
            controller : 'login',
            action : 'index'
        }, $.klear._doLoginSuccess, $.klear._doLoginSuccess, this);

        return this;
    };

    $.klear.loadCanvas = function() {
        /*
         * TABS
         */

        var tabTemplate = "<li title='#{label}'><span class='ui-silk'></span>"
                + "<span class='ui-icon ui-icon-close'></span>"
                + "<span class='ui-icon ui-icon-arrowrefresh-1-w'></span>"
                + "<a href='#{href}'>#{label}</a></li>";

        $.klear.canvas.tabs(
                {
                    tabTemplate : tabTemplate,
                    scrollable : true,
                    add : function(event, ui) {
                        if ($(ui.tab).parents('ul').css('display') == 'none') {
                            $(ui.tab).parents('ul').fadeIn();
                        }
                        var backgroundTab = false;
                        if ($(this).data('noFocus') === true) {
                            $(this).data('noFocus', false);
                            backgroundTab = true;
                        }

                        if (backgroundTab !== true) {
                            $.klear.canvas.tabs('select', ui.index);
                            $("html, body").animate({
                                scrollTop : 0
                            }, 600);
                        }

                        var $tabLi = $(ui.tab).parent("li");

                        $tabLi.klearModule({
                            ui : ui,
                            container : $.klear.canvas
                        }).tooltip();

                        // Se invoca custom event para actualizar objeto
                        // klear.module (si fuera necesario);
                        $.klear.canvas.trigger("tabspostadd", ui);
                        $tabLi.klearModule("dispatch");

                        $("li", $.klear.canvas).each(function(idx, elem) {
                            $(elem).klearModule("option", "tabIndex", idx);
                        });
                    },
                    select : function(event, ui) {
                        $("#tabsList li").each(function(idx, elem) {
                            $(elem).klearModule("highlightOff");
                        });

                        var $tabLi = $(ui.tab).parent("li");
                        $tabLi.klearModule("updateLoader");
                    },
                    show : function(event, ui) {
                        $(ui.panel).trigger("focusin");
                        var position = 0;
                        if ($("#applicationInfo").attr("data-rememberScroll") == "true") {
                            if ($(".ui-tabs-selected").data("scrollposition")) {
                                position = $(".ui-tabs-selected").data("scrollposition");
                            }
                        }
                        $('html, body').animate({
                            scrollTop: position
                            }, 600);
                    },
                    remove : function(event, ui) {

                        var iden = '#' + $(ui.panel).attr("id");
                        $.klear.tabPersist.remove(iden);
                        $("li", $.klear.canvas).each(function(idx, elem) {
                            $(elem).klearModule("option", "tabIndex", idx);
                        });
                        $.klear.canvas.tabs('select', $.klear.canvas.tabs(
                                'option', 'selected'));
                    }
                }).find(".ui-tabs-nav").sortable({
            axis : "x"
        });

        /*
         * CLOSE
         */
        $("#tabsList").on("click", "span.ui-icon-close", function() {
            var $tab = $(this).parent("li");
            $tab.klearModule("close");
        });

        $("#tabsList").on("click", "span.ui-icon-arrowrefresh-1-w", function() {
            var $tab = $(this).parent("li");
            $tab.klearModule("reDispatch");
        });
    };

    $.klear.ctrlAltActions = {
        81 : {
            key : 'Q',
            title : $.translate("close other tabs."),
            action : function(selectedTab) {
                $li = $("#tabsList :not(li:eq(" + selectedTab + "))");
                $li.klearModule('close');
            }
        },
        87 : {
            key : 'W',
            title : $.translate("closes current tab."),
            action : function(selectedTab) {
                $li = $("#tabsList li:eq(" + selectedTab + ")");
                $li.klearModule('close');
            }
        },
        82 : {
            key : 'R',
            title : $.translate("reloads current tab."),
            action : function(selectedTab) {
                $li = $("#tabsList li:eq(" + selectedTab + ")");
                $li.klearModule('reDispatch');
            }
        },
        34 : {
            key : 'RePag',
            title : $.translate("goes to previous tab."),
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
            title : $.translate("goes to next tab."),
            action : function(selectedTab) {
                selectedTab--;
                selectedTab = selectedTab < 0 ? $("#tabsList li").length - 1
                        : selectedTab;
                $.klear.canvas.tabs('select', selectedTab);
            }
        },
        67 : { // c
            key : 'C',
            action : function(selectedTab) {
                $.klear.cacheEnabled = !$.klear.cacheEnabled;
                $.console.log($.klear.cacheEnabled ? "Cache Habilitada"
                        : "Cache Deshabilitada");

            }
        },
        77 : {
            key : 'M',
            title : $.translate("toggles menu view."),
            action : function() {
                $.klear.toggleMenu();
            }
        },
        72 : {
            key : 'H',
            title : $.translate("toggles header view."),
            action : function() {
                $.klear.toggleHeader();
            }
        },
        88 : {
            key : 'X',
            title : $.translate("toggles fullscreen view."),
            action : function() {
                $.klear.toggleAll();
            }
        },
        71 : {
            key : 'G',
            action : function() {
                $.console.toggleDebugInfo();
            }
        },
        73 : {
            key : 'I',
            title : $.translate("toggles help dialog."),
            action : function() {
                $.klear.toggleHelpDialog();
            }
        },
        69 : {
            key : 'E',
            title : $.translate("toggles unselected tab names."),
            action : function() {
                $("#hideTabLabelNames", $toolsBar).trigger("change");
            }
        }
    };

    $(document).on(
            "keydown",
            function(e) {

                var altActions = {
                    37 : { // Disable alt + back arrow shortcut on browser
                        key : 'backArrow',
                        action : function() {
                            return;
                        }
                    }
                };

                if (e.altKey && e.ctrlKey || e.altKey && altActions[e.which]) {

                    var selectedTab = parseInt($.klear.canvas.tabs('option',
                            'selected'));

                    if (e.altKey && e.ctrlKey) {
                        if ($.klear.ctrlAltActions[e.which]) {
                            // Ctrl + Alt + Key
                            e.preventDefault();
                            e.stopPropagation();
                            $.klear.ctrlAltActions[e.which]['action']
                                    (selectedTab);
                        } else {
                            // Not a "registerd shortcut=?
                            // Handle it to klearModule, see if there is
                            // something to be done ;)
                            $("#tabsList li:eq(" + selectedTab + ")")
                                    .klearModule('shortcut', e);
                        }
                    } else {
                        // Alt + Key
                        e.preventDefault();
                        e.stopPropagation();
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
        } else {
            $.extend(opts, {
                openedSection : $("#sidebar").accordion('option', 'active')
            });
        }

        opts.isRestart = true;
        
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
         *  - check user
         * 
         */
        $.klear.hello();
        /*
         * Global Bindings
         */

        $.klear.keepAlive = function() {
            clearTimeout($.klear.keepAliveTimer);
            $.klear.keepAliveTimer = setTimeout(function() {
                $.klear.hello('setCallback', function() {
                    $.klear.keepAlive();
                });
            }, 300000);
        };
    };

})(jQuery);

/*
 * document ready Klear Launch
 */

;
(function($) {

    $(document).ready(function() {

        var __namespace__ = 'klear.documentReady';

        $(document).on('contextmenu', 'a', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        $.klear.start();

        window.onbeforeunload = function(e) {

            if ($("#tabsList li").length == 0) {
                return;
            }

            var _warnMsg = $.translate("Do you really want to leave?");
            e = (typeof e == 'undefined') ? window.event : e;

            if (e) {
                e.returnValue = _warnMsg;
            }

            return _warnMsg;
        };

        $(window).on("scroll", function() {
            $("#sidebar").trigger("reposition");
        });
    });

    $.console = {
        debugInfo : false,
        info : function() {
            if ($.console.debugInfo && console
                    && typeof console.info == 'function') {
                var message = Array.prototype.slice.apply(arguments).join(' ');
                console.info(message);
            }
        },
        log : function(message) {
            if (console && typeof console.log == 'function') {
                console.log(message);
            }
        },
        toggleDebugInfo : function() {
            $.console.setDebugInfo(!$.console.debugInfo);
        },
        setDebugInfo : function(value) {
            value = value ? true : false;
            $.console.log(((value) ? 'ACTIVATING' : 'DEACTIVATING')
                    + ' debug Info.');
            localStorage.setItem('debugInfo', value);
            $.console.debugInfo = value;
        },
        init : function() {
            $.console.debugInfo = $("html:eq(0)").data("stage") == "development";
            if (localStorage.getItem('debugInfo')) {
                $.console
                        .setDebugInfo(localStorage.getItem('debugInfo') == 'true');
            }
        }
    };
    $.console.init();

})(jQuery);
