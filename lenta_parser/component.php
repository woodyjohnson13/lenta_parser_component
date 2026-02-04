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
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Ошибка: " . $e->getMessage(),
                'data' => []
            ];
        }
    }
}
