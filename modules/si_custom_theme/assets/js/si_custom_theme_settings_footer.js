$(function(){
"use strict";
	$('#si_ct_validate').on('click',function(e){
		e.preventDefault();
		$('input[name="settings[si_custom_theme_activation_code]"]').parents('.form-group').removeClass('has-error');
		var si_ct_purchase_key = $('input[name="settings[si_custom_theme_activation_code]"]').val();
		var update_errors;
		if(si_ct_purchase_key != ''){
			var ubtn = $(this);
			ubtn.html($('#si_ct_validate_wrapper').data('wait-text'));
			ubtn.addClass('disabled');
			$.post(admin_url+'si_custom_theme/validate',{
				purchase_key:si_ct_purchase_key,
			}).done(function(response){
				response=JSON.parse(response);
				if(response['success']){
					$('input[name="settings[si_custom_theme_activated]"]').val(response['success']);
					$('#settings-form').submit();	
				}else{
					$('#si_ct_validate_messages').html('<div class="alert alert-danger"></div>');
					$('#si_ct_validate_messages .alert').append('<p>'+response['message']+'</p>');
					ubtn.removeClass('disabled');
					ubtn.html($('#si_ct_validate_wrapper').data('original-text'));
				}	
			}).fail(function(response){
				update_errors = JSON.parse(response.responseText);
				$('#si_ct_validate_messages').html('<div class="alert alert-danger"></div>');
				for (var i in update_errors){
					$('#si_ct_validate_messages .alert').append('<p>'+update_errors[i]+'</p>');
				}
				ubtn.removeClass('disabled');
				ubtn.html($('#si_ct_validate_wrapper').data('original-text'));
			});
		} else {
			$('input[name="settings[si_custom_theme_activation_code]"]').parents('.form-group').addClass('has-error');
		}
	});
});