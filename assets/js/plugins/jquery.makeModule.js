;(function($) {
	
	$.templateCache = {};
	
	$.fn.extend({
	 
		makeModule: function(ui) {
			
			var curUI = ui;
		
			return this.each(function() {
				 
	                var $li = $(this);
	                
	                var props = {
	                	mainEnl : $("a:first",$li),
	                	title : $("a:first",$li).html(),
	    	            file : $("a:first",$li).attr("href").replace(/\#tabs\-/,''),
	    	            panel : curUI.panel,
	    	            tab : curUI.tab,
	    	            tabIndex : curUI.index
	                }
	                
	                props.menuEnl = $('#target-'+props.file); 
	                
					if ($("span.ui-silk",props.menuEnl.parent()).length > 0) {
						
						var curClasses = $("span.ui-silk",props.menuEnl.parent()).attr("class").split(' ');
						$("span.ui-silk",$li)
							.addClass(curClasses[(curClasses.length-1)])
							.on('click',function() {
								$("a:eq(0)",$li).trigger("click");
							});
					}

					var _fn = {
						parseDispatchResponse : function(response) {
							
							
						}
					};
					
	                
	                $li.on("dispatch.makeModule",function() {
	                	$.ajax({
	                			url:$.baseurl + 'index/dispatch',
	                			dataType:'json',
	                			data : {file:props.file},
	                			type : 'get',
	                			success: _fn.parseDispatchResponse
	                	});
	                });
	                 
	         });

		}
	
	});
	
})(jQuery);
