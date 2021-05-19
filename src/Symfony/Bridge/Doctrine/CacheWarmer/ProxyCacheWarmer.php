<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Doctrine\CacheWarmer;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * The proxy generator cache warmer generates all entity proxies.
 *
 * In the process of generating proxies the cache for all the metadata is primed also,
 * since this information is necessary to build the proxies in the first place.
 *

 */
class ProxyCacheWarmer implements CacheWarmerInterface
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * This cache warmer is not optional, without proxies fatal error occurs!
     *
     * @return false
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[] A list of files to preload on PHP 7.4+
     */
    public function warmUp(string $cacheDir)
    {
        $files = [];
        foreach ($this->registry->getManagers() as $em) {
            // we need the directory no matter the proxy cache generation strategy
            if (!is_dir($proxyCacheDir = $em->getConfiguration()->getProxyDir())) {
                if (false === @mkdir($proxyCacheDir, 0777, true)) {
                    throw new \RuntimeException(sprintf('Unable to create the Doctrine Proxy directory "%s".', $proxyCacheDir));
                }
            } elseif (!is_writable($proxyCacheDir)) {
                throw new \RuntimeException(sprintf('The Doctrine Proxy directory "%s" is not writeable for the current system user.', $proxyCacheDir));
            }

            // if proxies are autogenerated we don't need to generate them in the cache warmer
            if ($em->getConfiguration()->getAutoGenerateProxyClasses()) {
                continue;
            }

            $classes = $em->getMetadataFactory()->getAllMetadata();

            $em->getProxyFactory()->generateProxyClasses($classes);

            foreach (scandir($proxyCacheDir) as $file) {
                if (!is_dir($file = $proxyCacheDir.'/'.$file)) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }
}
