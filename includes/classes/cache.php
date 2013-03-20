<?php
/**
 * Cache class
 *
 * @author Brett O'Donnell - cornernote@gmail.com
 * @copyright 2013, All Rights Reserved
 *
 */
class cache
{

    /**
     * @var array
     */
    public $memcacheOptions = array(
        'host' => 'localhost',
        'port' => '11211',

        // prevent conflicts when sharing the same memcache server
        'namespace' => 'unique-for-your-app',
    );

    /**
     * @var array
     */
    public $filecacheOptions = array(
        'path' => '/tmp/cache',
    );

    /**
     * Stores instance of static object
     *
     * @var cache
     */
    private static $_cache;

    /**
     * Stores the cache_id as a prefix so cache can be cleared
     *
     * @var string
     */
    private $_cache_id;

    /**
     * Stores the memcache connection
     *
     * @var string
     */
    private $_memcache;

    /**
     * Connect to memcache
     */
    public function __construct()
    {
        if (class_exists('Memcache')) {
            $this->_memcache = new Memcache;
            @$this->_memcache->connect($this->memcacheOptions['host'], $this->memcacheOptions['port']) or ($this->_memcache = false);
        }
    }

    /**
     * Initialize and set options
     */
    public static function init($options = array())
    {
        if (self::$_cache) return self::$_cache;
        self::$_cache = new cache();
        foreach ($options as $k => $v) {
            self::$_cache->$k = $v;
        }
        return self::$_cache;
    }

    /**
     * Get a cache key
     *
     * @param $key
     * @return array|bool|mixed|string
     */
    public static function get($key)
    {
        // assume null
        $value = false;

        // convert key
        $key = self::init()->getKey($key);

        // get the ttl
        $ttl = 0;
        if (self::$_cache->_memcache) {
            $ttl = self::$_cache->_memcache->get($key . '.ttl');
        }
        else if (file_exists($key . '.ttl')) {
            $ttl = file_get_contents($key . '.ttl');
        }

        // it lives!
        if ($ttl >= time()) {
            // memcache
            if (self::$_cache->_memcache) {
                $value = self::$_cache->_memcache->get($key . '.data');
            }
            // filecache
            else if (file_exists($key . '.data')) {
                $value = unserialize(file_get_contents($key . '.data'));
            }
        }

        // return cached value
        return $value;
    }

    /**
     * Set a cache key
     *
     * @param $key
     * @param $value
     * @param string $ttl
     * @return mixed
     */
    public static function set($key, $value, $ttl = null)
    {
        // convert key
        $key = self::$_cache->getKey($key);

        // set the expire time
        $ttl = $ttl ? $ttl : '+1 hour';
        if (is_numeric($ttl)) {
            $ttl += time();
        }
        else {
            $ttl = strtotime($ttl, time());
        }

        // memcache
        if (self::$_cache->_memcache) {
            self::$_cache->_memcache->set($key . '.data', $value);
            self::$_cache->_memcache->set($key . '.ttl', $ttl);
        }
        // filecache
        else {
            if (!file_exists(dirname($key))) {
                mkdir(dirname($key), 0700, true);
            }
            file_put_contents($key . '.data', serialize($value));
            file_put_contents($key . '.ttl', $ttl);
        }

        // return the data
        return $value;
    }

    /**
     * Delete a cache key
     *
     * @param $key
     */
    public static function delete($key)
    {
        // convert key
        $key = self::$_cache->getKey($key);

        // memcache
        if (self::$_cache->_memcache) {
            self::$_cache->_memcache->delete($key . '.time');
            self::$_cache->_memcache->delete($key . '.data');
        }

        // filecache
        else {
            if (file_exists($key . '.time')) unlink($key . '.time');
            if (file_exists($key . '.data')) unlink($key . '.data');
        }

    }


    /**
     * Clear all cache by resetting the cache_id prefix
     */
    public static function clear()
    {
        // delete the cache_id will unlink all the cache
        self::$_cache->delete('_cache_id');
        self::$_cache->_cache_id = null;
    }

    /**
     * Get the cache_id used for prefixing all other cache keys
     *
     * @param $key
     * @return string
     */
    private function getKey($key)
    {
        // do not process internal cache_id
        if ($key != '_cache_id') {
            if (!$this->_cache_id) {
                $this->_cache_id = $this->get('_cache_id');
            }
            if (!$this->_cache_id) {
                $cache_id = md5(microtime());
                $this->set('_cache_id', $cache_id);
            }
            $key = $this->_cache_id . '.' . $key;
        }

        // memcache
        if ($this->_memcache) {
            $key = $this->memcacheOptions['namespace'] . '.' . $key;
        }

        // filecache
        else {
            $md5 = md5($key);
            $key = $this->filecacheOptions['path'] . '/' . substr($md5, 0, 1) . '/' . substr($md5, 0, 2) . '/' . substr($md5, 0, 3) . '/' . $key;
        }

        return $key;
    }

}
