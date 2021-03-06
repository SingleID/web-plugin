

/*
 * SingleID WEB PLUGIN -> https://github.com/SingleID/web-plugin/
 * 
 * 
 */
 
 
var sid_div;
var sid_domain = document.domain;
var sid_url = location.origin + location.pathname;
var sid_plugin_url = sid_url.replace("plugin.js?op=init", "SingleID.php"); 
// console.log('reply to: '+sid_plugin_url);
var has_answer = 0;
var singleIDInterval;

jQuery(function(){
	sid_init();
})

function sid_init()
{
	sid_payment_hide();
}

function sid_payment_hide()
{
	var val = parent.window.jQuery("input[name='Ecom_payment_mode']:checked").val();
	
	if(val == 'paypal')
	{
		parent.window.jQuery("#toHide").fadeOut(500);
	}
	else
	{
		parent.window.jQuery("#toHide").fadeIn(500);
	}
}

function sid_get_hash()
{
	jQuery.post(sid_plugin_url, {op: 'get_hash'}, function(data){
	});
}

function sid_sendData()
{
	var single_id = jQuery('input[name="SingleID"]').val();
	
		// we need to create a string with all the field with SingleIDAuth class -> 2015-03-25 -> To put in white-paper
		var AuthArray = {};
		
		$(parent.window.jQuery('.SingleIDAuth')).each(function() {
			AuthArray[$(this).attr('id')] = $(this).val();
		});
		var AuthString = JSON.stringify(AuthArray);
	
	
	if(single_id)
	{
		jQuery.post(sid_plugin_url, {single_id:single_id, optionalAuth:AuthString, op: 'send'}, function(d){
		
		if (isNaN(d)) {
			clearInterval(singleIDInterval);
			jQuery('.singleid_waiting').html(d);
		}else{
			singleIDInterval = setInterval(sid_refresh, 1500);
		}
		
		});
		
		jQuery('.singleid_waiting').html('waiting for data').show();
	}
}

function sid_populateData()
{
	
	jQuery.post(sid_plugin_url, {op: 'getdata'}, function(d){
		var obj = jQuery.parseJSON(d);
		
		if (obj.ALREADY_REGISTERED === 1){ 
			// PHP has set this value so we haven't to fill a form but we need only to reload the main page !
			window.top.location.reload();
			return false;
		}
		
		jQuery.each( obj, function( key, value ) {
			
			// SingleID 1.2 support also binary image
			if (key == 'Ident_passport_thumbnail'){
				window.parent.document.getElementById(key).src = value;
				window.parent.document.getElementById(key).style.display = 'block';
			}
			if (key == 'Ident_identity_card_thumbnail'){
				window.parent.document.getElementById(key).src = value;
				window.parent.document.getElementById(key).style.display = 'block';
			}
			if (key == 'Ident_driver_license_thumbnail'){
				window.parent.document.getElementById(key).src = value;
				window.parent.document.getElementById(key).style.display = 'block';
			}
			
			
				
				var input_type = parent.window.jQuery('#'+key).attr('type');
				
				if(!input_type)
					input_type = parent.window.jQuery('.'+key).attr('type');
					
					if(input_type == 'text') {
						parent.window.jQuery('#'+key).val(value);
					}else if(input_type == 'checkbox')	{
						parent.window.jQuery('input[name="'+key+'"][value="'+value+'"]').prop("checked", true);
						sid_payment_hide();
					} else if(input_type == 'radio') {
						parent.window.jQuery('input[name="'+key+'"][value="'+value+'"]').prop("checked", true);
						sid_payment_hide();
					}else { // we are dealing with ???
					parent.window.jQuery("#"+key).val(value).change();
					}
				
		

				
	
		});
	});
}




function sid_refresh()
{
	jQuery.post(sid_plugin_url, {op: 'refresh'}, function(data){
		var res = parseInt(data);
		
		if(res == 500){ // an error as been given from the SingleID Server
			clearInterval(singleIDInterval);
			jQuery('.singleid_waiting').html('ERROR !');
		}else if(res == 1){


		}else if(res == 200){ // the post data has been received from the device so we launch the JS to populate the fields
							// is a form filling request !
			clearInterval(singleIDInterval);
			sid_populateData();
			jQuery('.singleid_waiting').html('Data received');
		}else if(res == 9) {
		
			clearInterval(singleIDInterval);
			jQuery('input[name="SingleID"]').val('');
			jQuery(".singleid_waiting").fadeOut(500);
		}else if(res == 400){ // too much time is passed
		
			clearInterval(singleIDInterval);
			jQuery('input[name="SingleID"]').val('');
			jQuery(".singleid_waiting").fadeOut(500);
		}
	});
}
