<?php

namespace Symfony\Component\Serializer\Normalizer;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Defines the interface of serializers.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface NormalizerInterface
{
    /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param mixed  $data   data to normalize
     * @param string $format format the normalization result will be encoded as
     *
     * @return array|scalar
     * @api
     */
    function normalize($data, $format = null);

    /**
     * Denormalizes data back into an object of the given class
     *
     * @param mixed  $data   data to restore
     * @param string $class  the expected class to instantiate
     * @param string $format format the given data was extracted from
     *
     * @throws UnsupportedException if input data, class, or format is not supported
     *
     * @return object
     * @api
     */
    function denormalize($data, $type, $format = null);

    /**
     * Whether normalization is supported by the implementation.
     *
     * @param mixed  $data   data to normalize
     * @param string $format format the normalization result will be encoded as
     *
     * @return Boolean
     * @api
     */
    function supportsNormalization($data, $format = null);

    /**
     * Whether deormalization is supported by the implementation.
     *
     * @param mixed  $data   data to normalize
     * @param string $type
     * @param string $format format the normalization result will be encoded as
     *
     * @return Boolean
     * @api
     */
    function supportsDenormalization($data, $type, $format = null);
}
