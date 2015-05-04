<?php
/* ----------------------------------------------------------------------------
Добавить снипет getImage следующего содержания :

<?php
// Script Name: GetImage version 2.3 (modx evo 1.xx)
// Creation Date: 30.05.2013
// Last Modified: 21.11.2013
// Autor: Swed <webmaster@collection.com.ua>
// Purpose: Get image (address) for document from tv params, content, or other

// Получить изображение (адрес) для ресурса из tv параметров, контента (или другого поля)
// Параметры: (умолчания)
//  &id        = [*id*]             // ID документа
//  &field     = "content"          // поле из которого парсить, указать пусто или 0, что бы не использовать
//  &urlOnly   = true               // получить только путь (если парсится источник), при false будет возвращен тег изображения (Не доработано на 04.06.2013)
//  &tv        = ""                 // Искать в tv (несколько, через запятую, порядок играет роль, первый найденный используется). Например, "image,photos"
//                                  // Поддерживются параметры для multiPhoto после знака "=", через ";" выбор номера элемента, по умолчанию =0;0.
//                                  // Воможно указать =rand,0 - для выбора случайного
//  &data      = ""                 // Использовать содержимое этой переменной для обработки (может использоваться как альтернатива)
//  &parseData = false              // Если data не пусто, если true, то искать также как в контенте - <img src="" .. />, при false - просто использовать
//  &parseTv   = false              // true==1 - считать все TV как Html и парсить как контент, указать через запятую те, которые нужно парсить
//  &order     = "tv,document,data" // Порядок поиска, через запятую. Например, data,tv,document.
//  &rand      =  false             // Выбрать случайное из всех найденных
//  &all       =  false             // обработать все (станет true если rand true), при rand false выведе все (при ulrOnly=true через запятую)
//  &save      =  ""                // не возвращать, сохранить в указанный плейсхолдер
//  &out       = "%s"               // Вернуть в формате. %s - будет заменено на полученный результат [2.2]

// Примеры:
//  [[getImage]] - Получить для текущего документа из контента
//  [[getImage? &tv=`image,photos`]] - из текущего из контента сначала искать в TV `image,photos`
//  [[getImage? &field=`anotation`]] - использовать другое поле
//  [[getImage? &tv=`image,photos` &data=`<img src="/images/no_image.jpg" atl="" />` $parseData=`1`]] Использовать альтернативный html если в остальных не найдено
//  [[getImage? &tv=`image,photos` &data=`/images/no_image.jpg` ]] Использовать адрес изображения если в остальных не найдено
//  [[getImage? &id=`32` &tv=`image, photos` &save=`myplace` ]] Сохранить результат в плейсхолдер [+myplace+], не выдавая его
//  [[getImage? &id=`32` &tv=`image, photos` &out=`<a ..><img src="%s" ... /></a>` ]] Сохранить результат в плейсхолдер [+myplace+], не выдавая его
//  [[getImage? &id=`32` &tv=`image,photos` &rand=`1` &data=`/images/image.jpg`  ]] Случайное из всего списка: &data, tv, content
//  [[getImage? &id=`32` &tv=`image, photos=0;1` &order=`document,tv` ]] Сначала искать в контенте, а потом в TV. Для мультифото photo использовать первую большую картинку
//  [[getImage? &id=`32` &tv=`photos=rand;0,image` ]] Получить случайное из multiphoto, если нет то из image или из документа
//  [[getImage? &id=`32` &tv=`image,photos=rand;0` &rand=`1` &data=`/images/image.jpg`  ]] Случайное, для мультифото - случайное из 1го поля (маленькая)
//  [[getImage? &id=`32` &tv=`image,photos=rand:0=/name/i;0` &rand=`1` &data=`/images/image.jpg`  ]] тоже самое, но с условием, что в этом поле есть "name" (regexp)
//  [[getImage? &id=`32` &tv=`image,photos=rand:2=/картинка/i;0` &rand=`1` &data=`/images/image.jpg`  ]] -- с условием, что в названии (3е поле) есть "картинка" (regexp)
//  [[getImage? &id=`32` &tv=`image,photos=rand:2=Слайд;0` &rand=`1` &data=`/images/image.jpg`  ]] -- с условием, что название равно "Слайд"

if (file_exists($includeFile = $modx->config['base_path']."assets/snippets/getImage/getImage.php")) {
 include_once($includeFile);
 if (class_exists("getImage")) {
  $getImage = new getImage(array(
     "id"        => isset($id)?$id:$modx->documentObject['id'],
     "field"     => isset($field)?$field:"content",
     "urlOnly"   => isset($urlOnly)?$urlOnly:true,
     "tv"        => isset($tv)?$tv:false,
     "data"      => isset($data)?$data:"",
     "parseData" => isset($parseData)?$parseData:false,
     "parseTv"   => isset($parseTv)?$parseTv:false,
     "order"     => isset($order)?$order:"",
     "rand"      => isset($rand)?$rand:"",
     "all"       => isset($all)?$all:"",
     "out"       => isset(out)?out:"%s",
  ));
  if (empty($save)) return $getImage->result();
  else $modx->setPlaceholder($save, $getImage->result());
 } else {
 return "Required class 'getImage' don't exists";
 }
} else {
 return "Required file $includeFile don't exists";
}
?>
/* ----------------------------------------------------------------------------  */

class getImage {
 var $p;
 var $result = array();

 function getImage($p = array()) {
  global $modx;
  $pd = array(
   "id"        => $modx->documentObject['id'],
   "field"     => "content",
   "urlOnly"   => true,
   "tv"        => false,
   "data"      => "",
   "parseData" => false,
   "parseTv"   => false,
   "order"     => explode(",","tv,document,data"),
   "rand"      => false,
   "all"       => false,
   "out"       => "%s",
  );
  foreach ($pd as $k=>$v) if (!isset($p[$k])) $p[$k] = $v;
  $this->p=&$p;
  if (!is_array($p["order"])) {
   $p["order"] = preg_split("/\s*,\s*/",$p["order"],-1,PREG_SPLIT_NO_EMPTY);
   foreach($pd["order"] as $v) if (!in_array($v,$p["order"])) $p["order"][] = $v; // add default
  }
  if ($p["rand"]) $p["all"] = true;
  $this->isCurrent = $p["id"] === $modx->documentObject['id'];
  foreach($p["order"] as $o) {
   switch ($o) {
    case "tv":
     if (strpos($p["tv"],"=")) $this->extendTv = true;
     $p["tv"] = preg_split("/\s*,\s*/",$p["tv"],-1,PREG_SPLIT_NO_EMPTY);
     if ($this->extendTv) {
      $this->extendTv = array();
      foreach ($p["tv"] as $t) {
       $t = preg_split("/\s*=\s*/",$t,2,PREG_SPLIT_NO_EMPTY);
       $this->extendTv[$t[0]] = !empty($t[1])?preg_split("/\s*;\s*/",$t[1],-1,PREG_SPLIT_NO_EMPTY):"";
      }
      $p["tv"] = array_keys($this->extendTv);
     }
     $tvs = array(); // name => value
     if ( !$this->isCurrent) foreach ((array)$modx->getTemplateVars($p["tv"], "name", $p["id"]) as $d) $tvs[$d["name"]] = $d["value"];
     foreach ($p["tv"] as $tvField) {
      if ($this->isCurrent) $tvs[$tvField] = $modx->documentObject[$tvField][1];
      if ($data = $this->exTv($tvField,$tvs[$tvField])) {
       if ($data = ($p["parseTv"]===true or $p["parseTv"]===1 or in_array($tvField,explode(",",$p["parseTv"]))) ? $this->parseData($data):$data) {
        if (is_array($data)) $this->result = array_merge($this->result,$data);
        else $this->result[] = $data;
       }
       if (!$p["all"]) break;
      }
     }
     break ($this->result and !$p["all"]) ? 2 : 1;
    case "document":
     if ($p["field"]) {
      if ( $this->isCurrent ) {
       $data = $modx->documentObject[$p["field"]];
      } else {
       $data = $modx->getDocument($p["id"],$p["field"]);
       if (is_array($data)) $data = reset($data);
      }
      if ($data = $this->parseData($data)) $this->result[] = $data;
     }
     break ($this->result and !$p["all"]) ? 2 : 1;
    case "data":
     if (!empty($p["data"]) and ($data = $p["parseData"]?$this->parseData($p["data"]):$p["data"])) {
      $this->result[] = $data;
     }
     break ($this->result and !$p["all"]) ? 2 : 1;
    default:       // default actions
   }
  }
 }

 function result() {
  if (!$this->p["rand"] or count($this->result) <=1) $result = reset($this->result);
  else if ($this->p["rand"]) $result = $this->result[rand(0,count($this->result)-1)];
  else if ($this->p["all"])
   $result = implode($this->p["urlOnly"]?"":",",$this->result);
  return $result?str_replace("%s",$result,$this->p["out"]):"";
 }

 function parseData($data="") {
  $p=&$this->p;
  if (($p["urlOnly"] and preg_match("/<img[^>]+src=[\"']([^\"']+)[\"']/i",$data,$m))
     or  (!$p["urlOnly"] and preg_match("/(<img[^>]+>)/i",$data,$m)))
    return $m[1];
  else return "";
 }

 function exTv($tvName,$data) { // получение изображения из формата json - multiphoto
  if (is_array($trJson = @json_decode($data))) { // проверка на пустой массив 
   if (!count($trJson)) return "";
   $o =& $this->extendTv[$tvName];
   $o = !empty($o) ? $o: array($this->p["all"]?"all":"0","0");
   if (!is_array($o)) $o = preg_split("/\s*;\s*/",$o);
   if (!isset($o[1])) $o[1] = 0;
   if (substr(strtolower($o[0]),0,4)=="rand") {
    if (preg_match("/^rand:(\d+)=(.*)$/i",$o[0],$m)) { // Условие для случайно выборки. Проверка по номеру элемента n=Значение(регулярное выражение)
     $nA = array();
     if (preg_match("/^\/.*\/\w*$/",$m[2])) { // check by regexp
      foreach ($trJson as $k => $a) if (preg_match($m[2],$a[$m[1]])) $nA[] = $trJson[$k];
     } else {
      foreach ($trJson as $k => $a) if ($a[$m[1]]==$m[2]) $nA[] = $trJson[$k];
     }
     if ($nA) {$trJson = $nA;unset($nA);}
     $o[0] = rand(0,count($trJson)-1);
    }
    $o[0] = rand(0,count($trJson)-1);
   } else
    if (strtolower($o[0])=="all") {
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