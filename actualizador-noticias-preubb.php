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

require_once( '/home/lukas/Local Sites/pusi/app/public/wp-load.php' );

function actualizar_noticias(){    
    $access_token = "EAANLwGqwXh4BO6fT9NEstjcuTZBc6GXPmHvRkgvJjwz3VeTHMFJOvmPOEr80qpULhgLuA0PWKRiBdCZApzkpUMb7k59OsZBrgNICSAEGDkBGZCQc6wXElA2pVT9AR2Nczv0v11C3CIDKzrKMqhtYoQru7Lu4xCZBX2NkYcWPCY5ZABJeiapvgxpE04XyZAQZAZBIhWXfzU43sXvGJqoltkXYWxo0ZD";
    $base_url = "https://graph.facebook.com/v21.0/281060260937/albums";
    $current_year = date('Y');
    $last_year = date('Y', strtotime('-2 year'));
    $since = $last_year . '-01-01';
    $until = $current_year . '-12-31';
    $url = $base_url . '?since=' . urlencode($since) . '&until=' . urldecode($until) . '&fields=name,description,created_time,photos{id,link}&access_token=' . urlencode($access_token);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    if ($response === false) {
        echo "cURL Error: " . curl_error($ch);
    } else {
        $fb_posts = json_decode($response, true)['data']; #facebbok publicaiones
        usort($fb_posts, function ($a, $b) {
            return strtotime($a['created_time']) - strtotime($b['created_time']);
        });
    
        #por cada publicacion buscar en db si existe
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        foreach($fb_posts as $post){
            $post_title = $wpdb->esc_like($post['name']);
            echo '<h2>' . esc_html($post_title) . '</h2>';
            $query = $wpdb->prepare("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'post' AND post_status = 'publish'",$post_title);
            $result = $wpdb->get_results($query);
            if ($result){
                echo '<h2 style="color: green;">' . esc_html($result[0]->post_title) . '</h2>'; echo '<p>' . esc_html($post['photos']['data'][0]['link']) . '</p>';
            } else {
                #no exite post en la pgina, crearlo
                echo '<h2 style="color: red;">' . esc_html($post_title) . '</h2>';echo '<p>' . esc_html($post['photos']['data'][0]['link']) . '</p>';
                
                #descargar imagen de facebook
                $req_pic_link = 'https://graph.facebook.com/v21.0/' . urlencode($post['photos']['data'][0]['id']) . '/?fields=picture&access_token=' . urlencode($access_token);
                $ch = curl_init($req_pic_link);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                curl_setopt($ch, CURLOPT_HEADER, false);
                $link_res = json_decode(curl_exec($ch));
                $url_pic = $link_res->picture;
                print_r($link_res);
                $ch = curl_init($url_pic);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                curl_setopt($ch, CURLOPT_HEADER, false); 
                $image_data = curl_exec($ch);
                echo $url_pic;
                if ($image_data === false) {
                    echo 'Error downloading image: ' . curl_error($ch);
                    curl_close($ch);
                    return;
                }

                curl_close($ch);
                $image_path = tempnam(sys_get_temp_dir(), 'wp_image_'); 
                file_put_contents($image_path, $image_data);

                $file_info = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($file_info, $image_path);
                finfo_close($file_info);
                $image_name = $link_res->id . '.jpg';
                $file_array = array(
                    'name'     => $image_name, 
                    'type'     => $mime_type,    
                    'tmp_name' => $image_path,   
                    'error'    => 0,
                    'size'     => filesize($image_path),
                );
                $entrada = array(
                    'post_title'    => $post_title,     
                    'post_content'  => esc_html($post['description']) . ' <a href="' . esc_html($post['photos']['data'][0]['link']) .'">Ver más aquí.</a>',   
                    'post_status'   => 'publish',                         
                    'post_author'   => 1,                                 
                    'post_type'     => 'post'
                );

                $posted_id = agregar_entrada($entrada);
                
                $attachment_id = media_handle_sideload($file_array, $post_id);
                set_post_thumbnail($posted_id, $attachment_id);
                if (is_wp_error($attachment_id)) {
                    echo 'Error uploading image: ' . $attachment_id->get_error_message();
                } else {
                    $image_url = wp_get_attachment_url($attachment_id);
                    echo 'Image uploaded successfully, URL: ' . $image_url;
                }
                if (file_exists($image_path)) {
                    unlink($image_path); 
                }
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
    AND post_status IN ('publish', 'private')
    ORDER BY post_date DESC
    LIMIT 20
    ";
    $results = $wpdb->get_results($query);

    if ($results) {
        foreach ($results as $post) {
            echo '<h2>' . esc_html($post->post_title) . '</h2>';
            echo '<p>' . esc_html($post->post_content) . '</p>';
        }
    } else {
        echo 'No posts found.';
    }
}

function agregar_entrada($entrada){

    $post_id = wp_insert_post($entrada);
    if(is_wp_error($post_id)){
        echo 'error al agregar entrada';
    }else{
        echo 'todo muito bem';
    }
    return $post_id;
}
#todo;

#seccion en admin
#boton para parar
#field para modificar accesstoken
#boton para verificar que accesstoken funciona
#boton para actualizar noticias

#refactorizar :v
?>

