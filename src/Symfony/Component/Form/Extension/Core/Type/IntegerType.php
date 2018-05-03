<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new IntegerToLocalizedStringTransformer($options['grouping'], $options['rounding_mode']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'grouping' => false,
            // Integer cast rounds towards 0, so do the same when displaying fractions
            'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_DOWN,
            'compound' => false,
            'invalid_message' => function (Options $options, $previousValue) {
                return ($options['legacy_error_messages'] ?? true) ?
                    'The integer is invalid.' :
                    $previousValue;
            },
        ]);

        $resolver->setAllowedValues('rounding_mode', [
            IntegerToLocalizedStringTransformer::ROUND_FLOOR,
            IntegerToLocalizedStringTransformer::ROUND_DOWN,
            IntegerToLocalizedStringTransformer::ROUND_HALF_DOWN,
            IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN,
            IntegerToLocalizedStringTransformer::ROUND_HALF_UP,
            IntegerToLocalizedStringTransformer::ROUND_UP,
            IntegerToLocalizedStringTransformer::ROUND_CEILING,
        ]);

        $resolver->setDefined('scale');
        $resolver->setAllowedTypes('scale', ['null', 'int']);
        $resolver->setDeprecated('scale');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integer';
    }
}
