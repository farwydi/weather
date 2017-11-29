<?php
/**
 * Created by PhpStorm.
 * User: zharikov
 * Date: 27.11.2017
 * Time: 19:10
 */

include_once __DIR__ . "/../vendor/autoload.php";
include_once __DIR__ . "/../src/weather-impl.php";

use DEX\Hook;
use Zend\Db\Adapter\Adapter;

try {
    $config = include __DIR__ . "/../config/current/common.php";

    $logger = new Zend\Log\Logger;
    $writer = new Zend\Log\Writer\Stream('php://output');

    $adapter_db = new Adapter($config['database']);

    $logger->addWriter($writer);

    $hook = new Hook(new WeatherImpl(0, $logger, $adapter_db), $config);

    $hook->do();
}
catch (Exception $e) {

}