<?php
/**
 * Array access storage useful as a wrapper around the superglobals for example
 *
 * PHP version 5.4
 *
 * @category   PHPoAuthConsole
 * @package    Storage
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace PHPoAuthConsole\Storage;

/**
 * Array access storage useful as a wrapper around the superglobals for example
 *
 * @category   PHPoAuthConsole
 * @package    Storage
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class ImmutableArray implements ImmutableKeyValue, \Iterator
{
    /**
     * @var array The array
     */
    private $array;

    /**
     * Creates instance
     *
     * @param array $array The array
     */
    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    /**
     * Gets a value from the storage based on the key or null on non existent key
     *
     * @param string $key     The key of which to get the value for
     * @param string $default The default value to return when the key does not exist
     *
     * @return mixed The value which belongs to the key or the default value
     */
    public function get($key, $default = null)
    {
        if ($this->isKeyValid($key)) {
            return $this->array[$key];
        }

        return $default;
    }

    /**
     * Checks whether the key is in the storage
     *
     * @return boolean true when the key is valid
     */
    public function isKeyValid($key)
    {
        return array_key_exists($key, $this->array);
    }

    /**
     * Rewinds the iterator
     */
    public function rewind()
    {
        reset($this->array);
    }

    /**
     * Gets the current iterator element
     *
     * @return mixed The current iterator element
     */
    public function current()
    {
        return current($this->array);
    }

    /**
     * Gets the key of the current iterator element
     *
     * @return mixed The key of the current iterator element
     */
    public function key()
    {
        return key($this->array);
    }

    /**
     * Advances the iterator to the next element
     */
    public function next()
    {
        next($this->array);
    }

    /**
     * Checks whether the current iterator position is valid
     *
     * @return boolean True when the current position is valid
     */
    public function valid()
    {
        return $this->key() !== null;
    }
}
