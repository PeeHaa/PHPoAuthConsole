<?php
/**
 * A simple dump prettyfier
 *
 * PHP version 5.4
 *
 * @category   PHPoAuthConsole
 * @package    Presentation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace PHPoAuthConsole\Presentation;

/**
 * A simple dump prettyfier
 *
 * @category   PHPoAuthConsole
 * @package    Presentation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Dump
{
    /**
     * @var string The indentation character(s)
     */
    const INDENT = '    ';

    /**
     * @var int The current indentation level
     */
    private $level = 0;

    /**
     * Parses raw variables and outputs a pretty dump
     *
     * @param mixed $data The data to prettify
     *
     * @return string The prettified data
     */
    public function parse($data)
    {
        if (is_object($data)) {
            return $this->parseObject($data);
        } elseif (is_resource($data)) {
            // @todo
        } elseif (is_array($data)) {
            return $this->parseArray($data);
        } else {
            return $this->parseScalar($data);
        }
    }

    /**
     * Parses scalar values
     *
     * @param mixed $data The scalar value
     *
     * @return string The prettified scalar value
     */
    private function parseScalar($data)
    {
        if (is_string($data)) {
            return '<span class="type">string(' . strlen($data) . ')</span> "<span class="value">' . $data . '</span>"' . "\n";
        }

        if (is_null($data)) {
            return '<span class="value">NULL</span>' . "\n";
        }

        return '<span class="type">int(<span class="value">' . $data . '</span>)</span>' . "\n";
    }

    /**
     * Parses arrays
     *
     * @param array $data The array
     *
     * @return string The prettified array
     */
    private function parseArray(array $data)
    {
        $output = '<span class="type">array(' . count($data) . ') {</span>' . "\n";

        $this->level++;

        foreach ($data as $key => $value) {
            $output .= str_repeat(self::INDENT, $this->level) . '[<span class="key">' . $key . '</span>] => ' . $this->parse($value);
        }

        $this->level--;

        $output .= str_repeat(self::INDENT, $this->level) . '<span class="type">}</span>' . "\n";

        return $output;
    }

    /**
     * Parses objects
     *
     * @param object $data The object
     *
     * @return string The prettified object
     */
    private function parseObject($data)
    {
        $output = str_repeat(self::INDENT, $this->level) . '<span class="type">object(' . get_class($data) . ') (' . count($data) . ') {<span>' . "\n";

        $this->level++;

        foreach ($data as $key => $value) {
            $output .= str_repeat(self::INDENT, $this->level) . '[<span class="key">' . $key . '</span>] => ' . $this->parse($value);
        }

        $this->level--;

        $output .= str_repeat(self::INDENT, $this->level) . '<span class="type">}</span>' . "\n";

        return $output;
    }
}
