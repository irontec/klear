var console = window.console || { log : function() {}};

(function() {

    var _base = document.getElementsByTagName('base')[0].getAttribute('href');
    var _loadIndicator = document.createElement("div");
    var _loader = document.createElement("div");
    _loader.setAttribute("class","initialLoader");
    _loader.appendChild(_loadIndicator);

    _loader.curPercent = 0;
    _loader.target = 0;
    _loader.total = 0;

    (function lazyLoader() {

        if (!document.getElementsByTagName('body')[0]) {
            setTimeout(lazyLoader,10);
            return;
        }

        document.getElementsByTagName('body')[0].appendChild(_loader);

        _loader.interval = setInterval(function() {

            if (_loader.total == 0) {
                return;
            }

            var _percentTarget = parseInt((100*_loader.target)/_loader.total);
            _loader.firstChild.innerHTML = _loader.curPercent + '%';

            if (_percentTarget >= _loader.curPercent) {

                _loader.curPercent++;
                _loader.firstChild.style.width = _loader.curPercent + '%';

                if (_loader.curPercent == 100) {
                    clearInterval(_loader.interval);
                    // Ya tenemos jQuery
                    $(_loader).fadeOut("slow",function() {
                        $(this).remove();
                    });
                }
            }
        }, 2);
    })();

    if (document.documentElement.getAttribute("data-stage") == "development") {
        var _baseScripts = [
            'base!js/plugins/jquery.cookie.js',
            'base!js/plugins/jquery.scrollabletab.js',
            'base!js/plugins/jquery.ui.tooltip.js',
            'base!js/plugins/jquery.selectBoxIt.js',
            'base!js/scripts/spin.min.js',
            'base!js/plugins/jquery.getStylesheet.js',
            'base!js/plugins/jquery.translate.js',
            'base!js/translation/jquery.klear.translation.js',
            'base!../default/js/translation/jquery.default.translation.js',
            'base!js/plugins/jquery.klear.request.js',
            'base!js/plugins/jquery.klear.module.js',
            'base!js/plugins/jquery.klear.module.dialog.js',
            'base!js/plugins/jquery.klear.errors.js',
            'base!js/navigation.js'
        ];
    } else  {
        var _baseScripts = [
            'base!js/klear.compiled.js',
            'base!js/translation/jquery.klear.translation.js',
            'base!../default/js/translation/jquery.default.translation.js',
            'base!js/navigation.js',
            'base!../klearMatrix/js/translation/jquery.klearmatrix.translation.js',
            'base!../klearMatrix/js/klearMatrix.compiled.js'
        ];
    }
    var _scripts = [];

    // El total de cargas serán los "base" + los 4 principales
    _loader.total = _baseScripts.length + 4;

    var _noCDN = document.getElementsByTagName('head')[0].getAttribute("rel") &&
    document.getElementsByTagName('head')[0].getAttribute("rel") == 'noCDN';

    yepnope.addPrefix('base', function(resourceObj) {

        resourceObj.url =  _base.replace(/\/\w+\.php\//, '/') + resourceObj.url;
        return resourceObj;
    });

    yepnope.addPrefix('cdnCheck', function(resourceObj) {
        if (_noCDN) {
            // Si estamos en un sistema "super seguro" que evita utilizar CDNs, evitamos también el preload
            resourceObj.url = 'about:blank';
            resourceObj.noexec = true;
        }

        return resourceObj;
    });

    var _seekAndDestroy = function(realSrc) {

        var _primitive = function(realSrc,node,attr) {
            var _sc = document.getElementsByTagName( node );
            for (var i=0; i<_sc.length; i++) {
                var _cSc = _sc[i];
                if (_cSc.getAttribute(attr) == realSrc) {
                    _cSc.setAttribute(attr, '');
                    _cSc.parentNode.removeChild(_cSc);
                    return;
                }
            }
        };
        setTimeout(function() {
            _primitive(realSrc,'object','data');
        },500);
    };

    yepnope['errorTimeout'] = 3500;

    yepnope([
        {
            load: {
                'jquery.min.js': 'timeout=1000!cdnCheck!base!js/libs/jquery.min.js',
                'jquery.tmpl.min.js': 'timeout=1000!cdnCheck!base!js//libs/jquery.tmpl.js',
                'jquery-ui.min.js': 'other!timeout=1000!cdnCheck!base!js//libs/jquery-ui.min.js',
                'ie-jquery-ui.min.js': 'ielt10!cdnCheck!base!js/libs/ie-jquery-ui.js',
                'jquery-ui-i18n.min.js': 'timeout=1000!cdnCheck!base!js//libs/jquery-ui-i18n.min.js'
            },
            callback : function(url, i, idx) {
                switch(idx) {
                    case 'jquery.min.js':
                        if (!window.jQuery) {
                            _seekAndDestroy(url);
                        }
                        break;
                    case 'jquery.tmpl.min.js':
                        if (!window.jQuery || !window.jQuery.tmpl) {
                            _seekAndDestroy(url);
                        }
                        break;
                    case 'jquery-ui.min.js':
                        if (!window.jQuery || !window.jQuery.ui) {
                            _seekAndDestroy(url);
                        }
                        break;
                    case 'ie-jquery-ui.min.js':
                        if (!window.jQuery || !window.jQuery.ui) {
                            _seekAndDestroy(url);
                        }
                        break;
                    case 'jquery-ui-i18n.min.js':
                        if (!window.jQuery || !window.jQuery.ui) {
                            _seekAndDestroy(url);
                        }
                        break;
                }
            },

            complete: function() {

                if (window.jQuery && window.jQuery.ui && window.jQuery.tmpl) {
                    _loader.target += 4;
                } else {
                    _scripts.push('base!js/libs/jquery.min.js');
                    _scripts.push('base!js/libs/jquery.tmpl.min.js');
                    _scripts.push('other!base!js/libs/jquery-ui.min.js');
                    _scripts.push('base!ielt10!js/libs/ie-jquery-ui.js');
                    _scripts.push('base!js/libs/jquery-ui-i18n.min.js');
                }

                yepnope([{
                    load:_scripts.concat(_baseScripts),
                    callback : function() {
                        _loader.target++;
                    }
                }]);
            }
        }
    ]);
})();
