<?php
/**
 * 
 * Helper functions, and Widget renderer funcitons
 * 
 */

defined('ABSPATH') || exit;

// ==============================================
//  Helper Functions
// ==============================================

if( !function_exists('hc_widgets') ){
    /**
     * Get valid widget tags and titles
     * 
     * @return Array
     */
    function hc_widgets(){
        return apply_filters('hc_widgets', array(
            'hc_dropdown_with_text' => array(
                "tag"      => "hc_dropdown_with_text",
                "title"    => "Dropdown with text",
                "renderer" => 'hc_dropdown_with_text_renderer'
            ),
            'hc_autocomplete_with_text' => array(
                "tag"   => "hc_autocomplete_with_text",
                "title" => "Autocomplete Search with text",
                "renderer" => 'hc_autocomplete_with_text_renderer'
            ),
        ));
    }
}

if( !function_exists('hc_get_valid_widget_tags') ){
    /**
     * Get valid widget tags
     *
     * @return Array of string
     */
    function hc_get_valid_widget_tags(){
        return apply_filters('hc_get_valid_widget_tags', array_map(function($widget){
            return $widget['tag'];
        }, hc_widgets()));
    }
}

if( !function_exists('hc_get_all_widgets') ){
    /**
     * Get all registered widgets
     *
     * @return Array
     */
    function hc_get_all_widgets(){
        $widget_names = get_option('corresponda_registered_widgets');
        if( !$widget_names ){
            return array();
        }
        $widgets = array();
        foreach($widget_names as $name){
            $widget = get_option('corresponda_widget_'.$name);
            if( $widget ){
                $widgets[] = $widget;
            }
        }
        return $widgets;
    }
}

if( !function_exists('hc_widget_exists') ){
    /**
     * Check whether a widget exists 
     *
     * @return Boolean
     */
    function hc_widget_exists( $widget_name ){
        $widget_names = get_option('corresponda_registered_widgets');
        if( !$widget_names || !is_array($widget_names)){
            return false;
        }
        return in_array($widget_name, $widget_names);
    }
}


if( !function_exists('hc_create_widget') ){
    /**
     * Create a new widget
     *
     * @param String $widget_name
     * @param String $widget_tag
     * @return Array|False 
     */
    function hc_create_widget( $widget_name, $widget_tag ){

        // get registered widgets
        $widgets = get_option('corresponda_registered_widgets');
        if( !$widgets ){
            $widgets = array();
        }

        // strip out whitespace from name, make lowercase, and sanitize
        $widget_name = sanitize_text_field(strtolower(preg_replace('/\s+/', '', $widget_name)));
        $widget_tag  = sanitize_text_field($widget_tag);

        // check widget doesn't already exist
        if( hc_widget_exists($widget_name) ){
            return false;
        }

        // add widget name to lookup list and update
        $widgets[] = $widget_name;
        update_option('corresponda_registered_widgets', $widgets);

        // create the widget
        add_option('corresponda_widget_'.$widget_name, array(
            'name'  => $widget_name,
            'title' => '',
            'tag'   => $widget_tag,
            'options' => array()
        ));

        return get_option('corresponda_widget_'.$widget_name);
    }
}

if( !function_exists('hc_get_widget') ){
    /**
     * Get a widget by name
     *
     * @param String $widget_name
     * @return Array|False 
     */
    function hc_get_widget( $widget_name ){

        $widget_name = sanitize_text_field($widget_name);
        
        $widget_names = get_option('corresponda_registered_widgets');
        if( !$widget_names ){
            return false;
        }
        if( !in_array($widget_name, $widget_names) ){
            return false;
        }
        $widget = get_option('corresponda_widget_'.$widget_name);
        if( !$widget ){
            return false;
        }
        return $widget;
    }
}

if( !function_exists('hc_update_widget') ){
    /**
     * Update a widget
     *
     * @param Array options to change:
     *              String title 
     *              Array  options
     * @return Array|False 
     */
    function hc_update_widget( $widget_name, $options_to_change ){

        $widget_name = sanitize_text_field($widget_name);

        $widget = hc_get_widget( $widget_name );
        if( !$widget ){
            return false;
        }
        if( !isset($options_to_change['title']) && !isset($options_to_change['tag']) && !isset($options_to_change['options']) ){
            return false;
        }
        
        // make changes to widget
        if( isset($options_to_change['title']) && is_string($options_to_change['title']) ){
            $widget['title'] = sanitize_text_field($options_to_change['title']);
        }
        if( isset($options_to_change['tag']) && is_string($options_to_change['tag']) ){
            $widget['tag'] = sanitize_text_field($options_to_change['tag']);
        }
        if( isset($options_to_change['options']) && is_array($options_to_change['options']) ){
            $widget['options'] = $options_to_change['options'];
        }

        // save
        update_option('corresponda_widget_'.$widget_name, $widget);

        return true;
    }
}

if( !function_exists('hc_delete_widget') ){
    /**
     * Delete a widget by name
     *
     * @param String $widget_to_delete - name of the widget to delete
     * @return Boolean
     */
    function hc_delete_widget( $widget_to_delete ){

        $widget_to_delete = sanitize_text_field($widget_to_delete);

        $widget_names = get_option('corresponda_registered_widgets');
        if( !$widget_names ){
            return false;
        }
        if( !in_array($widget_to_delete, $widget_names) ){
            return false;
        }

        // widget exists, now delete
        $filtered_widgets = array_filter($widget_names, function($widget) use($widget_to_delete){
            // keep widgets that are not the widget to delete
            return $widget != $widget_to_delete;
        });

        update_option('corresponda_registered_widgets', $filtered_widgets); // delete the lookup entry
        delete_option('corresponda_widget_'.$widget_to_delete); // delete the actual widget
        return true;
    }
}

// ==============================================
//  Widget Renderer Functions
// ==============================================

if( !function_exists('hc_dropdown_with_text_renderer') ){
    /** 
     * Dropdown with text widget renderer
     *
     * Renders widget
     * @param Array widget - the widget to render
     * @return String html
     */
    function hc_dropdown_with_text_renderer( $widget, $input_name = '' ){

        $title   = $widget['title'];
        $options = $widget['options'];
        
        // render options
        if( $options ){
            $options = array_map(function( $option ){
                $parsedown = new Parsedown();
                return sprintf("
                    <option class='hc_option' data-value='%s'> %s </option>
                    ", 
                    __( $parsedown->text( $option["value"] ), 'corresponda' ), 
                    __( $option["name"], 'corresponda' )
                );
            }, $options);
            $options = join('', $options);
        }else{
            $options = "";
        }

        // render required javascript
        $scripts = sprintf('
            <script>
                jQuery(document).ready(function() {
                    jQuery("#hc_select_corresponding_text").hide();
                    jQuery("#hc_select").change(function(){
                        let text = jQuery(this).find(":selected").data("value");
                        jQuery("#hc_selected_text").html(text);
                        jQuery("#hc_select_corresponding_text").show();
                    });
                });
            </script>  
        ');

        // return widget
        return sprintf('
            <select id="hc_select" style="width:100%%; height: 30px;" name="%s">
                <option selected disabled>%s</option>
                %s
            </select>
            <div id="hc_select_corresponding_text" style="width:100%%; margin-top: 50px;" class="alert alert-info">
                <div id="hc_selected_text" style="margin:0; padding:0; overflow-wrap: break-word;"></div>
            </div>
            %s',
            __( $input_name, 'corresponda' ),
            __( $title ?: 'Please select an option.', 'corresponda' ),
            $options,
            $scripts
        );
    }
}

if( !function_exists('hc_autocomplete_with_text_renderer') ){
    /** 
     * Dropdown with text widget renderer
     *
     * Renders widget
     * @param Array widget - the widget to render
     * @return String html
     */
    function hc_autocomplete_with_text_renderer( $widget, $input_name = '' ){

        do_action('corresponda_autocomplete_widget_before_load');

        $title   = $widget['title'];
        $options = $widget['options'];
        
        // render options 
        if( $options ){
            $options = join(" ", array_map(function( $option ){
                // return $option;
                $parsedown = new Parsedown();
                return sprintf("
                    <option class='hc_option' data-value='%s'> %s </option>
                    ", 
                    __( $parsedown->text( $option["value"] ), 'corresponda' ), 
                    __( $option["name"], 'corresponda' )
                );
            }, $options));
        }else{
            $options = "";
        }

        // render required javascript
        $scripts = sprintf('
            <script>
                jQuery(document).ready(function() {
                    jQuery("#hc_autocomplete").select2({width:"100%%"});
                    jQuery("#hc_autocomplete_corresponding_text").hide();
                    jQuery("#hc_autocomplete").on("select2:select", function (e) {
                        let corresponding_text = e.params.data.element.dataset.value;
                        jQuery("#hc_autocomplete_selected_text").html(corresponding_text);
                        jQuery("#hc_autocomplete_corresponding_text").show();
                    });
                });
            </script>  
        ');

        // return widget
        return sprintf('
            <select id="hc_autocomplete" name="%s">
                <option selected disabled>%s</option>
                %s
            </select>
            <div id="hc_autocomplete_corresponding_text" class="alert alert-info" style="margin-top:50px;">
                <div id="hc_autocomplete_selected_text" style="margin:0; padding:0;  overflow-wrap: break-word;"></div>
            </div>
            %s',
            __( $input_name, 'corresponda' ),
            __( $title ?: 'Please select an option.', 'corresponda' ),
            $options,
            $scripts
        );
    }
}