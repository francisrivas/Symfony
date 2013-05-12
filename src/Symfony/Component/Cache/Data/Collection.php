<?php

namespace Symfony\Component\Cache\Data;

use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\Cache\Exception\ObjectNotFoundException;

/**
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class Collection implements CollectionInterface
{
    /**
     * @var ItemInterface[]
     */
    private $items = array();

    /**
     * @var bool
     */
    private $hit = true;

    /**
     * @param ItemInterface[] $items
     */
    public function __construct(array $items = array())
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * @param array $values
     *
     * @return Collection
     */
    public static function fromCachedValues(array $values)
    {
        $collection = new self();
        foreach ($values as $key => $value) {
            $collection->add(new CachedItem($key, $value));
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if (!isset($this->items[$key])) {
            throw new ObjectNotFoundException(sprintf(
                'Collection does not contain item with "%s" key, presents keys are "%s".',
                $key, implode('", "', array_keys($this->items))
            ));
        }

        return $this->items[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        return array_keys($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        $values = array();
        foreach ($this->items as $item) {
            $values[$item->getKey()] = $item->getValue();
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ItemInterface $item)
    {
        if (!$item instanceof ValidItem) {
            throw new InvalidArgumentException('You can not add a non valid item in a collection.');
        }

        $this->items[$item->getKey()] = $item;
        $this->hit = $item->isHit() && $this->hit;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(CollectionInterface $collection)
    {
        foreach ($collection->all() as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        return $this->hit;
    }
}
