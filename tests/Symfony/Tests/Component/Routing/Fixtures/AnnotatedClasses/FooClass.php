<?php

namespace Symfony\Tests\Component\Routing\Fixtures\AnnotatedClasses;

class FooClass
{
    /**
     * @Symfony\Component\Routing\Annotation\Route("/foo-class/index")
     */
    public function indexAction()
    {

    }
}
