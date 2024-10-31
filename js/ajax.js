// rexCrawler AJAX
// rexCrawlerAJAX.ajaxurl: URL to admin-ajax.php
// rexCrawlerAJAX.pluginurl: URL to the plugin-folder (with trailing slash)


jQuery(document).ready(function(){
	var loadingDiv = '<div id="loading" style="clear:both; background:url('+rexCrawlerAJAX.pluginurl+'loading.gif) center top no-repeat; text-align:center;padding:33px 0px 0px 0px; font-size:12px; font-family:Myriad Pro;">LOADING!</div>';
	var loadingDivSmall = '<div id="loading" style="clear:both; background:url('+rexCrawlerAJAX.pluginurl+'loading.gif) center top no-repeat; text-align:center;padding:0; font-size:12px; font-family:Myriad Pro;"></div>';
	
	/*** OUTPUT SEARCH TEXT-FIELD ***/
	jQuery('#rc_output_search').keyup(function(event) {
		// Make sure 'return' doesnt submit the form
		if(event.keyCode == '13'){
			event.preventDefault();
		}
		
		// No need to check if we have a search-bar
		var searchdata = escape( jQuery('#rc_output_search').val());
		
		// Check if we have a data-list
		var datalist = '';
		if(jQuery('#rc_output_data').length){
			datalist = escape( jQuery('#rc_output_data').val());
		}
		
		// Check if we have a page-list
		var pagelist = '';
		if(jQuery('#rc_output_pages').length){
			pagelist = escape( jQuery('#rc_output_pages').val());
		}
		
		jQuery.ajax({
			type: "post",url: rexCrawlerAJAX.ajaxurl,data: { action: 'rc_ajax_output', rc_output_submitted: 1, rc_output_search: searchdata, rc_output_data: datalist, rc_output_pages: pagelist, rc_output_options: jQuery('#rc_output_options').val()},
			beforeSend: function() {
							jQuery("."+jQuery('#rc_output_element_prefix').val()+"_data-wrapper").html( loadingDiv );
						
						},
			success: function(html){ //so, if data is retrieved, store it in html
				jQuery("."+jQuery('#rc_output_element_prefix').val()+"_data-wrapper").html( html ); //show the html inside formstatus div
			}
		}); //close jQuery.ajax
		//return false;
	});
	
	/*** OUTPUT DATA LIST ***/
	jQuery('#rc_output_data').change(function(){
		// Checking whether we have a search-bar
		var searchdata = '';
		if(jQuery('#rc_output_search').length){
			searchdata = escape( jQuery('#rc_output_search').val());
		}
		
		// No need to check if we have a datalist
		var datalist = escape( jQuery('#rc_output_data').val());
		
		// Checking if we have a pagelist
		var pagelist = '';
		if(jQuery('#rc_output_pages').length){
			pagelist = escape( jQuery('#rc_output_pages').val());
		}
		
		jQuery.ajax({
				type: "post",url: rexCrawlerAJAX.ajaxurl,data: { action: 'rc_ajax_output', rc_output_submitted: 1, rc_output_search: searchdata, rc_output_data: datalist, rc_output_pages: pagelist, rc_output_options: jQuery('#rc_output_options').val()},
				beforeSend: function() {
								jQuery("."+jQuery('#rc_output_element_prefix').val()+"_data-wrapper").html( loadingDiv );
							
							},
				success: function(html){ //so, if data is retrieved, store it in html
					jQuery("."+jQuery('#rc_output_element_prefix').val()+"_data-wrapper").html( html ); //show the html inside formstatus div
				}
			}); //close jQuery.ajax
	});
	
	jQuery('#rc_output_data').keydown(function(event){
		// Make sure 'return' doesnt submit the form
		if(event.keyCode == '13'){
			event.preventDefault();
		}
	});
	
	/*** OUTPUT PAGES LIST ***/
	jQuery('#rc_output_pages').change(function(){
		// Checking whether we have a search-bar
		var searchdata = '';
		if(jQuery('#rc_output_search').length){
			searchdata = escape( jQuery('#rc_output_search').val());
		}
		
		// Check if we have a data-list
		var datalist = '';
		if(jQuery('#rc_output_data').length){
			datalist = escape( jQuery('#rc_output_data').val());
		}
		
		// No need to check if we have a pagelist
		var pagelist = escape( jQuery('#rc_output_pages').val());
	
		jQuery.ajax({
				type: "post",url: rexCrawlerAJAX.ajaxurl,data: { action: 'rc_ajax_output', rc_output_submitted: 1, rc_output_search: searchdata, rc_output_data: datalist, rc_output_pages: pagelist, rc_output_options: jQuery('#rc_output_options').val()},
				beforeSend: function() {
								jQuery("."+jQuery('#rc_output_element_prefix').val()+"_data-wrapper").html( '<div id="loading" style="clear:both; background:url('+rexCrawlerAJAX.pluginurl+'loading.gif) center top no-repeat; text-align:center;padding:33px 0px 0px 0px; font-size:12px; font-family:Myriad Pro;">LOADING!</div>' );
							
							},
				success: function(html){ //so, if data is retrieved, store it in html
					jQuery("."+jQuery('#rc_output_element_prefix').val()+"_data-wrapper").html( html ); //show the html inside formstatus div
				}
			}); //close jQuery.ajax
	});
	
	jQuery('#rc_output_pages').keydown(function(event){
		// Make sure 'return' doesnt submit the form
		if(event.keyCode == '13'){
			event.preventDefault();
		}
	});
});
