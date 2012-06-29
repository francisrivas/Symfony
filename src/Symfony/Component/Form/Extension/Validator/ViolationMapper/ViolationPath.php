<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Validator\ViolationMapper;

use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\Util\PropertyPathInterface;
use Symfony\Component\Form\Util\PropertyPathIterator;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ViolationPath implements \IteratorAggregate, PropertyPathInterface
{
    /**
     * @var array
     */
    private $elements = array();

    /**
     * @var array
     */
    private $positions = array();

    /**
     * @var array
     */
    private $isIndex = array();

    /**
     * @var array
     */
    private $mapsForm = array();

    /**
     * @var string
     */
    private $pathAsString = '';

    /**
     * @var integer
     */
    private $length = 0;

    /**
     * Creates a new violation path from a string.
     *
     * @param string $violationPath The property path of a {@link ConstraintViolation}
     *                              object.
     */
    public function __construct($violationPath)
    {
        $path = new PropertyPath($violationPath);
        $elements = $path->getElements();
        $positions = $path->getPositions();
        $data = false;

        for ($i = 0, $l = count($elements); $i < $l; ++$i) {
            if (!$data) {
                // The element "data" has not yet been passed
                if ('children' === $elements[$i] && $path->isProperty($i)) {
                    // Skip element "children"
                    ++$i;

                    // Next element must exist and must be an index
                    // Otherwise consider this the end of the path
                    if ($i >= $l || !$path->isIndex($i)) {
                        break;
                    }

                    $this->elements[] = $elements[$i];
                    $this->positions[] = $positions[$i];
                    $this->isIndex[] = true;
                    $this->mapsForm[] = true;
                } elseif ('data' === $elements[$i] && $path->isProperty($i)) {
                    // Skip element "data"
                    ++$i;

                    // End of path
                    if ($i >= $l) {
                        break;
                    }

                    $this->elements[] = $elements[$i];
                    $this->positions[] = $positions[$i];
                    $this->isIndex[] = $path->isIndex($i);
                    $this->mapsForm[] = false;
                    $data = true;
                } else {
                    // Neither "children" nor "data" property found
                    // Consider this the end of the path
                    break;
                }
            } else {
                // Already after the "data" element
                // Pick everything as is
                $this->elements[] = $elements[$i];
                $this->positions[] = $positions[$i];
                $this->isIndex[] = $path->isIndex($i);
                $this->mapsForm[] = false;
            }
        }

        $this->length = count($this->elements);
        $this->pathAsString = $violationPath;

        $this->resizeString();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->pathAsString;
    }

    /**
     * {@inheritdoc}
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        if ($this->length <= 1) {
            return null;
        }

        $parent = clone $this;

        --$parent->length;
        array_pop($parent->elements);
        array_pop($parent->isIndex);
        array_pop($parent->mapsForm);
        array_pop($parent->positions);

        $parent->resizeString();

        return $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * {@inheritdoc}
     */
    public function getElement($index)
    {
        if (!isset($this->elements[$index])) {
            throw new \OutOfBoundsException('The index ' . $index . ' is not within the violation path');
        }

        return $this->elements[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function isProperty($index)
    {
        if (!isset($this->isIndex[$index])) {
            throw new \OutOfBoundsException('The index ' . $index . ' is not within the violation path');
        }

        return !$this->isIndex[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function isIndex($index)
    {
        if (!isset($this->isIndex[$index])) {
            throw new \OutOfBoundsException('The index ' . $index . ' is not within the violation path');
        }

        return $this->isIndex[$index];
    }

    /**
     * Returns whether an element maps directly to a form.
     *
     * Consider the following violation path:
     *
     * <code>
     * children[address].children[office].data.street
     * </code>
     *
     * In this example, "address" and "office" map to forms, while
     * "street does not.
     *
     * @param  integer $index The element index.
     *
     * @return Boolean Whether the element maps to a form.
     *
     * @throws \OutOfBoundsException If the offset is invalid.
     */
    public function mapsForm($index)
    {
        if (!isset($this->mapsForm[$index])) {
            throw new \OutOfBoundsException('The index ' . $index . ' is not within the violation path');
        }

        return $this->mapsForm[$index];
    }


    /**
     * Returns a new iterator for this path
     *
     * @return ViolationPathIterator
     */
    public function getIterator()
    {
        return new ViolationPathIterator($this);
    }

    /**
     * Resizes the string representation to match the number of elements.
     */
    private function resizeString()
    {
        $lastIndex = $this->length - 1;

        if ($lastIndex < 0) {
            $this->pathAsString = '';
        } else {
            // +1 for the dot/opening bracket
            $length = $this->positions[$lastIndex] + strlen($this->elements[$lastIndex]) + 1;

            if ($this->isIndex[$lastIndex]) {
                // +1 for the closing bracket
                ++$length;
            }

            $this->pathAsString = substr($this->pathAsString, 0, $length);
        }
    }
}
