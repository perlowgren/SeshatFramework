<?php

require_once __DIR__.'/include/seshat-conf.php';

use Seshat\Ra;
use Seshat\IsisDB;

$db = IsisDB::open('SQLite3',DB_SQLITE3);

$ra = new Ra($db);

require_once Ra::getPagePath();

Ra::quit();

