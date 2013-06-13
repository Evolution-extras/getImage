//<?php
/**
 * getImage
 * 
 * Получить изображение из содержимого для указанного ID документа
 *
 * @category 	snippet
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties
 * @internal	@modx_category Utils
 * @internal   @installset base, sample
 */

/* Description:
 *  
 * 
 * Author: 
 *  Sergey Davydov <webmaster@collection.com.ua> for MODx CMF
*/ 

$id = isset($id)?$id:$modx->documentObject['id'];
$field = isset($field)?$field:"content"; // поле из которого парсить
$urlOnly = isset($urlOnly)?$urlOnly:true; // Только путь
$save = isset($save)?$save:false;
$data = "";

 if ( $id == $modx->documentObject['id']) $data = $modx->documentObject[$field];
 else  {
  $data = $modx->getDocument($id,$field);
  if (is_array($data)) $data = reset($data);
 }
 $result = "";
 if (($urlOnly and preg_match("/<img[^>]+src=[\"']([^\"']+)[\"']/i",$data,$m)) 
   or  (!$urlOnly and preg_match("/(<img[^>]+>)/i",$data,$m)))
  $result = $m[1];
 else $result = "";

if (!$save) return $result;
else $modx->setPlaceholder($save, $result);
?>