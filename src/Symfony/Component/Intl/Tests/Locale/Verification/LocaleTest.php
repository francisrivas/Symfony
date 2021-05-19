<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Intl\Tests\Locale\Verification;

use Symfony\Component\Intl\Tests\Locale\AbstractLocaleTest;
use Symfony\Component\Intl\Util\IntlTestHelper;

/**
 * Verifies that {@link AbstractLocaleTest} matches the behavior of the
 * {@link Locale} class with a specific version of ICU.
 *

 */
class LocaleTest extends AbstractLocaleTest
{
    protected function setUp(): void
    {
        IntlTestHelper::requireFullIntl($this, false);

        parent::setUp();
    }

    protected function call(string $methodName, ...$args)
    {
        return \Locale::{$methodName}(...$args);
    }
}
