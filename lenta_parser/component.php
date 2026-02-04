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
}
