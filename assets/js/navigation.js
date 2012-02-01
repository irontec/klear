/*
 * jQuery Klear
 */

;(function($) {
	
	/*
	 * setting / getting $.klear Namespace
	 */
	
	$.klear = $.klear || {};
	
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
	
	/*
	 * Hello Klear Server
	 */
	
	$.klear.hello = function(){
		
		$.klear._doHelloSuccess = function(response) {
			if (response.success && response.success === true) {
				$.klear.menu();
			}
		};
		
		$.klear._doHelloError = function(response) {
			console.log(response);
		};
		
		$.klear.request(
			{
				controller: 'index',
				action: 'hello'
			},
			$.klear._doHelloSuccess,
			$.klear._doErrorSuccess,
			this
		);
	};

	$.klear.menu = function(){

		var $sidebar = $('#sidebar');

		var $headerbar = $('#headerbar');
		var $footerbar = $('#footerbar');

		$.klear._doMenuSuccess = function(response) {

			var navMenus = response.data.navMenus;
			
			$sidebar.empty();
			
			$headerbar.empty();
			
			$footerbar.empty();

			$.tmpl('klearSidebarMenu', navMenus.sidebar).appendTo($sidebar);
			
			$.tmpl('klearHeaderbarMenu', navMenus.headerbar).appendTo($headerbar);
			
			$.tmpl('klearFooterbarMenu', navMenus.footerbar).appendTo($footerbar);

			$sidebar.fadeIn();
			
			$headerbar.fadeIn();
			
			$footerbar.fadeIn();
			
			/*
			 * JQ Decorartors 
			 */

			$sidebar.accordion({
				icons : {
						header: "ui-icon-circle-arrow-e",
						headerSelected: "ui-icon-circle-arrow-s"
				},
				autoHeight: false
			});
			
			$("li", $sidebar).on("mouseenter",function() {
				$(this).addClass("ui-state-highlight");
			}).on("mouseleave",function() {
				$(this).removeClass("ui-state-highlight");
			});
			
		};
		
		$.klear._doMenuError = function(response) {
			console.log(response);
		};
		
		$.klear.request(
			{
				controller: 'menu',
				action: 'index'
			},
			$.klear._doMenuSuccess,
			$.klear._doMenuSuccess,
			this
		);
		
		$sidebar.add($headerbar).add($footerbar).on("mouseup","a.subsection", function(e) {
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
			
		}).on("click",function(e) {
			e.preventDefault();
			e.stopPropagation();
		});
	};

	$.klear.loadCanvas = function(){
		/*
		 * TABS
		 */
		
		var tabTemplate = "<li title='#{label}'><span class='ui-silk'></span>"+
			"<span class='ui-icon ui-icon-close'></span><a href='#{href}'>#{label}</a></li>";
		
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
				}
				
				
				var $tabLi = $(ui.tab).parent("li");
				$tabLi.klearModule({
					ui: ui,
					container : $.klear.canvas,
					loadingSelector : '#loadingPanel' 
				}).tooltip();

				// Se invoca custom event para actualizar objeto klear.module (si fuera necesario);
				$.klear.canvas.trigger("tabspostadd",ui);
								
				$tabLi.klearModule("dispatch");
				
				if (backgroundTab !== true) {
					$tabLi.klearModule("highlightOn");
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
					.klearModule("selectCounter")
					.klearModule("updateLoader")
					.klearModule("highlightOn");
			},
			remove: function(event, ui) {

				$("li",$.klear.canvas).each(function(idx,elem) {
	                $(elem).klearModule("option","tabIndex",idx);
	            });
				
				$.klear.canvas.tabs('select', $.klear.canvas.tabs('option', 'selected'));
				
			}
		});
		/*
		 * CLOSE
		 */
		$( "#tabsList").on("click","span.ui-icon-close", function() {
			var $tab = $(this).parent("li");
			$tab.klearModule("close");
		});
		
		$(document).on("keydown",function(e) {
			if(e.shiftKey && e.ctrlKey && e.which==87) {
				e.preventDefault();
				var selectedTab = parseInt($.klear.canvas.tabs('option', 'selected'));
				$.klear.canvas.tabs('remove', selectedTab);
			}
			if(e.shiftKey && e.ctrlKey && e.which==34) {
				e.preventDefault();
				var selectedTab = parseInt($.klear.canvas.tabs('option', 'selected'));
				selectedTab++;
				selectedTab = selectedTab<$("#tabsList li").length ? selectedTab : 0;
				$.klear.canvas.tabs('select', selectedTab);
			}
			if(e.shiftKey && e.ctrlKey && e.which==33) {
				e.preventDefault();
				var selectedTab = parseInt($.klear.canvas.tabs('option', 'selected'));
				selectedTab--;
				selectedTab = selectedTab<0 ? $("#tabsList li").length-1 : selectedTab ;
				$.klear.canvas.tabs('select', selectedTab);
			}
		});
		
		
	};
	
	
	$.klear.start = function() {
		/*
		 * Setting klear.baseurl value
		 */
		$.klear.baseurl = $.klear.baseurl || $("base").attr("href");
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
	};

	
})(jQuery);


/*
 * document ready Klear Launch 
 */

;(function($) {
	
	$(document).ready(function() {
		$.klear.start();
		
		setTimeout(function() {$("#target-brandList").trigger("click");},1500);
	});

})(jQuery);
