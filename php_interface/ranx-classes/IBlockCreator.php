<?
use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

class IblockCreator
{
    private $iblockType = 'content'; // Тип инфоблока
    private $iblockNames = []; // Имена инфоблоков будут динамическими

    public function __construct($iblockType = 'content')
    {
        if (!Loader::includeModule('iblock')) {
            throw new \Exception("Модуль инфоблоков не подключен.");
        }

        $this->iblockType = $iblockType;
    }

    // Метод для динамической генерации массива с именами инфоблоков
    public function setIblockNames($count)
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->iblockNames[] = "Инфоблок $i";
        }
    }

    // Метод для проверки и создания типа инфоблока, если его не существует
    private function ensureIblockTypeExists()
    {
        $iblockType = TypeTable::getList([
            'filter' => ['=ID' => $this->iblockType]
        ])->fetch();

        if (!$iblockType) {
            $result = TypeTable::add([
                'ID' => $this->iblockType,
                'SECTIONS' => 'Y',
                'LANG' => [
                    'ru' => ['NAME' => 'Контент'],
                    'en' => ['NAME' => 'Content']
                ]
            ]);

            if ($result->isSuccess()) {
                echo "Тип инфоблока '{$this->iblockType}' успешно создан.<br>";
            } else {
                throw new \Exception("Ошибка при создании типа инфоблока: " . implode(', ', $result->getErrorMessages()));
            }
        } else {
            echo "Тип инфоблока '{$this->iblockType}' уже существует.<br>";
        }
    }

    // Метод создания инфоблоков
    public function createIblocks()
    {
        // Проверяем наличие типа инфоблока, создаем, если его нет
        $this->ensureIblockTypeExists();

        foreach ($this->iblockNames as $index => $name) {
            $iblockFields = [
                'ACTIVE' => 'Y',
                'NAME' => $name,
                'CODE' => 'infoblock_' . ($index + 1),
                'IBLOCK_TYPE_ID' => $this->iblockType,
                'SITE_ID' => ['s1'], // Идентификатор сайта
                'SORT' => 500,
                'LIST_PAGE_URL' => '#SITE_DIR#/content/' . 'infoblock_' . ($index + 1) . '/',
                'DETAIL_PAGE_URL' => '#SITE_DIR#/content/' . 'infoblock_' . ($index + 1) . '/#ELEMENT_ID#/',
                'SECTION_PAGE_URL' => '#SITE_DIR#/content/' . 'infoblock_' . ($index + 1) . '/#SECTION_ID#/',
                'DESCRIPTION' => "Описание инфоблока {$name}",
                'DESCRIPTION_TYPE' => 'text',
                'VERSION' => 2, // Хранение элементов по умолчанию в новой версии (хранение в одной таблице)
            ];

            $result = IblockTable::add($iblockFields);
            if ($result->isSuccess()) {
                echo "Инфоблок '{$name}' создан успешно с ID: " . $result->getId() . "<br>";
            } else {
                echo "Ошибка при создании инфоблока '{$name}': " . implode(', ', $result->getErrorMessages()) . "<br>";
            }
        }
    }

    // Метод полного удаления всех инфоблоков и сброса счетчика ID
    public function fullDeleteIblocks()
    {
        $connection = Application::getConnection();

        // Получаем список всех инфоблоков по типу
        $iblockList = IblockTable::getList([
            'filter' => ['=IBLOCK_TYPE_ID' => $this->iblockType],
            'select' => ['ID', 'NAME']
        ]);

        // Удаляем все инфоблоки
        while ($iblock = $iblockList->fetch()) {
            $result = IblockTable::delete($iblock['ID']);
            if ($result->isSuccess()) {
                echo "Инфоблок '{$iblock['NAME']}' удален успешно.<br>";
            } else {
                echo "Ошибка при удалении инфоблока '{$iblock['NAME']}': " . implode(', ', $result->getErrorMessages()) . "<br>";
            }
        }

        // Сбрасываем автоинкремент для таблиц инфоблоков
        $connection->queryExecute('ALTER TABLE b_iblock AUTO_INCREMENT = 1');
        $connection->queryExecute('ALTER TABLE b_iblock_element AUTO_INCREMENT = 1');
        $connection->queryExecute('ALTER TABLE b_iblock_section AUTO_INCREMENT = 1');

        echo "Счётчики ID инфоблоков и их элементов сброшены.<br>";
    }
}