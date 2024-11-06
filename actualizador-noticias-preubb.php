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
die("no direct access");
}

add_filter('cron_schedules', 'custom_cron_schedule');

function custom_cron_schedule($schedules) {
    $schedules['five_seconds'] = array(
        'interval' => 5,
        'display' => __('Every 5 Seconds'),
    );
    return $schedules;
}

add_action('wp', 'programar_siguiente_checkeo');

function programar_siguiente_checkeo() {
    if (!wp_next_scheduled('checkeo_hook')) {
        wp_schedule_event(time(), 'everyhour', 'checkeo_hook');
    }
}

add_action('checkeo_hook', 'checkeo');
function checkeo() {
    error_log('poi'); 
}

register_deactivation_hook(__FILE__, 'desactivar_checkeo');
function desactivar_checkeo() {
    $timestamp = wp_next_scheduled('checkeo_hook');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'checkeo_hook');
    }
}

function actualizar_noticias(){
    $base_url = "https://graph.facebook.com/v21.0/281060260937/feed";
    $url = $base_url . '?access_token=' . urlencode($access_token);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    
}


