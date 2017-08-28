<?php
/**
 * Functions to handle all the dynamic building and display of Gravity Forms
 * options inside of the system selector and photo quote forms.
 */

// require_once 'Twilio/autoload.php';
// use Twilio\Rest\Client;

// Set scroll distance to 0 for selection form
add_filter( 'gform_confirmation_anchor_12', function() { return 0; } );
// add_filter( 'gform_progress_bar_12', 'remove_progress_steps', 10, 3 );
// add_filter( 'gform_pre_render_12', 'display_choice_result' );
add_action( 'gform_post_paging_12', 'add_gtm_pagination', 10, 3 );
add_action( 'gform_post_paging_12', 'add_to_mailchimp', 10, 3 );
add_filter( 'gform_field_value_zip_check', 'populate_zip_code' );
// add_filter( 'gform_field_value_dealer_ref', 'check_for_referral_id' );
add_filter( 'gform_notification_12', 'get_dealer_email', 10, 3 );
add_filter( 'gform_pre_render_15', 'add_readonly_script' );
add_filter( 'gform_pre_render_20', 'dealer_review_id' );
add_filter( 'gform_submit_button_12', 'add_note_below_submit', 10, 2 );
add_filter( 'gform_notification_16', 'get_dealer_email', 10, 3 );
// add_filter( 'gform_replace_merge_tags', 'replace_dealer_notification', 10, 7 );
add_filter( 'gform_confirmation', 'custom_confirmation', 10, 4 );
add_filter( 'gform_ajax_spinner_url', 'add_hiq_spinner_image', 10, 2 );

add_filter( 'gform_field_validation_12_11', 'validate_name', 10, 4 );
add_filter( 'gform_field_validation_12_48', 'validate_phone_number', 10, 4 );
add_filter( 'gform_field_validation_12_47', 'validate_zip_zone', 10, 4 );
add_filter( 'gform_field_validation_16_12', 'validate_zip_zone', 10, 4 );

// add_action( 'gform_pre_submission_12', 'choose_new_dealer' );
add_action( 'gform_pre_submission_12', 'populate_zone' );
add_action( 'gform_after_submission_12', 'add_gtm_submission', 10, 2 );
add_action( 'gform_after_submission_15', 'update_report_entry_meta', 10, 2 );
// add_action( 'gform_pre_submission_16', 'choose_new_dealer' );


function remove_progress_steps( $progress_bar, $form, $confirmation_message ) {
	// TODO
}

function check_for_referral_id( ) {
	if( isset( $_GET['dealer_ref'] ) ) {
		return $_GET['dealer_ref'];
	}
	return;

}
/**
 * Builds an HTML structure to show the data for the selected product based
 * on inputs from user combined to make selection string
 * @param  object $form the gravity forms $form object
 * @return   object       returns the filtered $form with altered fields
 */
function display_choice_result( $form ) {
	$current_page 		= GFFormDisplay::get_current_page( $form['id'] );
	$content_output 		= "";
	$html_content 			= "";
	$prod_string 			= "";
	$market_key_field 	= rgpost( 'input_74' );

	// After page 7 we have the data to build the string
	if ( $current_page >= 7 ) {
		$prod_string .= $market_key_field;

		// Iterate through each field used for building product string and grab its value
		foreach ( $form['fields'] as &$field ) {

			if ( strpos( $field->cssClass, 'product-builder-item' ) === false ) {
				continue;
			}
			//gather form data to save into html field, exclude page break
			if ( $field->id != 14 && $field->type != 'page' ) {
				$is_hidden 	= RGFormsModel::is_field_hidden( $form, $field, array() );
				$populated 	= rgpost( 'input_' . $field->id );

				if ( !$is_hidden && $populated !='' ) {
					$html_content 	.= '<li>' . $field->label . ': ' . rgpost( 'input_' . $field->id ) . '</li>';
					$prod_string 	.= rgpost( 'input_' . $field->id );
				}
			}
		}

		// Get the chosen product object
		$prod_obj = get_page_by_path($prod_string, OBJECT, 'sqms_prod_select');

		// Somehow the builder strung together a product string that does not exist, send me a message so I can look into it
		if( $prod_obj === NULL ) {

			// Send error message with selection info
			$to 		= 'rolson@sequoiaims.com';
			$subject 	= 'HIQ Product Selection Error: No Product';
			$body 		= 'The following product was selected but not available<br><b>' . $prod_string . '</b><br>Function: <b>display_choice_result()</b>';
			$body 		.= '<h4>POST Data:</h4><pre>';
			$body 		.= print_r( $_POST, true );
			$body 		.= '</pre>';
			$headers 	= array('Content-Type: text/html; charset=UTF-8');

			wp_mail( $to, $subject, $body, $headers );

			$photo_page_link 		 = 'https://hvacinstantquote.com/heating-and-cooling-estimate/get-your-quote-by-photo/';
			$no_product_message = '<div class="avia_message_box avia-color-orange avia-size-large avia-icon_select-yes avia-border-solid  avia-builder-el-1  el_after_av_textblock  avia-builder-el-last "><span class="avia_message_box_title">Oops!</span><div class="avia_message_box_content"><span class="avia_message_box_icon" aria-hidden="true" data-av_icon="" data-av_iconfont="entypo-fontello"></span><p style="text-transform:none;font-size:16px;">We are so sorry, you appear to have found a bug in our system. An error message has been sent, but you can still get your quote by photo!</p></div></div><a href=" ' . $photo_page_link . ' " class="product-error-button button">Get Your Quote by Photo</a>';

			foreach( $form['fields'] as &$field ) {

				//get html field
				if ( $field->id == 14 ) {
					//set the field content to the html
					$field->content = $no_product_message;
				}
			}
			// Did not find a product, so return the form and bail out
			return $form;
		}

		// Get all the data related to the chosen product string
		$product_post_id 		= $prod_obj->ID;
		$prod_meta 			= get_post_meta( $product_post_id );
		$title 					= get_the_title( $product_post_id );
		$cat 					= get_the_terms ( $product_post_id, 'system_type' );
		$system_price 		= get_post_meta( $product_post_id, 'sqms-product-system-price', true );
		$warranty_price 		= get_post_meta( $product_post_id, 'sqms-product-warranty-price', true );
		$cmb 					= cmb2_get_metabox( 'sqms-product-overview-meta', $product_post_id );
		$cmb_fields 			= $cmb->prop( 'fields' );

		// Build the HTML that will be displayed in the form field
		$content_output .= '<p>Your total quote is the guaranteed price for your selected system, plus the estimated cost of installation. <a href="https://hvacinstantquote.com/resources/faqs#about-money" target="_blank" title="Factors about cost of installation">Click here for common factors that affect the cost of an installation</a>.</p>';
		$content_output .= '<h3>Your System Selection &amp; Quote</h3>';
		$content_output .= '<div class="highlight-box cost-wrapper">';
		$content_output .= '<h2>Your New HVAC System Equipment Quote is <span>' .  esc_html( $system_price ) . '</span></h2>';
		$content_output .= '<h3>And Your Installation Estimate is Between <span>$1,000.00 - $2,500.00</span></h3>';
		$content_output .= '<p><small>Note: Proper Equipment Selection Will Be Verified On Installation Inspection</small></p>';
		$content_output .= '</div>';
		$content_output .= '<div class="financing-box">' . get_finance_options( $system_price, $warranty_price ) . '</div>';
		$content_output .= '<p>By accepting this quote, you will be connected with a local dealer who will schedule a visit to your home for inspection. You are not committing to a purchase.</p>';

		//loop back through form fields to get html field ID that we are populating with the data gathered above
		foreach( $form['fields'] as &$field ) {

			// Set content the the HTML block output
			if ( $field->id == 14 ) {
				$field->content = $content_output;
			}
			// Set a hidden field to the constructed product string
			if ( $field->id == 56 ) {
				$field->defaultValue = $prod_string;
			}
		}
	}

	//return altered form so changes are displayed
	return $form;
}

/**
 * Add info to datalayer for Google Tag Manager on pagination evetns
 * @param object $form                GF $form object
 * @param int $source_page_number  page user coming from
 * @param int $current_page_number current page number
 * @return void
 */
function add_gtm_pagination( $form, $source_page_number, $current_page_number ) {
	$event 				= 'QuoteFormPaginate';
	$event_category 	= 'Forms';
	$event_action 		= 'Pagination';
	$event_label 		= sprintf( '%s::%d::%d', esc_html( $form['title'] ), absint( $source_page_number ), absint( $current_page_number ) ); ?>
	<script>
		window.parent.dataLayer = window.parent.dataLayer || [];
		window.parent.dataLayer.push({
			'event' : '<?php echo $event; ?>',
			'eventCategory' : '<?php echo $event_category; ?>',
			'eventAction' : '<?php echo $event_action; ?>',
			'eventLabel' : '<?php echo $event_label; ?>',
		});
	</script>
	<?php
}

function add_to_mailchimp( $form, $source_page_number, $current_page_number ) {

	$current_page 		= GFFormDisplay::get_current_page( $form['id'] );

	// After page 7 we have the data to build the string
	if ( $current_page == 7 ) {

	$first_name = rgpost( 'input_11_3' );
	$last_name = rgpost( 'input_11_6' );
	$email_address = rgpost( 'input_12' );
	$prod_string 			= "";
	$market_key_field 	= rgpost( 'input_74' );

	$prod_string .= $market_key_field;

	// Iterate through each field used for building product string and grab its value
	foreach ( $form['fields'] as &$field ) {

		if ( strpos( $field->cssClass, 'product-builder-item' ) === false ) {
			continue;
		}
		//gather form data to save into html field, exclude page break
		if ( $field->id != 14 && $field->type != 'page' ) {
			$is_hidden 	= RGFormsModel::is_field_hidden( $form, $field, array() );
			$populated 	= rgpost( 'input_' . $field->id );

			if ( !$is_hidden && $populated !='' ) {
				$prod_string 	.= rgpost( 'input_' . $field->id );
			}
		}
	}

	//loop back through form fields to get html field ID that we are populating with the data gathered above
	foreach( $form['fields'] as &$field ) {

		// Set a hidden field to the constructed product string
		if ( $field->id == 56 ) {
			$field->defaultValue = $prod_string;
		}
	}

	$data = [
	    'email'     => $email_address,
	    'status'    => 'subscribed',
	    'firstname' => $first_name,
	    'lastname'  => $last_name,
	];

	$result = syncMailchimp($data);

	// error_log('Result');
	// error_log( print_r( $result, true ) );
	}

}

function populate_zip_code() {
	return $_GET['zip_value'];
}

/**
 * Make some fields on dealer report form readonly
 * @param object $form the $form object
 */
function add_readonly_script($form){
	// Only load the script on initial load into the lightbox
	if( !$_POST ) : ?>

    <script type="text/javascript">
        jQuery(document).ready(function(){
            /* apply only to a textarea with a class of gf_readonly */
            jQuery("li.gf_readonly input").attr("readonly","readonly");
        });
    </script>

    <?php endif;
    return $form;
}

/**
 * Get the dealer name from ID to display who is being reviewed
 * @param  object $form GF $form object
 * @return object       Filtered GF $form
 */
function dealer_review_id( $form ) {

	$dealer_id = $_GET['dealer_id'];
	$dealer_name = get_the_title( $dealer_id );
	$title_content .= '<h3>You Are Reviewing: ' . $dealer_name . '</h3>';

	foreach( $form['fields'] as &$field ) {
	    // Get field to output dealer name in
	    if ( $field->id == 9 ) {
	        $field->content = $title_content;
	    }
	}

	return $form;
}

/**
 * Add a notice below submit button on quote form
 * @param string $button String containing the input tag to be filtered
 * @param object $form   GF $form object
 */
function add_note_below_submit( $button, $form ) {
	return $button .= '<p><b>Note</b>: Our use of your email address will be for delivery of your free estimate AND exclusive homeowner tips & tricks. HVAC Instant Quote will not sell or share your information and we never send spam.</p><p>*by submitting your contact info to a Payne dealer, you are not committing to a quote or appointment.</p>';
}
/**
 * Find the dealer email to send the GF notifcation to
 * @param  object $notification GF $notifcation
 * @param  object $form         GF $form
 * @param  object $entry        GF $entry
 * @return object               filtered GF $notifcation with updated email to address
 */
function get_dealer_email( $notification, $form, $entry ) {

	$notification['from'] 	= 'mail@hvacinstantquote.com';
	$notification['fromName'] 	= 'HVAC Instant Quote ';
	$notification['to'] 	= 'lscherer@siglers.com, KSturm@siglers.com, rolson@sequoiaims.com, jbenbrook@sequoiaims.com, mschwartz@sequoiaims.com';


	return $notification;
}

/**
 * Build a custom merge tag to override the default notifcation on selector form
 * @param  string $text       Current text where merge tag lives
 * @param  object $form       GF $form object
 * @param  object $entry      GF $entry object
 * @param  bool $url_encode encode urls?
 * @param  book $esc_html   encode html?
 * @param  bool $nl2br      new lines to breaks?
 * @param  string $format     How should value be formatted? Default HTML
 * @return string             Modified string for notification
 */
function replace_dealer_notification( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

	$custom_merge_tag = '{dealer_notification}';

	if ( strpos( $text, $custom_merge_tag ) === false ) {
		return $text;
	}
	$date_created 	= rgar( $entry, 'date_created' );
	$prod_string 		= rgar( $entry, '56' );
	$prod_obj 			= get_page_by_path($prod_string, OBJECT, 'sqms_prod_select');
	$product_post_id 	= $prod_obj->ID;
	$address_field_id 	= 47;
	// $accessory_field_id 	= 57;
	$street_value 		= rgar( $entry, $address_field_id . '.1' );
	$street2_value 		= rgar( $entry, $address_field_id . '.2' );
	$city_value 			= rgar( $entry, $address_field_id . '.3' );
	$state_value 		= rgar( $entry, $address_field_id . '.4' );
	$zip_value 			= rgar( $entry, $address_field_id . '.5' );
	$accessory_string 	= '';

	if( $street2_value ) {
		$formatted_street = $street_value . ', ' . $street2_value;
	}
	else {
		$formatted_street = $street_value;
	}

	$formatted_address_value = $formatted_street . '<br>' . $city_value . ', ' . $state_value . ' ' . $zip_value;

	// $accessory_field       = GFFormsModel::get_field( $form, $accessory_field_id );
	// $accessory_string = is_object( $accessory_field ) ? $accessory_field->get_value_export( $entry ) : '';

	// Bring in the template file with matching GF styling
	include 'template/template-merge-tag.php';

	$text = str_replace( $custom_merge_tag, $prod_data_output, $text );

	return $text;
}

/**
 * Build a custom confirmation page to display chosen dealer info
 * @param  string $confirmation default confirmation text
 * @param  object $form         GF $form object
 * @param  object $entry        GF $entry object
 * @param  book $ajax         is ajax being used on this form?
 * @return string               Rebuil HTML confirmation message
*/
function custom_confirmation( $confirmation, $form, $entry, $ajax ) {
	// Product selection form
	if( $form['id'] == 12 ) {
		$dealer_id = rgar( $entry, '69' );
		$prod_string = rgar( $entry, '56' );
	}
	// Photo quote form
	elseif( $form['id'] == 16 ) {
		$dealer_id = rgar( $entry, '18' );
	}
	// None of the above, abort
	else {
		return $confirmation;
	}

	$confirmation 		= "";
	// Get the chosen product object
	$prod_obj = get_page_by_path($prod_string, OBJECT, 'sqms_prod_select');

	// Somehow the builder strung together a product string that does not exist, send me a message so I can look into it
	if( $prod_obj === NULL ) {

		// Send error message with selection info
		$to 		= 'rolson@sequoiaims.com';
		$subject 	= 'HIQ Product Selection Error: No Product';
		$body 		= 'The following product was selected but not available<br><b>' . $prod_string . '</b><br>Function: <b>custom_confirmation()</b>';
		$body 		.= '<h4>POST Data:</h4><pre>';
		$body 		.= print_r( $_POST, true );
		$body 		.= '</pre>';
		$headers 	= array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $to, $subject, $body, $headers );

		$photo_page_link 		 = 'https://hvacinstantquote.com/heating-and-cooling-estimate/get-your-quote-by-photo/';
		$confirmation = '<div class="avia_message_box avia-color-orange avia-size-large avia-icon_select-yes avia-border-solid  avia-builder-el-1  el_after_av_textblock  avia-builder-el-last "><span class="avia_message_box_title">Oops!</span><div class="avia_message_box_content"><span class="avia_message_box_icon" aria-hidden="true" data-av_icon="" data-av_iconfont="entypo-fontello"></span><p style="text-transform:none;font-size:16px;">We are so sorry, you appear to have found a bug in our system. An error message has been sent, but you can still get your quote by photo!</p></div></div><a href=" ' . $photo_page_link . ' " class="product-error-button button">Get Your Quote by Photo</a>';

		// Did not find a product, so return the form and bail out
		return $confirmation;
	}

	// Get all the data related to the chosen product string
	$product_post_id 		= $prod_obj->ID;
	$prod_meta 			= get_post_meta( $product_post_id );
	$title 					= get_the_title( $product_post_id );
	$cat 					= get_the_terms ( $product_post_id, 'system_type' );
	$system_price 		= get_post_meta( $product_post_id, 'sqms-product-system-price', true );
	$warranty_price 		= get_post_meta( $product_post_id, 'sqms-product-warranty-price', true );
	$cmb 					= cmb2_get_metabox( 'sqms-product-overview-meta', $product_post_id );
	$cmb_fields 			= $cmb->prop( 'fields' );

	// Build the HTML that will be displayed in the form field
	$confirmation .= '<h3>Your System Selection &amp; Quote</h3>';
	$confirmation .= '<div class="highlight-box cost-wrapper">';
	$confirmation .= '<h2>Your New HVAC System Equipment Quote is <span>' .  esc_html( $system_price ) . '</span></h2>';
	$confirmation .= '<h3>And Your Installation Estimate is Between <span>$1,000.00 - $2,500.00</span></h3>';
	$confirmation .= '<p><small>Note: Proper Equipment Selection Will Be Verified On Installation Inspection</small></p>';
	$confirmation .= '</div>';
	$confirmation .= '<p>Your total quote is the guaranteed price for your selected system, plus the estimated cost of installation. <a href="https://hvacinstantquote.com/resources/faqs#about-money" target="_blank" title="Factors about cost of installation">Click here for common factors that affect the cost of an installation</a>.</p>';
	$confirmation .= '<div class="financing-box">' . get_finance_options( $system_price, $warranty_price ) . '</div>';

	$conversion_code = '<!-- Google Code for Instant Quote Form Conversion Page --><script type="text/javascript">/* <![CDATA[ */var google_conversion_id = 856718203;var google_conversion_language = "en";var google_conversion_format = "3";var google_conversion_color = "ffffff";var google_conversion_label = "62pkCKSq8m8Q-_bBmAM";var google_remarketing_only = false;/* ]]> */</script><script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script><noscript><div style="display:inline;"><img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/856718203/?label=62pkCKSq8m8Q-_bBmAM&amp;guid=ON&amp;script=0"/></div></noscript>';

	if( $form['id'] == 12 ) {
	$confirmation .= '<h4>You will be contacted by an HVAC Instant Quote team member within 24 hours to schedule your in home visit.</h4>';
	$confirmation .= '<p>A copy of your quote information has been emailed to you. You may also download a PDF copy below.</p>';
	$confirmation .= do_shortcode( '[gravitypdf name="Client Copy" id="57a03bc2e0cc7" class="button dealer-pdf" entry='.$entry['id'].' text="Download PDF"]' );
	}

	return $conversion_code . $confirmation;
	// return $confirmation;
}

/**
 * Replace default GF loading image with custom one
 * @param string $image_src Spinner image to be filtered
 * @param object $form      GF $form object
 * @return  string New spinner image URL
 */
function add_hiq_spinner_image( $image_src, $form ) {
	return SQMS_PROD_SEL_URL . '/assets/img/hiq-loader-small.gif';
	return " ";
}

/**
 * Validate if zip code entered is a serviceable area
 * @param  array $result Validation result to be filtered
 * @param  string|array $value  field values to be validated
 * @param  object $form   GF $form object
 * @param  object $field  GF $field object
 * @return array         return $result array
 */
function validate_zip_zone( $result, $value, $form, $field ) {

	$zip 		= rgar( $value, $field->id . '.5' );

	if( empty( $zip ) ) {
		$result['is_valid'] = false;
		$result['message']  = 'Please be sure you enter your valid zip code.';

		return $result;
	}

	$good_zip 	= is_serviceable_zip_code( $zip );

    //address field will pass $value as an array with each of the elements as an item within the array, the key is the field id
    if ( !$good_zip && $result['is_valid'] ) {

    	$result['is_valid'] = false;
    	$result['message']  = 'Sorry, we do not service that zip code yet.';

    }
    else {
                $result['is_valid'] = true;
                $result['message']  = '';
            }
// GFCommon::log_debug( __METHOD__ . '(): POST => ' . print_r( $_POST, true ) );
    return $result;
}

function validate_name( $result, $value, $form, $field ) {
	if ( $field->type == 'name' ) {

	    // Input values
	    $prefix = rgar( $value, $field->id . '.2' );
	    $first  = rgar( $value, $field->id . '.3' );
	    $middle = rgar( $value, $field->id . '.4' );
	    $last   = rgar( $value, $field->id . '.6' );
	    $suffix = rgar( $value, $field->id . '.8' );

	    if ( empty( $first ) ) {
	        $result['is_valid'] = false;
	        $result['message']  = empty( $field->errorMessage ) ? __( 'This field is required. Please enter a complete name.', 'gravityforms' ) : $field->errorMessage;
	    } else {
	        $result['is_valid'] = true;
	        $result['message']  = '';
	    }
	}

	return $result;
}

function validate_phone_number( $result, $value, $form, $field ) {

	$phone_number 		= $value;
	$access_key = NUMVERIFY_API;

	// Initialize CURL:
	$ch = curl_init('http://apilayer.net/api/validate?access_key='.$access_key.'&number='.$phone_number.'&country_code=US'.'');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// Store the data:
	$json = curl_exec($ch);
	curl_close($ch);

	// Decode JSON response:
	$validationResult = json_decode($json, true);

    // error_log('RESULT');
    // error_log( print_r( $validationResult, true ) );

	if( !$validationResult['valid'] ) {
	    	$result['is_valid'] = false;
		$result['message']  = 'Please enter a valid phone number';

	}

	return $result;


}

function populate_zone( $form ) {
	$zone 					= '';
	$customer_zone_field = 'input_78';
	$zip_check_field 	 	= sanitize_text_field( rgpost( 'input_75' ) );
	$zone = is_serviceable_zip_code( $zip_check_field );

	$_POST[$customer_zone_field] = $zone;

	// error_log('ZONE');
	// error_log( $zone );

	// error_log('POST');
	// error_log( print_r( $_POST, true ) );
}
/**
 * This finds the dealer we are assigning to this submission based on user location
 * @param  object $form GF $form object
 * @return void       Either aborts if not proper form, or updates $_POST
 */
function choose_new_dealer( $form ) {

	// error_log('POST');
	// error_log( print_r( $_POST, true ) );
	$zone 						= '';
	$address_field 			= '';
	$choose_error 			= '';
	$dealer_id_field 			= '';

	if( $form['id'] == 12 ) {
		$address_field 	= '47_5';
		$dealer_id_field 	= 'input_69';
		$zip_check_field 	 	= sanitize_text_field( rgpost( 'input_75' ) );
	}
	elseif( $form['id'] == 16 ) {
		$address_field 	= '12_5';
		$zip_check_field 	 	= sanitize_text_field( rgpost( 'input_12_5' ) );
		$dealer_id_field 	= 'input_18';
	}
	else {
		return;
	}

	if( isset( $_POST[$dealer_id_field] ) && $_POST[$dealer_id_field] != "" ) {
		error_log('Dealer ID Pre-Set');
		error_log( print_r( $_POST, true ) );
		return;
	}

	// GFCommon::log_debug( __METHOD__ . '(): POST => ' . print_r( $_POST, true ) );
	// Find out what zone this client is in

	$zone = is_serviceable_zip_code( $zip_check_field );

	// GFCommon::log_debug( __METHOD__ . 'Zone: ' . $zone );

	// Get a dealer that servics that zone
	$selected_dealer_id 	= zone_has_dealer( $zone );

	// GFCommon::log_debug( __METHOD__ . 'Dealer ID: ' . $selected_dealer_id );

	/**
	 * Set POST variable for the hidden dealer ID field to the dealer ID chosen via query above
	 * See gravityhelp.com/documentation/article/gform_pre_submission
	 */
	$_POST[$dealer_id_field] = $selected_dealer_id;
}

/**
 * Push form submission to dataLayer for GTM tracking
 * @param  object $entry GF $entry object
 * @param  object $form  GF $form object
 * @return void        adds script for GTM
 */
function add_gtm_submission( $entry, $form ) {
	setcookie( 'quotesubmitted', 1, strtotime( '+30 days' ), COOKIEPATH, COOKIE_DOMAIN, false, false );

	$event 				= 'quoteFormSubmit';
	$event_category 	= 'Forms';
	$event_action 		= 'Submission';
	$event_label 		= 'Quote Form Completed'; ?>
	<script>
		window.parent.dataLayer.push({
			'event' : '<?php echo $event; ?>',
			'eventCategory' : '<?php echo $event_category; ?>',
			'eventAction' : '<?php echo $event_action; ?>',
			'eventLabel' : '<?php echo $event_label; ?>',
		});
	</script>
	<script>fbq('track', 'Lead');</script>
	<?php
}

/**
 * Mark entry in list as reported on
 * @param  object $entry GF $entry object
 * @param  object $form  GF $form object
 * @return void        updates GF entry meta
 */
function update_report_entry_meta( $entry, $form ) {

	$reported 			= 'Yes';
	$quote_form_id 	= 12;
	$quote_id 			= $_GET['quote_id'];
	$quote_id 			= filter_input( INPUT_GET, 'quote_id', FILTER_SANITIZE_NUMBER_INT );

	gform_update_meta( intval( $quote_id ), 'quote_reported', $reported, $quote_form_id );

}

// Utility functions

/**
 * Validate if zip code entered is in service area
 * @param  int  $client_zip_code Zip code entered
 * @return bool|string                  false, or slug of zone
 */
function is_serviceable_zip_code( $client_zip_code ) {
	// Find out what zone this client is in
	$term 				= get_term_by( 'slug', $client_zip_code, 'zone' );
	$parent 			= get_term_by( 'id', $term->parent, 'zone' );

	if( !$parent ) {
		// Send error message with zip code info
		$to 		= 'rolson@sequoiaims.com';
		$subject 	= 'HIQ Product Selection Error: No Zone' ;
		$body 		= 'The following zip code was entered but not found in any zone:<br><b>' . $client_zip_code . '</b><br>Function: <b>is_serviceable_zip_code()</b>';
		$body 		.= '<h4>POST Data:</h4><pre>';
		$body 		.= print_r( $_POST, true );
		$body 		.= '</pre>';
		$headers 	= array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $to, $subject, $body, $headers );

		return false;
	}
	// 	error_log('Zip Check');
	// error_log( print_r( $parent->slug, true ) );

	return $parent->slug;
}

/**
 * Find a dealer that services the zone that was found via zip code
 * @param  string $zone_slug Slug of the zip code zone
 * @return bool|int            false, or dealer ID
 */
function zone_has_dealer( $zone_slug ) {

	$dealer_view_count_key 	= 'sqms_dealer_view_count';

	// Get all dealers who service this zone
	$args = array(
		'post_type' 			=> 'sqms_payne_dealer',
		'posts_per_page' 	=> -1,
		'tax_query' => array(
			array(
				'taxonomy' 		=> 'zone',
				'field' 			=> 'slug',
				'terms' 			=> $zone_slug,
				),
			),
		);

	// Search for dealer based on query args
	$dealer_array 			= get_posts( $args );
	// GFCommon::log_debug( __METHOD__ . '(): dealer_array => ' . print_r( $form, true ) );

	// No dealers found, send me an error to investigate
	if( !$dealer_array ) {

		$to 		= 'rolson@sequoiaims.com';
		$subject 	= 'HIQ Product Selection Error: No Dealers';
		$body 		= 'No dealer found for zone slug:<br>Zone Slug: ' . $zone_slug . '<br>Function: <b>zone_has_dealer()</b>';
		$body 		.= '<h4>POST Data:</h4><pre>';
		$body 		.= print_r( $_POST, true );
		$body 		.= '</pre>';
		$headers 	= array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $to, $subject, $body, $headers );

		return false;
	}

	$all_dealers = array();

	foreach ($dealer_array as $dealer) {
		$dealer_count 	= absint( get_post_meta( $dealer->ID, $dealer_view_count_key, true ) );
		$all_dealers[] = array(
	        'id' => $dealer->ID,
	        'count' => $dealer_count,
	    );
	}

	global $dealersMin;
	 $dealersMin = min( array_column( $all_dealers, 'count' ) );
	 	error_log('MIN');
	 error_log(  $dealersMin  );
	$dealersWithMinCount = array_filter($all_dealers, function ($dealer_min) {
	  	global $dealersMin;

	    return ($dealer_min['count'] == $dealersMin);
	});


	$chosen = $dealersWithMinCount[array_rand($dealersWithMinCount)]['id'];

	$current_dealer_count	= absint( get_post_meta( $chosen, $dealer_view_count_key, true ) );
	$current_dealer_count++;
	update_post_meta( $chosen, $dealer_view_count_key, $current_dealer_count );

	// found a dealer, get their ID
	return $chosen;
}

/**
 * Get the dealer name for output in PDF
 * @param  object $entry GF $entry object
 * @return string        Dealer name
 */
function get_dealer_name( $entry ) {

	$dealer_id = rgar( $entry, '69' );
	$dealer_name = get_the_title( $dealer_id );

	return $dealer_name;
}

/**
 * Given the product ID, get all its meta and build a table
 * @param  int $product_post_id the post ID of the chosen product
 * @return string                  HTML structure of product data
 */
function get_product_data( $product_post_id ) {

	$data_points = array(
		"sqms-product-tonnage",
		"sqms-product-odu-voltage",
		"sqms-product-odu-model",
		"sqms-product-furnace-model",
		"sqms-product-cooling-btu",
		"sqms-product-seer",
		"sqms-product-eer",
		"sqms-product-system-price"
		);

	$cmb 					= cmb2_get_metabox( 'sqms-product-overview-meta', $product_post_id );
	$cmb_fields 			= $cmb->prop( 'fields' );
	$prod_data_output 	= '<table>';

	foreach( $cmb_fields as $cmb_field ) {

		$cmb_field = $cmb->get_field( $cmb_field );

		if( $cmb_field && in_array(  $cmb_field->args( 'id' ), $data_points ) && 'NA' !== $cmb_field->escaped_value() && '0.00' !== $cmb_field->escaped_value() ) {

			$prod_data_output .= '<tr>';
			$prod_data_output .= '<td>'. $cmb_field->args( 'name' ) .'</td>';
			$prod_data_output .= '<td>'. $cmb_field->escaped_value() .'</td>';
			$prod_data_output .= '</tr>';
		}
	}

	$prod_data_output .= '</table>';

	return $prod_data_output;
}

/**
 * Build the finance options table
 * @param  string $system_price   Cost of the system
 * @param  string $warranty_price Cost of optional warranty
 * @return string                 HTML structure of warranty cost table
 */
function get_finance_options( $system_price, $warranty_price ) {

	$equip_cost 			= str_replace( ',', '', ltrim( $system_price, '$' ) );
	$install_cost_min 		= 1000.00;
	$install_cost_max 		= 2500.00;
	$total_cost_min 		= $equip_cost + $install_cost_min;
	$total_cost_max 		= $equip_cost + $install_cost_max;

	$term_options 	= array(
		35,
		47,
		59
		);

	$finance_data = '<h2>Interested in Financing?</h2>';
	$finance_data .= '<h4>Estimated Monthly Financing Payments, including installation costs, with <a href="https://hvacinstantquote.com/resources/appliance-financing/" target="_blank" title="Microf Financing">Microf Financing</a></h4>';
	$finance_data .= '<table><thead><tr><th>Payment Amount</th><th>35 monthly payments</th><th>47 monthly payments</th><th>59 monthly payments</th></tr></thead><tbody><tr><td>'.$system_price.' HVAC System + $1,000.00 Install Cost</td>';

	foreach ($term_options as $term) {
		$term_payment = microf_payment_calc($total_cost_min, $term);
		$finance_data .= '<td>$' . $term_payment . '</td>';
	}

	$finance_data .= '</tr><tr><td>'.$system_price.' HVAC System + $2,500.00 Install Cost</td>';

	foreach ($term_options as $term) {
		$term_payment = microf_payment_calc($total_cost_max, $term);
		$finance_data .= '<td>$' . $term_payment . '</td>';
	}

	$finance_data .= '</tr></tbody></table>';
	$finance_data .= '<p><small>Note: Actual monthly payment based upon actual installation cost provided by your dealer.</small></p>';

	return $finance_data;

}

/**
 * Use Microf's formula to calculate finance cost
 * @param  float $amount_financed Cost of system
 * @param  int $term            term length
 * @return float                  Payment amount
 */
function microf_payment_calc( $amount_financed, $term ) {

	$estimated_payment = ROUND(($amount_financed/$term), 2) + ROUND(($amount_financed *(53/2400)), 2);

	return $estimated_payment;

}

function syncMailchimp($data) {

    $apiKey = 'bf70c8feae6656aee33f2d370a31b28f-us15';
    $listId = 'f194b8d22b';

    $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
    $memberID = md5(strtolower($data['email']));
    $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberID;

    $json = json_encode([
        'email_address' => $data['email'],
        'status'        => $data['status'], // "subscribed","unsubscribed","cleaned","pending"
        'merge_fields'  => [
            'FNAME'     => $data['firstname'],
            'LNAME'     => $data['lastname']
        ]
    ]);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $result;
}

function is_valid( $dealer_id ) {
	return get_post_status( $dealer_id );
}
