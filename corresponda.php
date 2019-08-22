<?php
/**
 * Plugin Name: Corresponda
 * Plugin URI:  https://github.com/samnockels/Corresponda
 * Description: Create widgets to display present text/links to users based on their selection from a dropdown or search box.
 * Version: 1.0.0
 * Author: Mi Promotional Sourcing
 * Author URI: https://mipromotionalsourcing.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

defined('ABSPATH') || exit;

/**
 * Main Corresponda Class
 */
final class Corresponda
{
    protected static $_instance = null;

    /**
     * Main Corresponda Instance
     *
     * Ensures only one instance of Corresponda is loaded or can be loaded.
     *
     * @return  Corresponda - Main instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Corresponda Constructor.
     */
    public function __construct()
    {
        // Include required files
        $this->includes();

        // Load admin assets
        add_action('admin_enqueue_scripts', array($this, 'load_admin_assets'));

        // Enqueue jquery 2, if the autocomplete widget is about to be loaded on the current page
        add_action('corresponda_autocomplete_widget_before_load', array($this, 'corresponda_autocomplete_dependancies'));

        // register ajax handlers
        $ajax_handler = new Corresponda_Ajax();
        add_action('wp_ajax_corresponda_create_new_widget', array($ajax_handler, 'corresponda_create_new_widget'));
        add_action('wp_ajax_corresponda_get_widget',        array($ajax_handler, 'corresponda_get_widget'));
        add_action('wp_ajax_corresponda_update_widget',     array($ajax_handler, 'corresponda_update_widget'));
        add_action('wp_ajax_corresponda_delete_widget',     array($ajax_handler, 'corresponda_delete_widget'));
        add_action('wp_ajax_corresponda_get_widget_preview',array($ajax_handler, 'corresponda_get_widget_preview'));

        // add a link on the plugin page next to the deactivate link 
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'action_links'));

        // Core
        require_once 'includes/core/corresponda-core.php';

        // Admin stuff
        if (is_admin()) {
            require_once 'includes/admin/corresponda-admin-core.php';
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes()
    {
        // parsedown
        require_once 'includes/Parsedown.php';

        // Functions
        require_once 'includes/corresponda-functions.php';

        // Ajax
        require_once 'includes/corresponda-ajax.php';
    }

    /**
     * Handles loading assets
     */
    public function load_admin_assets($hook)
    {
        if (!strstr($hook, 'corresponda')) {
            return;
        }
        // bootstrap 4 for the admin dashboard
        wp_enqueue_style( 'corresponda_bs4_style',  plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css');
        wp_enqueue_script( 'corresponda_bs4_script',  plugin_dir_url(__FILE__) . 'assets/js/bootstrap.min.js');
        
        // sweetalert
        wp_enqueue_style(  'corresponda_sweetalert_style',  plugin_dir_url(__FILE__) . 'assets/css/sweetalert.css');
        wp_enqueue_script( 'corresponda_sweetalert_script', plugin_dir_url(__FILE__) . 'assets/js/sweetalert.min.js');
        
        // jquery select 2
        wp_enqueue_style('corresponda_select_2_style',   plugin_dir_url(__FILE__) . 'assets/css/select2.min.css');
        wp_enqueue_script( 'corresponda_select_2_script',  plugin_dir_url(__FILE__) . 'assets/js/select2.full.min.js');
        
        // corresponda scripts and styles
        wp_enqueue_style(  'corresponda_admin_style',  plugin_dir_url(__FILE__) . 'assets/css/corresponda-admin-page.css');
        wp_enqueue_script( 'corresponda_admin_script', plugin_dir_url(__FILE__) . 'assets/js/corresponda-admin-page.js');
    }

    /**
     * Load the autocomplete widget dependancies (for use on user facing pages not admin screen)
     */
    public function corresponda_autocomplete_dependancies(){
        // jquery select 2
        wp_enqueue_style('corresponda_select_2_style',   plugin_dir_url(__FILE__) . 'assets/css/select2.min.css');
        wp_enqueue_script( 'corresponda_select_2_script',  plugin_dir_url(__FILE__) . 'assets/js/select2.full.min.js');
    }

    /**
     * add a link on the plugin page next to the deactivate link 
     */
    public function action_links( $links ) {
        $links[] = '<a href="'.admin_url( 'admin.php?page=corresponda' ).'">Open Corresponda</a>';
        return $links;
    }
}



if (!function_exists('corresponda')) {   
    /**
     * Returns the main instance of Corresponda to prevent the need to use globals.
     *
     * @return Corresponda
     */
    function corresponda()
    {
        return Corresponda::instance();
    }
}

corresponda();