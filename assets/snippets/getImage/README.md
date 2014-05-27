Add snippet getImage 
(/install/assets/snippets/getimage.tpl)
========


```
// Script Name: GetImage version 2.4 (modx evo 1.xx)
// Creation Date: 30.05.2013
// Last Modified: 17.01.2014
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
//  &fullUrl   = "false"            // Использовать полный адрес изображения, если расположен локально [2.4]

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
     "out"       => isset($out)?$out:"%s",
     "fullUrl"   => isset($fullUrl)?$fullUrl:false,
                           ));
  if (empty($save)) return $getImage->result();
  else $modx->setPlaceholder($save, $getImage->result());
 } else {
  return "Required class 'getImage' don't exists";
 }
} else {
 return "Required file $includeFile don't exists";
}
```
