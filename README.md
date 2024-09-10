
# Bitrix IBlock Manager and News List
#### Тестовый проект

## Описание проекта

Этот проект предназначен для управления инфоблоками и получения списка новостей через API 1С-Битрикс. В проекте реализованы два класса:

- **IblockCreator** — для создания и удаления инфоблоков с их полным сбросом.
- **NewsList** — для получения списка новостей из инфоблока в формате JSON с поддержкой кэширования, пагинации и лимитов.

### Основные возможности:

1. **IblockCreator**:
   - Создание инфоблоков по переданным именам.
   - Удаление всех инфоблоков с возможностью сброса автоинкремента (счётчиков ID).
   - Проверка и создание типа инфоблока, если он отсутствует.

2. **NewsList**:
   - Получение списка новостей за определённый период с поддержкой фильтрации, пагинации и кэширования.
   - Форматирование новостей в JSON-ответ с полями `id`, `url`, `image`, `name`, `sectionName`, `date`, `author`, `tags`.

## Структура проекта

```
/project_root/
|-- /ranx-classes                 # Класс для создания и удаления инфоблоков
|   |-- IblockCreator.php         # Класс для создания и удаления инфоблоков
|   |-- NewsList.php              # Класс для получения новостей из инфоблоков
|-- /test-files                   # Директория с файлами для развертывания тестовой среды
|-- init.php                      # Подключение классов и логики в систему
|-- README.md                     # Документация проекта
```

## Установка

### 1. Копирование файлов

Скопируйте файлы проекта в директорию вашего проекта Bitrix. Обычно файлы классов размещаются в директории `/bitrix/php_interface/ranx-classes`.

### 2. Подключение классов в `init.php`

Чтобы классы автоматически подключались в вашем проекте, необходимо подключить их в файле `/bitrix/php_interface/init.php`. Добавьте следующий код:

```php
<?$directory = $_SERVER["DOCUMENT_ROOT"] . '/bitrix/php_interface/ranx-classes';

if (is_dir($directory)) {
    $files = scandir($directory);
    
    foreach ($files as $file) {
        $filePath = $directory . $file;

        // Проверяем, что это файл и его расширение .php
        if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
            require_once $filePath;
        }
    }
}
```

### 3. Использование IblockCreator

Для создания инфоблоков используйте следующий код:

```php
$iblockCreator = new IblockCreator('content'); // Укажите тип инфоблока
$iblockCreator->setIblockNames(10);            // Задайте количество инфоблоков
$iblockCreator->createIblocks();               // Создание инфоблоков
```

Для удаления инфоблоков и сброса счётчиков ID:

```php
$iblockCreator = new IblockCreator('content'); // Укажите тип инфоблока
$iblockCreator->fullDeleteIblocks();
```

### 4. Использование NewsList

Для получения списка новостей с кэшированием и пагинацией используйте следующий код:

```php
$pageSize = $_GET['pageSize'] ?? 10;
$page = $_GET['page'] ?? 1;
$iblockId = $_GET['iblockId'] ?? 12;
$year = $_GET['year'] ?? 2015;

$newsList = new NewsList($pageSize, $page, $iblockId, $year);
$newsList->toJson(true); //true используется для определения необходимости вывода вместо return в методе
//$newsList->getNewsList(true) //Получение данных в классическом формате массива PHP
```

### 5. Использование очистки кеша NewsList

```php
// Пример очистки кэша:
$newsList->clearCache();
```

## Деинсталляция

1. Откройте файл `/bitrix/php_interface/init.php` и удалите строки, связанные с подключением классов.
2. Удалите файлы классов из директории `/bitrix/php_interface/ranx-classes`.
3. При необходимости, удалите инфоблоки через административную панель или используя метод `$iblockCreator->fullDeleteIblocks()`.

## Автор

Разработано для использования с 1С-Битрикс. Автор: Pavel Afanasev
