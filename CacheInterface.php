<?php
/**
 * MemcachedHandler.php for cicache.
 * @author SamWu
 * @date 2017/12/5 15:47
 * @copyright istimer.com
 */


namespace Opdss\Cicache;

interface CacheInterface
{

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize();

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 *
	 * @param string $key  Cache item name
	 *
	 * @return mixed
	 */
	public function get($key);

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * @param string $key   Cache item name
	 * @param mixed  $value The data to save
	 * @param int    $ttl   Time To Live, in seconds (default 60)
	 *
	 * @return mixed
	 */
	public function save($key, $value, $ttl = 60);

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 *
	 * @param string $key  Cache item name
	 *
	 * @return mixed
	 */
	public function delete($key);

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string $key    Cache ID
	 * @param int    $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function increment($key, $offset = 1);

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string $key    Cache ID
	 * @param int    $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function decrement($key, $offset = 1);

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean();

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 *
	 * @return mixed
	 */
	public function getCacheInfo();

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 *
	 * @param string $key  Cache item name.
	 *
	 * @return mixed
	 */
	public function getMetaData($key);

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported();

	//--------------------------------------------------------------------
}
