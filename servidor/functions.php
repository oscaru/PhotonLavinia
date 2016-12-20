<?php

function createPath($path) {
    if (is_dir($path))
        return;
    mkdir($path, 0775, true);
}


function extractSizes($sizePattern,$imageUrl) {
    if(strpos($imageUrl, $sizePattern) === FALSE) return array(0,0);
    $partes = explode($sizePattern, $imageUrl);
    
    $sizes = explode('/',array_shift(explode('?', $partes[1])));
    list($width, $heigth) = explode('x', trim($sizes[0],'/'));
    return array( $width, $heigth);
}

function origImageUrl($patternSize,$request_uri,$query_string){
    
    if(strpos($request_uri, $patternSize) === FALSE){
        $origPath =  explode('/', trim($request_uri,'/'));
        $filename = array_pop( $origPath);
    }else {
        $partes = explode($patternSize,$request_uri );
        $origPath =  explode('/', trim($partes[0],'/'));
        $filename = array_pop( explode('/', trim($partes[1],'/')));
    }
    //filename 
    $filename = trim(array_shift(explode('?',$filename)),'/');

   
    
    
    $protocol = (strpos($origPath[0], 'http')!== FALSE)? array_shift($origPath) : 'http';
    $imageUrl =  "{$protocol}://".implode('/',$origPath)."/{$filename}";
    
    if(!empty($query_string)) $imageUrl .= "?{$query_string}";
    
    return $imageUrl;
}

function imageRelativePath($request_uri){
    $path = trim(array_shift( explode('?',$request_uri)),'/');
    $parsed = pathinfo($path);
   
    return array($parsed['dirname'],$parsed['filename'], $parsed['extension']);
    
}

function allowedSizes() {
    $allowed = array(
        array(50, 50),
        array(320, 320),
        array(640 , 480),
        array(960, 640),
        array(1024, 768),
        array(1920, 1200)
    );
    return $allowed;
}

function isAllowedSize($width,$height){
    if($width==0 && $height == 0) return true;
    foreach(allowedSizes() as $size){
        if($size[0] == $width && $size[1]== $height) return true;
    }
    return false;
}


