<?php
/**
 * MemcachedHandler.php for cicache.
 * @author SamWu
 * @date 2017/12/5 15:47
 * @copyright istimer.com
 */

namespace Opdss\Cicache\Handlers;

use Opdss\Cicache\CacheInterface;

class MemcachedHandler implements CacheInterface
{

	/**
	 * Prefixed to all cache names.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * The memcached object
	 *
	 * @var \Memcached|\Memcache
	 */
	protected $memcached;

	/**
	 * Memcached Configuration
	 *
	 * @var array
	 */
	protected $config = [
		'host' => '127.0.0.1',
		'port' => 11211,
		'weight' => 1,
		'raw' => false,
	];

	//--------------------------------------------------------------------

	public function __construct(array $config)
	{
		$this->prefix = $config['prefix'] ?: '';

		if (!empty($config)) {
			$this->config = array_merge($this->config, $config);
		}
	}

	/**
	 * Class destructor
	 *
	 * Closes the connection to Memcache(d) if present.
	 */
	public function __destruct()
	{
		if ($this->memcached instanceof \Memcached) {
			$this->memcached->quit();
		} elseif ($this->memcached instanceof \Memcache) {
			$this->memcached->close();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
		if (class_exists('\Memcached')) {
			$this->memcached = new \Memcached();
			if ($this->config['raw']) {
				$this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
			}
		} elseif (class_exists('\Memcache')) {
			$this->memcached = new \Memcache();
		} else {
			throw new \Exception('Cache: Not support Memcache(d) extension.');
		}

		if ($this->memcached instanceof \Memcached) {
			$this->memcached->addServer(
				$this->config['host'], $this->config['port'], $this->config['weight']
			);
		} elseif ($this->memcached instanceof \Memcache) {
			// Third parameter is persistance and defaults to TRUE.
			$this->memcached->addServer(
				$this->config['host'], $this->config['port'], true, $this->config['weight']
			);
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
		$key = $this->prefix . $key;

		$data = $this->memcached->get($key);

		return is_array($data) ? $data[0] : $data;
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
		$key = $this->prefix . $key;

		if (!$this->config['raw']) {
			$value = [$value, time(), $ttl];
		}

		if ($this->memcached instanceof \Memcached) {
			return $this->memcached->set($key, $value, $ttl);
		} elseif ($this->memcached instanceof \Memcache) {
			return $this->memcached->set($key, $value, 0, $ttl);
		}

		return false;
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
		$key = $this->prefix . $key;

		return $this->memcached->delete($key);
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
		if (!$this->config['raw']) {
			return false;
		}

		$key = $this->prefix . $key;

		return $this->memcached->increment($key, $offset, $offset, 60);
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
		if (!$this->config['raw']) {
			return false;
		}

		$key = $this->prefix . $key;

		//FIXME: third parameter isn't other handler actions.
		return $this->memcached->decrement($key, $offset, $offset, 60);
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean()
	{
		return $this->memcached->flush();
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
		return $this->memcached->getStats();
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
		$key = $this->prefix . $key;

		$stored = $this->memcached->get($key);

		if (count($stored) !== 3) {
			return FALSE;
		}

		list($data, $time, $ttl) = $stored;

		return [
			'expire' => $time + $ttl,
			'mtime' => $time,
			'data' => $data
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported()
	{
		return (extension_loaded('memcached') OR extension_loaded('memcache'));
	}

}
