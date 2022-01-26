<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\Tests\Fixtures\Controller;

use Symfony\Bridge\Twig\Attribute\Template;

class TemplateAttributeController
{
    #[Template('templates/foo.html.twig', vars: ['bar'])]
    public function foo($bar)
    {
    }
}
