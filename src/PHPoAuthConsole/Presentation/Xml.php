<?php
/**
 * A simple XML prettyfier
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
 * A simple XML prettyfier
 *
 * @category   PHPoAuthConsole
 * @package    Presentation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Xml
{
    /**
     * Parses raw XML and outputs a pretty xml
     *
     * @param string $xml The xml to prettify
     *
     * @return string The prettified xml
     */
    public function parse($xml)
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml);

        return $foo->saveXML();
    }
}
