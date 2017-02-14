<?php
/**
 * Functions to handle all the dynamic building and display of Gravity Forms
 * options inside of the system selector and photo quote forms.
 */

// Set scroll distance to 0 for selection form
add_filter( 'gform_confirmation_anchor_12', function() { return 0; } );
add_filter( 'gform_pre_render_12', 'display_choice_result' );
add_filter( 'gform_notification_12', 'get_dealer_email', 10, 3 );
add_filter( 'gform_pre_render_15', 'add_readonly_script' );
add_filter( 'gform_pre_render_20', 'dealer_review_id' );
add_filter( 'gform_notification_16', 'get_dealer_email', 10, 3 );
add_filter( 'gform_replace_merge_tags', 'replace_dealer_notification', 10, 7 );
add_filter( 'gform_confirmation', 'custom_confirmation', 10, 4 );
add_filter( 'gform_ajax_spinner_url', 'add_hiq_spinner_image', 10, 2 );

add_filter( 'gform_field_validation_12_47', 'validate_zip_zone', 10, 4 );
add_filter( 'gform_field_validation_16_12', 'validate_zip_zone', 10, 4 );

add_action( 'gform_pre_submission_12', 'choose_new_dealer' );
add_action( 'gform_after_submission_15', 'update_report_entry_meta', 10, 2 );
add_action( 'gform_pre_submission_16', 'choose_new_dealer' );

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
			$subject 	= 'HIQ Product Selection Error';
			$body 		= 'The following product was selected but not available<br>' . $prod_string;
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
		$content_output .= '<h3>Your Equipment Specifications:</h3>';
		$content_output .= get_product_data( $product_post_id );
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
 * Find the dealer email to send the GF notifcation to
 * @param  object $notification GF $notifcation
 * @param  object $form         GF $form
 * @param  object $entry        GF $entry
 * @return object               filtered GF $notifcation with updated email to address
 */
function get_dealer_email( $notification, $form, $entry ) {

	if ( $notification['name'] == 'Admin Notification' ) {

		if( $form['id'] == 12 ) {
			$dealer_id = rgpost( 'input_69'  );
			// $testing_email = rgpost( 'input_12'  );
		}
		elseif( $form['id'] == 16 ){
			$dealer_id = rgpost( 'input_18'  );
			// $testing_email = rgpost( 'input_10' );
		}
		else {
			return $notification;
		}

	      $dealer_email 	= get_post_meta( $dealer_id, 'sqms-product-email', true );
	      $notification['to'] 	= $dealer_email;
	      // $notification['to'] 	= $testing_email;

	  }

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

	$prod_string 		= rgar( $entry, '56' );
	$prod_obj 			= get_page_by_path($prod_string, OBJECT, 'sqms_prod_select');
	$product_post_id 	= $prod_obj->ID;
	$address_field_id 	= 47;
	$accessory_field_id 	= 57;
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

	$accessory_field       = GFFormsModel::get_field( $form, $accessory_field_id );
	$accessory_string = is_object( $accessory_field ) ? $accessory_field->get_value_export( $entry ) : '';

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
		$dealer_id = $entry['69'];
	}
	// Photo quote form
	elseif( $form['id'] == 16 ) {
		$dealer_id = $entry['18'];
	}
	// Temp testing form
	elseif( $form['id'] == 23 ) {
		$dealer_id = $entry['1'];
	}
	// None of the above, abort
	else {
		return $confirmation;
	}

	$confirmation 		= '';
	$dealer_name 	= get_the_title( $dealer_id );
	$dealer_link 		=  get_permalink( $dealer_id );
	$dealer_phone 	= get_post_meta( $dealer_id, 'sqms-product-phone', true );
	$dealer_address 	= get_post_meta( $dealer_id, 'sqms-product-address', true );
	$dealer_snippet 	= wpautop( get_post_meta( $dealer_id, 'sqms-product-snippet', true ) );
	$dealer_years 		= get_post_meta( $dealer_id, 'sqms-product-years', true );
	$dealer_license 	= get_post_meta( $dealer_id, 'sqms-product-license', true );
	$dealer_headshot = wp_get_attachment_image( get_post_meta( $dealer_id, 'sqms-product-headshot_id', 1 ), 'medium' );
	$dealer_logos 		= get_post_meta( $dealer_id, 'sqms-product-logos', 1 );

	$address = wp_parse_args( $dealer_address, array(
		'address-1' 	=> '',
		'address-2' 	=> '',
		'city' 			=> '',
		'state' 			=> '',
		'zip' 			=> '',
		)
	);

	$confirmation .= '<div class="dealer-conf-wrapper clearfix">';
	$confirmation .= '<h3>Thank you!</h3><p>Your certfied Payne dealer is <a href=" ' . $dealer_link . ' " target="_blank">' . $dealer_name . '</a> and they will be in contact to schedule your home visit within 24 hours.</p><hr />';
	$confirmation .= '<h1 class="dealer-conf-title"><a href=" ' . $dealer_link . ' " target="_blank">' . $dealer_name . '</a></h1>';
	$confirmation .= '<div class="clearfix">';
	$confirmation .= '<div class="flex_column av_one_half  flex_column_div first">';
	$confirmation .= '<div class="dealer-conf-headshot">' . $dealer_headshot . '</div>';
	$confirmation .= '</div>';
	$confirmation .= '<div class="flex_column av_one_half  flex_column_div dealer-conf-meta">';
	$confirmation .= '<p class="dealer-conf-address">' . esc_html( $address['address-1'] );
	if ( $address['address-2'] ) :
	$confirmation .= ' | ' . esc_html( $address['address-2'] );
	endif;
	$confirmation .= ' | ' . esc_html( $address['city'] ) . ' | ' . esc_html( $address['state'] ) . ' | ' . esc_html( $address['zip'] ) . '</p>';
	$confirmation .= '<p class="dealer-conf-phone">' . $dealer_phone . '</p>';
	$confirmation .= '<p class="dealer-conf-years"><span>Years in Business: </span>' . $dealer_years . '</p>';
	$confirmation .= '<p class="dealer-conf-license"><span>ROC#: </span>' . $dealer_license . '</p>';
	$confirmation .= '</div>';
	$confirmation .= '</div>';
	$confirmation .= '<div class="dealer-conf-snippet clearfix">' . $dealer_snippet . '</div>';
	$confirmation .= '<div class="dealer-conf-footer">';
	if( $dealer_logos ) :
	$confirmation .= '<h2>We our proud of our hard earned accredidations</h2>';
	$confirmation .= '<ul class="dealer-conf-icons">';
	foreach ( (array) $dealer_logos as $attachment_id => $attachment_url ) {
	$confirmation .= '<li class="dealer-conf-icon">';
	$confirmation .= wp_get_attachment_image( $attachment_id, 'medium' );
	$confirmation .= '</li>';
	}
	$confirmation .= '</ul>';
	endif;
	if( $form['id'] == 12 ) {
	$confirmation .= '<p>A copy of your quote information has been emailed to you. You may also download a PDF copy below.</p>';
	$confirmation .= do_shortcode( '[gravitypdf name="Client Copy" id="57a03bc2e0cc7" class="button dealer-pdf" entry='.$entry['id'].' text="Download PDF"]' );
	}
	$confirmation .= '</div>';
	$confirmation .= '</div>';

	return $confirmation;
}

/**
 * Replace default GF loading image with custom one
 * @param string $image_src Spinner image to be filtered
 * @param object $form      GF $form object
 * @return  string New spinner image URL
 */
function add_hiq_spinner_image( $image_src, $form ) {
	return SQMS_PROD_SEL_URL . '/assets/img/hiq-loader.gif';
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

/**
 * This finds the dealer we are assigning to this submission based on user location
 * @param  object $form GF $form object
 * @return void       Either aborts if not proper form, or updates $_POST
 */
function choose_new_dealer( $form ) {

	$zone 						= '';
	$address_field 			= '';
	$choose_error 			= '';
	$dealer_id_field 			= '';
	$dealer_view_count_key 	= 'sqms_dealer_view_count';

	if( $form['id'] == 12 ) {
		$address_field 	= '47_5';
		$dealer_id_field 	= 'input_69';
	}
	elseif( $form['id'] == 16 ) {
		$address_field 	= '12_5';
		$dealer_id_field 	= 'input_18';
	}
	else {
		return;
	}
// GFCommon::log_debug( __METHOD__ . '(): POST => ' . print_r( $_POST, true ) );
	// Find out what zone this client is in
	$zone = is_serviceable_zip_code( $_POST['input_'.$address_field] );

	GFCommon::log_debug( __METHOD__ . 'Zone: ' . $zone );

	// Get a dealer that servics that zone
	$selected_dealer_id 	= zone_has_dealer( $zone );

	GFCommon::log_debug( __METHOD__ . 'Dealer ID: ' . $selected_dealer_id );

	$dealer_count 			= absint( get_post_meta( $selected_dealer_id, $dealer_view_count_key, true ) );
	$dealer_count++;

	update_post_meta( $selected_dealer_id, $dealer_view_count_key, $dealer_count );

	/**
	 * Set POST variable for the hidden dealer ID field to the dealer ID chosen via query above
	 * See gravityhelp.com/documentation/article/gform_pre_submission
	 */
	$_POST[$dealer_id_field] = $selected_dealer_id;
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
		$choose_error = "No Zone Found";

		// Send error message with zip code info
		$to 		= 'rolson@sequoiaims.com';
		$subject 	= 'HIQ Product Selection Error: ' . $choose_error;
		$body 		= 'The following zip code was entered but not found in any zone:<br>' . $client_zip_code;
		$headers 	= array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $to, $subject, $body, $headers );

		return false;
	}

	return $parent->slug;
}

/**
 * Find a dealer that services the zone that was found via zip code
 * @param  string $zone_slug Slug of the zip code zone
 * @return bool|int            false, or dealer ID
 */
function zone_has_dealer( $zone_slug ) {

	// Get all dealers who service this zone
	$args = array(
		'post_type' 			=> 'sqms_payne_dealer',
		'posts_per_page' 	=> 1,
		'orderby'        		=> 'rand',
		'tax_query' => array(
			array(
				'taxonomy' 		=> 'zone',
				'field' 			=> 'slug',
				'terms' 			=> $zone_slug,
				),
			),
		);

	// Found a dealer, update their view count for later use...
	$dealer_array 			= get_posts( $args );
	// GFCommon::log_debug( __METHOD__ . '(): dealer_array => ' . print_r( $form, true ) );



	if( !$dealer_array ) {
		$choose_error = "No Dealers";

		$to 		= 'rolson@sequoiaims.com';
		$subject 	= 'HIQ Product Selection Error: ' . $choose_error;
		$body 		= 'The following zip code was entered but not found in any zone:<br>Client Zip: ' . $client_zip_code . '<br>Chosen Zone: ' . $zone;
		$headers 	= array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $to, $subject, $body, $headers );

		return false;
	}

	return $dealer_array[0]->ID;
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

	$finance_data = '';
	$finance_data .= '<h3>Estimated Monthly Financing Payments, including installation costs, with <a href="https://hvacinstantquote.com/resources/appliance-financing/" target="_blank" title="Microf Financing">Microf Financing</a></h3>';
	$finance_data .= '<table><thead><tr><th>Payment Amount</th><th>35 monthly payments</th><th>47 monthly payments</th><th>59 monthly payments</th></tr></thead><tbody><tr><td>$1,000.00 Install Cost</td>';

	foreach ($term_options as $term) {
		$term_payment = microf_payment_calc($total_cost_min, $term);
		$finance_data .= '<td>$' . $term_payment . '</td>';
	}

	$finance_data .= '</tr><tr><td>$2,500.00 Install Cost</td>';

	foreach ($term_options as $term) {
		$term_payment = microf_payment_calc($total_cost_max, $term);
		$finance_data .= '<td>$' . $term_payment . '</td>';
	}

	$finance_data .= '</tr></tbody></table>';
	$finance_data .= '<p><small>Note: Actual monthly payment based upon actual installation cost provided by your dealer.</small></p>';
	$finance_data .= '<h3>Optional Warranty Cost: <b>'.$warranty_price.'</b></h3>';
	$finance_data .= '<p><small>Note: Warranty cost not included in finance projections. Extended warranties are optional and can be added after accepting the final quote below.</small></p>';

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
