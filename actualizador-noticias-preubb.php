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


 function actualizar_noticias(){    
     $access_token = "EAANLwGqwXh4BO4XVX5MZBKhUnpuI3blGm3ZCin6O28wbNM5CcNt641ar6ZCldgNj3ZAyc7ZBdrivwnzZCNfhzR6RIfpmMQWV2RPVo7zFZAktZBAysuZAeepkEMI8nZBG4rlOb14SePuV1X2nzyWU0bjZA109uhXC2c1o3YwWbdry1HO8ZClrcEMEMSL0JrhFhcZABRW79u5OfLTAsH5UqHhm4pFmHewZDZD";
     $base_url = "https://graph.facebook.com/v21.0/281060260937/albums";
     $current_year = date('Y');
     $last_year = date('Y', strtotime('-1 year'));
     $since = $last_year . '-01-01';
     $until = $current_year . '-12-31';
     $url = $base_url . '?since=' . urlencode($since) . '&until=' . urldecode($until) . '&fields=name,created_time&access_token=' . urlencode($access_token);
     $ch = curl_init($url);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
     curl_setopt($ch, CURLOPT_HEADER, false);
 
     $response = curl_exec($ch);
     if ($response === false) {
         echo "cURL Error: " . curl_error($ch);
     } else {
         #echo $response;
         $fb_posts = json_decode($response, true)['data']; #facebbok publicaiones
         print_r($fb_posts);
 
         usort($fb_posts['data'], function ($a, $b) {
             return strtotime($b['created_time']) - strtotime($a['created_time']);
         });
     
         #por cada publicacion buscar en db si existe
         global $wpdb;
         require_once('/home/lukas/Local Sites/pusi/app/public/wp-load.php');
         foreach($fb_posts as $post){
             $post_title = $wpdb->esc_like($post['name']);
             echo '<h2>' . esc_html($post_title) . '</h2>';
             $query = $wpdb->prepare(
                 "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'post' AND post_status = 'publish'",
                 $post_title);
 
             $result = $wpdb->get_results($query);
 
             if ($result){
                 echo '<h2>' . esc_html($result[0]->post_title) . '</h2>';
             } else {
                 echo 'No postsresult';
             }
         }
         
     }
 }
 actualizar_noticias();
 
 
 # conseguir titulos de las entradas
 
 function conseguir_posts_pagina(){
    
     require_once('/home/lukas/Local Sites/pusi/app/public/wp-load.php');
     global $wpdb;
     $query = "
     SELECT ID, post_title, post_content 
     FROM {$wpdb->posts}
     WHERE post_type = 'post'
     ORDER BY post_date DESC
     LIMIT 20
     ";
     $results = $wpdb->get_results($query);
 
     if ($results) {
         foreach ($results as $post) {
             echo '<h2>' . esc_html($post->post_title) . '</h2>';
         }
     } else {
         echo 'No posts found.';
     }
 }
 
 conseguir_posts_pagina();
 ?>