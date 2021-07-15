<?php

use Seshat\AnubisAjax;
use Seshat\IsisDB;
use Seshat\WadjetWiki;

require_once '../include/seshat-conf.php';

$db = IsisDB::open('SQLite3',DB_SQLITE3);
$set = new AnubisAjax($db,'text/plain');
$wiki = new WadjetWiki();
$wiki->loadText();

AnubisAjax::output($wiki->getText());

