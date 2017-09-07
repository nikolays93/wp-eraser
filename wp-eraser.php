<?php

/*
Plugin Name: Ластик
Plugin URI:
Description: Отчистка Wordpress
Version: 0.2
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) )
    exit; // disable direct access

if( ! is_admin() )
    return;

if( ! function_exists('wp_is_ajax') ) {
    function wp_is_ajax() {
        return (defined('DOING_AJAX') && DOING_AJAX);
    }
}

define('ERASER_DIR', __DIR__);

function initialize_eraser(){
    $dir = rtrim( plugin_dir_path( __FILE__ ), '/' );

    require_once $dir . '/include/class-wp-eraser.php';
    require_once $dir . '/include/class-wp-admin-page.php';

    require_once $dir . '/include/admin-page.php';
}
add_action('plugins_loaded', 'initialize_eraser', 10);
add_action('wp_loaded', array('WP_Eraser', 'init'), 10);
