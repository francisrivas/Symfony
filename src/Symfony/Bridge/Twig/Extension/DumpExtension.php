<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\Extension;

use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * Provides integration of the dump() function with Twig.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class DumpExtension extends \Twig_Extension_Debug
{
    private $cloner;
    private $dumper;

    public function __construct(ClonerInterface $cloner, HtmlDumper $dumper = null)
    {
        $this->cloner = $cloner;
        $this->dumper = $dumper ?: new HtmlDumper();
    }

    public function getFunctions()
    {
        if (!$this->cloner) {
            // use native dump function, as the DebugBundle is not registered
            return parent::getFunctions();
        }

        return array(
            new \Twig_SimpleFunction('dump', array($this, 'dump'), array('is_safe' => array('html'), 'needs_context' => true, 'needs_environment' => true)),
        );
    }

    public function getTokenParsers()
    {
        return array(new DumpTokenParser());
    }

    public function getName()
    {
        return 'dump';
    }

    public function dump(\Twig_Environment $env, $context)
    {
        if (!$env->isDebug()) {
            return;
        }

        if (2 === func_num_args()) {
            $vars = array();
            foreach ($context as $key => $value) {
                if (!$value instanceof \Twig_Template) {
                    $vars[$key] = $value;
                }
            }

            $vars = array($vars);
        } else {
            $vars = func_get_args();
            unset($vars[0], $vars[1]);
        }

        $dump = fopen('php://memory', 'r+b');
        $this->dumper->setCharset($env->getCharset());

        foreach ($vars as $value) {
            $this->dumper->dump($this->cloner->cloneVar($value), $dump);
        }

        return stream_get_contents($dump, -1, 0);
    }
}
