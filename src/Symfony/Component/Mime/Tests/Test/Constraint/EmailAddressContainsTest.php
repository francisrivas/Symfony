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
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Test\Constraint\EmailAddressContains;

class EmailAddressContainsTest extends TestCase
{
    public function testToString()
    {
        $constraint = new EmailAddressContains('headerName', 'expectedValue');

        $this->assertSame('contains address "headerName" with value "expectedValue"', $constraint->toString());
    }

    public function testFailureDescription()
    {
        $headerName = 'headerName';
        $headers = new Headers();
        $headers->addMailboxHeader($headerName, 'actualValue@example.com');

        try {
            (new EmailAddressContains($headerName, 'expectedValue'))->evaluate(new Email($headers));
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Failed asserting that the Email contains address "headerName" with value "expectedValue" (value is actualValue@example.com).', $e->getMessage());

            return;
        }

        $this->fail('Expected ExpectationFailedException to be thrown.');
    }
}
