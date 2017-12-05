<?php
/**
 * demo-file.php for cicache.
 * @author SamWu
 * @date 2017/12/5 10:00
 * @copyright istimer.com
 */

include '../vendor/autoload.php';

$config = [
	'handler' => 'predis',
	'backupHandler' => 'file',
	'path' => './cache',
	'prefix' => 'cache_predis_',

	'redis' => [
		'scheme' => 'tcp',
		'host' => '127.0.0.1',
		'password' => null,
		'port' => 6379,
		'timeout' => 0
	],
];

$cache = \Opdss\Cicache\Cache::factory($config);
$cache->save('test', 1000);
$cache->increment('test', 3);
$cache->decrement('test', 5);
$cache->save('str', '我是测试信息');
$cache->save('arr', array('a', 2, 'b', 3));
var_dump($cache->getMetaData('test'));
var_dump($cache->get('test'));
var_dump($cache->get('str'));
var_dump($cache->get('arr'));