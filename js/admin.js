jQuery(document).ready(function(){
	var loadingDiv = '<div id="loading" style="clear:both; background:url('+rexCrawlerAJAX.pluginurl+'loading.gif) center top no-repeat; text-align:center;padding:33px 0px 0px 0px; font-size:12px; font-family:Myriad Pro;">LOADING!</div>';
	var loadingDivSmall = '<div id="loading" style="clear:both; background:url('+rexCrawlerAJAX.pluginurl+'loading.gif) center top no-repeat; text-align:center;padding:0; font-size:12px; font-family:Myriad Pro;"></div>';
	
	/*** ADMIN: STYLESHEET SANITIZED NAME ***/
	jQuery('#style_name').change( function(){
		if(jQuery('#style_name').val() != 0){
			jQuery('#style_new_name').toggle();
			jQuery.ajax({
				type: 'post', url: rexCrawlerAJAX.ajaxurl, data: {action: 'rc_ajax_admin_stylesheet_name', styleName: jQuery('#style_name :selected').text()},
				beforeSend: function(){
					jQuery('#style_sanitize').html( loadingDivSmall );
				},
				success: function(html){
					jQuery('#style_sanitize').html( html );
				}
			});
			
			// Get the stylesheet data
			jQuery.ajax({
				type: 'post', 
				url: rexCrawlerAJAX.ajaxurl, 
				data: {
					action: 'rc_ajax_admin_stylesheet_get_data', 
					styleID: jQuery('#style_name').val()
				}, 
				dataType: 'json',
				success: function(data){
					jQuery('#style_col1').val( data.col1 );
					jQuery('#style_col2').val( data.col2 );
					jQuery('#style_css').val( data.css );
					jQuery('#style_row_diff').val( data.rows );
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					jQuery('#style_css').val( 'error!'+errorThrown );
				}
			});
		}else{
			jQuery('#style_new_name').toggle();
			jQuery('#style_col1').val( '' );
			jQuery('#style_col2').val( '' );
			jQuery('#style_css').val( '' );
			jQuery('#style_row_diff').val( '1' );
			jQuery('#style_sanitize').html( '' );
		}
	});
	
	jQuery('#style_name_new').keyup(function(event) {
		// Make sure 'return' doesnt submit the form
		if(event.keyCode == '13'){
			event.preventDefault();
		}
		jQuery.ajax({
			type: 'post', url: rexCrawlerAJAX.ajaxurl, data: {action: 'rc_ajax_admin_stylesheet_name', styleName: jQuery('#style_name_new').val()},
			beforeSend: function(){
				jQuery('#style_sanitize').html( loadingDivSmall );
			},
			success: function(html){
				jQuery('#style_sanitize').html( html );
			}
		});
	});
	
	/*** TOGGLE HEADER/FOOTER - OUTPUT OPTIONS ***/
	jQuery('#def_table_layout').change(function(){
		if(jQuery('#def_table_layout').val() == 1){
			jQuery('#header_option').show();
			jQuery('#footer_option').show();
		}else{
			jQuery('#header_option').hide();
			jQuery('#footer_option').hide();
		}
	});
});

function confirmMsg(strMsg,strRedir) {
	if (confirm(strMsg)){
		window.location = strRedir;
	}
}

function toggleLayer( whichLayer ){
	var elem, vis;
	if( document.getElementById ) // this is the way the standards work
		elem = document.getElementById( whichLayer );
	else if( document.all ) // this is the way old msie versions work
		elem = document.all[whichLayer];
	else if( document.layers ) // this is the way nn4 works
		elem = document.layers[whichLayer];
	vis = elem.style;
	
	// if the style.display value is blank we try to figure it out here
	if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)
		vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';
	vis.display = (vis.display==''||vis.display=='block')?'none':'block';
}