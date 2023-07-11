<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Tests\HttpCache;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class HttpCacheTestCase extends TestCase
{
    protected TestHttpKernel|TestMultipleHttpKernel|null $kernel;
    protected ?HttpCache $cache;
    protected ?array $caches;
    protected ?array $cacheConfig;
    protected ?Request $request;
    protected ?Response $response;
    protected ?array $responses;
    protected ?bool $catch;
    protected ?Esi $esi;

    protected Store $store;

    protected function setUp(): void
    {
        $this->kernel = null;

        $this->cache = null;
        $this->esi = null;
        $this->caches = [];
        $this->cacheConfig = [];

        $this->request = null;
        $this->response = null;
        $this->responses = [];

        $this->catch = false;

        $this->clearDirectory(sys_get_temp_dir().'/http_cache');
    }

    protected function tearDown(): void
    {
        $this->cache?->getStore()->cleanup();
        $this->kernel = null;
        $this->cache = null;
        $this->caches = null;
        $this->request = null;
        $this->response = null;
        $this->responses = null;
        $this->cacheConfig = null;
        $this->catch = null;
        $this->esi = null;

        $this->clearDirectory(sys_get_temp_dir().'/http_cache');
    }

    public function assertHttpKernelIsCalled(): void
    {
        $this->assertTrue($this->kernel->hasBeenCalled());
    }

    public function assertHttpKernelIsNotCalled(): void
    {
        $this->assertFalse($this->kernel->hasBeenCalled());
    }

    public function assertResponseOk(): void
    {
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function assertTraceContains($trace): void
    {
        $traces = $this->cache->getTraces();
        $traces = current($traces);

        $this->assertMatchesRegularExpression('/'.$trace.'/', implode(', ', $traces));
    }

    public function assertTraceNotContains($trace): void
    {
        $traces = $this->cache->getTraces();
        $traces = current($traces);

        $this->assertDoesNotMatchRegularExpression('/'.$trace.'/', implode(', ', $traces));
    }

    public function assertExceptionsAreCaught(): void
    {
        $this->assertTrue($this->kernel->isCatchingExceptions());
    }

    public function assertExceptionsAreNotCaught(): void
    {
        $this->assertFalse($this->kernel->isCatchingExceptions());
    }

    public function request($method, $uri = '/', $server = [], $cookies = [], $esi = false, $headers = []): void
    {
        if (null === $this->kernel) {
            throw new \LogicException('You must call setNextResponse() before calling request().');
        }

        $this->kernel->reset();

        $this->store = new Store(sys_get_temp_dir().'/http_cache');

        if (!isset($this->cacheConfig['debug'])) {
            $this->cacheConfig['debug'] = true;
        }

        if (!isset($this->cacheConfig['terminate_on_cache_hit'])) {
            $this->cacheConfig['terminate_on_cache_hit'] = false;
        }

        $this->esi = $esi ? new Esi() : null;
        $this->cache = new HttpCache($this->kernel, $this->store, $this->esi, $this->cacheConfig);
        $this->request = Request::create($uri, $method, [], $cookies, [], $server);
        $this->request->headers->add($headers);

        $this->response = $this->cache->handle($this->request, HttpKernelInterface::MAIN_REQUEST, $this->catch);

        $this->responses[] = $this->response;
    }

    public function getMetaStorageValues(): array
    {
        $values = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(sys_get_temp_dir().'/http_cache/md', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            $values[] = file_get_contents($file);
        }

        return $values;
    }

    // A basic response with 200 status code and a tiny body.
    public function setNextResponse($statusCode = 200, array $headers = [], $body = 'Hello World', \Closure $customizer = null, EventDispatcher $eventDispatcher = null): void
    {
        $this->kernel = new TestHttpKernel($body, $statusCode, $headers, $customizer, $eventDispatcher);
    }

    public function setNextResponses($responses): void
    {
        $this->kernel = new TestMultipleHttpKernel($responses);
    }

    public function catchExceptions($catch = true): void
    {
        $this->catch = $catch;
    }

    public static function clearDirectory($directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $fp = opendir($directory);
        while (false !== $file = readdir($fp)) {
            if (!\in_array($file, ['.', '..'])) {
                if (is_link($directory.'/'.$file)) {
                    unlink($directory.'/'.$file);
                } elseif (is_dir($directory.'/'.$file)) {
                    self::clearDirectory($directory.'/'.$file);
                    rmdir($directory.'/'.$file);
                } else {
                    unlink($directory.'/'.$file);
                }
            }
        }

        closedir($fp);
    }
}
