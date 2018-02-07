<?php
/**
 * WincacheHandler.php for cicache.
 * @author SamWu
 * @date 2017/12/5 15:47
 * @copyright istimer.com
 */

namespace Opdss\Cicache\Handlers;

use Opdss\Cicache\CacheInterface;

class WincacheHandler implements CacheInterface
{

	/**
	 * Prefixed to all cache names.
	 *
	 * @var string
	 */
	protected $prefix;

	//--------------------------------------------------------------------

	public function __construct(array $config)
	{
		$this->prefix = isset($config['prefix']) ? $config['prefix'] : '';
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
		// Nothing to see here...
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

		$success = false;
		$data = wincache_ucache_get($key, $success);

		// Success returned by reference from wincache_ucache_get()
		return ($success) ? $data : false;
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

		return wincache_ucache_set($key, $value, $ttl);
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

		return wincache_ucache_delete($key);
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
		$key = $this->prefix . $key;

		$success = false;
		$value = wincache_ucache_inc($key, $offset, $success);

		return ($success === true) ? $value : false;
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
		$key = $this->prefix . $key;

		$success = false;
		$value = wincache_ucache_dec($key, $offset, $success);

		return ($success === true) ? $value : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean()
	{
		return wincache_ucache_clear();
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
		return wincache_ucache_info(true);
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

		if ($stored = wincache_ucache_info(false, $key)) {
			$age = $stored['ucache_entries'][1]['age_seconds'];
			$ttl = $stored['ucache_entries'][1]['ttl_seconds'];
			$hitcount = $stored['ucache_entries'][1]['hitcount'];

			return [
				'expire' => $ttl - $age,
				'hitcount' => $hitcount,
				'age' => $age,
				'ttl' => $ttl,
			];
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported()
	{
		return (extension_loaded('wincache') && ini_get('wincache.ucenabled'));
	}

	//--------------------------------------------------------------------
}
