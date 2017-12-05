<?php
/**
 * PredisHandler.php for cicache.
 * @author SamWu
 * @date 2017/12/5 15:47
 * @copyright istimer.com
 */

namespace Opdss\Cicache\Handlers;

use Opdss\Cicache\CacheInterface;

class PredisHandler implements CacheInterface
{

	/**
	 * Prefixed to all cache names.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Default config
	 *
	 * @static
	 * @var    array
	 */
	protected $config = [
		'scheme' => 'tcp',
		'host' => '127.0.0.1',
		'password' => null,
		'port' => 6379,
		'timeout' => 0,
	];

	/**
	 * Predis connection
	 *
	 * @var    Predis
	 */
	protected $redis;

	//--------------------------------------------------------------------

	public function __construct($config)
	{
		$this->prefix = $config['prefix'] ?: '';

		if (isset($config['redis'])) {
			$this->config = array_merge($this->config, $config['redis']);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
		try {
			// Create a new instance of Predis\Client
			$this->redis = new \Predis\Client($this->config, ['prefix' => $this->prefix]);

			// Check if the connection is valid by trying to get the time.
			$this->redis->time();
		} catch (\Exception $e) {
			// thrown if can't connect to redis server.
			throw new \Exception('Cache: Predis connection refused (' . $e->getMessage() . ')');
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		$data = array_combine(
			['__ci_type', '__ci_value'], $this->redis->hmget($key, ['__ci_type', '__ci_value'])
		);

		if (!isset($data['__ci_type'], $data['__ci_value']) OR $data['__ci_value'] === false) {
			return false;
		}

		switch ($data['__ci_type']) {
			case 'array':
			case 'object':
				return unserialize($data['__ci_value']);
			case 'boolean':
			case 'integer':
			case 'double': // Yes, 'double' is returned and NOT 'float'
			case 'string':
			case 'NULL':
				return settype($data['__ci_value'], $data['__ci_type']) ? $data['__ci_value'] : false;
			case 'resource':
			default:
				return false;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * @param string $key Cache item name
	 * @param mixed $value The data to save
	 * @param int $ttl Time To Live, in seconds (default 60)
	 *
	 * @return mixed
	 */
	public function save($key, $value, $ttl = 60)
	{
		switch ($data_type = gettype($value)) {
			case 'array':
			case 'object':
				$value = serialize($value);
				break;
			case 'boolean':
			case 'integer':
			case 'double': // Yes, 'double' is returned and NOT 'float'
			case 'string':
			case 'NULL':
				break;
			case 'resource':
			default:
				return false;
		}

		if (!$this->redis->hmset($key, ['__ci_type' => $data_type, '__ci_value' => $value])) {
			return false;
		}

		$this->redis->expireat($key, time() + $ttl);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function delete($key)
	{
		return ($this->redis->del($key) === 1);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string $key Cache ID
	 * @param int $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function increment($key, $offset = 1)
	{
		return $this->redis->hincrby($key, '__ci_value', $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string $key Cache ID
	 * @param int $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function decrement($key, $offset = 1)
	{
		return $this->redis->hincrby($key, '__ci_value', -$offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean()
	{
		return $this->redis->flushdb();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 *
	 * @return mixed
	 */
	public function getCacheInfo()
	{
		return $this->redis->info();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 *
	 * @param string $key Cache item name.
	 *
	 * @return mixed
	 */
	public function getMetaData($key)
	{
		$data = array_combine(['__ci_value'], $this->redis->hmget($key, ['__ci_value']));

		if (isset($data['__ci_value']) AND $data['__ci_value'] !== false) {
			return [
				'expire' => time() + $this->redis->ttl($key),
				'data' => $data['__ci_value']
			];
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported()
	{
		return class_exists('\Predis\Client');
	}

}
