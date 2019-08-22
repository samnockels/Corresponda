<?php

defined('ABSPATH') || exit;

/**
 * Register the Corresponda shortcode
 */
class Corresponda_Register_Shortcode
{
    public function __construct()
    {
        add_shortcode('corresponda', array($this, 'render'));
    }

    public function render( $args )
    {
        if( !isset($args['name']) ){
            return "Corresponda Shortcode Error: Name not provided";
        }

        $name = sanitize_text_field( $args['name'] );

        $widget = hc_get_widget($name);

        if( !$widget ){
            return "Corresponda Shortcode Error: The widget '" . __( $name, 'corresponda' ) . "' does not exist!";
        }

        $renderer = hc_widgets()[$widget['tag']]['renderer'];

        if( !function_exists($renderer) ){
            return "Corresponda Shortcode Error: Could not render the widget. Please try reselecting the widget type.";
        }

        // render the widget
        if( isset($args['inputname']) ){
            return call_user_func( $renderer, $widget, $input_name = sanitize_text_field($args['inputname']) );
        }else{
            return call_user_func( $renderer, $widget );
        }
    }
}
return new Corresponda_Register_Shortcode();