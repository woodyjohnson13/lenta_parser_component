<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class LentaParserComponent extends CBitrixComponent
{
    private $iblock_id = null;
    private $highblock_id = null;
    private $parse_response = null;
    
    public function executeComponent()
    {
        try {
            $this->check_modules();
            $this->init_blocks();
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'parse') {
                $this->parse_response = $this->parse_news();
            }
            
            $this->arResult = $this->get_template_data();
            
            $this->includeComponentTemplate();
            
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }
    
    private function check_modules()
    {
        if (!CModule::IncludeModule("iblock")) {
            throw new Exception("Модуль инфоблоков не установлен");
        }
        if (!CModule::IncludeModule("highloadblock")) {
            throw new Exception("Модуль highload блоков не установлен");
        }
    }
    
    private function init_blocks()
    {
        $res = CIBlock::GetList([], [
            'CODE' => 'news',
            'TYPE' => 'content',
            'ACTIVE' => 'Y'
        ]);

        if ($iblock = $res->Fetch()) {
            $this->iblock_id = $iblock['ID'];
        } else {
            throw new Exception("Инфоблок не найден");
        }
        
        $highload_block = Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => ['=NAME' => 'Categories']
        ])->fetch();
        
        if ($highload_block) {
            $this->highblock_id = $highload_block['ID'];
        } else {
            throw new Exception("Highload блок не найден");
        }
    }
    
    private function parse_news()
    {
        //!Bad practice
        //TODO: Replace with using env variable later
        $url = "https://lenta.ru/rss";
        
        try {
            
            $content = @file_get_contents($url, false);
            
            if (!$content) {
                throw new Exception("Не удалось загрузить ленту!");
            }
            
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            
            if ($xml === false) {
                $errors = libxml_get_errors();
                throw new Exception("Ошибка парсинга XML: " . $errors[0]->message);
            }
            
            $items = $xml->channel->item;
            
            if (empty($items)) {
                throw new Exception("Нет новостей в ленте");
            }
            
            $saved = 0;
            $updated = 0;
            $category_map = [];
            
            $highload_block = Bitrix\Highloadblock\HighloadBlockTable::getById($this->highblock_id)->fetch();
            $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($highload_block);
            //? Basically our db table object now
            $entity_class = $entity->getDataClass(); 
            
            foreach ($items as $item) {
                $guid = (string)$item->guid;
                $title = (string)$item->title;
                
                if (empty($guid) || empty($title)) {
                    continue; 
                }
                
                //! Bad practice, but didn`t come to better solution yet
                $existing = CIBlockElement::GetList(
                    [],
                    ['IBLOCK_ID' => $this->iblock_id, '=XML_ID' => $guid],
                    false,
                    false,
                    ['ID']
                )->Fetch();
                
                $category_id = null;
                $category_name = (string)$item->category;
                
                if (!empty($category_name)) {
                    if (!isset($category_map[$category_name])) {
                        $category = $entity_class::getList([
                            'filter' => ['=UF_NAME' => $category_name]
                        ])->fetch();
                        
                        if ($category) {
                            $category_id = $category['ID'];
                        } else {
                            $result = $entity_class::add([ 
                                'UF_NAME' => $category_name,
                                'UF_XML_ID' => CUtil::translit($category_name, "ru"),
                                'UF_SORT' => 500,
                            ]);
                            
                            if ($result->isSuccess()) {
                                $category_id = $result->getId();
                            }
                        }
                        $category_map[$category_name] = $category_id;
                    } else {
                        $category_id = $category_map[$category_name];
                    }
                }
                
                $properties = [
                    'LINK' => (string)$item->link,
                    'AUTHOR' => (string)$item->author
                ];
                
                if ($category_id) {
                    $properties['CATEGORY'] = $category_id;
                }
                
                $publication_date = $this->format_date((string)$item->pubDate);
                if ($existing) {
                    $el = new CIBlockElement();
                    $result = $el->Update($existing['ID'], [
                        'NAME' => $title,
                        'DATE_ACTIVE_FROM' => $publication_date,
                        'PROPERTY_VALUES' => $properties
                    ]);
                    
                    if ($result) {
                        $updated++;
                    }
                } else {
                    $el = new CIBlockElement();
                    $result = $el->Add([
                        'IBLOCK_ID' => $this->iblock_id,
                        'NAME' => $title,
                        'CODE' => CUtil::translit($title, "ru", ['replace_space' => '-']),
                        'XML_ID' => $guid,
                        'ACTIVE' => 'Y',
                        'DATE_ACTIVE_FROM' => $publication_date,
                        'PROPERTY_VALUES' => $properties
                    ]);
                    
                    if ($result) {
                        $saved++;
                    }
                }
            }
            
            return [
                'success' => true,
                'message' => "Успешно обработано!",
                'data' => [
                    'total' => count($items),
                    'saved' => $saved,
                    'updated' => $updated,
                    'categories' => count($category_map)
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Ошибка: " . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    private function get_template_data()
    {
        $data = [];
        
        $selected_category = $_GET['category'] ?? 'all';
        
        //? Maybe should make global highload block entity?
        $highload_block = Bitrix\Highloadblock\HighloadBlockTable::getById($this->highblock_id)->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($highload_block);
        $entity_class = $entity->getDataClass(); 
        
        $categories_response = $entity_class::getList([ 
            'order' => ['UF_NAME' => 'ASC'],
            'select' => ['ID', 'UF_NAME']
        ]);
        
        $categories = [];
        while ($cat = $categories_response->fetch()) {
            $categories[] = [
                'ID' => $cat['ID'],
                'NAME' => $cat['UF_NAME']
            ];
        }
        
        $filter = ['IBLOCK_ID' => $this->iblock_id, 'ACTIVE' => 'Y'];
        
        if ($selected_category !== 'all') {
            $category_id = null;
            foreach ($categories as $cat) {
                if ($cat['NAME'] == $selected_category) {
                    $category_id = $cat['ID'];
                    break;
                }
            }
            
            if ($category_id) {
                $filter['PROPERTY_CATEGORY'] = $category_id;
            }
        }
        
        $news_response = CIBlockElement::GetList(
            ['DATE_ACTIVE_FROM' => 'DESC'],
            $filter,
            false,
            ['nTopCount' => $this->arParams['NEWS_COUNT'] ?? 50],
            ['ID', 'NAME', 'DATE_ACTIVE_FROM', 'PREVIEW_TEXT', 'PROPERTY_LINK', 'PROPERTY_CATEGORY', 'PROPERTY_AUTHOR']
        );
        
        $news = [];
        while ($item = $news_response->Fetch()) {
            $catName = '';
            if ($item['PROPERTY_CATEGORY_VALUE']) {
                foreach ($categories as $cat) {
                    if ($cat['ID'] == $item['PROPERTY_CATEGORY_VALUE']) {
                        $catName = $cat['NAME'];
                        break;
                    }
                }
            }
            
            $news[] = [
                'ID' => $item['ID'],
                'TITLE' => $item['NAME'],
                'LINK' => $item['PROPERTY_LINK_VALUE'],
                'DATE' => FormatDate('d.m.Y H:i', MakeTimeStamp($item['DATE_ACTIVE_FROM'])),
                'CATEGORY' => $catName,
                'AUTHOR' => $item['PROPERTY_AUTHOR_VALUE'],
            ];
        }
        
        $data['CATEGORIES'] = $categories;
        $data['NEWS'] = $news;
        $data['SELECTED_CATEGORY'] = $selected_category;
        $data['PARSE_RESULT'] = $this->parse_response;
        
        return $data;
    }
    
    private function format_date($date)
    {
        $timestamp = MakeTimeStamp($date, "D, d M Y H:i:s T");
        if ($timestamp === false) {
            $timestamp = time();
        }
        return ConvertTimeStamp($timestamp, 'FULL');
    }
}
