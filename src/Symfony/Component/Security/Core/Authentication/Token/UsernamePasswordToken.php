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
 * @author Fabien Potencier <fabien@symfony.com>
 */
class UsernamePasswordToken extends AbstractToken
{
    private $credentials;
    private $firewallName;

    /**
     * @param UserInterface $user
     * @param string[]      $roles
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($user, /* string */ $firewallName, /* array */ $roles = [])
    {
        if (\is_string($roles)) {
            trigger_deprecation('symfony/security-core', '5.4', 'The $credentials argument of "%s" is deprecated.', static::class.'::__construct');

            $credentials = $firewallName;
            $firewallName = $roles;
            $roles = \func_num_args() > 3 ? func_get_arg(3) : [];
        }

        parent::__construct($roles);

        if ('' === $firewallName) {
            throw new \InvalidArgumentException('$firewallName must not be empty.');
        }

        $this->setUser($user);
        $this->credentials = $credentials ?? null;
        $this->firewallName = $firewallName;

        parent::setAuthenticated(\count($roles) > 0, false);
    }

    public function setAuthenticated(bool $isAuthenticated)
    {
        if ($isAuthenticated) {
            throw new \LogicException('Cannot set this token to trusted after instantiation.');
        }

        parent::setAuthenticated(false, false);
    }

    public function getCredentials()
    {
        trigger_deprecation('symfony/security-core', '5.4', 'Method "%s" is deprecated.', __METHOD__);

        return $this->credentials;
    }

    /**
     * Returns the provider key.
     *
     * @return string The provider key
     *
     * @deprecated since Symfony 5.2, use getFirewallName() instead
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

    public function eraseCredentials()
    {
        parent::eraseCredentials();

        $this->credentials = null;
    }

    public function __serialize(): array
    {
        return [$this->credentials, $this->firewallName, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$this->credentials, $this->firewallName, $parentData] = $data;
        $parentData = \is_array($parentData) ? $parentData : unserialize($parentData);
        parent::__unserialize($parentData);
    }
}
