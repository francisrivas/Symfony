<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Finder\Iterator;

/**
 * ReverseIterator returns the input with the order of the elements reversed.
 *
 * @author stealth35 <jinmoku@hotmail.com>
 */
class ReverseIterator implements \IteratorAggregate
{
     private $iterator;
     private $preserve_keys = false;

    /**
     * Constructor.
     *
     * @param \Traversable $iterator The \Traversable to reverse
     * @param Boolean $preserve_keys If set to TRUE keys are preserved
     */
    public function __construct(\Traversable $iterator, $preserve_keys = false)
    {
        $this->iterator = $iterator;
        $this->preserve_keys = (Boolean) $preserve_keys;
    }

    /**
     * Return an \ArrayIterator with elements in reverse order
     *
     * @return \ArrayIterator Returns the reversed input
     */
    public function getIterator()
    {
        $array = iterator_to_array($this->iterator, $this->preserve_keys);
        $reverse = array_reverse($array, $this->preserve_keys);

        return new \ArrayIterator($reverse);
    }
}
