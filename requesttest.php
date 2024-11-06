<?php


actualizar_noticias();

function actualizar_noticias(){    
    $access_token = "";
    $base_url = "https://graph.facebook.com/v21.0/281060260937/albums";
    $url = $base_url . '?since=2023-01-01&until=2025-01-01&fields=id,name,created_time&access_token=' . urlencode($access_token);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    if ($response === false) {
        echo "cURL Error: " . curl_error($ch);
    } else {
        echo $response;
    }
}

