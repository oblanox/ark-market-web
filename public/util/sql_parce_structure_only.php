<?php

libxml_use_internal_errors(true);

function getDomXPath(string $html): DOMXPath
{
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    return new DOMXPath($dom);
}

function fetchPageHtml(string $url): string
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => "Mozilla/5.0",
        CURLOPT_TIMEOUT => 10,
    ]);
    $html = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "❌ cURL: " . curl_error($ch) . " ($url)\n";
    }
    curl_close($ch);
    return $html ?: '';
}

function sanitizeTextForSQL(string $text): string
{
    // Декодируем HTML-сущности
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Удаляем все переносы строк, табы
    $text = preg_replace('/[\r\n\t]+/', ' ', $text);

    // Удаляем управляющие символы (Unicode category C), кроме пробелов
    $text = preg_replace('/\p{C}+/u', '', $text);

    // Приводим к одному пробелу между словами
    $text = preg_replace('/ {2,}/', ' ', $text);

    return trim($text);
}

function extractStructureItemFromHtml(string $html): array
{
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);

    $code = '';
    $blueprintPath = '';
    $shortCode = '';
    $description = '';
    $type = 'structure';
    $nameEN = '';

    // GFI-код
    $codeNodes = $xpath->query('//span[contains(@class,"copy-content")]');
    foreach ($codeNodes as $codeNode) {
        $text = $codeNode->nodeValue;
        $text = html_entity_decode($text);
        $text = str_replace(['\"', "\'"], ['"', "'"], $text);

        if (preg_match("/Blueprint'\/Game\/(?:PrimalEarth\/)?(.*?)\.PrimalItemStructure_.*?'/", $text, $matches)) {
            $code = $matches[1];
        }

        if (preg_match("/Blueprint'\/Game\/(.*?)'/", $text, $matches)) {
            $blueprintPath = $matches[1];
            $bpFile = basename($blueprintPath);
            $shortCode = strtolower(preg_replace('/^PrimalItem(Skin|Structure)?_/', '', pathinfo($bpFile, PATHINFO_FILENAME)));
            break;
        }
    }



    // EN имя
    $imgNode = $xpath->query('//div[contains(@class, "info-arkitex")]//img[contains(@class, "mw-file-element") and @data-image-name]')->item(0);
    if ($imgNode instanceof DOMElement) {
        $imgName = $imgNode->getAttribute('data-image-name');
        $nameEN = preg_replace(['/\.png$/i', '/_/'], ['', ' '], $imgName);
    }

    // описание
    $descNode = $xpath->query('//div[contains(@class,"info-arkitex") and contains(@class,"info-framework")]//i')->item(0);
    if ($descNode) {
        $description = trim($descNode->textContent);
    } else {
        $metaDesc = $xpath->query('//meta[@property="og:description"]')->item(0);
        if ($metaDesc instanceof DOMElement) {
            $description = $metaDesc->getAttribute('content');
        }
    }

    return compact('blueprintPath', 'shortCode', 'description', 'type', 'code', 'nameEN');
}

// =========================== ПАРСИНГ ===========================

$listHtml = file_get_contents("items_structure.html");
$xpath = getDomXPath($listHtml);
$links = $xpath->query('//div[contains(@class,"mw-category-group")]//li/a');

$id = 1;
$sql = [];
$errors = [];
$unique = [];
$count = 0;
foreach ($links as $link) {
    /** @var DOMElement $link */
    $nameRU = trim(sanitizeTextForSQL($link->nodeValue));
    $href = $link->getAttribute('href');
    $url = 'https://ark.fandom.com' . $href;

    echo "🔍 [$id] $nameRU...\n";
    $count++;
    //if ($count >= 10) break; // ограничение по тесту

    $html = fetchPageHtml($url);
    if (!str_contains($html, 'Blueprint')) {
        $errors[] = "❌ Blueprint не найден: $nameRU ($url)";
        continue;
    }

    $data = extractStructureItemFromHtml($html);

    if (empty($data['blueprintPath'])) {
        $errors[] = "❌ Blueprint пуст: $nameRU ($url)";
        continue;
    }
    if (str_contains($nameRU, 'Primitive Plus')) {
        $errors[] = "❌ Blueprint от мода: $nameRU ($url)";
        continue;
    }
    if (in_array($data['shortCode'], $unique)) {
        $errors[] = "❌ Значение не уникально: $nameRU ($url)";
        continue;
    }


    if (empty($nameEN)) $nameEN = $id;
    $unique[] = $data['shortCode'];
    $sql[] = sprintf(
        "INSERT INTO `items` (`id`, `NameRU`, `NameEN`, `Pic`, `Price`, `ShortCode`, `Type`, `StackSize`, `Enable`, `Visible`, `HasQuality`, `Customizable`, `DefaultQuality`, `Note`, `Code`) VALUES " .
            "(%d, '%s', '%s', '%s', %d, '%s', '%s', %d, 1, 1, 0, 0, 'Primitive', '%s', '%s');",
        $id++,
        addslashes($nameRU),
        addslashes($data['nameEN']),
        'no-photo.png',
        rand(80, 150),
        addslashes($data['shortCode']),
        $data['type'],
        1,
        addslashes($data['description']),
        addslashes($data['code'])
    );
}

// ======================== ВЫВОД ===========================

file_put_contents(__DIR__ . "/items_structure.sql", implode("\n", $sql));
if (!empty($errors)) {
    file_put_contents(__DIR__ . "/items_structure_errors.log", implode("\n", $errors));
}

echo "=========================\n";
echo "✅ Всего: " . count($sql) . " строк. Ошибок: " . count($errors) . "\n";
