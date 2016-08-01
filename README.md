##Snippet getImage for Modx evo 
Получить изображение (адрес) для ресурса из tv параметров, контента (или другого поля)

###Параметры:
Параметр|по умолчанию|Описание
--------|--------|--------
&id        | [*id*]             |  ID документа
&field     | "content"          |  поле из которого парсить, указать пусто или 0, что бы не использовать
&urlOnly   | true               |  получить только путь (если парсится источник), при false будет возвращен тег изображения (Не доработано на 04.06.2013)
&tv        | ""                 |  Искать в tv (несколько, через запятую, порядок играет роль, первый найденный используется). Например, "image,photos". Поддерживются параметры для multiPhoto после знака "=", через ";" выбор номера элемента, по умолчанию =0;0. Воможно указать =rand,0 - для выбора случайного
&data      | ""                 |  Использовать содержимое этой переменной для обработки (может использоваться как альтернатива)
&parseData | false              |  Если data не пусто, если true, то искать также как в контенте - <img src="" .. /&gt;, при false - просто использовать
&parseTv   | false              |  true==1 - считать все TV как Html и парсить как контент, указать через запятую те, которые нужно парсить
&order     | "tv,document,data" |  Порядок поиска, через запятую. Например, data,tv,document.
&rand      |  false             |  Выбрать случайное из всех найденных
&all       |  false             |  обработать все (станет true если rand true), при rand false выведе все (при ulrOnly=true через запятую)
&save      |  ""                |  не возвращать, сохранить в указанный плейсхолдер
&out       | "%s"               |  Вернуть в формате. %s - будет заменено на полученный результат [2.2]
&fullUrl   | "false"            |  Использовать полный адрес изображения, если расположен локально [2.4]
&runSnippet| "false"            | Выполнить сниппет для обработки каждого найденного значения, формат строки как при обычном вызове, но без ограничивающих скобок, можно использовать другие кавычки. Для подстановки значения использовать %s [2.6]

###Примеры:
Вызов   |  Описание
--------|--------
```[[getImage]]``` | Получить для текущего документа из контента
```[[getImage? &tv=`image,photos`]]``` | из текущего из контента сначала искать в TV "image,photos"
```[[getImage? &field=`anotation`]]``` | использовать другое поле
```[[getImage? &tv=`image,photos` &data=`<img src="/images/no_image.jpg" atl="" />` $parseData=`1`]]```  | Использовать альтернативный html если в остальных не найдено
```[[getImage? &tv=`image,photos` &data=`/images/no_image.jpg` ]]``` | Использовать адрес изображения если в остальных не найдено
```[[getImage? &id=`32` &tv=`image, photos` &save=`myplace` ]]```  | Сохранить результат в плейсхолдер [+myplace+], не выдавая его
```[[getImage? &id=`32` &tv=`image, photos` &out=`<a ..><img src="%s" ... /></a>` ]]``` | Сохранить результат в плейсхолдер [+myplace+], не выдавая его
```[[getImage? &id=`32` &tv=`image,photos` &rand=`1` &data=`/images/image.jpg`  ]]``` | Случайное из всего списка: &data, tv, content
```[[getImage? &id=`32` &tv=`image, photos=0;1` &order=`document,tv` ]]``` | Сначала искать в контенте, а потом в TV. Для мультифото photo использовать первую большую картинку
```[[getImage? &id=`32` &tv=`photos=rand;0,image` ]]``` | Получить случайное из multiphoto, если нет то из image или из документа
```[[getImage? &id=`32` &tv=`image,photos=rand;0` &rand=`1` &data=`/images/image.jpg`  ]]``` | Случайное, для мультифото - случайное из 1го поля (маленькая)
```[[getImage? &id=`32` &tv=`image,photos=rand:0=/name/i;0` &rand=`1` &data=`/images/image.jpg`  ]]``` | тоже самое, но с условием, что в этом поле есть "name" (regexp)
```[[getImage? &id=`32` &tv=`image,photos=rand:2=/картинка/i;0` &rand=`1` &data=`/images/image.jpg`  ]]``` | с условием, что в названии (3е поле) есть "картинка" (regexp)
```[[getImage? &id=`32` &tv=`image,photos=rand:2=Слайд;0` &rand=`1` &data=`/images/image.jpg`  ]]``` |  с условием, что название равно "Слайд"
```<meta property="og:image" content="[[getImage? &tv=`image` &id=`[*id*]` &fullUrl=`1` ]]" />```  |  Вывести полную ссылку для локального изображения в meta
```<img src="[[phpthumb? &input=`[[getImage? &data=`[[ddGetMultipleField? &docField=`gallery` &columns=`0` &totalRows=`1` &docId=`[+id+]`]]` &tv=`image` &id=`[+id+]` &order=`data,tv,document`]]` &options=`w=300,h=300`]]" alt="[+pagetitle:specialchar+]" />``` | Полный пример вызова для элемента ditto. Используется phpthumb. Приоритет (параметр &order) получения изображения через multiField (соответственно результат сниппета в параметр &data)
```[[getImage? &tv=`image,photos` &id=`[+id+]` &runSnippet=` phpthumb? &input='%s' &options='w=300,h=300' `  &out=`<a href="[(site_url)][~[+id+]~]" class="image"  title="[+pagetitle:specialchar+]"><img src="%s" alt="[+pagetitle:specialchar+]" /></a>`]]``` | Другой полный пример для ditto. Запуск спиппета для обработки найденного значения. В отличие от предыдущего примера, при пустом результате ничего не выведет. 