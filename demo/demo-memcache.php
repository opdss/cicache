<?php
/**
 * demo-file.php for cicache.
 * @author SamWu
 * @date 2017/12/5 10:00
 * @copyright istimer.com
 */

include '../vendor/autoload.php';

$config = [
	'handler' => 'memcached',
	'backupHandler' => 'file',
	'path' => './cache',
	'prefix' => 'cache_memcache_',

	'host' => '127.0.0.1',
	'port' => 11211,
	'weight' => 1,
	'raw' => false,
];

$cache = \Opdss\Cicache\Cache::factory($config);

$cache->save('test', 1000);
$cache->increment('test', 3);
$cache->decrement('test', 5);
$cache->save('str', '23232322323');
var_dump($cache->getMetaData('test'));
var_dump($cache->get('test'));
var_dump($cache->get('str'));