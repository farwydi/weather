<?php
/**
 * Created by PhpStorm.
 * User: zharikov
 * Date: 17.11.2017
 * Time: 11:42
 */

return array(
    'url' => "https://www.gismeteo.ru/city/weekly/",
    'database' => [
        'driver' => 'Pdo',
        'dsn' => 'pgsql:host=postgres-db;dbname=meteo',
        'username' => 'dex',
        'password' => 'd1'
    ]
);