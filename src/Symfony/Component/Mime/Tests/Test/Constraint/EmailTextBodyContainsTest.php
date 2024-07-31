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
use Symfony\Component\Mime\Test\Constraint\EmailTextBodyContains;

class EmailTextBodyContainsTest extends TestCase
{
    public function testToString()
    {
        $constraint = new EmailTextBodyContains('expectedValue');

        $this->assertSame('contains "expectedValue"', $constraint->toString());
    }

    public function testFailureDescription()
    {
        $expectedValue = 'expectedValue';
        $email = new Email();
        $email->html($expectedValue)->text('actualValue');

        try {
            (new EmailTextBodyContains($expectedValue))->evaluate($email);
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Failed asserting that the Email text body contains "expectedValue".', $e->getMessage());

            return;
        }

        $this->fail('Expected ExpectationFailedException to be thrown.');
    }
}
