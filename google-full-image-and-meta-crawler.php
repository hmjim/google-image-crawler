<?php
header('Content-Type: text/html; charset=utf-8');
ini_set('max_execution_time', 0);
ini_set('display_errors',1);
ini_set('error_reporting',2047);
/*
Template Name: crawler
*/
// class SimpleImage {
function imageResize($src, $dst, $width, $height, $crop=0){
 
    if(!($info = @getimagesize($src)))
        return false;
 
    $w = $info[0];
    $h = $info[1];
    $type = substr($info['mime'], 6);
 
    $func = 'imagecreatefrom' . $type;
 
    if(!function_exists($func))
        return false;
    $img = $func($src);
 
    if($crop) // изменение размера (непропорциональное)
    {
        if($w < $width || $h < $height)
            return false; //еще меньше
        $ratio = max($width/$w, $height/$h);
        $h = $height / $ratio;
        $x = ($w - $width / $ratio) / 2;
        $w = $width / $ratio;
    }
    else // пропорциональное
    {
        if($w < $width && $h < $height)
            return false; // еще меньше
        $ratio = min($width/$w, $height/$h);
        $width = $w * $ratio;
        $height = $h * $ratio;
        $x = 0;
    }
 
    $new = imagecreatetruecolor($width, $height);
    // прозрачность
    if($type == 'gif' || $type == 'png')
    {
        imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
        imagealphablending($new, false);
        imagesavealpha($new, true);
    }
    imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);
 
    $save = 'image' . $type;
 
    return $save($new, $dst);
}
$fp = file_get_contents(RC_TC_PLUGIN_URL.'views/links.txt');
$link_key = preg_split('/\n|\r\n?/', $fp);

foreach($link_key as $key => $val){

	$name = $val;
	$source = array(
	  'post_title' => $name,      
	  'post_status' => 'publish',                     
	  'post_author' => 1,                              
	  'post_type' => 'movie',                          
	  'tags_input' => $name,  
	);
	$post_id = wp_insert_post($source);	
	$name = '';
	$dsa = str_replace(" ", "+", $val);
  $location = 'https://www.google.ru/search?q='.$dsa.'&gs_l=300&newwindow=1&client=safari&rls=en&source=lnms&tbm=isch&sa=X&tbs=isz:lt&ved=0ahUKEwiawYPdvJzUAhVC6CwKHd8CAQoQ_AUICigB&biw=1920&bih=1019#q='.$dsa.'&newwindow=1&tbm=isch&tbs=isz:lt,islt:4mp';
  
 	print '<pre>';
	var_dump("Ссылка: ".$location);
	print '</pre>';	
  $ch = curl_init($location);

  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.0; rv:20.0) Gecko/20100101 Firefox/20.0');
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $data = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
	$content_data = $data;
	$regex = '|<div class="rg_meta notranslate">(.*?)</div>|is';
	preg_match_all($regex, $content_data, $out);

$lucky = 0;
	foreach($out[1] as $key => $element) { 
		if(!empty($element)){
			$json_polychen_bleat = json_decode(trim($element));
			$hhh = get_headers($json_polychen_bleat->ou);
			$size = getimagesize ($json_polychen_bleat->ou);
				if($hhh[0] == 'HTTP/1.1 200 OK' && $size[0] >= '1920' && $size[0] <= '3840' && $size[1] >= '1080' && $size[1] <= '2160'){
				$to = '/var/www/html/images/'.$dsa.$key.'.jpg';
				file_put_contents($to, file_get_contents($json_polychen_bleat->ou));
				imageResize($json_polychen_bleat->ou,'/var/www/html/images/thumb_'.$dsa.$key.'.jpg', '1100','768' );
				add_post_meta($post_id, 'link', get_site_url().'/images/'.$dsa.$key.'.jpg');	
				add_post_meta($post_id, 'thumb', get_site_url().'/images/thumb_'.$dsa.$key.'.jpg');	
				add_post_meta($post_id, 'size', $size[0].'x'.$size[1]);
				add_post_meta($post_id, 'alt', $json_polychen_bleat->s);
				print '<pre>';
				echo 'Ключ: '.$key;
				var_dump('Ответ: '.$hhh[0]);
				var_dump('Мета: '.$element);
				var_dump('Размер: '.$size[0].'x'.$size[1]);
				var_dump('Альт: '.$json_polychen_bleat->s);
				echo "<img src='".get_site_url().'/images/'.$dsa.$key.'.jpg'."'>";
				echo "<img src='".get_site_url().'/images/thumb_'.$dsa.$key.'.jpg'."'>";
				print '</pre>';				
				$lucky++;
				if($lucky == 30){
					break;
				}
			}		
		} 
		
	}
	$dsa = '';
}
?>