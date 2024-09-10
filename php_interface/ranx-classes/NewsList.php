<?
use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Web\Json;

class NewsList
{
    private $iblockId;
    private $year;
    private $cacheTime = 86400; // Время кэширования в секундах (24 часа)
    private $pageSize;
    private $currentPage;

    public function __construct($pageSize = 10, $currentPage = 1, $iblockId = 12, $year = 2015)
    {
        $this->pageSize = $pageSize;
        $this->currentPage = $currentPage;
        $this->iblockId = $iblockId;
        $this->year = $year;
    }

    // Метод получения новостей
    public function getNewsList($bPrint = false)
    {
        if (!Loader::includeModule('iblock')) {
            throw new \Exception("Модуль инфоблоков не подключен.");
        }

        $cache = Cache::createInstance();
        $cacheId = 'news_list_' . $this->iblockId . '_year_' . $this->year . '_page_' . $this->currentPage . '_size_' . $this->pageSize;
        $cacheDir = '/news_list_cache/';

        if ($cache->initCache($this->cacheTime, $cacheId, $cacheDir)) {
            // Если кэш существует, вернем его содержимое
            $newsList = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            // Если кэша нет, делаем запрос и записываем результат в кэш
            $newsList = $this->fetchNewsList();
            if (empty($newsList)) {
                $cache->abortDataCache();
            } else {
                $cache->endDataCache($newsList);
            }
        }
        if ($bPrint) {
            print_r($newsList);
        } else {
            return $newsList;
        }

    }

    // Метод очистки кэша
    public function clearCache()
    {
        $cache = Cache::createInstance();
        $cacheDir = '/news_list_cache/';
        $cache->cleanDir($cacheDir);
        echo "Кэш очищен.<br>";
    }

    // Запрос новостей из инфоблока
    private function fetchNewsList()
    {
        $newsList = [];
        $filter = [
            'IBLOCK_ID' => $this->iblockId,
            'ACTIVE' => 'Y',
            '>=DATE_ACTIVE_FROM' => '01.01.' . $this->year,
            '<=DATE_ACTIVE_FROM' => '31.12.' . $this->year,
        ];

        $select = [
            'ID',
            'NAME',
            'DATE_ACTIVE_FROM',
            'PREVIEW_PICTURE',
            'DETAIL_PAGE_URL',
            'IBLOCK_SECTION_ID',
            'PROPERTY_AUTHOR',
            'TAGS',
        ];

        $dbResult = \CIBlockElement::GetList(
            ['DATE_ACTIVE_FROM' => 'DESC'], // Сортировка
            $filter,
            false,
            ['nPageSize' => $this->pageSize, 'iNumPage' => $this->currentPage],
            $select
        );

        while ($element = $dbResult->GetNextElement()) {
            $fields = $element->GetFields();
            $properties = $element->GetProperties();

            $newsList[] = [
                'id' => $fields['ID'],
                'url' => $fields['DETAIL_PAGE_URL'],
                'image' => \CFile::GetPath($fields['PREVIEW_PICTURE']),
                'name' => $fields['NAME'],
                'sectionName' => $this->getSectionName($fields['IBLOCK_SECTION_ID']),
                'date' => $this->formatDate($fields['DATE_ACTIVE_FROM']),
                'author' => $this->getAuthorName($properties['AUTHOR']['VALUE']),
                'tags' => explode(',', $fields['TAGS']),
            ];
        }

        return $newsList;
    }

    // Форматирование даты
    private function formatDate($date)
    {
        return FormatDate('d F Y H:i', MakeTimeStamp($date));
    }

    // Получение названия раздела по ID
    private function getSectionName($sectionId)
    {
        $section = \CIBlockSection::GetByID($sectionId)->GetNext();
        return $section ? $section['NAME'] : '';
    }

    // Получение имени автора по его ID
    private function getAuthorName($authorId)
    {
        if (!$authorId) {
            return '';
        }
        
        if (!empty($authorId) && is_numeric($authorId)) {
            $authorName = \CIBlockElement::GetById($authorId)->Fetch();
            if (!$authorName) {
                $authorName = '';
            }
        } else {
            $authorName = '';
        }

        return $authorName ? $authorName['NAME'] : '';
    }

    // Преобразование данных в формат JSON
    public function toJson($bPrint = false)
    {
        if ($bPrint) {
            print_r(Json::encode($this->getNewsList()));
        } else {
            return Json::encode($this->getNewsList());
        }

    }
}
