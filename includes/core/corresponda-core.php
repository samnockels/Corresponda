<?php

defined('ABSPATH') || exit;

/**
 * Core stuff
 */
class Corresponda_Core
{
    public function __construct()
    {
        // Register the Corresponda shortcode
        require_once 'corresponda-register-shortcode.php';
    }
}
return new Corresponda_Core();
