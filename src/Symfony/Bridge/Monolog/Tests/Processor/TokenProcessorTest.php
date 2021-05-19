<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Monolog\Tests\Processor;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Monolog\Processor\TokenProcessor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Tests the TokenProcessor.
 *

 */
class TokenProcessorTest extends TestCase
{
    public function testLegacyProcessor()
    {
        if (method_exists(UsernamePasswordToken::class, 'getUserIdentifier')) {
            $this->markTestSkipped('This test requires symfony/security-core <5.3');
        }

        $token = new UsernamePasswordToken('user', 'password', 'provider', ['ROLE_USER']);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $processor = new TokenProcessor($tokenStorage);
        $record = ['extra' => []];
        $record = $processor($record);

        $this->assertArrayHasKey('token', $record['extra']);
        $this->assertEquals($token->getUsername(), $record['extra']['token']['username']);
        $this->assertEquals($token->isAuthenticated(), $record['extra']['token']['authenticated']);
        $this->assertEquals(['ROLE_USER'], $record['extra']['token']['roles']);
    }

    public function testProcessor()
    {
        if (!method_exists(UsernamePasswordToken::class, 'getUserIdentifier')) {
            $this->markTestSkipped('This test requires symfony/security-core 5.3+');
        }

        $token = new UsernamePasswordToken('user', 'password', 'provider', ['ROLE_USER']);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $processor = new TokenProcessor($tokenStorage);
        $record = ['extra' => []];
        $record = $processor($record);

        $this->assertArrayHasKey('token', $record['extra']);
        $this->assertEquals($token->getUserIdentifier(), $record['extra']['token']['user_identifier']);
        $this->assertEquals($token->isAuthenticated(), $record['extra']['token']['authenticated']);
        $this->assertEquals(['ROLE_USER'], $record['extra']['token']['roles']);
    }
}
