<?php
/**
 * Functions for altering schema markup
 * @link http://www.kriesi.at/documentation/enfold/customize-schema-org-markup/
 */

/*
Add new input option field to the shortcode option windows. Also include sub-element option windows in case a custom markup is required for image slides, content slides, etc.
*/

add_filter('avf_template_builder_shortcode_elements','avia_custom_markup_element', 10, 2);
function avia_custom_markup_element($elements, $config)
{
	$elements[] = array(
		"name" => __("Custom Schema.org Markup Context",'avia_framework' ),
		"desc" => __("Set a custom schema.org markup context",'avia_framework' ),
		"id" => "custom_markup",
		"type" => "input",
		"std" => "");

	foreach($elements as $key => $data)
	{
		if(!empty($data['subelements']))
		{
			$elements[$key]['subelements'][] = array(
			"name" => __("Custom Schema.org Markup Context",'avia_framework' ),
			"desc" => __("Set a custom schema.org markup context",'avia_framework' ),
			"id" => "custom_markup",
			"type" => "input",
			"std" => "");
		}
	}

	return $elements;
}

/*
Check if the custom_markup option is set. If yes store it into the §meta variable to pass it to the shortcode handler
*/
add_filter('avf_template_builder_shortcode_meta', 'add_markup_to_meta', 10, 4);
function add_markup_to_meta($meta, $atts, $content, $shortcodename)
{
	$meta['custom_markup'] = isset($atts['custom_markup']) ? $atts['custom_markup'] : '';
	return $meta;
}

/*
Check if the custom_markup option is set. If yes store it into the §meta variable to pass it to the shortcode handler
*/
add_filter('avf_markup_helper_args','print_args_schema_org', 10, 1);
function print_args_schema_org($args)
{
	if(!empty($args['custom_markup']))
	{
		$args['context'] = $args['custom_markup'];
	}

	if($args['context'] == "no_markup") $args['context'] = false;

	return $args;
}
