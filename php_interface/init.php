<?$directory = $_SERVER["DOCUMENT_ROOT"] . '/bitrix/php_interface/ranx-classes/';

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
