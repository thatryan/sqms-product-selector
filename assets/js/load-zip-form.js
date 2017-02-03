(function (ZipFormLoader, $) {

	$("#get_gravity_form").validate({

		submitHandler: function(form) {
			var zipEntered = $("#zip-check-input").val(),
				formWrapper = $(".sqms-form-chooser-wrapper");

			$.ajax({
				url: zip_form_params.ajaxurl,
				type: 'get',
				data: {
					'action':'get_gravity_form',
					'zip_value' : zipEntered,
				},
				beforeSend: function (jqXHR, settings) {
				  // console.log( settings );
				},
				success: function( response ) {
					formWrapper.empty().html( response );
					// console.log( response );
				},
			}); // close ajax call
		},

		rules: {
			zip_code_input : {
				required: true,
				zipcode: true,
			}
		},
		messages: {
		  zip_code_input: {
		    required: "We need your zip code to find your products!",
		    zipcode: "Please enter a valid zipcode."
		  },
		},
	});

	// $("#get_gravity_form").submit( function( e ){
	// 	e.preventDefault();
	// 	var $this = $(this);
	// 	var zipEntered = $("#zip-check-input").val(),
	// 		formWrapper = $(".sqms-form-chooser-wrapper");

	// 	$.ajax({
	// 		url: zip_form_params.ajaxurl,
	// 		type: 'get',
	// 		data: {
	// 			'action':'get_gravity_form',
	// 			'zip_value' : zipEntered,
	// 		},
	// 		beforeSend: function (jqXHR, settings) {
	// 		  // console.log( settings );
	// 		},
	// 		success: function( response ) {
	// 			formWrapper.empty().html( response );
	// 			// console.log( response );
	// 		},
	// 	}); // close ajax call

	// });

	jQuery.validator.addMethod("zipcode", function(value, element) {
	  return this.optional(element) || /^\d{5}$/.test(value);
	});

}(window.ZipFormLoader = window.ZipFormLoader || {}, jQuery));
