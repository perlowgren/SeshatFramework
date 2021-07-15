<?php

use Seshat\AnubisAjax;
use Seshat\IsisDB;
use Seshat\MaatMark;

require_once '../include/seshat-conf.php';

$text = file_get_contents('php://input');

$db = IsisDB::open('SQLite3',DB_SQLITE3);
$sess = new AnubisAjax($db,'text/html');

$maat = new MaatMark();
$html = $maat->parse($text);

AnubisAjax::output($html);

