;(function($) {

    var checkQueues = true;
    $.klear = $.klear || {};

    $.klear.cacheEnabled = true;

    $.klear.removeCache = function() {
        $.klear.loadedScripts = {};
        $.klear.loadedTemplates = {};
    };

    $.klear.errorCodes = {
            auth : ['1002']
    };


    var __namespace__ = 'klear.buildRequest';

    var scriptsInQueue = {},
        templatesInQueue = {};
    
    $.klear.buildRequest = function(params) {
        var options = {
                controller : 'index',
                action: 'dispatch',
                file : 'index',
                post : false
        };

        $.extend(options,params);

        var _validParams = "execute type file screen dialog command pk language str namespace theme".split(" ");
        var _params = {};

        $.each(_validParams,function(idx,_value) {
            if (options[_value]) {
                _params[_value] = options[_value];
            }
        });

        var _type = options.post? 'post':'get';
        var _async = options.async != undefined ? options.async : true;

        var _action = $.klear.baseurl + options.controller + '/' + options.action;

        if (_type == 'post') {
            _action += '?' + $.param(_params);
            _data = options.post;
        } else {
            _data = _params;
        }

        return {
            action: _action,
            data: _data,
            type: _type,
            async: _async
        };
    };

    $.klear.requestTranslations = new Array();
    $.klear.requestSearchTranslations = function(){
        $('script[src*="js/translation"]', $('head')).each(function(){
            $.klear.requestTranslations.push($(this).attr('src'));
        });
    };

    $.klear.requestReloadTranslations = function(){
        var l = $.klear.requestTranslations.length;
        var done = {};
        var nScr = [];
        for (var i = 0; i<l; i++) {
            if (done[$.klear.requestTranslations[i]] != undefined) continue;
            done[$.klear.requestTranslations[i]] = true;
            var $el = $('script[src="'+$.klear.requestTranslations[i]+'"]');
            if ($el.length>0) {
                $el.remove();
            }
            $.ajax({
                 url: $.klear.requestTranslations[i] + '?'+(new Date).getTime() + '&language='+$.klear.language,
                 dataType:'script',
                 type : 'get',
                 cache : true,
                 async: true,
                 success: function() {},
                 error : function(r) {}
            });
            nScr.push($.klear.requestTranslations[i]);
        }
        $.klear.requestTranslations = nScr;
    };

    $.klear.request = function(params,successCallback,errorCallback,context) {

        var _arguments = arguments;
        var reCall = function() {
            _arguments.callee.apply(_arguments.callee, Array.prototype.slice.call(_arguments));
        };

        var request_baseurl = '';
        var clean_baseurl = '';

        if (context && context.element) {
            var loaderTrace = function(action, param) {
                var p = param || true;
                $(context.element).klearModule("option",action, p);
            };
        } else {
            var loaderTrace = function() {};
        }
        var totalItems = 0;

        var _parseResponse = function _parseResponse(response) {
            if (response == null) return;
            if ( (response.mustLogIn) && (params.controller != 'login') ) {
                if (!params.isLogin) {
                    $.klear.hello('rememberCallback', reCall);
                }
                $.klear.login();
                return;
            }

            $.klear.login('close');
            loaderTrace("mainModuleLoaded");
            switch(response.responseType) {
                case 'dispatch':
                    return _parseDispatchResponse(response);
                case 'simple':
                    return _parseSimpleResponse(response);
                case 'redirect':
                    window.location = response.data;
                    break;
                default:
                    errorCallback.apply(context,["Unknown response type"]);
                break;
            }

        };

        var _parseSimpleResponse = function _parseSimpleResponse(response) {

            if (!response.data) {
                errorCallback.apply(context,[$.translate("Unknown response format in Simple Response")]);
                return;
            }
            successCallback.apply(context,[response.data]);
            return;
        };

        var _parseDispatchResponse = function _parseDispatchResponse(response) {

            var responseCheck = ['baseurl', 'templates', 'scripts', 'css', 'data', 'plugin'];

            for(var i=0; i<responseCheck.length; i++) {

                if (typeof response[responseCheck[i]] == 'undefined') {
                    errorCallback.apply(context,[$.translate("Module registration error.")]);
                    return;
                }
            }

            request_baseurl = response.baseurl;
            clean_baseurl = response.cleanBaseurl;

            var doCount = function(object, iden) {
                var _len = 0;
                for(var iden in object) {
                    _len++;
                }
                return _len;
            };

            $.when(
                    _loadTemplates(response.templates, doCount(response.templates,'template')),
                    _loadCss(response.css, doCount(response.css,'css')),
                    _loadScripts(response.scripts, doCount(response.scripts,'scripts'))

            ).done( function(tmplReturn,scriptsReturn,cssReturn) {

                var tryOuts = 0;
                (function tryAgain() {

                    if ( (false === response.plugin) || (typeof $.fn[response.plugin] == 'function' ) ) {
                        successCallback.apply(context,[response]);
                        return;

                   } else {
                       if (++tryOuts == 40) {
                           errorCallback.apply(context,[response.plugin + ' plugin not found']);
                           return;
                       } else {
                           window.setTimeout(tryAgain,50);
                       }
                    }
                })();

            }).fail( function( data ){

                errorCallback.apply(context,[$.translate('Module registration error.'),data]);
            });
        };

        var _errorResponse = function _errorResponse(xhr) {

            try {

                var response = $.parseJSON(xhr.responseText);

            } catch(e) {
                var response = {
                        message : $.translate("Undefined Error"),
                        raw : xhr.responseText
                };
            }

            if ( ( (response.mustLogIn) && (params.controller != 'login') ) ||
                ($.inArray(response.code,$.klear.errorCodes.auth) != -1) )
                {
                if (!params.isLogin) {
                    $.klear.hello('setCallback', reCall);
                }
                $.klear.login();
                return;
            }

            errorCallback.apply(context,[response]);
        };


        var _loadTemplates = function(templates, total) {


            var dfr = $.Deferred();

            loaderTrace("addToBeLoadedFile", total);

            if (total == 0) {
                dfr.resolve();
                return;
            }

            var done = 0;
            var successCallback = function() {
                loaderTrace("addLoadedFile");
                total--;
                done++;
                if (total == 0) {
                    dfr.resolve(done);
                }
            };

            var targetUrl = '';

            $.each(templates,function(tmplIden,tmplSrc) {

                if ($.klear.cacheEnabled && $.klear.loadedTemplates[tmplIden]) {
                    successCallback();
                    return;
                }
                
                // ALREADY IN QUEUE
                if (checkQueues && templatesInQueue[tmplIden]) {
                    templatesInQueue[tmplIden].push(successCallback);
                    return;
                }

                if (typeof tmplSrc == 'object' && typeof tmplSrc.module != 'undefined') {

                    targetUrl = clean_baseurl + "/" + tmplSrc.module + tmplSrc.tmpl;

                } else {

                   targetUrl = request_baseurl + tmplSrc;
                }
                
                if (!templatesInQueue[tmplIden]) {
                    templatesInQueue[tmplIden] = [];
                }
                
                $.ajax({
                    url: targetUrl,
                    dataType:'text',
                    type : 'get',
                    cache : true,
                    success: function(r) {
                        $.template(tmplIden, r);
                        $.klear.loadedTemplates[tmplIden] = true;
                        successCallback();
                        for(var i in templatesInQueue[tmplIden]) {
                            templatesInQueue[tmplIden][i]();
                        }
                        templatesInQueue[tmplIden] = false;
                    },
                    error : function(r) {
                        dfr.reject($.translate("Error downloading template [%s].", tmplIden));
                    }
                });
            });
            return dfr.promise();
        };

        var _checkScript = function(script) {

            if (script.match(/js\/translation/)) {
                $.klear.requestTranslations.push(script);
            }

        };

        var _loadScripts = function(scripts, total) {

            var dfr = $.Deferred();
            loaderTrace("addToBeLoadedFile", total);

            if (total == 0) {
                dfr.resolve();
                return;
            }
            var done = 0;
            var isAjax = false;
            var _self = this;

            $.each(scripts, function(iden, _script) {
                if ($.klear.cacheEnabled && $.klear.loadedScripts[iden]) {
                    loaderTrace("addLoadedFile");
                    total--;
                    return;
                }
                if ("" == _script) {
                    loaderTrace("addLoadedFile");
                    total--;
                    return;
                }
                
                // ALREADY IN QUEUE
                if (checkQueues && true == scriptsInQueue[iden]) {
                    loaderTrace("addLoadedFile");
                    total--;
                    return;
                }
                
                
                isAjax = true;


                var targetUrl = "";
                if (typeof _script == 'object' && typeof _script.module != 'undefined') {

                    targetUrl = clean_baseurl + "/" + _script.module + _script.tmpl;

                } else {

                   targetUrl = request_baseurl + _script;
                   _checkScript(targetUrl);

                }
                
                scriptsInQueue[iden] = true;

                try {
                $.ajax({
                        url: targetUrl,
                        dataType:'script',
                        type : 'get',
                        cache : true,
                        async: true,
                        success: function() {
                            $.klear.loadedScripts[iden] = true;
                            loaderTrace("addLoadedFile");
                            total--;
                            done++;
                            if (total == 0) {
                                dfr.resolve(done);
                            }
                        },
                        error : function(r) {
                            scriptsInQueue[iden] = false;
                            dfr.reject("Error downloading script ["+_script+"]");
                            //console.log("Error downloading script ["+_script+"]" , r);
                        }
                 });
                } catch(e) {
                    console.log(e);
                }
            });
            if (!isAjax) {
                return dfr.resolve(0);
            } else {
                return dfr.promise();
            }
        };

        var _loadCss = function(css, total) {
            var dfr = $.Deferred();

            loaderTrace("addToBeLoadedFile", total);

            if (total == 0) {
                dfr.resolve();
                return;
            }

            for(var iden in css) {

                $.getStylesheet(request_baseurl + css[iden],iden);
                $("#" + iden).on("load",function() {
                    loaderTrace("addLoadedFile");
                    total--;
                    if (total == 0) {
                        dfr.resolve(true);
                    }
                });
            }
            dfr.promise(true);
        };

        var req = $.klear.buildRequest(params);

        if (params.external) {

            //La petición se realizará sobre un iframe oculto, no se controla response
            var _name = 'ftarget' + Math.round(Math.random(1000,1000));

            var _iframe = $("<iframe />",{"name":_name}).hide();

            var _theForm = $("<form />")
                                .attr({action: req.action,method: req.type, target: _name});


            $.each($.param(req.data).split('&'),function(idx, val) {
                var _item = val.split('=');
                $("<input>")
                    .attr("name",decodeURIComponent(_item[0]))
                    .attr("type","hidden")
                    .val(_item[1])
                    .appendTo(_theForm);
            });

            _iframe.appendTo("body");
             
            _theForm
                .appendTo('body')
                .on('submit',function() {
                    var $self = $(this);
                    setTimeout(function() {
                        $self.remove();
                        _iframe.remove();
                    },100000);
                })
                .trigger('submit');
                
            if (typeof successCallback == 'function') {
                successCallback(true);
            }

            return false;
        }

        $.ajax({
            url : req.action,
            dataType:'json',
            context : this,
            data : req.data,
            async: req.async,
            type : req.type,
            success: _parseResponse,
            error: _errorResponse
        });
    };

})(jQuery);
