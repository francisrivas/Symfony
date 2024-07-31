<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonEncoder\Tests\CacheWarmer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\JsonEncoder\CacheWarmer\LazyGhostCacheWarmer;
use Symfony\Component\JsonEncoder\Tests\Fixtures\Model\ClassicDummy;

class LazyGhostCacheWarmerTest extends TestCase
{
    private string $cacheDir;
    private string $lazyGhostsDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = sprintf('%s/symfony_json_encoder_test', sys_get_temp_dir());
        $this->lazyGhostsDir = $this->cacheDir.'/json_encoder/lazy_ghost';

        if (is_dir($this->lazyGhostsDir)) {
            array_map('unlink', glob($this->lazyGhostsDir.'/*'));
            rmdir($this->lazyGhostsDir);
        }
    }

    public function testWarmUpLazyGhost()
    {
        (new LazyGhostCacheWarmer([ClassicDummy::class], $this->cacheDir))->warmUp('useless');

        $this->assertSame(
            array_map(fn (string $c): string => sprintf('%s/%s.php', $this->lazyGhostsDir, hash('xxh128', $c)), [ClassicDummy::class]),
            glob($this->lazyGhostsDir.'/*'),
        );
    }
}
