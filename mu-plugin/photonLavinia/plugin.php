<?php

define ('PhotonLavinia_fronturl', "http://{$_SERVER['HTTP_HOST']}/wp-content/mu-plugins/photonLavinia");

require __DIR__.'/PhotonLavinia.php';
add_filter( 'final_output', 'activePhoton' );

function activePhoton( $content ) {
   $content = photonLavinia\PhotonLavinia::add_script($content);
   $content = photonLavinia\PhotonLavinia::add_image_placeholders($content);
   return $content;
}

