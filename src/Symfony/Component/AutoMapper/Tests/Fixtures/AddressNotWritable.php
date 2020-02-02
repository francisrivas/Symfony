<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\AutoMapper\Tests\Fixtures;

class AddressNotWritable
{
    /**
     * @var string|null
     */
    private $city;

    public function getCity(): ?string
    {
        return $this->city;
    }
}
