<?php

libxml_use_internal_errors(true);

function getDomXPath(string $html): DOMXPath
{
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    return new DOMXPath($dom);
}

function sanitizeFilename(string $url): string
{
    return basename(strtok($url, '?'));
}


function extractBlueprintInfoFromHtml(string $html): ?array
{
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    file_put_contents(__DIR__ . '/debug_parsed.html', $dom->saveHTML()); // посмотреть, что реально видит PHP
    $xpath = new DOMXPath($dom);

    //print_r($xpath->query('//div[contains(@class,"info-arkitex") and contains(@class,"info-framework")]'));
    $infoBlock = $xpath->query('//div[contains(@class,"info-arkitex") and contains(@class,"info-framework")]')->item(0);
    print_r($infoBlock);
    if (!$infoBlock) return null;

    // Картинка
    $imgNode = $xpath->query('.//img', $infoBlock)->item(0);
    /** @var DOMElement $imgNode */
    $imgUrl = $imgNode ? $imgNode->getAttribute('src') : null;
    $imageName = $imgUrl ? basename(parse_url($imgUrl, PHP_URL_PATH)) : null;

    // Blueprint путь и имя
    $codeNodes = $xpath->query('.//code[contains(text(), "Blueprint")]', $infoBlock);
    $blueprintPath = null;
    $blueprintName = null;
    $shortCode = null;

    foreach ($codeNodes as $code) {
        $text = $code->nodeValue;
        if (preg_match('/Blueprint\'([^\']+)\'/', $text, $matches)) {
            $blueprintFull = $matches[1]; // Game/...
            $blueprintClean = preg_replace('~^Game/~', '', $blueprintFull); // без Game/
            $blueprintName = $blueprintClean;
            $blueprintPath = dirname($blueprintClean);
            $blueprintFile = basename($blueprintClean);
            $shortCode = strtolower(preg_replace('/^PrimalItem(Skin)?_/', '', $blueprintFile));
            break;
        }
    }

    // Тип предмета (по html)
    $type = 'resource';
    if (str_contains($html, 'Категория:Скины')) $type = 'skin';
    elseif (str_contains($html, 'Категория:Структуры')) $type = 'structure';
    elseif (str_contains($html, 'Категория:Расходуемые')) $type = 'consumable';
    elseif (str_contains($html, 'Категория:Инвентарь')) $type = 'inventory';

    return [
        'imageName'      => $imageName,
        'imageUrl'       => $imgUrl,
        'blueprintPath'  => $blueprintPath,
        'blueprintName'  => $blueprintName,
        'shortCode'      => $shortCode,
        'type'           => $type,
    ];
}



function getShortCodeFromGFI(string $gfi): string
{
    if (preg_match('/cheat gfi (\w+)/i', $gfi, $m)) {
        return strtolower($m[1]);
    }
    return '';
}

function getBlueprintPath(string $html): array
{
    if (preg_match('/cheat giveitem \"Blueprint\'([^\']+)\'/i', $html, $m)) {
        return [
            'path' => $m[1],
            'folder' => preg_replace("/^\/Game\//", '', dirname($m[1]))
        ];
    }
    return ['', ''];
}

function downloadImage(string $url, string $folder, string $filename): bool
{
    $fullFolder = __DIR__ . '/img/' . $folder;
    if (!is_dir($fullFolder)) mkdir($fullFolder, 0777, true);
    $data = @file_get_contents($url);
    if (!$data) return false;
    return file_put_contents("$fullFolder/$filename", $data);
}

function fetchPageHtml(string $url): string
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36",
        CURLOPT_HTTPHEADER => [
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
        ],
        CURLOPT_ENCODING => "", // поддержка gzip/deflate
        CURLOPT_TIMEOUT => 10,
    ]);
    $html = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "Ошибка cURL при $url: " . curl_error($ch) . "\n";
    }
    curl_close($ch);
    return $html ?: '';
}


$base = "https://ark.fandom.com";
$listHtml = file_get_contents("items_table.html"); // сохранённая таблица
$xpath = getDomXPath($listHtml);
$rows = $xpath->query('//table[contains(@class,"cargo-item-table")]//tr[position()>1]');

$sql = [];
$id = 1000;

foreach ($rows as $row) {

    /** @var DOMElement $row */
    $cols = $row->getElementsByTagName("td");

    if ($cols->length < 3) continue;

    $link = $cols->item(0)->getElementsByTagName('a')->item(1)?->getAttribute('href') ?? '';
    $picTag = $cols->item(0)->getElementsByTagName('img')->item(0);
    $picUrl = $picTag?->getAttribute('src') ?? '';
    $pic = sanitizeFilename($picUrl);

    $NameRU = trim($cols->item(0)->textContent);
    echo $NameRU . PHP_EOL;
    $NameEN = preg_replace("/[^A-Za-z]/", ' ', pathinfo($pic, PATHINFO_FILENAME));
    $StackSize = is_numeric(trim($cols->item(1)->textContent)) ? trim($cols->item(1)->textContent) : 1;

    // Переход на вложенную страницу
    $pageUrl = $base . $link;

    $html = fetchPageHtml($pageUrl);
    $data = extractBlueprintInfoFromHtml($html);
    print_r($html);
    exit;

    //downloadImage("https:" . $data['imageUrl'], $data['blueprintPath'], $data['imageName']);
    print_r($data);
    exit;
    $sql[] = sprintf(
        "INSERT INTO `items` (`id`, `NameRU`, `NameEN`, `Pic`, `Price`, `ShortCode`, `Type`, `StackSize`, `Enable`, `Visible`, `HasQuality`, `Customizable`, `DefaultQuality`, `Note`, `Code`) VALUES " .
            "(%d, '%s', '%s', '%s', %d, '%s', '%s', %d, 1, 1, 0, 0, NULL, '', '%s');",
        $id++,
        addslashes($NameRU),
        trim($NameEN),
        $data['imageName'],
        rand(30, 150),
        $data['shortCode'],
        $data['type'],
        $StackSize,
        "$blueprintPath/$blueprintName"
    );
}
file_put_contents(__DIR__ . "/import_items.sql", implode("\n", $sql));
echo "✅ Готово! Строк SQL: " . count($sql) . "\n";
