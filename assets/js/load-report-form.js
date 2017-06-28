(function (ReportFormLoader, $) {

	$(".open-report-form").on( "click", function(){
		var button = $(this),
			container = $("#gf_button_form_container"),
			quoteId = button.attr("data-entryid"),
			quotecost = button.attr("data-quotecost"),
			client_email = button.attr("data-client_email");

		if( button.hasClass("open-report-form") ) {
			$.ajax({
				url: form_params.ajaxurl,
				type: 'get',
				data: {
					'action':'gf_button_get_form',
					'form_id':form_params.report_form_id,
					'dealer_id':form_params.dealer_id,
					'dealer_name':form_params.dealer_name,
					'dealer_slug':form_params.dealer_slug,
					'quote_id':quoteId,
					'quoted_cost':quotecost,
					'client_email':client_email,
				},
				// beforeSend: function (jqXHR, settings) {
				//   console.log( settings );
				// },
				success: function( response ) {
					vex.open({
						className: 	"vex-theme-default",
						overlayClosesOnClick: 	false,
						content: 	response,
						afterClose: function () {
						  window.location.reload(true);
						},
					});
				},
			}); // close ajax call
			button.removeClass("open-report-form").addClass("close-report-form").text("Cancel");
		}
		else{
			container.empty();
			button.removeClass("close-report-form").addClass("open-report-form").text("Report");
		} //end if button class check

	}); // close click

}(window.ReportFormLoader = window.ReportFormLoader || {}, jQuery));


(function (ExportCSV, $) {

	$(".export-entry-list").on( "click", function(){
		var button = $(this);
		// console.log(button);

		$.ajax({
			url: csv_form_params.ajaxurl,
			type: 'get',
			data: {
				'action':'button_build_csv_export',
			},

			success: function( response ) {
				console.log(response);
				location.href = response;
			},
		}); // close ajax call


	}); // close click

}(window.ExportCSV = window.ExportCSV || {}, jQuery));
