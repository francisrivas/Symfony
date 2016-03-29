<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Config;

use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
use Symfony\Component\DependencyInjection\Compiler\AutowirePass;

class AutowireServiceResource implements SelfCheckingResourceInterface, \Serializable
{
    private $class;
    private $filePath;
    private $autowiringMetadata = array();

    public function __construct($class, $path, array $autowiringMetadata)
    {
        $this->class = $class;
        $this->filePath = $path;
        $this->autowiringMetadata = $autowiringMetadata;
    }

    public function isFresh($timestamp)
    {
        if (!file_exists($this->filePath)) {
            return false;
        }

        // has the file *not* been modified? Definitely fresh
        if (@filemtime($this->filePath) <= $timestamp) {
            return true;
        }

        try {
            $reflectionClass = new \ReflectionClass($this->class);
        } catch (\ReflectionException $e) {
            // the class does not exist anymore!

            return false;
        }

        $newResource = AutowirePass::createResourceForClass($reflectionClass);

        return $newResource == $this;
    }

    public function __toString()
    {
        return 'service.autowire.'.$this->class;
    }

    public function serialize()
    {
        return serialize(array(
            $this->class,
            $this->filePath,
            $this->autowiringMetadata,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->class,
            $this->filePath,
            $this->autowiringMetadata
        ) = unserialize($serialized);
    }

    /**
     * @deprecated Implemented for compatibility with Symfony 2.8
     */
    public function getResource()
    {
        return $this->filePath;
    }
}
