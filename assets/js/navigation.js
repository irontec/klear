/*
 * jQuery Klear
 */

;(function($) {
	
	$.klear = $.klear || {};
	
	$.klear.navctrlKey = function(e, $el){
		if(e.ctrlKey) {
			$el.data('ctrlKey', true);
		}
	};	
	
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
		
		$.klear._doMenuSuccess = function(plg, response) {
			var sidebar = $('#sidebar');
			sidebar.empty();
			$.tmpl('klearMenu', response).appendTo(sidebar);
			sidebar.fadeIn();
			$( "#sidebar" ).accordion({
				icons : {
						header: "ui-icon-circle-arrow-e",
						headerSelected: "ui-icon-circle-arrow-s"
				},
				autoHeight: false
			});

			$("#sidebar li").on("mouseenter",function() {
				$(this).addClass("ui-state-highlight");
			}).on("mouseleave",function() {
				$("#sidebar li").removeClass("ui-state-highlight");
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
		
		$( "#sidebar").on("click","a.subsection", function(e) {
			e.preventDefault();
			e.stopPropagation();
			var iden = $(this).attr("id").replace(/^target-/,'');
			
			$.klear.navctrlKey(e, $.klear.canvas);
			
			if ($("#tabs-"+iden).length > 0) {
				$.klear.canvas.tabs('select', '#tabs-'+iden);
				return;
			}
			var idContent = "#tabs-" + iden;
			var title = $(this).text();
			$.klear.canvas.tabs( "add", idContent, title);
			
		});
	};

	$.klear.loadCanvas = function(){
		/*
		 * TABS
		 */
		$.klear.canvas.tabs({
			tabTemplate: "<li title='#{label}'><span class='ui-silk'></span><span class='ui-icon ui-icon-close'></span><a href='#{href}'>#{label}</a></li>",
			scrollable: true,
			add : function( event, ui ) {
				if ($(ui.tab).parents('ul').css('display') == 'none') {
					$(ui.tab).parents('ul').fadeIn();
				}
				var backgroundTab = false;
				if ($(this).data('ctrlKey')===true) {
					$(this).data('ctrlKey', false)
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
				
				$("#tabsList li").each(function(idx,elem) {
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
				$("#tabsList li").each(function(idx,elem) {
					$(elem).klearModule("option","tabIndex",idx);
				});
				
				$.klear.canvas.tabs('select', $.klear.canvas.tabs('option', 'selected'));
				
			}
		});
		/*
		 * CLOSE
		 */
		$( "#tabsList").on("click","span.ui-icon-close", function() {
			var index = $( "li", $.klear.canvas ).index( $( this ).parent() );
			var $tab = $(this).parent("li");
			$tab.klearModule("close");
		});
	};
	
	
	$.klear.start = function() {
		/*
		 * Setting klear.baseurl value
		 */
		$.klear.baseurl = $.klear.baseurl || $("base").attr("href");
		
		$.klear.canvas = $("#canvas");
		
		$.klear.loadCanvas();
		
		$.klear.hello();
	};

	
})(jQuery);


/*
 * document ready Klear Launch 
 */
;(function($) {
	
	$(document).ready(function() {
		$.klear.start();
	});

})(jQuery);