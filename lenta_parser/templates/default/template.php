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

    <?php if ($arResult['PARSE_RESULT']): ?>
        <div class="parse-result <?= $arResult['PARSE_RESULT']['success'] ? 'success' : 'error' ?>">
            <?= $arResult['PARSE_RESULT']['message'] ?>
            <?php if ($arResult['PARSE_RESULT']['success'] && !empty($arResult['PARSE_RESULT']['data'])): ?>
                <div class="parse-details">
                    Всего в RSS: <?= $arResult['PARSE_RESULT']['data']['total'] ?><br>
                    Новых: <?= $arResult['PARSE_RESULT']['data']['saved'] ?><br>
                    Обновлено: <?= $arResult['PARSE_RESULT']['data']['updated'] ?><br>
                    Категорий: <?= $arResult['PARSE_RESULT']['data']['categories'] ?>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
            setTimeout(function() {
                window.location.href = window.location.pathname + '?category=<?= $arResult['SELECTED_CATEGORY'] ?>';
            }, 2000);
        </script>
    <?php endif; ?>
</div>
