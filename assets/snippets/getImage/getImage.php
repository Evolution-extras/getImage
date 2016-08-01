<?php
if (!defined('MODX_BASE_PATH')) die('What are you doing? Get out of here!'); 
/*
 * Main class for use in snippet getImage 
 * @version       2.6
 * @documentation README.md
 * @documentation https://github.com/Evolution-extras/getImage/blob/master/assets/snippets/getImage/README.md
 * @repository    https://github.com/Evolution-extras/getImage/
 * @author        Sergey Davydov <webmaster@sdcollection.com>
 * @lastupdate    01.08.2016
 */

class getImage {
	var $p;
	var $result = array();

	function __construct($p = array()) {
		global $modx;
		$pd = array(
			"id" => $modx->documentObject['id'],
			"field" => "content",
			"urlOnly" => true,
			"tv" => false,
			"data" => "",
			"parseData" => false,
			"parseTv" => false,
			"order" => explode(",", "tv,document,data"),
			"rand" => false,
			"all" => false,
			"out" => "%s",
			"fullUrl" => isset($fullUrl) ? $fullUrl : false,
			"runSnippet" => false
		);
		foreach ($pd as $k => $v) if (!isset($p[$k])) $p[$k] = $v;
		$this->p =& $p;
		if (!is_array($p["order"])) {
			$p["order"] = preg_split("/\s*,\s*/", $p["order"], -1, PREG_SPLIT_NO_EMPTY);
			foreach ($pd["order"] as $v) if (!in_array($v, $p["order"])) $p["order"][] = $v; // add default
		}
		if ($p["rand"]) $p["all"] = true;
		$this->isCurrent = $p["id"] === $modx->documentObject['id'];
		foreach ($p["order"] as $o) {
			switch ($o) {
				case "tv":
					if (strpos($p["tv"], "=")) $this->extendTv = true;
					$p["tv"] = preg_split("/\s*,\s*/", $p["tv"], -1, PREG_SPLIT_NO_EMPTY);
					if ($this->extendTv) {
						$this->extendTv = array();
						foreach ($p["tv"] as $t) {
							$t = preg_split("/\s*=\s*/", $t, 2, PREG_SPLIT_NO_EMPTY);
							$this->extendTv[$t[0]] = !empty($t[1]) ? preg_split("/\s*;\s*/", $t[1], -1, PREG_SPLIT_NO_EMPTY) : "";
						}
						$p["tv"] = array_keys($this->extendTv);
					}
					$tvs = array(); // name => value
					if (!$this->isCurrent) foreach ((array)$modx->getTemplateVars($p["tv"], "name", $p["id"]) as $d) $tvs[$d["name"]] = $d["value"];
					foreach ($p["tv"] as $tvField) {
						if ($this->isCurrent) $tvs[$tvField] = $modx->documentObject[$tvField][1];
						if ($data = $this->exTv($tvField, $tvs[$tvField])) {
							if ($data = ($p["parseTv"] === true or $p["parseTv"] === 1 or in_array($tvField, explode(",", $p["parseTv"]))) ? $this->parseData($data) : $data) {
								if (is_array($data)) $this->result = array_merge($this->result, $data);
								else $this->result[] = $data;
							}
							if (!$p["all"]) break;
						}
					}
					if ($this->result and !$p["all"]) break 2; else break 1;
				case "document":
					if ($p["field"]) {
						if ($this->isCurrent) {
							$data = $modx->documentObject[$p["field"]];
						} else {
							$data = $modx->getDocument($p["id"], $p["field"]);
							if (is_array($data)) $data = reset($data);
						}
						if ($data = $this->parseData($data)) $this->result[] = $data;
					}
					if ($this->result and !$p["all"]) break 2; else break 1;
				case "data":
					if (!empty($p["data"]) and ($data = $p["parseData"] ? $this->parseData($p["data"]) : $p["data"])) {
						$this->result[] = $data;
					}
					if ($this->result and !$p["all"]) break 2; else break 1;
				default:       // default actions
			}
		}
	}

	function result() {
		global $modx;
		$result = '';
		if ($this->p["fullUrl"] and $this->p["urlOnly"]) {
			foreach ($this->result as &$data)
				if (!preg_match("/^(http|ftp|data):/i", trim($data)))
					$data = "http://".$_SERVER['SERVER_NAME']."/".preg_replace("/^\//", "", trim($data));
		}
		if (!$this->p["rand"] or count($this->result) <= 1) $result = reset($this->result);
		else if ($this->p["rand"]) $result = $this->result[rand(0, count($this->result) - 1)];
		else if ($this->p["all"])
			$result = implode($this->p["urlOnly"] ? "" : ",", $this->result);
		if ($result && $this->p["runSnippet"]) $result = $modx->evalSnippets("[[".sprintf($this->p["runSnippet"],$result)."]]");
		return $result ? str_replace("%s", $result, $this->p["out"]) : "";
	}

	function parseData($data = "") {
		$p =& $this->p;
		if (($p["urlOnly"] and preg_match("/<img[^>]+src=[\"']([^\"']+)[\"']/i", $data, $m))
			   or (!$p["urlOnly"] and preg_match("/(<img[^>]+>)/i", $data, $m)))
			return $m[1];
		else return "";
	}

	function exTv($tvName, $data) { // получение изображения из формата json - multiphoto
		if (is_array($trJson = @json_decode($data))) { // проверка на пустой массив 
			if (!count($trJson)) return "";
			$o =& $this->extendTv[$tvName];
			$o = !empty($o) ? $o : array($this->p["all"] ? "all" : "0", "0");
			if (!is_array($o)) $o = preg_split("/\s*;\s*/", $o);
			if (!isset($o[1])) $o[1] = 0;
			if (substr(strtolower($o[0]), 0, 4) == "rand") {
				if (preg_match("/^rand:(\d+)=(.*)$/i", $o[0], $m)) { // Условие для случайно выборки. Проверка по номеру элемента n=Значение(регулярное выражение)
					$nA = array();
					if (preg_match("/^\/.*\/\w*$/", $m[2])) { // check by regexp
						foreach ($trJson as $k => $a) if (preg_match($m[2], $a[$m[1]])) $nA[] = $trJson[$k];
					} else {
						foreach ($trJson as $k => $a) if ($a[$m[1]] == $m[2]) $nA[] = $trJson[$k];
					}
					if ($nA) { $trJson = $nA; unset($nA); }
					$o[0] = rand(0, count($trJson) - 1);
				}
				$o[0] = rand(0, count($trJson) - 1);
			} else
				if (strtolower($o[0]) == "all") {
				$collect = array();
				foreach ($trJson as &$v) $collect[] = $v[$o[1]];
				return $collect;
			} else
				$o[0] = (int)$o[0];
			return $trJson[$o[0]][$o[1]];
		}
		return $data;
	}

}

?>