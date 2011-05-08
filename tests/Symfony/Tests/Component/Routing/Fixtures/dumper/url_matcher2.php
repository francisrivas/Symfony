<?php

use Symfony\Component\Routing\Matcher\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Matcher\Exception\NotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * ProjectUrlMatcher
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class ProjectUrlMatcher extends Symfony\Tests\Component\Routing\Fixtures\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();

        // foo
        if (0 === strpos($pathinfo, '/foo') && preg_match('#^/foo/(?P<bar>baz|symfony)$#x', $pathinfo, $matches)) {
            return array_merge($this->mergeDefaults($matches, array (  'def' => 'test',)), array('_route' => 'foo'));
        }

        // bar
        if (0 === strpos($pathinfo, '/bar') && preg_match('#^/bar/(?P<foo>[^/]+?)$#x', $pathinfo, $matches)) {
            if (!in_array($this->context->getMethod(), array('get', 'head'))) {
                $allow = array_merge($allow, array('get', 'head'));
                goto not_bar;
            }
            $matches['_route'] = 'bar';
            return $matches;
        }
        not_bar:

        // baz
        if ($pathinfo === '/test/baz') {
            return array('_route' => 'baz');
        }

        // baz2
        if ($pathinfo === '/test/baz.html') {
            return array('_route' => 'baz2');
        }

        // baz3
        if (rtrim($pathinfo, '/') === '/test/baz3') {
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', 'baz3');
            }
            return array('_route' => 'baz3');
        }

        // baz4
        if (0 === strpos($pathinfo, '/test') && preg_match('#^/test/(?P<foo>[^/]+?)/?$#x', $pathinfo, $matches)) {
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', 'baz4');
            }
            $matches['_route'] = 'baz4';
            return $matches;
        }

        // baz5
        if (0 === strpos($pathinfo, '/test') && preg_match('#^/test/(?P<foo>[^/]+?)/?$#x', $pathinfo, $matches)) {
            if ($this->context->getMethod() != 'post') {
                $allow[] = 'post';
                goto not_baz5;
            }
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', 'baz5');
            }
            $matches['_route'] = 'baz5';
            return $matches;
        }
        not_baz5:

        // baz.baz6
        if (0 === strpos($pathinfo, '/test') && preg_match('#^/test/(?P<foo>[^/]+?)/?$#x', $pathinfo, $matches)) {
            if ($this->context->getMethod() != 'put') {
                $allow[] = 'put';
                goto not_bazbaz6;
            }
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', 'baz.baz6');
            }
            $matches['_route'] = 'baz.baz6';
            return $matches;
        }
        not_bazbaz6:

        // foofoo
        if ($pathinfo === '/foofoo') {
            return array (  'def' => 'test',  '_route' => 'foofoo',);
        }

        if (0 === strpos($pathinfo, '/a')) {
            if (0 === strpos($pathinfo, '/a/b')) {
                // foo
                if (0 === strpos($pathinfo, '/a/b') && preg_match('#^/a/b/(?P<foo>[^/]+?)$#x', $pathinfo, $matches)) {
                    $matches['_route'] = 'foo';
                    return $matches;
                }
        
                // bar
                if (0 === strpos($pathinfo, '/a/b') && preg_match('#^/a/b/(?P<bar>[^/]+?)$#x', $pathinfo, $matches)) {
                    $matches['_route'] = 'bar';
                    return $matches;
                }
        
            }
    
        }

        // secure
        if ($pathinfo === '/secure') {
            if ($this->context->getScheme() !== 'https') {
                return $this->redirect($pathinfo, 'secure', 'https');
            }
            return array('_route' => 'secure');
        }

        // nonsecure
        if ($pathinfo === '/nonsecure') {
            if ($this->context->getScheme() !== 'http') {
                return $this->redirect($pathinfo, 'nonsecure', 'http');
            }
            return array('_route' => 'nonsecure');
        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new NotFoundException();
    }
}
