<?php
defined('ABSPATH') or die('Pls b nice!');

/**
 * Handles AJAX Requests made by the admin dashboard
 */
class Corresponda_Ajax
{
    /**
     * Validate the incoming request
     *
     * @param Array $_REQUEST - the request to validate
     * @param Array $required_fields - fields that need to be set
     * @param Array $optional_fields - at least one of the optional fields must be set
     * @param boolean $is_admin - whether the user needs to be an admin
     * @return void or exit if invalid request
     */
    private function validate_request( $request, $required_fields, $optional_fields = false, $is_admin = true ){

        // user is not admin
        if( $is_admin && !current_user_can('administrator') ){
            wp_send_json(array("success"=>false));
            exit();
        }

        // missing required field
        foreach ($required_fields as $required) {
            if( !isset( $_POST[$required] ) ){
                wp_send_json(array("success"=>false));
                exit();
            }
        }
        
        if( is_array($optional_fields) ){
            $opt_fields = array_map(function($required){
                return isset($_POST[$required]);
            }, $optional_fields);
            // missing all optional fields (at least 1 is required)
            if( !in_array(true, $opt_fields) ){
                wp_send_json(array("success"=>false));
                exit();
            }
        }
    }

    /**
     * Create a new widget
     */
    public function corresponda_create_new_widget(){
        
        $this->validate_request( $_POST, array(
            'widget_name',
            'widget_tag'
        ), $is_admin = true);

        $widget_name = sanitize_text_field($_POST['widget_name']);
        $widget_tag  = sanitize_text_field($_POST['widget_tag']);

        // strip out whitespace from name, make lowercase, and sanitize
        $widget_name = strtolower(preg_replace('/\s+/', '', $widget_name));

        // widget already exists
        if( hc_widget_exists($widget_name) ){
            wp_send_json(array(
                'success' => false,
                'error'   => 'That widget name already exists.'
            ));
            exit();
        }

        // create widget
        $success = hc_create_widget($widget_name, $widget_tag);

        wp_send_json(array('success' => $success));
        exit();
    }

    /**
     * Get Widget by name
     */
    public function corresponda_get_widget( $widget_name ){

        $this->validate_request( $_POST, array(
            'widget_name'
        ), $is_admin = false);

        $widget_name = sanitize_text_field($_POST['widget_name']);
    
        // get the widget
        $widget = hc_get_widget( $widget_name );

        // widget didnt exist
        if( !$widget ){
            wp_send_json(array(
                'success' => false,
                'error'   => "Widget not found"
            ));
            exit();
        }

        wp_send_json(array(
            'success' => true,
            'widget'  => $widget
        ));
        exit();
    }

    /**
     * Update a widget
     */
    public function corresponda_update_widget(){
        
        $this->validate_request( $_POST, array(
            'widget_name'
        ), array(
            'title',
            'tag',
            'options'
        ), $is_admin = true);

        $widget_name = sanitize_text_field($_POST['widget_name']);

        // build array of options to change
        $options = array();

        if(isset($_POST['title'])){
            $options['title'] = sanitize_text_field($_POST['title']);
        }
        if(isset($_POST['tag'])){
            $options['tag'] = sanitize_text_field($_POST['tag']);
        }
        if(isset($_POST['options'])){
            if( $_POST['options'] == 'unset' ){
                $options['options'] = array();
            }else{
                $options['options'] = array_map(function($option){
                    return array(
                        'name'  => sanitize_text_field($option['name']), 
                        'value' => sanitize_text_field($option['value'])
                    );
                }, $_POST['options']);
            }
        }

        // update the widget
        $success = hc_update_widget($widget_name, $options);

        wp_send_json(array('success' => $success));
    }

    public function corresponda_delete_widget(){
        
        $this->validate_request( $_POST, array(
            'widget_name',
        ), $is_admin = true);

        $widget_name = sanitize_text_field($_POST['widget_name']);

        if( !hc_widget_exists($widget_name) ){
            wp_send_json(array(
                'success' => false,
                'error'   => "Widget doesn't exist"
            ));
        }

        $success = hc_delete_widget($widget_name);
        wp_send_json(array(
            'success' => $success
        ));
    }
    
    public function corresponda_get_widget_preview(){
        
        $this->validate_request( $_POST, array(
            'widget_name'        
        ), $is_admin = true);

        $widget_name = sanitize_text_field($_POST['widget_name']);

        $html = do_shortcode(sprintf("[corresponda name='%s']", $widget_name));

        echo $html;
        exit();
    }
}

return new Corresponda_Ajax();