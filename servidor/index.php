<?php

/**
 * Espera recibir una imagen con el formato:
 * 
 * http://este.server/http/dominio_original/path/-lsize-200x200/imagen.jpg
 *  http://photonserver/http/osl.ugr.es/CTAN/macros/latex/contrib/incgraph/-lsize-320x320/example.jpg
 * REQUEST_URI
 * QUERY_STRING
 */

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/functions.php';

$serverDir = 'pictures';
$imagesDir = __DIR__;
$sizePattern  = '_lsize_';

$request_uri = (!empty($serverDir))? trim(str_replace("{$serverDir}", '', $_SERVER['REQUEST_URI']) ,'/') : $_SERVER['REQUEST_URI'];

list($pathImage,$filename,$extension) = imageRelativePath($request_uri); 
$pathImage = "{$imagesDir}/{$pathImage}";

$urlImage = origImageUrl($sizePattern,$request_uri,$_SERVER['QUERY_STRING']);
list($width, $height) = extractSizes($sizePattern,$_SERVER['REQUEST_URI']);
if(!isAllowedSize($width, $height)) die('not allowed');



$file_contents = file_get_contents($urlImage);
$handle = new upload('data:'.$file_contents);
$handle->file_src_name_ext = $extension;



unset($file_contents);

if ($handle->uploaded) {
    if($width){
        $handle->image_resize          = true;
        $handle->image_ratio           = true;
        $handle->image_y               = ($handle->image_src_y > $height)? $height : $handle->image_src_x;;
        $handle->image_x               = ($handle->image_src_x > $width)? $width : $handle->image_src_x;
        $handle->file_overwrite        = TRUE;
        
        $handle->file_dst_name_ext     = $extension;
        $handle->image_convert         = $extension;

        $pathImageSize= "{$pathImage}/{$sizePattern}{$width}x{$height}";
    
    }else {
        $pathImageSize = $pathImage;
    }
    
    $handle->file_new_name_body = "{$filename}";
    $handle->Process($pathImage);
    $handle->Clean();

}




switch(  strtolower($extension) ) {
    case "gif": $ctype="image/gif"; break;
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpeg"; break;
    default:
}

header('Content-type: ' . $ctype);
echo file_get_contents("{$pathImage}/{$filename}.{$extension}");