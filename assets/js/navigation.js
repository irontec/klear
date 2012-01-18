;(function($) {
	$(document).ready(function() {
		
		$.klear = $.klear || {};
		
		$.klear.baseurl = $("base").attr("href");
		
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
		
		
		$("#header .langs").buttonset();
	
		$("#header button").each(function() {
			$(this).button({
				icons: {
	                primary: $(this).data("icon")
	            },
	            text: $(this).data("text")
			})
		});
		
		var $main = $("#canvas");
		$main.tabs({
			tabTemplate: "<li title='#{label}'><span class='ui-silk'></span><span class='ui-icon ui-icon-close'></span><a href='#{href}'>#{label}</a></li>",
			scrollable: true,
			add : function( event, ui ) {
				
				$main.tabs('select', ui.index)
				var $tabLi = $(ui.tab).parent("li");
				
				$tabLi.klearModule({
					ui: ui,
					container : $main,
					loadingSelector : '#loadingPanel'
				}).tooltip();

				// Se invoca custom event para actualizar objeto klear.module (si fuera necesario);
				$main.trigger("tabspostadd",ui);
								
				$tabLi.klearModule("dispatch").klearModule("checkModuleDialog");
				
			},
			select : function(event, ui) {
				$("li:eq("+ui.index+")",$main).klearModule("updateLoader");		
				var $tabLi = $(ui.tab).parent("li");
				$tabLi.klearModule("chekModuleDialog");
				
			}
			
		});
		
		$( "#tabsList").on("click","span.ui-icon-close", function() {
			var index = $( "li", $main ).index( $( this ).parent() );
			var $tab = $(this).parent("li");
			$tab.klearModule("close", {callback: function() {
				$main.tabs( "remove", index );
			}});
						
		});

		$( "#sidebar").on("click","a.subsection", function(e) {
			e.preventDefault();
			e.stopPropagation();
			var iden = $(this).attr("id").replace(/^target-/,'');
			if ($("#tabs-"+iden).length > 0) {
				$main.tabs('select', '#tabs-'+iden);
				return;
			}
			var idContent = "#tabs-" + iden;
			var title = $(this).text();
			$main.tabs( "add", idContent, title);
			
		});

		
	});
	
	
	
})(jQuery);