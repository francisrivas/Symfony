<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Tests\Loader;

use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Config\Resource\FileResource;

class PoFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {
        if (!class_exists('Symfony\Component\Config\Loader\Loader')) {
            $this->markTestSkipped('The "Config" component is not available');
        }
    }

    public function testLoad()
    {
        $loader = new PoFileLoader();
        $resource = __DIR__.'/../fixtures/resources.po';
        $catalogue = $loader->load($resource, 'en', 'domain1');

        $this->assertEquals(array('foo' => 'bar'), $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new FileResource($resource)), $catalogue->getResources());
    }

    public function testLoadPlurals()
    {
        $loader = new PoFileLoader();
        $resource = __DIR__.'/../fixtures/plurals.po';
        $catalogue = $loader->load($resource, 'en', 'domain1');

        $this->assertEquals(array('foo' => 'bar', 'foos' => '{0} bar|{1} bars'), $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new FileResource($resource)), $catalogue->getResources());
    }

    public function testLoadDoesNothingIfEmpty()
    {
        $loader = new PoFileLoader();
        $resource = __DIR__.'/../fixtures/empty.po';
        $catalogue = $loader->load($resource, 'en', 'domain1');

        $this->assertEquals(array(), $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new FileResource($resource)), $catalogue->getResources());
    }

    public function testLoadMultiline()
    {
        $loader = new PoFileLoader();
        $resource = __DIR__.'/../fixtures/multiline.po';
        $catalogue = $loader->load($resource, 'en', 'domain1');

        $this->assertEquals(3, count($catalogue->all('domain1')));
        //var_dump($catalogue->all('domain1'));
        /*
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('PoFileLoader', $loader);
        $translator->addResource('PoFileLoader', $resource, 'nl');

        // force catalogue loading
        $translator->trans('Translation has multiple lines.');

        $translator->setFallbackLocale('nl');

        $this->assertEquals('trans single line', $translator->trans('both single line'));

        $this->assertEquals('trans multi line', $translator->trans('source single line'));

        $this->assertEquals('trans single line', $translator->trans('source multi line'));
         *
         */
    }

}
