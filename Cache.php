<?php
/**
 * MemcachedHandler.php for cicache.
 * @author SamWu
 * @date 2017/12/5 15:47
 * @copyright istimer.com
 */


namespace Opdss\Cicache;

/**
 * Class Cache
 *
 * A factory for loading the desired
 *
 * @package CodeIgniter\Cache
 */
class Cache
{
	private static $defaultHandler = 'dummy';
	/**
	 * Attempts to create the desired cache handler, based upon the
	 *
	 * @param        $config
	 * @param string $handler
	 * @param string $backup
	 *
	 * @return CacheInterface
	 */
	public static function factory($config, $handler = null, $backup = null)
	{
		$handler = !empty($handler) ? $handler : (isset($config['handler']) ? $config['handler'] : '');

		if (!($handler = self::getHandler($handler))) {
			throw new \InvalidArgumentException('Cache.cacheHandlerNotFound');
		}

		// Get an instance of our handler.
		$adapter = new $handler($config);

		if (!$adapter->isSupported()) {
			$backup = !empty($backup) ? $backup : (isset($config['backupHandler']) ? $config['backupHandler'] : self::$defaultHandler);
			$backup = self::getHandler($backup) ?: self::getHandler(self::$defaultHandler);
			$adapter = new $backup($config);
			if (!$adapter->isSupported()) {
				// Log stuff here, don't throw exception. No need to raise a fuss.
				// Fall back to the dummy adapter.
				$defaultHandler = self::getHandler(self::$defaultHandler);
				$adapter = new $defaultHandler();
			}
		}

		$adapter->initialize();

		return $adapter;
	}

	private static function getHandler($handler)
	{
		if (empty($handler)) {
			return false;
		}
		$handlerPrefix = 'Opdss\\Cicache\\Handlers';
		$handler = $handlerPrefix.'\\'.ucfirst($handler).'Handler';
		if (!class_exists($handler)) {
			return false;
		}
		return $handler;
	}

	//--------------------------------------------------------------------
}
