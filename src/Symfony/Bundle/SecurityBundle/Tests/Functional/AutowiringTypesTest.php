<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SecurityBundle\Tests\Functional;

class AutowiringTypesTest extends WebTestCase
{
    public function testAccessDecisionManagerAutowiring()
    {
        static::bootKernel(array('debug' => false));
        $container = static::$kernel->getContainer();

        $accessDecisionManager = $container->get('test.autowiring_types.autowired_services')->getAccessDecisionManager();
        $this->assertInstanceOf('Symfony\Component\Security\Core\Authorization\AccessDecisionManager', $accessDecisionManager);
    }

    protected static function createKernel(array $options = array())
    {
        return parent::createKernel(array('test_case' => 'AutowiringTypes') + $options);
    }
}
