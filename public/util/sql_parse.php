<?php

libxml_use_internal_errors(true);

// Путь к локальному HTML-файлу с таблицей
$html = file_get_contents("resources_table.html"); // или скопированный HTML как строка

$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// Получим все строки таблицы (кроме thead)
$rows = $xpath->query("//table[contains(@class,'wikitable')]//tr[position()>1]");

$id = 1000; // начальный id для вставки
$sql = [];

function extractShortCodeFromBlueprint(string $blueprintClass): string
{
    // Убираем префикс и суффикс
    $short = preg_replace('/^PrimalItem(?:Resource|Structure|Consumable|Skin|Weapon)?_/', '', $blueprintClass);
    $short = preg_replace('/_C$/', '', $short);
    return strtolower($short);
}


foreach ($rows as $row) {
    /** @var DOMElement $row */
    $cols = $row->getElementsByTagName("td");
    if ($cols->length < 6) continue;

    $nameEN = trim($cols->item(1)->textContent);
    $stackSize = trim($cols->item(2)->textContent);
    $picUrl = $cols->item(0)->getElementsByTagName('img')->item(0)?->getAttribute('data-src') ?? '';
    $pic = basename(strtok($picUrl, '?'));
    $blueprintClass = trim($cols->item(4)->textContent);

    // Генерация ShortCode
    $shortCode = strtolower(preg_replace('/[^a-z0-9]/', '', substr($nameEN, 0, 12)));

    // Устанавливаем цену условно от стэка
    $price = is_numeric($stackSize) && $stackSize > 0 ? intval(200 / $stackSize) * 5 : 100;
    $stack = is_numeric($stackSize) ? $stackSize : 1;

    // Генерация SQL строки
    $sql[] = sprintf(
        "INSERT INTO `items` (`id`, `NameRU`, `NameEN`, `Pic`, `Price`, `ShortCode`, `Type`, `StackSize`, `Enable`, `Visible`, `HasQuality`, `Customizable`, `DefaultQuality`, `Note`, `Code`) " .
            "VALUES (%d, '', '%s', '%s', %d, '%s', 'resource', %d, 1, 1, 0, 0, NULL, '', '%s');",
        $id++,
        addslashes($nameEN),
        $pic,
        $price,
        $shortCode,
        $stack,
        $blueprintClass
    );
}

// Сохраняем результат в файл
file_put_contents(__DIR__ . "/import_resources.sql", implode("\n", $sql));

echo "✅ Готово! Создан файл import_resources.sql с " . count($sql) . " строками.\n";
