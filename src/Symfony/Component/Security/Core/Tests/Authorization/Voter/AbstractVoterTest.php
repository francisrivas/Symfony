<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Tests\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AbstractVoterTest extends \PHPUnit_Framework_TestCase
{
    protected $token;

    protected function setUp()
    {
        $this->token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
    }

    public function getTests()
    {
        return array(
            array(array('EDIT'), VoterInterface::ACCESS_GRANTED, new \stdClass(), 'ACCESS_GRANTED if attribute and class are supported and attribute grants access'),
            array(array('CREATE'), VoterInterface::ACCESS_DENIED, new \stdClass(), 'ACCESS_DENIED if attribute and class are supported and attribute does not grant access'),

            array(array('DELETE', 'EDIT'), VoterInterface::ACCESS_GRANTED, new \stdClass(), 'ACCESS_GRANTED if one attribute is supported and grants access'),
            array(array('DELETE', 'CREATE'), VoterInterface::ACCESS_DENIED, new \stdClass(), 'ACCESS_DENIED if one attribute is supported and denies access'),

            array(array('CREATE', 'EDIT'), VoterInterface::ACCESS_GRANTED, new \stdClass(), 'ACCESS_GRANTED if one attribute grants access'),

            array(array('DELETE'), VoterInterface::ACCESS_ABSTAIN, new \stdClass(), 'ACCESS_ABSTAIN if no attribute is supported'),

            array(array('EDIT'), VoterInterface::ACCESS_ABSTAIN, $this, 'ACCESS_ABSTAIN if class is not supported'),

            array(array('EDIT'), VoterInterface::ACCESS_ABSTAIN, null, 'ACCESS_ABSTAIN if object is null'),

            array(array('EDIT'), VoterInterface::ACCESS_ABSTAIN, 'foo', 'ACCESS_ABSTAIN for a non-object'),

            array(array(), VoterInterface::ACCESS_ABSTAIN, new \stdClass(), 'ACCESS_ABSTAIN if no attributes were provided'),
        );
    }

    /**
     * @dataProvider getTests
     */
    public function testVote(array $attributes, $expectedVote, $object, $message)
    {
        $voter = new AbstractVoterTest_Voter();

        $this->assertEquals($expectedVote, $voter->vote($this->token, $object, $attributes), $message);
    }
}

class AbstractVoterTest_Voter extends AbstractVoter
{
    protected function getSupportedClasses()
    {
        return array('stdClass');
    }

    protected function getSupportedAttributes()
    {
        return array('EDIT', 'CREATE');
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        return 'EDIT' === $attribute;
    }
}
