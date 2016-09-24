var gfp_auto_advance_page_js = {"fields":["3","21","25","22","4","37","6","65","27","59"],"form_id":"12"};

function gfp_add_auto_advance_field_events(_, e) {
    if (e == gfp_auto_advance_page_js.form_id) {
        // jQuery(".gform_previous_button").hide(), jQuery(".gform_next_button").hide(), jQuery("#gform_submit_button_" + gfp_auto_advance_page_js.form_id).hide();
        for (var a = 0; a < gfp_auto_advance_page_js.fields.length; a++) jQuery("input[name='input_" + gfp_auto_advance_page_js.fields[a] + "'],select[name='input_" + gfp_auto_advance_page_js.fields[a] + "']").change(gfp_auto_advance_page);
    }
}
function gfp_auto_advance_page() {
    var _ = jQuery(this).parents(".gform_page").find(".gform_next_button");
    if (0 < _.length) _.trigger("click");
    else {
        var e = jQuery(this).parents(".gform_page").find("#gform_submit_button_" + gfp_auto_advance_page_js.form_id);
        0 < e.length && e.trigger("click");
    }
}
jQuery(document).on("gform_post_render", gfp_add_auto_advance_field_events);


