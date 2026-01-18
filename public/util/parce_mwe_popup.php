<?php

/**
 * Извлекает имя файла с расширением из URL
 */
function extractImageFilename(string $url): string
{
    // Удаляем query-параметры (?cb=...)
    $cleanUrl = strtok($url, '?');

    // Получаем имя файла перед "/revision"
    $parts = explode('/revision', $cleanUrl);

    // Берем basename последнего куска
    return basename($parts[0]);
}

/**
 * Получает корректную ссылку на изображение из тега <img>
 * Использует data-src, если есть, иначе src
 */
function getImageUrlFromNode(DOMElement $img): ?string
{
    $src = null;

    if ($img->hasAttribute('data-src')) {
        $src = $img->getAttribute('data-src');
    } elseif ($img->hasAttribute('src')) {
        $src = $img->getAttribute('src');
    }

    if (!$src) return null;

    // Пропускаем крестики и мелкие иконки
    if (
        strpos($src, '/latest/') === false &&
        strpos($src, '/scale-to-width-down/') === false
    ) {
        return null;
    }

    if (strpos($src, '_mark') !== false) {
        return null;
    }

    // Преобразуем в абсолютный URL
    if (!str_starts_with($src, 'http')) {
        $src = 'https:' . $src;
    }

    return $src;
}

// === Основная логика ===

$baseUrl = "https://ark.fandom.com/ru/wiki/Ресурсы";
$saveDir = __DIR__ . "/resource_large";

// Создаем папку, если не существует
if (!file_exists($saveDir)) {
    mkdir($saveDir, 0777, true);
}

// Получаем HTML через cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) " .
        "AppleWebKit/537.36 (KHTML, like Gecko) " .
        "Chrome/122.0.0.0 Safari/537.36",
]);

$html = curl_exec($ch);
if (!$html) {
    die("Ошибка cURL: " . curl_error($ch) . "\n");
}
curl_close($ch);

// Загружаем DOM
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// Находим все <img> внутри <a class="mwe-popups">
$nodes = $xpath->query('//a[contains(@class, "mwe-popups")]//img');

$downloaded = 0;

foreach ($nodes as $node) {
    /** @var DOMElement $img */
    $img = $node;

    $src = getImageUrlFromNode($img);
    if (!$src) continue;

    $filename = extractImageFilename($src);
    $filepath = $saveDir . "/" . $filename;

    echo $src . PHP_EOL;
    echo $filename . PHP_EOL;
    echo $filepath . PHP_EOL;

    // Скачиваем файл
    $image = @file_get_contents($src);
    if ($image === false) {
        echo "Ошибка загрузки: $src\n";
        continue;
    }

    file_put_contents($filepath, $image);
    echo "Сохранено: $filename\n";
    $downloaded++;
}

echo "Готово! Загружено иконок: $downloaded\n";
