<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\ChoiceList\Factory\Cache;

use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * A template decorator for static {@see ChoiceType} options.
 *
 * Used as fly weight for {@see CachingFactoryDecorator}.
 *
 * @internal
 *

 */
abstract class AbstractStaticOption
{
    private static $options = [];

    /** @var bool|callable|string|array|\Closure|ChoiceLoaderInterface */
    private $option;

    /**
     * @param FormTypeInterface|FormTypeExtensionInterface $formType A form type or type extension configuring a cacheable choice list
     * @param mixed                                        $option   Any pseudo callable, array, string or bool to define a choice list option
     * @param mixed|null                                   $vary     Dynamic data used to compute a unique hash when caching the option
     */
    final public function __construct($formType, $option, $vary = null)
    {
        if (!$formType instanceof FormTypeInterface && !$formType instanceof FormTypeExtensionInterface) {
            throw new \TypeError(sprintf('Expected an instance of "%s" or "%s", but got "%s".', FormTypeInterface::class, FormTypeExtensionInterface::class, get_debug_type($formType)));
        }

        $hash = CachingFactoryDecorator::generateHash([static::class, $formType, $vary]);

        $this->option = self::$options[$hash] ?? self::$options[$hash] = $option;
    }

    /**
     * @return mixed
     */
    final public function getOption()
    {
        return $this->option;
    }

    final public static function reset(): void
    {
        self::$options = [];
    }
}
