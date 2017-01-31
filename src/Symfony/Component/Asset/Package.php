<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Asset;

use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\Context\NullContext;
use Symfony\Component\Asset\Exception\LogicException;
use Symfony\Component\Asset\Preload\PreloadManagerInterface;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

/**
 * Basic package that adds a version to asset URLs.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class Package implements PreloadedPackageInterface
{
    private $versionStrategy;
    private $context;
    private $preloadManager;

    public function __construct(VersionStrategyInterface $versionStrategy, ContextInterface $context = null, PreloadManagerInterface $preloadManager = null)
    {
        $this->versionStrategy = $versionStrategy;
        $this->context = $context ?: new NullContext();
        $this->preloadManager = $preloadManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion($path)
    {
        return $this->versionStrategy->getVersion($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($path)
    {
        if ($this->isAbsoluteUrl($path)) {
            return $path;
        }

        return $this->versionStrategy->applyVersion($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getAndPreloadUrl($path, $as = '')
    {
        if (null === $this->preloadManager) {
            throw new LogicException('There is no preload manager, configure one first.');
        }

        $url = $this->getUrl($path);
        $this->preloadManager->addResource($url, $as);

        return $url;
    }

    /**
     * @return ContextInterface
     */
    protected function getContext()
    {
        return $this->context;
    }

    /**
     * @return VersionStrategyInterface
     */
    protected function getVersionStrategy()
    {
        return $this->versionStrategy;
    }

    protected function isAbsoluteUrl($url)
    {
        return false !== strpos($url, '://') || '//' === substr($url, 0, 2);
    }
}
