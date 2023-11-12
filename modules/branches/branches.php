<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Branches
Description: Default module to apply changes to the menus
Version: 2.3.0
Requires at least: 2.3.*
*/

define('BRANCHES_MODULE_NAME', 'branches');

$CI = &get_instance();

hooks()->add_action('admin_init', 'branches_init_menu_items');


/**
 * Load the module helper
 */
$CI->load->helper(BRANCHES_MODULE_NAME . '/branches');

/**
 * Register activation module hook
 */
register_activation_hook(BRANCHES_MODULE_NAME, 'branches_activation_hook');

function branches_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(BRANCHES_MODULE_NAME, [BRANCHES_MODULE_NAME]);

/**
 * Init menu setup module menu items in setup in admin_init hook
 * @return null
 */
function branches_init_menu_items()
{
    /**
     * If the logged in user is administrator, add custom menu in Setup
     */
    if (is_admin()) {
        $CI = &get_instance();
        $CI->app_menu->add_sidebar_menu_item('menu-branches', [
            'slug'     => 'settings-menu-options',
            'name'     => _l('branches'),
            'href'     => admin_url('branches'),
            'position' => 5,
            'icon' => 'fa fa-building-o',
        ]);
    }
}
