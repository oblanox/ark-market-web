<?php

function extractImageFilename(string $url): string
{
    // Удаляем query-параметры (?cb=...)
    $cleanUrl = strtok($url, '?');

    // Получаем имя файла перед "/revision"
    $parts = explode('/revision', $cleanUrl);

    // Берем последнюю часть перед revision и её basename
    return basename($parts[0]);
}

function toOriginalImageUrl(string $url): string
{
    // Удаляем query (?cb=...)
    $cleanUrl = strtok($url, '?');

    // Убираем всё после /revision/
    $parts = explode('/revision', $cleanUrl);

    return $parts[0];
}


$baseUrl = "https://ark.fandom.com/ru/wiki/%D0%A3%D0%B4%D0%BE%D0%B1%D1%80%D0%B5%D0%BD%D0%B8%D0%B5";
$saveDir = __DIR__ . "/resource2_icons";

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
// https://static.wikia.nocookie.net/arksurvivalevolved_gamepedia/images/c/c9/Clay_%28Primitive_Plus%29.png/revision/latest?cb=20160822075408
// Ищем все <img src="...">
preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches);
$downloaded = 0;

foreach ($matches[1] as $imgUrl) {
    // Пропускаем мелкие иконки, берем только большие '/scale-to-width-down/
    if (strpos($imgUrl, '/latest/') === false && strpos($imgUrl, '/scale-to-width-down/') === false) {
        continue;
    }

    // Пропускаем крестики и прочие технические иконки
    if (strpos($imgUrl, '_mark') !== false) {
        continue;
    }

    // Преобразуем относительные URL
    if (strpos($imgUrl, 'http') !== 0) {
        $imgUrl = 'https:' . $imgUrl;
    }

    // Имя файла
    $filename = extractImageFilename($imgUrl);
    $filepath = $saveDir . "/" . $filename;
    echo $imgUrl . PHP_EOL;
    echo $filename . PHP_EOL;
    echo $filepath . PHP_EOL;

    // Скачиваем файл
    $image = @file_get_contents(toOriginalImageUrl($imgUrl));
    if ($image === false) {
        echo "Ошибка загрузки: $imgUrl\n";
        continue;
    }

    file_put_contents($filepath, $image);
    echo "Сохранено: $filename\n";
    $downloaded++;
}


echo "Готово! Загружено иконок: $downloaded\n";
