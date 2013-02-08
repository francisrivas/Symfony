<?php

namespace Symfony\Component\Form\Test;

use Symfony\Component\Form\FormRenderer;

class FormRendererTest extends \PHPUnit_Framework_TestCase
{
    private $renderer;
    
    public function setUp()
    {
        $engine = $this->getMock('Symfony\Component\Form\FormRendererEngineInterface');
        $this->renderer = new FormRenderer($engine);
    }
    
    public function testHumanize()
    {
        $this->assertEquals('Is active', $this->renderer->humanize('is_active'));
        $this->assertEquals('Is active', $this->renderer->humanize('isActive'));
    }
    
    public function tearDown()
    {
        $this->renderer = null;
    }
}