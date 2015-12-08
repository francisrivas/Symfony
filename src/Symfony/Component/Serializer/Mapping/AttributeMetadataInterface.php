<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Mapping;

use Symfony\Component\PropertyInfo\Type;

/**
 * Stores metadata needed for serializing and deserializing attributes.
 *
 * Primarily, the metadata stores serialization groups.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
interface AttributeMetadataInterface
{
    /**
     * Gets the attribute name.
     *
     * @return string
     */
    public function getName();

    /**
     * Adds this attribute to the given group.
     *
     * @param string $group
     */
    public function addGroup($group);

    /**
     * Gets groups of this attribute.
     *
     * @return string[]
     */
    public function getGroups();

    /**
     * Sets types of this attribute.
     *
     * @param Type[]|null $types
     */
    public function setTypes(array $types);

    /**
     * Gets types of this attribute.
     *
     * @return Type[]|null
     */
    public function getTypes();

    /**
     * Merges an {@see AttributeMetadataInterface} with in the current one.
     *
     * @param AttributeMetadataInterface $attributeMetadata
     */
    public function merge(AttributeMetadataInterface $attributeMetadata);
}
