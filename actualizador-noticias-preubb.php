<?php
/**
 * @package ActualizadorNoticiasPreubb
 */

/*
 Plugin Name: Actualizador Noticias Preubb
 Plugin URI: https://github.com/Lukakao/actualizador-noticias-preubb
 Description: Sube las publicaciones de la pagina de facebook del preuniversitario 
 Version: 1.0.0
 Author: Lukas Sanhueza Solar
 Author URI: https://github.com/Lukakao
 License: GPLv2 or later
 Text Domain: actualizador-noticias-preubb
 License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

 if (!defined('ABSPATH')) {
    die("GUgu gaga no direct access chupalo");
}

add_filter('cron_schedules', 'custom_cron_schedule');

function custom_cron_schedule($schedules) {
    $schedules['one_minute'] = array(
    'interval' => 60,
    'display' => __('Every 1 Minute'),
);
}

add_action('wp', 'programar_siguiente_checkeo');

function programar_siguiente_checkeo() {
    if (!wp_next_scheduled('checkeo_hook')) {
        wp_schedule_event(time(), 'five_seconds', 'checkeo_hook');
    }
}

function checkeo() {
    error_log('poi'); 
}

add_action('checkeo_hook', 'checkeo');

register_deactivation_hook(__FILE__, 'desactivar_checkeo');

function desactivar_checkeo() {
    $timestamp = wp_next_scheduled('checkeo_hook');
    wp_unschedule_event($timestamp, 'checkeo_hook');
    
}


