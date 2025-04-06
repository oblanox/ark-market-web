<?php
function render(string $page, string $title = 'ARK Market'): void
{
    $pagePath = $_SERVER['DOCUMENT_ROOT'] . '/pages/' . $page;
    $pageTitle = $title;
    require $_SERVER['DOCUMENT_ROOT'] . '/layout.php';
}
