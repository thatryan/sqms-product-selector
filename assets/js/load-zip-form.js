(function (ZipFormLoader, $) {

	$(".gf-test-zip-code").on( "click", function(){

		var zipEntered = $("#zip-check-input").val(),
			formWrapper = $(".sqms-form-chooser-wrapper");

		$.ajax({
			url: zip_form_params.ajaxurl,
			type: 'get',
			data: {
				'action':'get_gravity_form',
				'zipValue' : zipEntered,
			},
			// beforeSend: function (jqXHR, settings) {
			//   console.log( settings );
			// },
			success: function( response ) {
				formWrapper.empty().html( response );
				// console.log( response );
			},
		}); // close ajax call

	});

}(window.ZipFormLoader = window.ZipFormLoader || {}, jQuery));
