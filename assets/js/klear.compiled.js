jQuery.cookie=function(a,b,c){if(1<arguments.length&&(null===b||"object"!==typeof b)){c=jQuery.extend({},c);null===b&&(c.expires=-1);if("number"===typeof c.expires){var d=c.expires,e=c.expires=new Date;e.setDate(e.getDate()+d)}return document.cookie=[encodeURIComponent(a),"=",c.raw?""+b:encodeURIComponent(""+b),c.expires?"; expires="+c.expires.toUTCString():"",c.path?"; path="+c.path:"",c.domain?"; domain="+c.domain:"",c.secure?"; secure":""].join("")}c=b||{};e=c.raw?function(a){return a}:decodeURIComponent;
return(d=RegExp("(?:^|; )"+encodeURIComponent(a)+"=([^;]*)").exec(document.cookie))?e(d[1]):null};(function(a){a.extend({getStylesheet:function(b,c,d){a("link[type='text/css']",a("head:first"));var c=c||"rand"+Math.floor(1E4*Math.random()),e=a("link#"+c,a("head:first"));0<e.length?e.attr("href",b):a("<link />",{href:b,media:d||"screen",type:"text/css",rel:"stylesheet",id:c}).appendTo("head:first")},removeStylesheet:function(b){try{a("link#"+b).remove()}catch(c){}}})})(jQuery);(function(a){a.klear=a.klear||{};a.klear.errors={};a.klear.addErrors=function(b){a.extend(a.klear.errors,b)};a.klear.fetchErrorByCode=function(b){return"undefined"!=typeof a.klear.errors[b]?a.klear.errors[b]:!1}})(jQuery);(function(a){a.widget("klear.moduleDialog",a.ui.dialog,{_superClass:a.ui.dialog.prototype,_getKlearPosition:function(){return this.options.klearPosition?a(this.options.klearPosition):document.body},_makeDraggable:function(){this.uiDialog.draggable({handle:".ui-dialog-titlebar",containment:this._getKlearPosition()})},_create:function(){this.originalTitle=this.element.attr("title");this.options.isHidden=!1;"string"!==typeof this.originalTitle&&(this.originalTitle="");this.options.title=this.options.title||
this.originalTitle;var b=this,c=b.options,d=c.title||"&#160;",e=a.ui.dialog.getTitleId(b.element),g=(b.uiDialog=a("<div></div>")).appendTo(this._getKlearPosition()).hide().addClass("ui-dialog ui-widget ui-widget-content ui-corner-all "+c.dialogClass).css({zIndex:c.zIndex}).attr("tabIndex",-1).css("outline",0).keydown(function(d){if(c.closeOnEscape&&!d.isDefaultPrevented()&&d.keyCode&&d.keyCode===a.ui.keyCode.ESCAPE){b.close(d);d.preventDefault()}}).attr({role:"dialog","aria-labelledby":e}).mousedown(function(a){b.moveToTop(false,
a)});b.element.show().removeAttr("title").addClass("ui-dialog-content ui-widget-content").appendTo(g);var i=(b.uiDialogTitlebar=a("<div></div>")).addClass("ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix").prependTo(g),f=a('<a href="#"></a>').addClass("ui-dialog-titlebar-close ui-corner-all").attr("role","button").hover(function(){f.addClass("ui-state-hover")},function(){f.removeClass("ui-state-hover")}).focus(function(){f.addClass("ui-state-focus")}).blur(function(){f.removeClass("ui-state-focus")}).click(function(a){b.close(a);
return false}).appendTo(i);(b.uiDialogTitlebarCloseText=a("<span></span>")).addClass("ui-icon ui-icon-closethick").text(c.closeText).appendTo(f);a("<span></span>").addClass("ui-dialog-title").attr("id",e).html(d).prependTo(i);a.isFunction(c.beforeclose)&&!a.isFunction(c.beforeClose)&&(c.beforeClose=c.beforeclose);i.find("*").add(i).disableSelection();c.draggable&&a.fn.draggable&&b._makeDraggable();c.resizable&&a.fn.resizable&&b._makeResizable();b._createButtons(c.buttons);b._isOpen=!1;a.fn.bgiframe&&
g.bgiframe()},close:function(b){var c=this,d,e;this._getKlearPosition().css("overflow","auto");var g=this._getKlearPosition().attr("id");a(window).off("scroll."+g);if(!1!==c._trigger("beforeClose",b))return c.overlay&&c.overlay.destroy(),c.uiDialog.unbind("keypress.ui-dialog"),c._isOpen=!1,c.options.hide?c.uiDialog.hide(c.options.hide,function(){c._trigger("close",b)}):(c.uiDialog.hide(),c._trigger("close",b)),a.ui.dialog.overlay.resize(),c.options.modal&&(d=0,a(".ui-dialog").each(function(){if(this!==
c.uiDialog[0]){e=a(this).css("z-index");isNaN(e)||(d=Math.max(d,e))}}),a.ui.dialog.maxZ=d),c},open:function(){if(!this._isOpen){var b=this,c=b.options,d=b.uiDialog;b.overlay=c.modal?new a.ui.dialog.overlay(b):null;b.overlay.$el.appendTo(this._getKlearPosition());this._getKlearPosition().css("overflow","hidden");b._size();b._position(c.position);d.show(c.show);b.moveToTop(!0);var e=a(window).scrollTop(),g=this._getKlearPosition().attr("id");a(window).on("scroll."+g,function(){var c=a(window).scrollTop()-
e;e=a(window).scrollTop();var d=0>c?"-=":"+=";a(b.uiDialog).animate({top:d+Math.abs(c)+"px"},{duration:350,complete:function(){0>parseFloat(a(this).css("top"))&&a(this).css("top","0px")}})});c.modal&&d.bind("keydown.ui-dialog",function(b){if(b.keyCode===a.ui.keyCode.TAB){var c=a(":tabbable",this),d=c.filter(":first"),c=c.filter(":last");if(b.target===c[0]&&!b.shiftKey)return d.focus(1),!1;if(b.target===d[0]&&b.shiftKey)return c.focus(1),!1}});a(b.element.find(":tabbable").get().concat(d.find(".ui-dialog-buttonpane :tabbable").get().concat(d.get()))).eq(0).focus();
b._isOpen=!0;b._trigger("open");return b}},getContext:function(){return this.element},updateContent:function(b){var c=this,d=a(c.element).height();a(this.element).slideUp("fast",function(){a(this).html(b);var e=(a(this).height()-d)/2;a(c.uiDialog).stop().animate({top:"-="+e+"px"});a(this).slideDown()})},updateTitle:function(b){a(".ui-dialog-title",this.uiDialogTitlebar).html(b)},setAsLoading:function(){a(this.element).html('<br /><div class="loadingCircle"></div><div class="loadingCircle1"></div>')}});
a.extend(a.ui.dialog.overlay,{create:function(b){var c="klearModule"==b.widgetName&&a(b.element).moduleDialog("option","klearPosition")?a(b.element).moduleDialog("option","klearPosition"):document;0===this.instances.length&&(a(c).bind("keydown.dialog-overlay",function(d){a(c).is(":visible")&&(b.options.closeOnEscape&&!d.isDefaultPrevented()&&d.keyCode&&d.keyCode===a.ui.keyCode.ESCAPE)&&(b.close(d),d.preventDefault())}),a(window).bind("resize.dialog-overlay",a.ui.dialog.overlay.resize));c==document&&
(c=document.body);var d=a("<div></div>").addClass("ui-widget-overlay").appendTo(c).css({width:this.width(),height:this.height()});this.instances.push(d);return d}})})(jQuery);(function(a){a.klear=a.klear||{};a.klear.loadedScripts={};a.widget("klear.module",{_create:function(){a.klear.module.instances.push(this.element)},_init:function(){this._initOptions();this.setAsloading();this._initTab()},destroy:function(){a(this.options.menuLink).removeClass("ui-state-highlight");var b=a.inArray(this.element,a.klear.module.instances);-1<b&&a.klear.module.instances.splice(b,1);a.Widget.prototype.destroy.call(this)},_setOptions:function(){a.Widget.prototype._setOptions.apply(this,
arguments)},_setOption:function(b,c){"mainModuleLoaded"===b&&c?this.setMainLoaded():"addToBeLoadedFile"===b?(this.totalToBeLoadedItems+=c,this.updateTotalLoadingItems()):"addLoadedFile"===b?(this.totalLoadedItems++,this.updateCurrentLoadingItem()):a.Widget.prototype._setOption.apply(this,arguments)},enable:function(){},disable:function(){},_trigger:function(){},_getOtherInstances:function(){var b=this.element;return a.grep(a.klear.module.instances,function(a){return a!==b})},options:{ui:null,container:null,
mainEnl:null,title:null,file:null,panel:null,tabIndex:null,baseurl:null,menuLink:null,screen:null,dialog:null,dispatchOptions:{},tabLock:!1,parentScreen:!1,moduleDialog:null,PostDispatchMethod:null,PreDispatchMethod:null},_initOptions:function(){this.options.mainEnl=a("a:first",this.element);this.options.title=a("a:first",this.element).html();this.options.file=a("a:first",this.element).attr("href").replace(/\#tabs\-([^\_]+).*/,"$1");this.options.panel=this.options.ui.panel;this.options.tabIndex=this.options.ui.index;
0<a("#target-"+this.options.file).length&&(this.options.menuLink=a("#target-"+this.options.file))},_initTab:function(){if(this.options.menuLink&&!(0>=a("span.ui-silk",this.options.menuLink).length)){var b=this.options.mainEnl;a("span.ui-silk",this.element).attr("class",this._getTabIconClass()).on("click",function(){a(b).trigger("click")});var c=this;a(this.options.ui.tab).on("mouseup",function(b){button=null==b.which?2>b.button?"LEFT":4==b.button?"MIDDLE":"RIGHT":2>b.which?"LEFT":2==b.which?"MIDDLE":
"RIGHT";if("MIDDLE"==button){b.stopPropagation();b.preventDefault();var e=a(this).attr("href");a(this).removeAttr("href");var g=a(this);setTimeout(function(){g.attr("href",e)},100);c.close()}})}},reload:function(){this._initTab()},dispatch:function(){var b={file:this.options.file};a.extend(b,this.options.dispatchOptions);"function"==typeof this.options.PreDispatchMethod&&this.options.PreDispatchMethod.apply(this);a.klear.request(b,this._parseDispatchResponse,this._errorResponse,this)},_errorResponse:function(){this.setAsloaded();
var b='<span class="ui-silk inline dialogTitle '+this._getTabIconClass()+' "></span>',c=[],c=arguments[0]&&void 0!=arguments[0].message?arguments[0].message:Array.prototype.join.call(arguments,"</em><br /><em>",["klear.module"]);this.showDialogError(a.translate("Module registration error.",["klear.module"])+"<br /><br />"+a.translate("Error: %s.","<em>"+c+"</em>"),{title:a.translate("Klear Module Error",["klear.module"])+" - "+b+"",closeTab:this.options.tabIndex})},_parseDispatchResponse:function(b){this.setAsloaded();
a(this.options.panel).html("");b.mainTemplate&&(b.data.mainTemplate=b.mainTemplate);a(this.element)[b.plugin]({data:b.data});"function"==typeof this.options.PostDispatchMethod&&this.options.PostDispatchMethod.apply(this)},reDispatch:function(){this.setAsloading();this.dispatch()},getPanel:function(){return this.options.panel},getContainer:function(){return this.options.container},close:function(b){if(this.isLocked())if(a(this.options.container).tabs("select",this.options.tabIndex),"function"==typeof this.options.tabLock){if(this.options.tabLock())return}else this.showInlineWarn(a.translate("This tab is locked.",
["klear.module"]));b&&(b.callback&&"function"==typeof b.callback)&&b.callback();a(this.options.container).tabs("remove",this.options.tabIndex)},getModuleDialog:function(){return this.options.moduleDialog},dialogMessageTmpl:'<div class="ui-widget"><div class="ui-state-${state} ui-corner-all inlineMessage"><p><span class="ui-icon ${icon} inlineMessage-icon"></span>{{html text}}</p></div></div>',showDialog:function(b,c){var d={icon:c.icon||"ui-icon-info",state:c.state||"highlight",text:b,resizable:c.resizable||
!1,buttons:c.buttons||null},e=a.tmpl(c.template||this.dialogMessageTmpl,d),g=c.dialogType||"moduleDialog",i=this,f=i._getTabIconClass(),l=!1===c.title?!1:'<span class="ui-icon  inlineMessage-icon dialogTitle '+d.icon+' "></span>'+c.title+""||'<span class="ui-silk inline dialogTitle '+f+' "></span>'+this.options.title+"",j=0==c.closeTab||c.closeTab?c.closeTab.toString():!1;"moduleDialog"==g?(this.options.moduleDialog=e,this.options.moduleDialog.moduleDialog({position:{my:"center bottom",at:"center center",
collision:"none"},buttons:d.buttons,title:l,modal:!0,resizable:d.resizable,klearPosition:this.getPanel(),open:function(){a(i.options.ui.tab).addClass("ui-state-disabled")},close:function(){a(this).moduleDialog("option","isHidden")||(a(i.options.ui.tab).removeClass("ui-state-disabled"),a(this).remove(),j&&i.close())}})):e.dialog({title:'<span class="ui-silk inline dialogTitle '+f+' "></span>'+this.options.title+"",modal:c.modal||!1,close:function(){a(this).remove();j&&i.close()}})},showDialogMessage:function(b,
c){var d={type:"msg",dialogType:"moduleDialog"},c=c||{};a.extend(d,c);this.showDialog(b,d)},showDialogWarn:function(b,c){var d={type:"warn",icon:"ui-icon-alert",dialogType:"moduleDialog"},c=c||{};a.extend(d,c);this.showDialog(b,d)},showDialogError:function(b,c){var d={type:"error",icon:"ui-icon-alert",state:"highlight",dialogType:"moduleDialog"},c=c||{};a.extend(d,c);this.showDialog(b,d)},isLocked:function(){return!1!==this.options.tabLock},lockTab:function(a){"function"==typeof a?this._setOption("tabLock",
a):this._setOption("tabLock",!0)},unLockTab:function(){this._setOption("tabLock",!1)},setAsChanged:function(a){this.element.addClass("changed");this.lockTab(a)},setAsUnChanged:function(){this.element.removeClass("changed");this.unLockTab()},_getTabIconClass:function(){return this.options.menuLink&&0<a("span.ui-silk",this.options.menuLink).length?a("span.ui-silk",this.options.menuLink).attr("class"):this.options.menuLink&&0<a("span.ui-silk",this.options.menuLink.parent()).length?a("span.ui-silk",this.options.menuLink.parent()).attr("class"):
""},inlineMessageTmpl:'<div class="ui-widget"><div class="ui-state-${state} ui-corner-all inlineMessage"><p><span class="ui-icon ${icon} inlineMessage-icon"></span>{{html text}}</p></div></div>',showInline:function(b,c){var d=a.tmpl(this.inlineMessageTmpl,{icon:c.icon?c.icon:"ui-icon-info",state:c.state?c.state:"highlight",text:b});d.prependTo(this.options.panel);var e=parseInt(c.timeout);0<c.timeout&&window.setTimeout(function(){d.fadeOut(function(){d.remove();c.fn&&"function"==typeof c.fn&&fn()})},
e)},showInlineMessage:function(a,c,d){this.showInline(a,{type:"msg",fn:c||null,timeout:0==d||d?d:5E3})},showInlineWarn:function(a,c,d){this.showInline(a,{type:"warn",fn:c||null,timeout:0==d||d?d:5E3,icon:"ui-icon-alert"})},showInlineError:function(a,c,d){this.showInline(a,{type:"error",fn:c||null,timeout:0==d||d?d:5E3,state:"error",icon:"ui-icon-alert"})},dialog:function(b){a("<div title='Aviso'>"+b+"</div>").dialog({open:function(){},position:"center",draggable:!1,resizable:!1})},highlightOn:function(){a(this.element).addClass("ui-state-highlight")},
highlightOff:function(){a(this.element).removeClass("ui-state-highlight")},_loading:!1,setAsloading:function(){this._loading=!0;this.updateLoader()},setAsloaded:function(){this._loading=!1;this.updateLoader()},loadingTmpl:['<div id="loadingTemplate" class="loadingPanel ui-widget-content ui-corner-all">',"<p>${loadingText}</p>",'<p class="extra main">${loadingTextMain}<span class="ui-icon ui-icon-circle-check inline"></span></p>','<p class="extra">${loadingTextExtra}(<span class="current">0</span>/<span class="total">?</span>)<span class="ui-icon ui-icon-circle-check inline"></span></p>',
"</div>"],totalToBeLoadedItems:0,totalLoadedItems:0,updateTotalLoadingItems:function(){var b=a(this.options.panel);a(".loadingPanel",b).find(".total").html(this.totalToBeLoadedItems)},updateCurrentLoadingItem:function(){var b=a(this.options.panel),c=20<100*this.totalLoadedItems/this.totalToBeLoadedItems?100*this.totalLoadedItems/this.totalToBeLoadedItems/100:".2";a(".loadingPanel",b).find(".current").css("opacity",c).html(this.totalLoadedItems)},setMainLoaded:function(){var b=a(this.options.panel);
a(".loadingPanel",b).find("p.main").addClass("complete")},updateLoader:function(){var b=a(this.options.panel);if(0==a(".loadingPanel",b).length)c=a.tmpl(this.loadingTmpl.join(""),{loadingText:a.translate("Loading content",["klear.module"]),loadingTextMain:a.translate("Loading Main Module",["klear.module"]),loadingTextExtra:a.translate("Loading Secondary modules",["klear.module"])}),c.removeAttr("id").spin({lines:8,length:18,width:4,radius:10,trail:100,speed:1.2}).hide().appendTo(b);else var c=a(".loadingPanel",
b);a("<div />").addClass("overlay").css({opacity:"0.6",width:b.width()+"px",height:b.height()+"px"}).appendTo(b);this._loading?(c.show(),a(this.options.ui.tab).addClass("ui-state-disabled")):a(this.options.ui.tab).removeClass("ui-state-disabled")},updateTitle:function(b){console.log(b,a(this.options.ui.tab));b&&""!=b&&a(this.options.ui.tab).html(b)}});a.extend(a.klear.module,{instances:[]});a.widget.bridge("klearModule",a.klear.module)})(jQuery);(function(a){a.klear=a.klear||{};a.klear.cacheEnabled=!0;a.klear.removeCache=function(){a.klear.loadedScripts={}};a.klear.errorCodes={auth:["1002"]};a.klear.buildRequest=function(b){var c={controller:"index",action:"dispatch",file:"index",post:!1};a.extend(c,b);var d={};a.each("execute type file screen dialog command pk language str namespace".split(" "),function(a,b){c[b]&&(d[b]=c[b])});var b=c.post?"post":"get",e=a.klear.baseurl+c.controller+"/"+c.action;"post"==b?(e+="?"+a.param(d),_data=c.post):
_data=d;return{action:e,data:_data,type:b}};a.klear.requestTranslations=[];a.klear.requestSearchTranslations=function(){a('script[src*="js/translation"]',a("head")).each(function(){a.klear.requestTranslations.push(a(this).attr("src"))})};a.klear.requestReloadTranslations=function(){for(var b=a.klear.requestTranslations.length,c={},d=[],e=0;e<b;e++)if(void 0==c[a.klear.requestTranslations[e]]){c[a.klear.requestTranslations[e]]=!0;var g=a('script[src="'+a.klear.requestTranslations[e]+'"]');0<g.length&&
g.remove();a.ajax({url:a.klear.requestTranslations[e]+"?"+(new Date).getTime()+"&language="+a.klear.language,dataType:"script",type:"get",cache:!0,async:!0,success:function(){},error:function(){}});d.push(a.klear.requestTranslations[e])}a.klear.requestTranslations=d};a.klear.request=function(b,c,d,e){var g=arguments,i=function(){g.callee.apply(g.callee,Array.prototype.slice.call(g))},f="",l="",j=e&&e.element?function(b,c){a(e.element).klearModule("option",b,c||!0)}:function(){},k=function(b){for(var h=
"baseurl templates scripts css data plugin".split(" "),m=0;m<h.length;m++)if("undefined"==typeof b[h[m]]){d.apply(e,[a.translate("Module registration error.",["klear.buildRequest"])]);return}f=b.baseurl;l=b.cleanBaseurl;h=function(a,b){var c=0;for(b in a)c++;return c};a.when(q(b.templates,h(b.templates,"template")),r(b.css,h(b.css,"css")),o(b.scripts,h(b.scripts,"scripts"))).done(function(){var h=0;(function u(){!1===b.plugin||"function"==typeof a.fn[b.plugin]?c.apply(e,[b]):20==++h?d.apply(e,[b.plugin+
" plugin not found"]):window.setTimeout(u,20)})()}).fail(function(b){d.apply(e,[a.translate("Module registration error.",["klear.buildRequest"]),b])})},q=function(b,c){var d=a.Deferred();j("addToBeLoadedFile",c);if(0==c)d.resolve();else{var h=0,e=function(){j("addLoadedFile");c--;h++;0==c&&d.resolve(h)},m="";a.each(b,function(b,c){a.klear.cacheEnabled&&void 0!==a.template[b]?e():(m="object"==typeof c&&"undefined"!=typeof c.module?l+"/"+c.module+c.tmpl:f+c,a.ajax({url:m,dataType:"text",type:"get",
cache:!0,success:function(c){a.template(b,c);e()},error:function(){d.reject(a.translate("Error downloading template [%s].",b,["klear.buildRequest"]))}}))});return d.promise()}},p=function(b){b.match(/js\/translation/)&&a.klear.requestTranslations.push(b)},o=function(b,c){var d=a.Deferred();j("addToBeLoadedFile",c);if(0==c)d.resolve();else{var h=0,e=!1;a.each(b,function(b,m){if(a.klear.cacheEnabled&&a.klear.loadedScripts[b])j("addLoadedFile"),c--;else if(""==m)j("addLoadedFile"),c--;else{e=!0;var s=
"";"object"==typeof m&&"undefined"!=typeof m.module?s=l+"/"+m.module+m.tmpl:(s=f+m,p(s));try{a.ajax({url:s,dataType:"script",type:"get",cache:!0,async:!0,success:function(){a.klear.loadedScripts[b]=true;j("addLoadedFile");c--;h++;c==0&&d.resolve(h)},error:function(){d.reject("Error downloading script ["+m+"]")}})}catch(t){console.log(t)}}});return e?d.promise():d.resolve(0)}},r=function(b,c){var d=a.Deferred();j("addToBeLoadedFile",c);if(0==c)d.resolve();else{for(var h in b)a.getStylesheet(f+b[h],
h),a("#"+h).on("load",function(){j("addLoadedFile");c--;0==c&&d.resolve(!0)});d.promise(!0)}},n=a.klear.buildRequest(b);if(b.external){var t="ftarget"+Math.round(Math.random(1E3,1E3)),h=a("<iframe />",{name:t}).hide(),m=a("<form />").attr({action:n.action,method:n.type,target:t});a.each(a.param(n.data).split("&"),function(b,c){var d=c.split("=");a("<input>").attr("name",decodeURIComponent(d[0])).attr("type","hidden").val(d[1]).appendTo(m)});h.appendTo("body");m.appendTo("body").on("submit",function(){var b=
a(this);setTimeout(function(){b.remove();h.remove()},1E5)}).trigger("submit");"function"==typeof c&&c(!0);return!1}a.ajax({url:n.action,dataType:"json",context:this,data:n.data,type:n.type,success:function(h){if(null!=h)if(h.mustLogIn&&"login"!=b.controller)b.isLogin||a.klear.hello("rememberCallback",i),a.klear.login();else switch(a.klear.login("close"),j("mainModuleLoaded"),h.responseType){case "dispatch":return k(h);case "simple":h.data?c.apply(e,[h.data]):d.apply(e,[a.translate("Unknown response format in Simple Response",
["klear.buildRequest"])]);break;case "redirect":window.location=h.data;break;default:d.apply(e,["Unknown response type"])}},error:function(c){try{var h=a.parseJSON(c.responseText)}catch(m){h={message:a.translate("Undefined Error",["klear.buildRequest"]),raw:c.responseText}}h.mustLogIn&&"login"!=b.controller||-1!=a.inArray(h.code,a.klear.errorCodes.auth)?(b.isLogin||a.klear.hello("setCallback",i),a.klear.login()):d.apply(e,[h])}})}})(jQuery);(function(a){a.xui||(a.xui={});var b=a.extend({},a.ui.tabs.prototype),c=b._create,d=b._update;a.xui.tabs=a.extend(b,{options:a.extend({},b.options,{scrollable:!1,closable:!1,animationSpeed:500}),_create:function(){var b=this.options;c.apply(this);if(b.scrollable){var d=this.element;d.parent().is(".ui-scrollable-tabs")&&d.parent(".ui-scrollable-tabs").replaceWith(d);d.parent().width();var i=d.wrap("<div></div>").parent().addClass("ui-scrollable-tabs ui-widget-content ui-corner-all"),f=this.element.find(".ui-tabs-nav:first").removeClass("ui-corner-all"),
l=a('<ol class="ui-helper-reset ui-helper-clearfix ui-tabs-nav-arrows"></ol>').prependTo(i),j=a('<li class="ui-tabs-arrow-previous ui-state-default ui-widget-content" title="Previous"><a href="#"><span class="ui-icon ui-icon-carat-1-w">Previous tab</span></a></li>').prependTo(l),k=a('<li class="ui-tabs-arrow-next ui-state-default ui-widget-content" title="Next"><a href="#"><span class="ui-icon ui-icon-carat-1-e">Next tab</span></a></li>').appendTo(l);a.fn.reverse=[].reverse;var q=function(a){var b=
{};b.right=a.next("li").length!=0?a.next("li")[0].offsetLeft+15:a[0].offsetLeft+a.outerWidth(true);b.right=b.right+f[0].offsetLeft>l.width();b.left=a[0].offsetLeft+f[0].offsetLeft<0+(j.is(":visible")?j.outerWidth():0);return b},p=function(a,c){if(a!="none")if(a=="left"){j.show("fade");c.next("li").length==0&&k.hide("fade");var d=0,d=c.next("li").length!=0?c.next("li")[0].offsetLeft:c[0].offsetLeft+c.outerWidth(true),d=l.width()-d,d=d-(c.next("li").length==0?1:k.outerWidth());f.animate({"margin-left":d+
"px"},b.animationSpeed)}else{k.show("fade");c.prev("li").length==0&&j.hide("fade");d=0;d=c.prev("li").length==0?2:j.outerWidth()+2;d=(c[0].offsetLeft-d)*-1;f.animate({"margin-left":d},b.animationSpeed)}},o=function(a){return a?a?a[0].offsetLeft+a.outerWidth(true):f.find("li:last")[0].offsetLeft+f.find("li:last").outerWidth(true):0},r=function(){if(o()>l.width())k.show("fade");else{k.hide("fade");j.hide("fade");f.css("margin-left",0)}},n=function(c){b.closable&&(c||f.addClass("ui-tabs-closable").find("li")).each(function(){var b=
a(this).addClass("stHasCloseBtn");a(this).append(a("<span/>").addClass("ui-state-default ui-corner-all ui-tabs-close").hover(function(){a(this).toggleClass("ui-state-hover")}).append(a("<span/>").addClass("ui-icon ui-icon-circle-close").html("Close").attr("title","Close this tab").click(function(){d.tabs("remove",b.prevAll("li").length)})))})};a.fn.refreshTabs=function(){var a=f.find("li.ui-tabs-selected");d.trigger("tabsselect",[{tab:a.find("a")}]);if(o()>l.width()){a.next("li").length!=0&&k.show("fade");
a=f.find("li:last");a=a[0].offsetLeft+a.outerWidth(true);a=l.width()-f[0].offsetLeft-a;if(a>1){f.css("margin-left",f[0].offsetLeft+a-1);k.hide("fade")}}else{k.hide("fade");j.hide("fade");f.css("margin-left",0)}};(function(){n();r();k.on("click",function(a){a.preventDefault();a.stopPropagation();a=f.find("li.ui-tabs-selected").next("li");a.length&&a.find("a").trigger("click")});j.on("click",function(a){a.preventDefault();a.stopPropagation();a=f.find("li.ui-tabs-selected").prev("li");a.length&&a.find("a").trigger("click")});
d.bind("tabsselect",function(b,c){var d=a(c.tab).parent();d.next("li").length==0&&k.hide("fade");var e=q(d);p(e.right?"left":e.left?"right":"none",d)}).bind("tabsadd",function(b,c){a(c.tab).parent();k.show("fade")}).bind("tabsremove",function(){if(b.closable){if(d.tabs("length")==1){d.find("li .ui-tabs-close").hide();f.removeClass("ui-tabs-closable");d.trigger("tabsselect")}if(o()<l.width()){k.hide("fade");j.hide("fade")}else{q(f.find("li:last")).right||p("left",f.find("li:last"));r()}}})})()}return this},
_update:function(){d.apply(this)}});a.widget("xui.tabs",a.xui.tabs)})(jQuery);(function(a){a.extend({translate:function(b){var c=arguments,d=arguments.length;if(0>=d)return"0";var e=null;1<d&&"object"==typeof arguments[d-1]&&(e=arguments[d-1][0]);var g=b.replace(/'/g,"").replace(/"/g,"");void 0==a.translations[g]?void 0!=a.translationRegister&&a.translationRegister(b,e):b=a.translations[g];d=null==e?d:d-1;for(e=1;e<d;e++)void 0!=c[e]&&(b=b.replace(/%s/,c[e]));return b},addTranslation:function(b){a.extend(a.translations,b)},translations:{}})})(jQuery);(function reLoader(){"undefined"==typeof jQuery?setTimeout(reLoader,100):$.extend({translationRegister:function(b,c){$.klear.request({controller:"index",action:"registertranslation",namespace:"javascript/"+c,str:b},function(){},function(){})}})})();(function(a){a.widget("ui.combobox",{_create:function(){var b,c=this,d=this.element.hide(),e=d.children(":selected"),e=e.val()?e.text():"",g=this.wrapper=a("<span>").addClass("ui-combobox").insertAfter(d);(function f(){c.element&&0>=c.element.width()?setTimeout(f,1E3):(g.css("width",c.element.outerWidth()+25+"px"),a("a.ui-combobox-toggle",g).css("left",c.element.outerWidth()-5+"px"))})();b=a("<input>").appendTo(g).val(e).addClass("ui-state-default ui-combobox-input").css("width","78%").autocomplete({delay:0,
minLength:0,source:function(b,c){var e=RegExp(a.ui.autocomplete.escapeRegex(b.term),"i");c(d.children("option").map(function(){var c=a(this).text();if(this.value&&(!b.term||e.test(c)))return{label:c.replace(RegExp("(?![^&;]+;)(?!<[^<>]*)("+a.ui.autocomplete.escapeRegex(b.term)+")(?![^<>]*>)(?![^&;]+;)","gi"),"<strong>$1</strong>"),value:c,option:this}}))},select:function(a,b){b.item.option.selected=!0;c._trigger("selected",a,{item:b.item.option})},change:function(c,e){if(!e.item){var g=RegExp("^"+
a.ui.autocomplete.escapeRegex(a(this).val())+"$","i"),k=!1;d.children("option").each(function(){if(a(this).text().match(g))return this.selected=k=!0,!1});if(!k)return a(this).val(""),d.val(""),b.data("autocomplete").term="",!1}},close:function(){}}).addClass("ui-widget ui-widget-content ui-corner-left");setTimeout(function(){b.removeClass("ui-corner-all")},500);b.data("autocomplete")._renderItem=function(b,c){return a("<li></li>").data("item.autocomplete",c).append("<a>"+c.label+"</a>").appendTo(b)};
a("<a>").attr("tabIndex",-1).attr("title",a.translate("Show All Items",["klear"])).appendTo(g).button({icons:{primary:"ui-icon-triangle-1-s"},text:!1}).css({width:"25px",opacity:"0.8"}).removeClass("ui-corner-all").addClass("ui-corner-right ui-combobox-toggle").on("mousedown",function(){a(b).addClass("ui-state-disabled")}).on("mouseleave",function(){a(b).removeClass("ui-state-disabled")}).on("click",function(){a(b).removeClass("ui-state-disabled");b.autocomplete("widget").is(":visible")?b.autocomplete("close"):
(a(this).trigger("focusout"),b.autocomplete("search",""),b.focus())})},destroy:function(){this.wrapper.remove();this.element.show();a.Widget.prototype.destroy.call(this)}})})(jQuery);(function(a){a(document.body).is("[role]")||a(document.body).attr("role","application");var b=0;a.widget("ui.tooltip",{options:{tooltipClass:"ui-widget-content",content:function(){return a(this).attr("title")},position:{my:"center bottom",at:"center bottom",offset:"50 50"}},_init:function(){var c=this;this.showTimeout;this.tooltip=a("<div></div>").attr("id","ui-tooltip-"+b++).attr("role","tooltip").attr("aria-hidden","true").addClass("ui-tooltip ui-widget ui-corner-all").addClass(this.options.tooltipClass).appendTo(document.body).hide();
this.tooltipContent=a("<div></div>").addClass("ui-tooltip-content").appendTo(this.tooltip);this.opacity=this.tooltip.css("opacity");this.element.bind("focus.tooltip mouseenter.tooltip",function(a){c.open(a)}).bind("blur.tooltip mouseleave.tooltip",function(a){c.close(a)})},enable:function(){this.options.disabled=!1},disable:function(){this.options.disabled=!0},destroy:function(){this.tooltip.remove();a.Widget.prototype.destroy.apply(this,arguments)},widget:function(){return this.element.pushStack(this.tooltip.get())},
open:function(a){var b=this.element;if(!(this.current&&this.current[0]==b[0])){var e=this;this.current=b;this.currentTitle=b.attr("title");var g=this.options.content.call(b[0],function(g){setTimeout(function(){e.current==b&&e._show(a,b,g)},13)});g&&(e.showTimeout=setTimeout(function(){e._show(a,b,g)},350))}},_show:function(b,d,e){e&&(d.attr("title",""),this.options.disabled||(this.tooltipContent.html(e),this.tooltip.css({top:0,left:0}).show().position(a.extend({of:d},this.options.position)).hide(),
parseInt(this.tooltip.css("left").replace(/px/,""))+this.tooltip.width()>window.innerWidth&&this.tooltip.css("left",window.innerWidth-(this.tooltip.width()+this.tooltip.width()/2)+"px"),this.tooltip.attr("aria-hidden","false"),d.attr("aria-describedby",this.tooltip.attr("id")),this.tooltip.stop(!1,!0).fadeIn(),this._trigger("open",b)))},close:function(a){if(this.current){var b=this.current.attr("title",this.currentTitle);this.current=null;this.options.disabled||(clearTimeout(this.showTimeout),b.removeAttr("aria-describedby"),
this.tooltip.attr("aria-hidden","true"),this.tooltip.stop(!1,!0).fadeOut(),this._trigger("close",a))}}})})(jQuery);(function(a,b,c){function d(a,c){var d=b.createElement(a||"div"),e;for(e in c)d[e]=c[e];return d}function e(a){for(var b=1,c=arguments.length;b<c;b++)a.appendChild(arguments[b]);return a}function g(a,b,c,d){var e=["opacity",b,~~(100*a),c,d].join("-"),c=0.01+100*(c/d),d=Math.max(1-(1-a)/b*(100-c),a),f=p.substring(0,p.indexOf("Animation")).toLowerCase();q[e]||(o.insertRule("@"+(f&&"-"+f+"-"||"")+"keyframes "+e+"{0%{opacity:"+d+"}"+c+"%{opacity:"+a+"}"+(c+0.01)+"%{opacity:1}"+(c+b)%100+"%{opacity:"+
a+"}100%{opacity:"+d+"}}",0),q[e]=1);return e}function i(a,b){var d=a.style,e,f;if(d[b]!==c)return b;b=b.charAt(0).toUpperCase()+b.slice(1);for(f=0;f<k.length;f++)if(e=k[f]+b,d[e]!==c)return e}function f(a,b){for(var c in b)a.style[i(a,c)||c]=b[c];return a}function l(a){for(var b=1;b<arguments.length;b++){var d=arguments[b],e;for(e in d)a[e]===c&&(a[e]=d[e])}return a}function j(a){for(var b={x:a.offsetLeft,y:a.offsetTop};a=a.offsetParent;)b.x+=a.offsetLeft,b.y+=a.offsetTop;return b}var k=["webkit",
"Moz","ms","O"],q={},p,o=function(){var a=d("style");e(b.getElementsByTagName("head")[0],a);return a.sheet||a.styleSheet}(),r={lines:12,length:7,width:5,radius:10,rotate:0,color:"#000",speed:1,trail:100,opacity:0.25,fps:20,zIndex:2E9,className:"spinner",top:"auto",left:"auto"},n=function h(a){if(!this.spin)return new h(a);this.opts=l(a||{},h.defaults,r)};n.defaults={};l(n.prototype,{spin:function(a){this.stop();var b=this,c=b.opts,e=b.el=f(d(0,{className:c.className}),{position:"relative",zIndex:c.zIndex}),
g=c.radius+c.length+c.width,k,i;a&&(a.insertBefore(e,a.firstChild||null),i=j(a),k=j(e),f(e,{left:("auto"==c.left?i.x-k.x+(a.offsetWidth>>1):c.left+g)+"px",top:("auto"==c.top?i.y-k.y+(a.offsetHeight>>1):c.top+g)+"px"}));e.setAttribute("aria-role","progressbar");b.lines(e,b.opts);if(!p){var l=0,n=c.fps,o=n/c.speed,q=(1-c.opacity)/(o*c.trail/100),r=o/c.lines;!function v(){l++;for(var a=c.lines;a;a--){var d=Math.max(1-(l+a*r)%o*q,c.opacity);b.opacity(e,c.lines-a,d,c)}b.timeout=b.el&&setTimeout(v,~~(1E3/
n))}()}return b},stop:function(){var a=this.el;a&&(clearTimeout(this.timeout),a.parentNode&&a.parentNode.removeChild(a),this.el=c);return this},lines:function(a,b){function c(a,e){return f(d(),{position:"absolute",width:b.length+b.width+"px",height:b.width+"px",background:a,boxShadow:e,transformOrigin:"left",transform:"rotate("+~~(360/b.lines*j+b.rotate)+"deg) translate("+b.radius+"px,0)",borderRadius:(b.width>>1)+"px"})}for(var j=0,k;j<b.lines;j++)k=f(d(),{position:"absolute",top:1+~(b.width/2)+
"px",transform:b.hwaccel?"translate3d(0,0,0)":"",opacity:b.opacity,animation:p&&g(b.opacity,b.trail,j,b.lines)+" "+1/b.speed+"s linear infinite"}),b.shadow&&e(k,f(c("#000","0 0 4px #000"),{top:"2px"})),e(a,e(k,c(b.color,"0 0 1px rgba(0,0,0,.1)")));return a},opacity:function(a,b,c){b<a.childNodes.length&&(a.childNodes[b].style.opacity=c)}});!function(){function a(b,c){return d("<"+b+' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">',c)}var b=f(d("group"),{behavior:"url(#default#VML)"});!i(b,
"transform")&&b.adj?(o.addRule(".spin-vml","behavior:url(#default#VML)"),n.prototype.lines=function(b,c){function d(){return f(a("group",{coordsize:k+" "+k,coordorigin:-j+" "+-j}),{width:k,height:k})}function g(b,i,k){e(l,e(f(d(),{rotation:360/c.lines*b+"deg",left:~~i}),e(f(a("roundrect",{arcsize:1}),{width:j,height:c.width,left:c.radius,top:-c.width>>1,filter:k}),a("fill",{color:c.color,opacity:c.opacity}),a("stroke",{opacity:0}))))}var j=c.length+c.width,k=2*j,i=2*-(c.width+c.length)+"px",l=f(d(),
{position:"absolute",top:i,left:i});if(c.shadow)for(i=1;i<=c.lines;i++)g(i,-2,"progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");for(i=1;i<=c.lines;i++)g(i);return e(b,l)},n.prototype.opacity=function(a,b,c,d){a=a.firstChild;d=d.shadow&&d.lines||0;if(a&&b+d<a.childNodes.length&&(a=(a=(a=a.childNodes[b+d])&&a.firstChild)&&a.firstChild))a.opacity=c}):p=i(b,"animation")}();a.Spinner=n})(window,document);
jQuery.fn.spin=function(a){this.each(function(){var b=$(this),c=b.data();c.spinner&&(c.spinner.stop(),delete c.spinner);!1!==a&&(c.spinner=(new Spinner($.extend({color:b.css("color")},a))).spin(this))});return this};(function(){$.klear=$.klear||{};$.klear.baseurl=$.klear.baseurl||$("base").attr("href");$.ajax({url:$.klear.baseurl+"../klear/template/cache",dataType:"json",success:function(a){if(a.templates)for(var b in a.templates)$.template(b,a.templates[b])}})})();
