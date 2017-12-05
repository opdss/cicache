<?php
/**
 * demo-file.php for cicache.
 * @author SamWu
 * @date 2017/12/5 10:00
 * @copyright istimer.com
 */

include '../vendor/autoload.php';

$config = [
	'handler' => 'file',
	'backupHandler' => 'dummy',
	'path' => './cache',
	'prefix' => 'cache_file_'
];

$cache = \Opdss\Cicache\Cache::factory($config);

$cache->save('test', 1000);
$cache->increment('test', 3);
$cache->decrement('test', 4);
$cache->save('str', '23232322323');
var_dump($cache->get('test'));
var_dump($cache->get('str'));
var_dump($cache->getMetaData('test'));