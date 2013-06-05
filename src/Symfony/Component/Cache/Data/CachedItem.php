<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Data;

/**
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class CachedItem extends ValidItem
{
    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        return true;
    }
}
