<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\JoliNotif\Tests;

use Symfony\Component\Notifier\Bridge\JoliNotif\JoliNotifTransportFactory;
use Symfony\Component\Notifier\Test\TransportFactoryTestCase;

/**
 * @author Ahmed Ghanem <ahmedghanem7361@gmail.com>
 */
final class JoliNotifTransportFactoryTest extends TransportFactoryTestCase
{
    public static function createProvider(): iterable
    {
        yield [
            'jolinotif://default',
            'jolinotif://default?some_option=true',
        ];
    }

    public static function supportsProvider(): iterable
    {
        yield [true, 'jolinotif://default'];
        yield [false, 'somethingElse://default'];
    }

    public static function unsupportedSchemeProvider(): iterable
    {
        yield ['somethingElse://user:pass@host.test?some_option=88'];
    }

    public function createFactory(): JoliNotifTransportFactory
    {
        return new JoliNotifTransportFactory();
    }
}
