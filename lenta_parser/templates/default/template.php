<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->addExternalCss($this->GetFolder() . '/style.css');
?>

<div class="lenta-parser">
    <div class="lenta-header">
        <h1>Парсер новостей "Ленты"</h1>
        <div class="subtitle">Самые свежие новости в реальном времени</div>
    </div>

    <div class="actions-container">
        <form method="post" id="parse-form">
            <input type="hidden" name="action" value="parse">
            <button type="submit" class="action-button success" id="parse-button">
                <span>🔄</span> Загрузить свежие новости
            </button>
        </form>
    </div>
</div>
