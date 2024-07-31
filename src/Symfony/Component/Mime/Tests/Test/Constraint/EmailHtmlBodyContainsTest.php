<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Tests\Test\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Test\Constraint\EmailHtmlBodyContains;

class EmailHtmlBodyContainsTest extends TestCase
{
    public function testToString()
    {
        $constraint = new EmailHtmlBodyContains('expectedValue');

        $this->assertSame('contains "expectedValue"', $constraint->toString());
    }

    public function testFailureDescription()
    {
        $expectedValue = 'expectedValue';
        $email = new Email();
        $email->html('actualValue')->text($expectedValue);

        try {
            (new EmailHtmlBodyContains($expectedValue))->evaluate($email);
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Failed asserting that the Email HTML body contains "expectedValue".', $e->getMessage());

            return;
        }

        $this->fail('Expected ExpectationFailedException to be thrown.');
    }
}
