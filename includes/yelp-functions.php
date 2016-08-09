<?php

/**
 * CURLs the Yelp API with our url parameters and returns JSON response
 */
function yelp_curl( $signed_url ) {

    // Send Yelp API Call using WP's HTTP API
    $data = wp_remote_get( $signed_url );

    //Use curl only if necessary
    if ( empty( $data['body'] ) ) {
        $ch = curl_init( $signed_url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        $data = curl_exec( $ch ); // Yelp response
        curl_close( $ch );
        $data     = yelp_update_http_for_ssl( $data );
        $response = json_decode( $data );
    } else {
        $data     = yelp_update_http_for_ssl( $data );
        $response = json_decode( $data['body'] );
    }

    // Handle Yelp response data
    return $response;
}

/**
 * Function update http for SSL
 *
 */
function yelp_update_http_for_ssl( $data ) {
    if ( ! empty( $data['body'] ) && is_ssl() ) {
        $data['body'] = str_replace( 'http:', 'https:', $data['body'] );
    } elseif ( is_ssl() ) {
        $data = str_replace( 'http:', 'https:', $data );
    }
    $data = str_replace( 'http:', 'https:', $data );
    return $data;
}
