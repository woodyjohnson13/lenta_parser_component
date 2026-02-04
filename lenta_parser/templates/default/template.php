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

    <div class="categories-container">
        <div class="categories-title">Категории</div>
        <div class="categories-tags">
            <a href="?category=all" 
               class="category-tag <?= $arResult['SELECTED_CATEGORY'] == 'all' ? 'active' : '' ?>">
                Все новости
            </a>
            <?php foreach ($arResult['CATEGORIES'] as $category): ?>
                <a href="?category=<?= urlencode($category['NAME']) ?>" 
                   class="category-tag <?= $arResult['SELECTED_CATEGORY'] == $category['NAME'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($category['NAME']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($arResult['NEWS'])): ?>
        <div class="news-count">
            Найдено новостей: <strong><?= count($arResult['NEWS']) ?></strong>
            <?php if ($arResult['SELECTED_CATEGORY'] != 'all'): ?>
                в категории "<?= htmlspecialchars($arResult['SELECTED_CATEGORY']) ?>"
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($arResult['NEWS'])): ?>
        <div class="news-grid">
            <?php foreach ($arResult['NEWS'] as $news): ?>
                <div class="news-card">
                    <div class="news-card-header">
                        <?php if (!empty($news['CATEGORY'])): ?>
                            <div class="news-card-category">
                                <?= htmlspecialchars($news['CATEGORY']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="news-card-title">
                            <a href="<?= htmlspecialchars($news['LINK']) ?>" target="_blank" rel="noopener">
                                <?= htmlspecialchars($news['TITLE']) ?>
                            </a>
                        </h3>
                    </div>
                      
                    <div class="news-card-footer">
                        <div class="news-card-date">
                            <span class="icon">📅</span>
                            <?= htmlspecialchars($news['DATE']) ?>
                        </div>
                        
                        <?php if (!empty($news['AUTHOR'])): ?>
                            <div class="news-card-author">
                                <span class="icon">✍️</span>
                                <?= htmlspecialchars($news['AUTHOR']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-news">
            <?php if ($arResult['SELECTED_CATEGORY'] != 'all'): ?>
                📭 В категории "<?= htmlspecialchars($arResult['SELECTED_CATEGORY']) ?>" новостей не найдено
            <?php else: ?>
                📭 Новости не найдены. Нажмите "Загрузить свежие новости"
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
