<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SecurityBundle\Tests\Functional\app;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * App Test Kernel for functional tests.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AppKernel extends Kernel
{
    protected $mode;

    private $varDir;
    private $testCase;
    private $rootConfig;
    private $authenticatorManagerEnabled;

    public function __construct($varDir, $testCase, $rootConfig, $mode, $debug, $authenticatorManagerEnabled = false)
    {
        if (!is_dir(__DIR__.'/'.$testCase)) {
            throw new \InvalidArgumentException(sprintf('The test case "%s" does not exist.', $testCase));
        }
        $this->varDir = $varDir;
        $this->testCase = $testCase;

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($rootConfig) && !is_file($rootConfig = __DIR__.'/'.$testCase.'/'.$rootConfig)) {
            throw new \InvalidArgumentException(sprintf('The root config "%s" does not exist.', $rootConfig));
        }
        $this->rootConfig = $rootConfig;
        $this->authenticatorManagerEnabled = $authenticatorManagerEnabled;

        $this->mode = $mode;
        parent::__construct($mode, $debug);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerClass(): string
    {
        return parent::getContainerClass().substr(md5($this->rootConfig.$this->authenticatorManagerEnabled), -16);
    }

    public function registerBundles(): iterable
    {
        if (!is_file($filename = $this->getProjectDir().'/'.$this->testCase.'/bundles.php')) {
            throw new \RuntimeException(sprintf('The bundles file "%s" does not exist.', $filename));
        }

        return include $filename;
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/'.$this->varDir.'/'.$this->testCase.'/cache/'.$this->mode;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/'.$this->varDir.'/'.$this->testCase.'/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->rootConfig);

        if ($this->authenticatorManagerEnabled) {
            $loader->load(function ($container) {
                $container->loadFromExtension('security', [
                    'enable_authenticator_manager' => true,
                ]);
            });
        }
    }

    public function serialize()
    {
        return serialize([$this->varDir, $this->testCase, $this->rootConfig, $this->mode, $this->isDebug()]);
    }

    public function unserialize($str)
    {
        $a = unserialize($str);
        $this->__construct($a[0], $a[1], $a[2], $a[3], $a[4]);
    }

    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();
        $parameters['kernel.test_case'] = $this->testCase;

        return $parameters;
    }
}
