<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UsernamePasswordToken implements a username and password token.
 *

 */
class UsernamePasswordToken extends AbstractToken
{
    private $credentials;
    private $firewallName;

    /**
     * @param string|\Stringable|UserInterface $user        The username (like a nickname, email address, etc.) or a UserInterface instance
     * @param mixed                            $credentials
     * @param string[]                         $roles
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($user, $credentials, string $firewallName, array $roles = [])
    {
        parent::__construct($roles);

        if ('' === $firewallName) {
            throw new \InvalidArgumentException('$firewallName must not be empty.');
        }

        $this->setUser($user);
        $this->credentials = $credentials;
        $this->firewallName = $firewallName;

        parent::setAuthenticated(\count($roles) > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthenticated(bool $isAuthenticated)
    {
        if ($isAuthenticated) {
            throw new \LogicException('Cannot set this token to trusted after instantiation.');
        }

        parent::setAuthenticated(false);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Returns the provider key.
     *
     * @return string The provider key
     *
     * @deprecated since 5.2, use getFirewallName() instead
     */
    public function getProviderKey()
    {
        if (1 !== \func_num_args() || true !== func_get_arg(0)) {
            trigger_deprecation('symfony/security-core', '5.2', 'Method "%s" is deprecated, use "getFirewallName()" instead.', __METHOD__);
        }

        return $this->firewallName;
    }

    public function getFirewallName(): string
    {
        return $this->getProviderKey(true);
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        parent::eraseCredentials();

        $this->credentials = null;
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->credentials, $this->firewallName, parent::__serialize()];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->credentials, $this->firewallName, $parentData] = $data;
        $parentData = \is_array($parentData) ? $parentData : unserialize($parentData);
        parent::__unserialize($parentData);
    }
}
