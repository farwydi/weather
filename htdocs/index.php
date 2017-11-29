<?php
/**
 * Created by PhpStorm.
 * User: zharikov
 * Date: 27.11.2017
 * Time: 12:11
 */

set_time_limit(480);
ini_set("html_errors", 0);
header('Content-Type: text/plain; charset=utf-8');

include_once __DIR__ . "/../vendor/autoload.php";

use Zend\Db\Adapter\Adapter;

$db = new Adapter([
    'driver' => 'Pdo',
    'dsn' => 'pgsql:host=postgres-db;dbname=meteo',
    'username' => 'dex',
    'password' => 'd1'
]);

$res = $db->query("SELECT * FROM forecast",
    Adapter::QUERY_MODE_EXECUTE);
$res = $res->current();

var_dump($res);