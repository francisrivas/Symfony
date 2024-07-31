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
use Symfony\Component\Mime\Test\Constraint\EmailAttachmentCount;

class EmailAttachmentCountTest extends TestCase
{
    public function testToString()
    {
        $constraint = new EmailAttachmentCount(1);

        $this->assertSame('has sent "1" attachment(s)', $constraint->toString());
    }

    public function testFailureDescription()
    {
        $email = new Email();
        $email->attach('attachment content', 'attachment.txt');

        try {
            (new EmailAttachmentCount(2))->evaluate($email);
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Failed asserting that the Email has sent "2" attachment(s).', $e->getMessage());

            return;
        }

        $this->fail('Expected ExpectationFailedException to be thrown.');
    }
}
