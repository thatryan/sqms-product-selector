<?php

// add_filter( 'gform_confirmation_anchor', '__return_false' );
add_filter( 'gform_confirmation_anchor_12', function() {
    return 0;
} );

add_filter( 'gform_pre_render_12', 'create_dynamic_seer_dropdown' );
add_filter( 'gform_pre_render_12', 'create_dynamic_eff_dropdown' );
add_filter( 'gform_pre_render_12', 'create_dynamic_orientation_dropdown' );

add_filter( 'gform_pre_render_12', 'build_dealer_list' );
add_filter( 'gform_pre_render_16', 'build_dealer_list' );

add_filter( 'gform_pre_render_12', 'display_choice_result' );

add_filter( 'gform_notification_12', 'get_dealer_email', 10, 3 );
add_filter( 'gform_notification_16', 'get_dealer_email', 10, 3 );

add_filter( 'gform_entry_meta', 'add_meta_to_entry', 10, 2);

add_filter('gform_pre_render_15', 'add_readonly_script');

add_filter( 'gform_replace_merge_tags', 'replace_dealer_notification', 10, 7 );

add_action( 'gform_after_submission_15', 'update_report_entry_meta', 10, 2 );

function add_readonly_script($form){
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

function display_choice_result( $form ) {

    $current_page = GFFormDisplay::get_current_page( $form['id'] );
    $content_output = "";
    $html_content = "";
    $prod_string = "";

    if ( $current_page == 11 ) {
        foreach ( $form['fields'] as &$field ) {
        	if ( strpos( $field->cssClass, 'product-builder-item' ) === false ) {
        	    continue;
        	}

            //gather form data to save into html field (id 14 on my form), exclude page break
            if ( $field->id != 14 && $field->type != 'page' ) {
                	$is_hidden = RGFormsModel::is_field_hidden( $form, $field, array() );
                	$populated = rgpost( 'input_' . $field->id );

                    if ( !$is_hidden && $populated !='' ) {
                    	$html_content .= '<li>' . $field->label . ': ' . rgpost( 'input_' . $field->id ) . '</li>';
                    	$prod_string .= rgpost( 'input_' . $field->id );
                    }
            }
        }


        // Get the chosen product object
        $prod_obj = get_page_by_path($prod_string, OBJECT, 'sqms_prod_select');

	        // Get link to single product page
	        $prod_link = esc_url( get_permalink( $prod_obj->ID ) );

	        $product_post_id = $prod_obj->ID;

	        $prod_meta = get_post_meta( $product_post_id );
	        $title = get_the_title( $product_post_id );
	        $cat = get_the_terms ( $product_post_id, 'system_type' );

	        $system_price = get_post_meta( $product_post_id, 'sqms-product-system-price', true );

	        $cmb = cmb2_get_metabox( 'sqms-product-overview-meta', $product_post_id );
	        $cmb_fields = $cmb->prop( 'fields' );

	        $content_output .= '<p>Your total quote is the guaranteed price for your selected system, plus the estimated cost of installation. <a href="https://hvacinstantquote.com/resources/faqs#about-money" target="_blank" title="Factors about cost of installation">Click here for common factors that affect the cost of an installation</a>.</p>';
	        $content_output .= '<p><b>Your System Selection &amp; Quote</b></p>';
	        $content_output .= '<div class="col-wrapper">';
	        $content_output .= '<div class="col-left">' . get_product_data( $product_post_id ) . '</div>';
	        $content_output .= '<div class="col-right">';
	        $content_output .= '<div class="highlight-box cost-wrapper">';
	        $content_output .= '<h3>Your HVAC System Equipment Quote: ' .  esc_html( $system_price ) . '</h3>';
	        $content_output .= '<h3>Your HVAC System Installation Estimate: $1,000.00 - $2,500.00</h3>';
	        $content_output .= '<p>Note: Proper Equipment Selection Will Be Verified On Installation Inspection</p>';
	        $content_output .= '</div>';
	        $content_output .= '</div>';
	        $content_output .= '</div>';


	        $content_output .= '<h5 class="financing-box-title">Estimated Monthly Payments, including installation costs, with <a href="https://hvacinstantquote.com/resources/appliance-financing/" target="_blank" title="Microf Financing">Microf Financing</a></h5>';
	        $content_output .= '<div class="financing-box">' . get_finance_options( $system_price ) . '</div>';

	        $content_output .= '<div class="highlight-box">';
	        $content_output .= '<p>Choose a <b>Payne Certified Dealer</b> & accept quote below to submit your contact information and schedule a <b>No Cost Installation Inspection</b> to verify the equipment selected is correct and provide an exact installation charge.</p>';
	        $content_output .= '</div>';

	        //loop back through form fields to get html field (id 14 on my form) that we are populating with the data gathered above
	        foreach( $form['fields'] as &$field ) {
	            //get html field
	            if ( $field->id == 14 ) {
	                //set the field content to the html
	                $field->content = $content_output;
	            }

	            if ( $field->id == 56 ) {
	                //set the field content to the html
	                $field->defaultValue = $prod_string;
	            }
	        }
	    }

    //return altered form so changes are displayed
    return $form;
}

function get_product_data( $product_post_id ) {

	$data_points = array(
				"sqms-product-ahri",
				"sqms-product-tonnage",
				"sqms-product-odu-voltage",
				"sqms-product-odu-model",
				"sqms-product-coil-model",
				"sqms-product-furnace-model",
				"sqms-product-cooling-btu",
				"sqms-product-seer",
				"sqms-product-eer",
				"sqms-product-system-price"
		);

	$cmb = cmb2_get_metabox( 'sqms-product-overview-meta', $product_post_id );
	$cmb_fields = $cmb->prop( 'fields' );
	$prod_data_output = '';

	$prod_data_output .= '<table>';

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

function build_disclaimer_html() {
$disclaimer_html = <<<'DISCLAIMERS'
<h3>Factors that may affect price when dealer evaluates your home:</h3>
<div class="col-wrapper">
<div class="col-left">
<h4>Ductwork Issues</h4>
<ul>
<li>Inadequate return or supply air</li>
<li>Leaking ductwork</li>
<li>Collapsed flex duct</li>
</ul>
<h4>Electrical</h4>
<ul>
<li>Inadequate or improve power supply</li>
<li>Requires new disconnect / fuse sizes</li>
<li>New thermostat required</li>
</ul>
<h4>Unit Location</h4>
<ul>
<li>Diificult to access unit due to fences, roof location, etc.</li>
<li>Unit location changes</li>
</ul>
<h4>Split System Issues</h4>
<ul>
<li>New refrigerant line set</li>
<li>Evap coil dimension changes from original</li>
<li>New concrete mounting pad needed</li>
</ul>
<h4>Package Units</h4>
<ul>
<li>New roof curb required</li>
<li>New downturn plennum required</li>
</ul>
</div>
<div class="col-right">
<img src="https://hvacinstantquote.com/wp-content/uploads/2016/09/hvac-installation.jpg" class="alignright" />
</div>
</div>
DISCLAIMERS;

return $disclaimer_html;
}

function build_dealer_list( $form ) {
	$current_page = GFFormDisplay::get_current_page( $form['id'] );

	if( $form['id'] == 12 ) {
		if ( $current_page == 10 ) {

			require_once('OAuth.php');
			include_once 'yelp-functions.php';

			foreach ( $form['fields'] as &$field ) {
				// This is the customer address field
				if ( $field->id == 47  ) {
					$user_location = build_user_location_string( $field );
				}
			} //end foreach fields loop

			    $posts = get_posts( 'post_type=sqms_payne_dealer&numberposts=-1' );
			    $dealers_count = 0;
			    $dealers_in_range = array();
			    $i = 0;
			    $range = 20;
			    $max_range = 50;
			    while ( $i < count($posts) ) {
			    	$post = $posts[$i];
			    	$post_id = $post->ID;

			    	$map = get_post_meta( $post_id, 'sqms-product-location', true );

			    	$lat = $map['latitude'];
			    	$long = $map['longitude'];

			    	$distance_string_call = 'https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins='.$user_location.'&destinations='. $lat . ',' . $long.'&key='.GMAP_API_KEY;

			    	$distance_data = wp_remote_get( $distance_string_call );

			    	$response = json_decode( $distance_data['body'] );

			    	$dealer_distance_from_customer = $response->rows[0]->elements[0]->distance->value;

			    	$distance_miles = $dealer_distance_from_customer *  0.000621371192;

			    	if ( $distance_miles <= $range ) {
			    		$dealers_in_range[] = $post_id;
			    	}

			    	// increment our index
			    	$i++;

			    	// If we're on the last post, look to see if we've found any dealers yet
			    	if ( $i == count($posts) && empty( $dealers_in_range ) ) {

			    		if( $range == $max_range ) break;
			    		// Reset our index
			    		$i = 0;

			    		// Increase the range
			    		$range += 10; // or whatever distance you want to increase by
			    	}
			    }


			$dealers_count = sizeof( $dealers_in_range );

			// Tell the hidden field that is used for conditional logic check to hold the number that is the size of array built. Should be 0 if no dealers
			foreach( $form['fields'] as &$field ) {
				if ( $field->id != 60 ) {
				    continue;
				}
				$field->defaultValue = $dealers_count;
			}

			// No dealers found, get out now
			if( !$dealers_count ) {
				return $form;
			}

			if( $dealers_count > 3 ) {
				shuffle( $dealers_in_range );
				$dealers_in_range_trimmed = array_slice( $dealers_in_range, 0, 3 );
				$dealers_in_range = $dealers_in_range_trimmed;
			}

			// This is the select dealer field of radio buttons, placeholder for this dynamic update
			foreach( $form['fields'] as &$field ) {
				if ( $field->id != 55 ) {
				    continue;
				}
				$field->choices = get_dealer_list_data( $dealers_in_range );
			}

		} // end page 10 check
	}

	elseif( $form['id'] == 16 ){

		if ( $current_page == 2 ) {

			require_once('OAuth.php');
			include_once 'yelp-functions.php';

			foreach ( $form['fields'] as &$field ) {
				// This is the customer address field
				if ( $field->id == 12  ) {
					$user_location = build_user_location_string( $field );
				}
			} //end foreach fields loop

			    $posts = get_posts( 'post_type=sqms_payne_dealer&numberposts=-1' );
			    $dealers_count = 0;
			    $dealers_in_range = array();
			    $i = 0;
			    $range = 20;
			    $max_range = 50;
			    while ( $i < count($posts) ) {
			    	$post = $posts[$i];
			    	$post_id = $post->ID;

			    	$map = get_post_meta( $post_id, 'sqms-product-location', true );

			    	$lat = $map['latitude'];
			    	$long = $map['longitude'];

			    	$distance_string_call = 'https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins='.$user_location.'&destinations='. $lat . ',' . $long.'&key='.GMAP_API_KEY;

			    	$distance_data = wp_remote_get( $distance_string_call );

			    	$response = json_decode( $distance_data['body'] );

			    	$dealer_distance_from_customer = $response->rows[0]->elements[0]->distance->value;

			    	$distance_miles = $dealer_distance_from_customer *  0.000621371192;

			    	if ( $distance_miles <= $range ) {
			    		$dealers_in_range[] = $post_id;
			    	}

			    	// increment our index
			    	$i++;

			    	// If we're on the last post, look to see if we've found any dealers yet
			    	if ( $i == count($posts) && empty( $dealers_in_range ) ) {

			    		if( $range == $max_range ) break;
			    		// Reset our index
			    		$i = 0;

			    		// Increase the range
			    		$range += 10; // or whatever distance you want to increase by
			    	}
			    }


			$dealers_count = sizeof( $dealers_in_range );

			// Tell the hidden field that is used for conditional logic check to hold the number that is the size of array built. Should be 0 if no dealers
			foreach( $form['fields'] as &$field ) {
				if ( $field->id != 16 ) {
				    continue;
				}
				$field->defaultValue = $dealers_count;
			}

			// No dealers found, get out now
			if( !$dealers_count ) {
				return $form;
			}

			if( $dealers_count > 3 ) {
				shuffle( $dealers_in_range );
				$dealers_in_range_trimmed = array_slice( $dealers_in_range, 0, 3 );
				$dealers_in_range = $dealers_in_range_trimmed;
			}

			// This is the select dealer field of radio buttons, placeholder for this dynamic update
			foreach( $form['fields'] as &$field ) {
				if ( $field->id != 15 ) {
				    continue;
				}
				$field->choices = get_dealer_list_data( $dealers_in_range );
			}

		} // end page 2 check
	}
	else {
		return $form;
	}


	return $form;
}

function get_dealer_list_data( $dealers_in_range ) {

	$dealers = array();
	$dealer_view_count_key = 'sqms_dealer_view_count';

	foreach ($dealers_in_range as $dealer_id ) {

		$dealer_count = absint( get_post_meta( $dealer_id, $dealer_view_count_key, true ) );
		$dealer_count++;
		update_post_meta( $dealer_id, $dealer_view_count_key, $dealer_count );

		$thumb = get_the_post_thumbnail( $dealer_id );
		$dealer_name = get_the_title( $dealer_id );
		$yelp_id = get_post_meta( $dealer_id, 'sqms-product-yelp', true );
		$phone = get_post_meta( $dealer_id, 'sqms-product-phone', true );
		$address = get_post_meta( $dealer_id, 'sqms-product-address', 1 );
		$address = wp_parse_args( $address, array(
		    'address-1' => '',
		    'address-2' => '',
		    'city'      => '',
		    'state'     => '',
		    'zip'       => '',
		) );
		$map = get_post_meta( $dealer_id, 'sqms-product-location', true );
		$lat = $map['latitude'];
		$long = $map['longitude'];

		$display = '<span class="product-choice-title">' . $dealer_name . '</span>';
		$display .= $thumb;
		$display .= '<p class="dealer-phone">' . $phone . '</p><p class="dealer-address">';
		$display .= esc_html( $address['address-1'] );
		if( $address['address-2'] ) {
			$display .= ', ' . esc_html( $address['address-2'] );
		}
		$display .= '<br>' . esc_html( $address['city'] );
		$display .= ' ' . esc_html( $address['state'] );
		$display .= ', ' . esc_html( $address['zip'] );
		$display .= '</p><img src="//maps.googleapis.com/maps/api/staticmap?center=' . $lat . ',' . $long . '&size=400x220&markers=' . $lat . ',' . $long  . '&key=' . GMAP_API_KEY . ' " class="dealer-map" />';
		$display .= build_dealer_yelp_output( $yelp_id );

		$dealers[] = array( 'text' => $display, 'value' => $dealer_id );
	}

	return $dealers;
}

function create_dynamic_seer_dropdown( $form ) {

	$current_page = GFFormDisplay::get_current_page( $form['id'] );

	if ( $current_page >= 6 ) {

		include 'data/data-seer.php';

		foreach ( $form['fields'] as &$field ) {

		    if ( $field->type != 'select' || strpos( $field->cssClass, 'seer-rating-dynamic' ) === false ) {
		        continue;
		    }

		    $system_choice = rgpost( 'input_3' );

		    // Square foot select
		    $tonnage = rgpost( 'input_4' );

		    switch ( $system_choice ) {
		    	case 's':
		    		$split_choice = rgpost( 'input_21' );

		    		switch ( $split_choice ) {
		    			case 'gv-':
		    				$field->choices = $split_vert_split_hor;
		    				break;
		    			case 'gh-':
		    				if ( $tonnage == '3.5-' || $tonnage == '5.0-' ) {
		    					$field->choices = $split_hor_3_5_5;
		    				}
		    				else {
		    					$field->choices = $split_vert_split_hor;
		    				}
		    				break;
		    			case 'hp-':
		    				if ( $tonnage == '1.5-' || $tonnage == '2.5-' || $tonnage == '3.5-') {
		    					$field->choices = $heat_pump_5s;
		    				}
		    				elseif ( $tonnage == '2.0-' || $tonnage == '3.0-' ) {
		    					$field->choices = $heat_pump_2_3;
		    				}
		    				elseif ( $tonnage == '4.0-' || $tonnage == '5.0-' ) {
		    					$field->choices = $heat_pump_4_5;
		    				}
		    				break;

		    			default:
		    				$field->choices = $error_select;
		    				break;
		    		}


		    		break;

		    	case 'our-':
		    		$field->choices = $cool_rep;
		    		break;

		    	case 'spp':
		    		$field->choices = $packaged;
		    		break;

		    	default:
		    		$field->choices = $error_select;
		    		break;
		    }

		    $field->placeholder = 'Select a SEER';

		}
	}

    return $form;
}

function create_dynamic_eff_dropdown( $form ) {

	$current_page = GFFormDisplay::get_current_page( $form['id'] );

	if ( $current_page >= 7 ) {

		include 'data/data-efficiency.php';

		foreach ( $form['fields'] as &$field ) {

		    if ( $field->type != 'select' || strpos( $field->cssClass, 'efficiency-dynamic' ) === false ) {
		        continue;
		    }

		    $system_choice = rgpost( 'input_3' );
		    $system_type = rgpost( 'input_21' );

		    // Square foot select
		    $tonnage = rgpost( 'input_4' );
		    $seer = rgpost( 'input_37' );

		    $string = $system_choice . $system_type . $tonnage . $seer;

		    if( $string === 'sgh-2.5-14.0-' || $string === 'sgv-3.5-16.0-' ) {
		    	$field->choices = $split_80;
		    }
		    elseif( $string === 'sgh-3.0-14.5-' ) {
		    	$field->choices = $split_90;
		    }
		    else {
		    	$field->choices = $split_all;
		    }

		    $field->placeholder = 'Choose Efficiency Rating';

		}
	}

    return $form;
}

function create_dynamic_orientation_dropdown( $form ) {

	$current_page = GFFormDisplay::get_current_page( $form['id'] );

	if ( $current_page >= 7 ) {

		include 'data/data-orientation.php';

		foreach ( $form['fields'] as &$field ) {

		    if ( $field->type != 'radio' || strpos( $field->cssClass, 'orientation-dynamic' ) === false ) {
		        continue;
		    }

		    $system_choice = rgpost( 'input_3' );
		    $tonnage = rgpost( 'input_4' );
		    $seer = rgpost( 'input_37' );

		    $string = $system_choice . $tonnage . $seer;

		    if( $string === 'our-3.0-14.5-' || $string === 'our-5.0-14.0-' ) {
		    	$field->choices = $out_v;
		    }
		    else {
		    	$field->choices = $out_all;
		    }

		    $field->placeholder = 'Choose Unit Orientation';

		}
	}

    return $form;
}
function build_user_location_string( $field ) {

	foreach ( $field->inputs as $input ) {
	    $input_name = 'input_' . str_replace( '.', '_', $input['id'] );
	    $value = rgpost( $input_name );
	    if( $value ) {
	    	$address_string .= $value . ' ';
	    }
	}

	$address_clean = rtrim( $address_string );

	$location_string = urlencode($address_clean);

	return $location_string;
}

function build_dealer_yelp_output( $yelp_id ) {

	$yelp_data = get_business( $yelp_id );

	$output = '';

	$output .= '	<br /><img class="rating" src=" ' . esc_attr( $yelp_data->rating_img_url ) . ' " alt=" ' . esc_attr( $yelp_data->name ) .  ' " title="' . esc_attr( $yelp_data->name )  . '" /><br />';

	$output .= '<a class="yelp-branding" href=" ' . esc_attr( $yelp_data->url ) . ' " target="_blank"><img src=" ' . SQMS_PROD_SEL_URL . '/assets/img/yelp.png' . ' " alt="Powered by Yelp" /></a>';

	return $output;
}


function get_business($business_id) {

	$token = new OAuthToken( YELP_TOKEN, YELP_TOKEN_SECRET );

	$consumer = new OAuthConsumer( YELP_CONSUMER_KEY, YELP_CONSUMER_SECRET );

	$signature_method = new OAuthSignatureMethod_HMAC_SHA1();

	$unsigned_url = YELP_UNSIGNED_URL . $business_id;

	$oauthrequest = OAuthRequest::from_consumer_and_token( $consumer, $token, 'GET', $unsigned_url );

	$oauthrequest->sign_request( $signature_method, $consumer, $token );

	$signed_url = $oauthrequest->to_url();

	$transient = 'yelp_response_id_' . $business_id;

	if ( ( $response = get_transient( $transient ) ) == false ) {
		$expiration = 60 * 60 * 24;

		// Cache data wasn't there, so regenerate the data and save the transient
		$response = yelp_curl( $signed_url );
		set_transient( $transient, $response, $expiration );
	}

	return $response;
}

function get_dealer_email( $notification, $form, $entry ) {

	if ( $notification['name'] == 'Admin Notification' ) {

		if( $form['id'] == 12 ) {
			$dealer_id = rgpost( 'input_55'  );
		}
		elseif( $form['id'] == 16 ){
			$dealer_id = rgpost( 'input_15'  );
		}
		else {
			return $notification;
		}

	      $dealer_email = get_post_meta( $dealer_id, 'sqms-product-email', true );

	      $notification['to'] = $dealer_email;

	  }

	return $notification;
}


function get_dealer_name( $entry ) {

	$dealer_id = rgar( $entry, '55' );
	$dealer_name = get_the_title( $dealer_id );

	return $dealer_name;
}

function add_meta_to_entry($entry_meta, $form_id){

    $entry_meta['dealer'] = array(
        'label' => 'Dealer',
        'is_numeric' => false,
        'update_entry_meta_callback' => 'update_dealer_entry_meta',
        'is_default_column' => false
    );

    return $entry_meta;
}

function update_dealer_entry_meta( $key, $lead, $form ){

	if( $form['id'] == 12 ) {
		$dealer_id = rgpost( 'input_55'  );
	}
	elseif( $form['id'] == 16 ){
		$dealer_id = rgpost( 'input_15'  );
	}
	else {
		return '';
	}

	$dealer = get_post( $dealer_id );
	$dealer_name = $dealer->post_name;
	$value = $dealer_name;

	return $value;
}


function replace_dealer_notification( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

    $custom_merge_tag = '{dealer_notification}';

    if ( strpos( $text, $custom_merge_tag ) === false ) {
        return $text;
    }

    $prod_string = rgar( $entry, '56' );
    $prod_obj = get_page_by_path($prod_string, OBJECT, 'sqms_prod_select');
    $product_post_id = $prod_obj->ID;

    // Address data
    $address_field_id      = 47;
    $street_value  = rgar( $entry, $address_field_id . '.1' );
    $street2_value = rgar( $entry, $address_field_id . '.2' );
    $city_value    = rgar( $entry, $address_field_id . '.3' );
    $state_value   = rgar( $entry, $address_field_id . '.4' );
    $zip_value     = rgar( $entry, $address_field_id . '.5' );

    if( $street2_value ) {
    	$formatted_street = $street_value . ', ' . $street2_value;
    }
    else {
    	$formatted_street = $street_value;
    }
    $formatted_address_value = $formatted_street . '<br>' . $city_value . ', ' . $state_value . ' ' . $zip_value;

    // contact day data
    $contact_field_id      = 49;
    $contact_count 		= 7;
    $contact_string		= '';

    for( $i=1; $i <= 7; $i++ ) {
    	$contact_val = rgar( $entry, $contact_field_id . '.' . $i );
    	if( $contact_val ) {
    		$contact_string .= $contact_val . '<br>';
    	}
    }

    // accessories data
    $accessory_field_id      = 57;
    $accessory_count 		= 7;
    $accessory_string		= '';

    for( $i=1; $i <= 7; $i++ ) {
    	$acc_val = rgar( $entry, $accessory_field_id . '.' . $i );
    	if( $acc_val ) {
    		$accessory_string .= $acc_val . '<br>';
    	}
    }

    include 'template/template-merge-tag.php';

    $text = str_replace( $custom_merge_tag, $prod_data_output, $text );

    return $text;
}

function update_report_entry_meta( $entry, $form ) {

	$reported = 'Yes';
	$quote_form_id = 12;
	$quote_id = $_GET['quote_id'];

	$quote_id = filter_input( INPUT_GET, 'quote_id', FILTER_SANITIZE_NUMBER_INT );


	gform_update_meta( intval( $quote_id ), 'quote_reported', $reported, $quote_form_id );

}

function get_finance_options( $system_price ) {

	$equip_cost = str_replace( ',', '', ltrim( $system_price, '$' ) );
	$install_cost = 2500.00;
	$total_cost = $equip_cost + $install_cost;

	$term_options = array(
			35,
			47,
			59
		);

		$finance_data = '';
		$finance_data .= '<table><thead><tr><th>&nbsp;</th><th>35 monthly payments</th><th>47 monthly payments</th><th>59 monthly payments</th></tr></thead><tbody><tr><td>Payment Amount</td>';

		foreach ($term_options as $term) {
			$term_payment = microf_payment_calc($total_cost, $term);

			$finance_data .= '<td><i class="fa fa-dollar"></i>' . $term_payment . '</td>';
		}


		$finance_data .= '</tr></tbody></table>';


	return $finance_data;
}

function microf_payment_calc($amount_financed, $term){
  /*MSRP*/
  $estimated_payment = ROUND(($amount_financed/$term), 2) + ROUND(($amount_financed *(40/2400)), 2)
  /*Admin Fee*/
  + ROUND((200/$term), 2) + ROUND((200*(40/2400)), 2)
  /*SECURITY DEPOSIT*/
  + ROUND(((ROUND(($amount_financed/$term), 2) + ROUND(($amount_financed *(40/2400)), 2))/$term), 2)
  + ROUND(((ROUND(($amount_financed/$term), 2) + ROUND(($amount_financed *(40/2400)), 2))*(40/2400)), 2)
  /*LDW Fee*/
  + 10;
  /*SALES TAX
  + ROUND(((
  ROUND(($amount_financed/$term), 2) + ROUND(($amount_financed *(40/2400)), 2)
  + ROUND((200/$term), 2) + ROUND((200*(40/2400)), 2)
  + ROUND(((ROUND(($amount_financed/$term), 2) + ROUND(($amount_financed *(40/2400)), 2))/$term), 2)
  ) * Sales_Tax__c), 2)
  */
  return $estimated_payment;
}

/**
 * to exclude field from notification add 'exclude[ID]' option to {all_fields} tag
 * 'include[ID]' option includes HTML field / Section Break field description / Signature image in notification
 * see http://www.gravityhelp.com/documentation/page/Merge_Tags for a list of standard options
 * example: {all_fields:exclude[2,3]}
 * example: {all_fields:include[6]}
 * example: {all_fields:include[6],exclude[2,3]}
 */
add_filter( 'gform_merge_tag_filter', 'all_fields_extra_options', 11, 5 );
function all_fields_extra_options( $value, $merge_tag, $options, $field, $raw_value ) {
    if ( $merge_tag != 'all_fields' ) {
        return $value;
    }

    // usage: {all_fields:include[ID],exclude[ID,ID]}
    $include = preg_match( "/include\[(.*?)\]/", $options , $include_match );
    $include_array = explode( ',', rgar( $include_match, 1 ) );

    $exclude = preg_match( "/exclude\[(.*?)\]/", $options , $exclude_match );
    $exclude_array = explode( ',', rgar( $exclude_match, 1 ) );

    $log = "all_fields_extra_options(): {$field['label']}({$field['id']} - {$field['type']}) - ";

    if ( $include && in_array( $field['id'], $include_array ) ) {
        switch ( $field['type'] ) {
            case 'html' :
                $value = $field['content'];
                break;
            case 'section' :
                $value .= sprintf( '<tr bgcolor="#FFFFFF">
                                                        <td width="20">&nbsp;</td>
                                                        <td>
                                                            <font style="font-family: sans-serif; font-size:12px;">%s</font>
                                                        </td>
                                                   </tr>
                                                   ', $field['description'] );
                break;
            case 'signature' :
                $url = GFSignature::get_signature_url( $raw_value );
                $value = "<img alt='signature' src='{$url}'/>";
                break;
        }
        GFCommon::log_debug( $log . 'included.' );
    }
    if ( $exclude && in_array( $field['id'], $exclude_array ) ) {
        GFCommon::log_debug( $log . 'excluded.' );
        return false;
    }
    return $value;
}
