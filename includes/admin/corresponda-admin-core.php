<?php

defined('ABSPATH') || exit;

/**
 * Handles admin stuff
 */
class Corresponda_Admin_Core
{
    public function __construct()
    {
        // register admin sidebar menu item
        add_action('admin_menu', array($this, 'menu'));
        
        // add some links next to the 'deactivate' link on the plugins screen
    }
    
    

    public function menu()
    {
        add_menu_page(
            'Corresponda',
            'Corresponda',
            'manage_options',
            'corresponda',
            function() { require_once plugin_dir_path(__FILE__) . 'corresponda-admin-main.php'; },
            'dashicons-chart-area',
            3
        );
    }
}

return new Corresponda_Admin_Core();
