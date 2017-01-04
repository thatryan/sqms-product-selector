<?php

// add_filter( 'gform_confirmation_anchor', '__return_false' );
add_filter( 'gform_confirmation_anchor_12', function() {
    return 0;
} );

// add_filter( 'gform_pre_render_12', 'create_dynamic_seer_dropdown' );
// add_filter( 'gform_pre_render_12', 'create_dynamic_eff_dropdown' );
add_filter( 'gform_pre_render_12', 'create_dynamic_orientation_dropdown' );

add_filter( 'gform_pre_render_20', 'dealer_review_id' );

add_filter( 'gform_pre_render_12', 'display_choice_result' );

add_filter( 'gform_notification_12', 'get_dealer_email', 10, 3 );
add_filter( 'gform_notification_16', 'get_dealer_email', 10, 3 );

// add_filter( 'gform_entry_meta', 'add_meta_to_entry', 10, 2);

add_filter('gform_pre_render_15', 'add_readonly_script');

add_filter( 'gform_replace_merge_tags', 'replace_dealer_notification', 10, 7 );

add_action( 'gform_pre_submission_12', 'choose_new_dealer' );
add_action( 'gform_pre_submission_16', 'choose_new_dealer' );

add_filter( 'gform_confirmation', 'custom_confirmation', 10, 4 );

add_action( 'gform_after_submission_15', 'update_report_entry_meta', 10, 2 );

function custom_confirmation( $confirmation, $form, $entry, $ajax ) {

	if( $form['id'] == 12 ) {
		$dealer_id = $entry['69'];
	}
	elseif( $form['id'] == 16 ) {
		$dealer_id = $entry['18'];
	}
	else {
		return $confirmation;
	}

	$confirmation = '';
	$dealer_name = get_the_title( $dealer_id );
	$dealer_link =  get_permalink( $dealer_id );

	$confirmation .= '<h4>Thank you!</h4><p>Your certfied Payne dealer, <a href=" ' . $dealer_link . ' " target="_blank">' . $dealer_name . '</a>, will be in contact to schedule your home visit within 24 hours.</p><p>A copy of your quote information has been emailed to you. You may also download a PDF copy below.</p>';

	$confirmation .= do_shortcode( '[gravitypdf name="Client Copy" id="57a03bc2e0cc7" entry='.$entry['id'].' text="Download PDF"]' );

    return $confirmation;
}


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

    if ( $current_page >= 7 ) {

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
	        $warranty_price = get_post_meta( $product_post_id, 'sqms-product-warranty-price', true );

	        $cmb = cmb2_get_metabox( 'sqms-product-overview-meta', $product_post_id );
	        $cmb_fields = $cmb->prop( 'fields' );

	        $content_output .= '<p>Your total quote is the guaranteed price for your selected system, plus the estimated cost of installation. <a href="https://hvacinstantquote.com/resources/faqs#about-money" target="_blank" title="Factors about cost of installation">Click here for common factors that affect the cost of an installation</a>.</p>';
	        $content_output .= '<h3>Your System Selection &amp; Quote</h3>';
	        $content_output .= '<div class="highlight-box cost-wrapper">';
	        $content_output .= '<h2>Your New HVAC System Equipment Quote is <span>' .  esc_html( $system_price ) . '</span></h2>';
	        $content_output .= '<h3>And Your Installation Estimate is Between <span>$1,000.00 - $2,500.00</span></h3>';
	        $content_output .= '<p><small>Note: Proper Equipment Selection Will Be Verified On Installation Inspection</small></p>';
	        $content_output .= '</div>';
	        $content_output .= get_product_data( $product_post_id );
	        $content_output .= '<div class="financing-box">' . get_finance_options( $system_price, $warranty_price ) . '</div>';

	        $content_output .= '<div class="highlight-box cost-wrapper">';
	        $content_output .= '<h2>Accept your guaranteed system quote.</h2>';
	        $content_output .= '</div>';
	        $content_output .= '<p>Once you accept this quote, you will be contacted by an HVACInstantQuote Certified professional HVAC specialist to set up a time for installation.</p>';

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
			    			$field->choices = $split_vert_split_hor;
			    			break;
		    			case 'hp-':
		    				if ( $tonnage == '1.5-' || $tonnage == '3.5-') {
		    					$field->choices = $hp_1_5_3_5;
		    				}
		    				else{
		    					$field->choices = $hp_all;
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

	if ( $current_page >= 6 ) {

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

function choose_new_dealer( $form ) {

	$zone = '';
	$address_field = '';
	$dealer_id_field = '';
	$dealer_view_count_key = 'sqms_dealer_view_count';

	if( $form['id'] == 12 ) {
		$address_field = '47_5';
		$dealer_id_field = 'input_69';
	}
	elseif( $form['id'] == 16 ) {
		$address_field = '12_5';
		$dealer_id_field = 'input_18';
	}
	else {
		return;
	}

	$client_zip_code = $_POST['input_'.$address_field];
	$term = get_term_by( 'slug', $client_zip_code, 'zone' );
	$parent = get_term_by( 'id', $term->parent, 'zone' );

	if( $parent ) {
		$zone = $parent->slug;
	}

	$args = array(
	    'post_type' => 'sqms_payne_dealer',
	                'posts_per_page' => 1,
	                'orderby'        => 'rand',
	    'tax_query' => array(
	        array(
	            'taxonomy' => 'zone',
	            'field'    => 'slug',
	            'terms'    =>  $zone,
	        ),
	    ),
	);

	$dealer_array = get_posts( $args );
	$selected_dealer_id = $dealer_array[0]->ID;

	$dealer_count = absint( get_post_meta( $selected_dealer_id, $dealer_view_count_key, true ) );
	$dealer_count++;
	update_post_meta( $selected_dealer_id, $dealer_view_count_key, $dealer_count );

	$_POST[$dealer_id_field] = $selected_dealer_id;

}

function dealer_review_id( $form ) {
	$dealer_id = $_GET['dealer_id'];
	$dealer_name = get_the_title( $dealer_id );
	$title_content = '';
	$title_content .= '<h3>You Are Reviewing: ' . $dealer_name . '</h3>';

	foreach( $form['fields'] as &$field ) {
	    //get html field
	    if ( $field->id == 9 ) {
	        //set the field content to the html
	        $field->content = $title_content;
	    }
	}

	return $form;
}

function get_dealer_email( $notification, $form, $entry ) {

	if ( $notification['name'] == 'Admin Notification' ) {

		if( $form['id'] == 12 ) {
			$dealer_id = rgpost( 'input_69'  );
		}
		elseif( $form['id'] == 16 ){
			$dealer_id = rgpost( 'input_18'  );
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

	$dealer_id = rgar( $entry, '69' );
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
		$dealer_id = rgpost( 'input_69'  );
	}
	elseif( $form['id'] == 16 ){
		$dealer_id = rgpost( 'input_18'  );
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

	// accessories data
	$accessory_string       = '';

	foreach($form_data['field']['57.Are you interested in any accessories?'] as $acc_val){
	    $accessory_string .= $acc_val . '<br>';
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

function get_finance_options( $system_price, $warranty_price ) {

	$equip_cost = str_replace( ',', '', ltrim( $system_price, '$' ) );
	$install_cost_min = 1000.00;
	$install_cost_max = 2500.00;
	$total_cost_min = $equip_cost + $install_cost_min;
	$total_cost_max = $equip_cost + $install_cost_max;

	$term_options = array(
			35,
			47,
			59
		);

		$finance_data = '';
		$finance_data .= '<h3>Estimated Monthly Payments, including installation costs, with <a href="https://hvacinstantquote.com/resources/appliance-financing/" target="_blank" title="Microf Financing">Microf Financing</a></h3>';
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
		$finance_data .= '<p><small>Note: Warranty cost not included in finance projections.</small></p>';


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
