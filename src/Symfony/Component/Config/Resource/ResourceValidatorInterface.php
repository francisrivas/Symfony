<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Resource;

/**
 * ResourceValidators check instances of ResourceInterface and tell whether a particular
 * resource is still fresh or if it has been changed since it was cached.
 *
 * @author Matthias Pigulla <mp@webfactory.de>
 */
interface ResourceValidatorInterface {

    /**
     * Check whether the given resource is still fresh.
     * @param ResourceInterface $resource
     * @return boolean|null Return true if the resource is still fresh, false if not.
     * Return null if this particular validator cannot tell for the resource.
     */
    public function isFresh(ResourceInterface $resource);
}

