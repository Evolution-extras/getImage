//<?php
/**
 * getImage
 *
 * Получить изображение (адрес) для ресурса из tv параметров, контента (или другого поля)
 *
 * @category  snippet
 * @version       2.6
 * @license  http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal @properties
 * @internal @modx_category Utils
 * @internal @installset base, sample
 * @documentation assets/snippets/getImage/README.md
 * @documentation https://github.com/Evolution-extras/getImage/blob/master/assets/snippets/getImage/README.md
 * @reportissues https://github.com/Evolution-extras/getImage/
 * @author  Sergey Davydov <webmaster@sdcollection.com>
 * @lastupdate    01.08.2016
 */

if (file_exists($includeFile = $modx->config['base_path']."assets/snippets/getImage/getImage.php")) {
	include_once($includeFile);
	if (class_exists("getImage")) {
		$getImage = new getImage(array(
			"id" => isset($id) ? $id : $modx->documentObject['id'],
			"field" => isset($field) ? $field : "content",
			"urlOnly" => isset($urlOnly) ? $urlOnly : true,
			"tv" => isset($tv) ? $tv : false,
			"data" => isset($data) ? $data : "",
			"parseData" => isset($parseData) ? $parseData : false,
			"parseTv" => isset($parseTv) ? $parseTv : false,
			"order" => isset($order) ? $order : "",
			"rand" => isset($rand) ? $rand : "",
			"all" => isset($all) ? $all : "",
			"out" => isset($out) ? $out : "%s",
			"fullUrl" => isset($fullUrl) ? $fullUrl : false,
			"runSnippet" => isset($runSnippet)?$runSnippet : false)
		);
		if (empty($save)) return $getImage->result();	else $modx->setPlaceholder($save, $getImage->result());
	} else {
		return "Required class 'getImage' don't exists";
	}
} else {
	return "Required file $includeFile don't exists";
}
