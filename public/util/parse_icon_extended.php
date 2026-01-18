<?php

function extractImageFilename(string $url): string
{
    $cleanUrl = strtok($url, '?');
    $parts = explode('/revision', $cleanUrl);
    return basename($parts[0]);
}

function toOriginalImageUrl(string $url): string
{
    $cleanUrl = strtok($url, '?');
    $parts = explode('/revision', $cleanUrl);
    return $parts[0];
}

function getPageHtml(string $url): string
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0 Safari/537.36",
    ]);
    $html = curl_exec($ch);
    if (!$html) {
        echo "Ошибка загрузки страницы: $url\n" . curl_error($ch) . "\n";
    }
    curl_close($ch);
    return $html;
}

function isValidImage(string $imgUrl): bool
{
    return (
        strpos($imgUrl, '/revision/') !== false &&
        (
            strpos($imgUrl, '/latest/') !== false ||
            strpos($imgUrl, '/scale-to-width-down/') !== false
        ) &&
        strpos($imgUrl, '_mark') === false
    );
}

// === Основной блок ===

$baseUrl = "https://ark.fandom.com";
$categoryUrl = "$baseUrl/ru/wiki/Тканевая_Бандана";
// == /ru/wiki/Категория:Постройки";
$saveDir = __DIR__ . "/shirt_icons";

if (!file_exists($saveDir)) {
    mkdir($saveDir, 0777, true);
}

// 1. Получаем HTML категории
$html = getPageHtml($categoryUrl);

// 2. Извлекаем ссылки на все страницы построек
preg_match_all('/<a href="(\/ru\/wiki\/[^"]+)" title="([^"]+)">/', $html, $matches);
$urls = array_unique($matches[1]);

$downloaded = 0;

foreach ($urls as $relativePath) {
    $fullUrl = $baseUrl . $relativePath;
    echo "\n▶ Загружаем страницу: $fullUrl\n";

    $pageHtml = getPageHtml($fullUrl);
    if (!$pageHtml) continue;

    // Загружаем в DOM и ищем img внутри нужного контейнера
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($pageHtml, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);

    // Выбор img внутри нужного контейнера
    $imgNodes = $xpath->query('//div[contains(@class,"info-arkitex")]/div[1]/div[2]//img');

    foreach ($imgNodes as $imgNode) {
        if (!($imgNode instanceof DOMElement)) continue;

        $imgUrl = $imgNode->getAttribute('src');
        if (!$imgUrl) continue;

        if (!str_starts_with($imgUrl, 'http')) {
            $imgUrl = 'https:' . $imgUrl;
        }

        if (!isValidImage($imgUrl)) continue;

        $originalUrl = toOriginalImageUrl($imgUrl);
        $filename = extractImageFilename($originalUrl);
        $filepath = $saveDir . "/" . $filename;

        echo "📦 Картинка: $originalUrl\n";

        if (file_exists($filepath)) {
            echo "⏩ Уже загружено: $filename\n";
            continue;
        }

        $image = @file_get_contents($originalUrl);
        if ($image === false) {
            echo "Ошибка загрузки: $originalUrl\n";
            continue;
        }

        file_put_contents($filepath, $image);
        echo "✅ Сохранено: $filename\n";
        $downloaded++;
    }
}

echo "\n🎉 Готово! Загружено иконок: $downloaded\n";
