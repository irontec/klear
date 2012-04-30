;(function($) {

    $.klear = $.klear || {};

    $.klear.cacheEnabled = true;

    $.klear.removeCache = function() {
        $.klear.loadedScripts = {};
    };

    $.klear.errorCodes = {
            auth : ['1002']
    };


    var __namespace__ = 'klear.buildRequest';

    $.klear.buildRequest = function(params) {
        var options = {
                controller : 'index',
                action: 'dispatch',
                file : 'index',
                post : false
        };

        $.extend(options,params);

        var _validParams = "execute type file screen dialog command pk str namespace".split(" ");
        var _params = {};

        $.each(_validParams,function(idx,_value) {
            if (options[_value]) {
                _params[_value] = options[_value];
            }
        })

        var _type = options.post? 'post':'get';
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
            type: _type
        };
    };

    $.klear.request = function(params,successCallback,errorCallback,context) {

        var caller = arguments;
        var reCall = function() {
            caller.callee.apply(caller.callee, Array.prototype.slice.call(caller));
        }

        var request_baseurl = '';
        var clean_baseurl = '';

        var _parseResponse = function _parseResponse(response) {

            if ( (response.mustLogIn) && (params.controller != 'login') ) {
                if (!params.isLogin) {
                    $.klear.hello('setCallback', reCall);
                }
                $.klear.login();
                return;
            }

            $.klear.login('close');

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
                errorCallback.apply(context,[$.translate("Unknown response format in Simple Response", [__namespace__])]);
                return;
            }
            successCallback.apply(context,[response.data]);
            return;
        };

        var _parseDispatchResponse = function _parseDispatchResponse(response) {

            var responseCheck = ['baseurl', 'templates', 'scripts', 'css', 'data', 'plugin'];

            for(var i=0; i<responseCheck.length; i++) {

                if (typeof response[responseCheck[i]] == 'undefined') {
                    errorCallback.apply(context,[$.translate("Module registration error.", [__namespace__])]);
                    return;
                }
            }

            request_baseurl = response.baseurl;
            clean_baseurl = response.cleanBaseurl;

            $.when(
                    _loadTemplates(response.templates),
                    _loadCss(response.css),
                    _loadScripts(response.scripts)

            ).done( function(tmplReturn,scriptsReturn,cssReturn) {

                var tryOuts = 0;
                (function tryAgain() {

                    if ( (false === response.plugin) || (typeof $.fn[response.plugin] == 'function' ) ) {

                        successCallback.apply(context,[response]);
                        return;

                   } else {

                       if (++tryOuts == 20) {
                           errorCallback.apply(context,[response.plugin + ' plugin not found']);
                           return;
                       } else {
                           window.setTimeout(tryAgain,20);
                       }
                    }
                })();

            }).fail( function( data ){

                errorCallback.apply(context,[$.translate('Module registration error.', [__namespace__]),data]);
            });
        };

        var _errorResponse = function _errorResponse(xhr) {

            try {
                var response = $.parseJSON(xhr.responseText);
            } catch(e) {
                console.log(e);
                //TODO: lanzar error...
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

            errorCallback.apply(context,arguments);
        };


        var _loadTemplates = function(templates) {


            var dfr = $.Deferred();

            var total = 0;
            for(var iden in templates) total++;
            if (total == 0) {
                dfr.resolve();
                return;
            }

            var done = 0;
            var successCallback = function() {
                total--;
                done++;
                if (total == 0) {
                    dfr.resolve(done);
                }
            };

            var targetUrl = '';

            $.each(templates,function(tmplIden,tmplSrc) {

                if ($.klear.cacheEnabled && undefined !== $.template[tmplIden]) {

                    successCallback();
                    return;
                }

                if (typeof tmplSrc == 'object' && typeof tmplSrc.module != 'undefined') {

                    targetUrl = clean_baseurl + "/" + tmplSrc.module + tmplSrc.tmpl;

                } else {

                   targetUrl = request_baseurl + tmplSrc;
                }

                $.ajax({
                    url: targetUrl,
                    dataType:'text',
                    type : 'get',
                    cache : true,
                    success: function(r) {

                        $.template(tmplIden, r);
                        successCallback();
                    },
                    error : function(r) {
                        dfr.reject($.translate("Error downloading template [%s].", tmplIden, [__namespace__]));
                    }
                });
            });
            return dfr.promise();
        };

        var _loadScripts = function(scripts) {

            var dfr = $.Deferred();
            var total = 0;
            for(var iden in scripts) total++;
            if (total == 0) {
                dfr.resolve();
                return;
            }
            var done = 0;
            var isAjax = false;
            var _self = this;

            $.each(scripts, function(iden, _script) {
                if ($.klear.cacheEnabled && $.klear.loadedScripts[iden]) {
                    total--;
                    return;
                }
                if ("" == _script) {
                    total--;
                    return;
                }
                isAjax = true;


                var targetUrl = "";
                if (typeof _script == 'object' && typeof _script.module != 'undefined') {

                    targetUrl = clean_baseurl + "/" + _script.module + _script.tmpl;

                } else {

                   targetUrl = request_baseurl + _script;
                }

                try {
                $.ajax({
                        url: targetUrl,
                        dataType:'script',
                        type : 'get',
                        cache : true,
                        async: true,
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

        var _loadCss = function(css) {
            var dfr = $.Deferred();
            var total = $(css).length;

            if (total == 0) {
                dfr.resolve();
                return;
            }

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

        var req = $.klear.buildRequest(params);

        if (params.external) {

            //La petición se realizará sobre un iframe oculto, no se controla response
            var _name = 'ftarget' + Math.round(Math.random(1000,1000));
            var _theForm = $("<form />")
                                .attr({action: req.action,method: req.type, target: _name});

            for(var _idx in req.data) {
                $("<input>")
                    .attr("name",_idx)
                    .attr("type","hidden")
                    .val(req.data[_idx])
                    .appendTo(_theForm);
            }

            _theForm
                .appendTo('body')
                .on('submit',function() {
                    var $self = $(this);
                    setTimeout(function() {
                        $self.remove();
                    },10000);
                })
                .trigger('submit');

            successCallback(true);

            return false;
        }

        $.ajax({
            url : req.action,
               dataType:'json',
               context : this,
               data : req.data,
               type : req.type,
               success: _parseResponse,
               error: _errorResponse
        });
    };

})(jQuery);