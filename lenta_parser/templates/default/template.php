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
                window.location.href = window.location.pathname + '?category=<?= urlencode($arResult['SELECTED_CATEGORY']) ?>';
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
            <?php if ($arResult['PAGINATION']['TOTAL_ITEMS'] > count($arResult['NEWS'])): ?>
                (показано <?= count($arResult['NEWS']) ?> из <?= $arResult['PAGINATION']['TOTAL_ITEMS'] ?>)
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
        
        <?php if ($arResult['PAGINATION']['TOTAL_PAGES'] > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <?php if ($arResult['PAGINATION']['CURRENT_PAGE'] > 1): ?>
                        <a href="?category=<?= urlencode($arResult['SELECTED_CATEGORY']) ?>&page=1" class="pagination-item pagination-first">
                            ««
                        </a>
                        <a href="?category=<?= urlencode($arResult['SELECTED_CATEGORY']) ?>&page=<?= $arResult['PAGINATION']['CURRENT_PAGE'] - 1 ?>" class="pagination-item pagination-prev">
                            «
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    $start_page = max(1, $arResult['PAGINATION']['CURRENT_PAGE'] - 2);
                    $end_page = min($arResult['PAGINATION']['TOTAL_PAGES'], $arResult['PAGINATION']['CURRENT_PAGE'] + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?category=<?= urlencode($arResult['SELECTED_CATEGORY']) ?>&page=<?= $i ?>" 
                           class="pagination-item <?= $i == $arResult['PAGINATION']['CURRENT_PAGE'] ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($arResult['PAGINATION']['CURRENT_PAGE'] < $arResult['PAGINATION']['TOTAL_PAGES']): ?>
                        <a href="?category=<?= urlencode($arResult['SELECTED_CATEGORY']) ?>&page=<?= $arResult['PAGINATION']['CURRENT_PAGE'] + 1 ?>" class="pagination-item pagination-next">
                            »
                        </a>
                        <a href="?category=<?= urlencode($arResult['SELECTED_CATEGORY']) ?>&page=<?= $arResult['PAGINATION']['TOTAL_PAGES'] ?>" class="pagination-item pagination-last">
                            »»
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="pagination-info">
                    Страница <?= $arResult['PAGINATION']['CURRENT_PAGE'] ?> из <?= $arResult['PAGINATION']['TOTAL_PAGES'] ?>
                    | Всего новостей: <?= $arResult['PAGINATION']['TOTAL_ITEMS'] ?>
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="no-news">
            <?php if ($arResult['SELECTED_CATEGORY'] != 'all'): ?>
                📭 В категории "<?= htmlspecialchars($arResult['SELECTED_CATEGORY']) ?>" новостей не найдено
            <?php else: ?>
                📭 Новости не найдены. Нажмите "Загрузить свежие новости"
            <?php endif; ?>
        </div>
    <?php endif; ?>

        
        <footer class="lenta-footer">
            <div class="footer-content">
                <div class="footer-text">
                    Developed by 
                    <a href="https://github.com/woodyjohnson13" target="_blank" rel="noopener" class="developer-link">
                        WoodyJohnson
                    </a>
                </div>
            </div>
        </footer>
</div>

<script>
document.getElementById('parse-form')?.addEventListener('submit', function(e) {
    var button = document.getElementById('parse-button');
    if (button) {
        button.innerHTML = '<span>⏳</span> Загружаем...';
        button.disabled = true;
    }
});
</script>
