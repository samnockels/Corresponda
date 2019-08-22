<?php
/**
 * Uninstall Script
 * 
 * This scrips is called when the plugin is uninstalled (not deactivated). 
 * We make sure to clean the db up.
 */

// Stop someone from just navigating to this 
// file and deleting everything!

if( !defined('WP_UNINSTALL_PLUGIN') ){
    // not so fast...
    exit;
}
if( !defined('ABSPATH') ){
    exit;
}

//
// Now it is safe to go ahead and delete all widgets
//

// fetch registered widgets
$registered_widgets = get_option('corresponda_registered_widgets');

// if no widgets, then we are done here
if( !$registered_widgets ){
    exit;
}

// otherwise, we will remove all widgets that the user has created
foreach( $registered_widgets as $widget_name ){
    hc_delete_widget($widget_name);
}

// finally delete the list of registered widgets
delete_option('corresponda_registered_widgets');

// done.