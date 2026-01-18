<?php
// Парсер таблицы предметов ARK из items_table.html
libxml_use_internal_errors(true);


$html = file_get_contents(__DIR__ . '/all_items.html');
$dom = new DOMDocument();
$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$xpath = new DOMXPath($dom);

$rows = $xpath->query('//table[contains(@class,"wikitable")]//tr');
$sql = [];
$id = 1;

foreach ($rows as $row) {
    /** @var DOMElement $row */
    $cells = $row->getElementsByTagName('td');
    if ($cells->length < 6) continue;

    $linkTag = $cells->item(0)->getElementsByTagName('a')->item(0);
    $imgTag = $cells->item(0)->getElementsByTagName('img')->item(0);
    if (!$linkTag || !$imgTag) continue;

    $links = $cells->item(0)->getElementsByTagName('a');
    $linkTag = $links->item($links->length - 1); // последний <a>
    $NameRU = $linkTag ? trim($linkTag->textContent) : '';

    $NameEN = $linkTag->getAttribute('title');
    $pic = basename($imgTag->getAttribute('data-image-name') ?: $imgTag->getAttribute('src'));
    $StackSize = (int)trim($cells->item(2)->nodeValue);
    $className = trim($cells->item(4)->nodeValue);
    $blueprintRaw = trim($cells->item(5)->nodeValue);


    if (!preg_match("/Blueprint'?\/?([^']+)'/", $blueprintRaw, $match)) continue;

    $blueprintName = $match[1];
    $blueprintPath = dirname($blueprintName);
    $shortCode = strtolower(preg_replace('/^PrimalItem(Skin)?_/', '', basename($blueprintName)));

    $type = 'resource'; // заглушка
    $code = "cheat giveitem \"Blueprint'/Game/{$blueprintName}'\" 1 0 0";

    $sql[] = sprintf(
        "INSERT INTO `items` (`id`, `NameRU`, `NameEN`, `Pic`, `Price`, `ShortCode`, `Type`, `StackSize`, `Enable`, `Visible`, `HasQuality`, `Customizable`, `DefaultQuality`, `Note`, `Code`) " .
            "VALUES (%d, '%s', '%s', '%s', %d, '%s', '%s', %d, 1, 1, 0, 0, NULL, '', '%s');",
        $id++,
        trim($NameRU),
        trim($NameEN),
        $pic,
        rand(30, 150),
        $shortCode,
        $type,
        $StackSize,
        $code
    );
}

file_put_contents(__DIR__ . "/import_items.sql", implode("\n", $sql));
echo "✅ Готово! Строк SQL: " . count($sql) . "\n";
