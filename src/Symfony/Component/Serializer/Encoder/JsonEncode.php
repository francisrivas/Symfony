<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Encoder;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * Encodes JSON data.
 *
 * @author Sander Coolen <sander@jibber.nl>
 */
class JsonEncode implements EncoderInterface
{
    const OPTIONS = 'json_encode_options';

    private $propertyAccessor;
    private $defaultContext = array(
        self::OPTIONS => 0,
        JsonEncoder::JSON_PROPERTY_PATH => null
    );

    /**
     * @param array $defaultContext
     */
    public function __construct($defaultContext = array())
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        if (!\is_array($defaultContext)) {
            @trigger_error(sprintf('Passing an integer as first parameter of the "%s()" method is deprecated since Symfony 4.2, use the "json_encode_options" key of the context instead.', __METHOD__), E_USER_DEPRECATED);

            $this->defaultContext[self::OPTIONS] = (int) $defaultContext;
        } else {
            $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
        }
    }

    /**
     * Encodes PHP data to a JSON string.
     *
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = array())
    {
        $jsonEncodeOptions = $context[self::OPTIONS] ?? $this->defaultContext[self::OPTIONS];
        $encodedJson = json_encode($data, $jsonEncodeOptions);

        if (JSON_ERROR_NONE !== json_last_error() && (false === $encodedJson || !($jsonEncodeOptions & JSON_PARTIAL_OUTPUT_ON_ERROR))) {
        if ($propertyPath = $context[JsonEncoder::JSON_PROPERTY_PATH]) {
            $data = $this->wrapEncodableData($propertyPath, $data);
        }

        $encodedJson = json_encode($data, $context['json_encode_options']);

        if (JSON_ERROR_NONE !== json_last_error() && (false === $encodedJson || !($context['json_encode_options'] & JSON_PARTIAL_OUTPUT_ON_ERROR))) {
            throw new NotEncodableValueException(json_last_error_msg());
        }

        return $encodedJson;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return JsonEncoder::FORMAT === $format;
    }
}
