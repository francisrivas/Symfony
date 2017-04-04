<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Link\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Link\LinkManager;
use Symfony\Component\Link\LinkManagerInterface;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class LinkManagerTest extends TestCase
{
    public function testManageResources()
    {
        $manager = new LinkManager();
        $this->assertInstanceOf(LinkManagerInterface::class, $manager);

        $manager->add('/hello.html', 'prerender', array('pr' => 0.7));

        $manager->add('/foo/bar.js', 'preload', array('as' => 'script', 'nopush' => false));
        $manager->add('/foo/baz.css', 'preload');
        $manager->add('/foo/bat.png', 'preload', array('as' => 'image', 'nopush' => true));

        $this->assertEquals('</hello.html>; rel=prerender; pr=0.7,</foo/bar.js>; rel=preload; as=script,</foo/baz.css>; rel=preload,</foo/bat.png>; rel=preload; as=image; nopush', $manager->buildValues());
    }
}
