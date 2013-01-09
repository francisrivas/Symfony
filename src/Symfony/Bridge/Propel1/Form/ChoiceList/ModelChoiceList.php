<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Propel1\Form\ChoiceList;

use \ModelCriteria;
use \BaseObject;
use \Persistent;

use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\Exception\StringCastException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ORMChoiceList;

/**
 * Widely inspired by the EntityChoiceList.
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Toni Uebernickel <tuebernickel@gmail.com>
 */
class ModelChoiceList extends ORMChoiceList
{
    /**
     * The fields of which the identifier of the underlying class consists
     *
     * This property should only be accessed through identifier.
     *
     * @var array
     */
    protected $identifier = array();

    /**
     * The query to retrieve the choices of this list.
     *
     * @var ModelCriteria
     */
    protected $query;

    /**
     * The query to retrieve the preferred choices for this list.
     *
     * @var ModelCriteria
     */
    protected $preferredQuery;

    /**
     * Whether to use the identifier for index generation
     *
     * @var Boolean
     */
    private $identifierAsIndex = false;

    /**
     * Constructor.
     *
     * @see Symfony\Bridge\Propel1\Form\Type\ModelType How to use the preferred choices.
     *
     * @param string              $class       The FQCN of the model class to be loaded.
     * @param string              $labelPath   A property path pointing to the property used for the choice labels.
     * @param array               $choices     An optional array to use, rather than fetching the models.
     * @param ModelCriteria       $queryObject The query to use retrieving model data from database.
     * @param string              $groupPath   A property path pointing to the property used to group the choices.
     * @param array|ModelCriteria $preferred   The preferred items of this choice.
     *                                         Either an array if $choices is given,
     *                                         or a ModelCriteria to be merged with the $queryObject.
     */
    public function __construct($class, $labelPath = null, $choices = null, $queryObject = null, $groupPath = null, $preferred = array())
    {
        $this->class        = $class;

        $queryClass         = $this->class . 'Query';
        $query              = new $queryClass();

        $this->identifier   = $query->getTableMap()->getPrimaryKeys();
        $this->query        = $queryObject ?: $query;
        $this->loaded       = is_array($choices) || $choices instanceof \Traversable;

        if ($preferred instanceof ModelCriteria) {
            $this->preferredQuery = $preferred->mergeWith($this->query);
        }

        if (!$this->loaded) {
            // Make sure the constraints of the parent constructor are
            // fulfilled
            $choices = array();
            $preferred = array();
        }

        if (1 === count($this->identifier) && $this->isInteger(current($this->identifier))) {
            $this->identifierAsIndex = true;
        }

        parent::__construct($choices, $labelPath, $preferred, $groupPath);
    }

    /**
     * Returns the class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns the model objects corresponding to the given values.
     *
     * @param array $values
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getChoicesForValues(array $values)
    {
        if (!$this->loaded) {
            if (1 === count($this->identifier)) {
                $filterBy = 'filterBy' . current($this->identifier)->getPhpName();

                return (array) $this->query->create()
                    ->$filterBy($values)
                    ->find();
            }

            $this->load();
        }

        return parent::getChoicesForValues($values);
    }

    /**
     * Returns the models corresponding to the given values.
     *
     * @param array $values
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getIndicesForValues(array $values)
    {
        if (!$this->loaded) {
            // Optimize performance for single-field identifiers. We already
            // know that the IDs are used as indices and values

            // Attention: This optimization does not check values for existence
            if ($this->identifierAsIndex) {
                return $this->fixIndices($values);
            }

            $this->load();
        }

        return parent::getIndicesForValues($values);
    }

    /**
     * Creates a new unique index for this model.
     *
     * If the model has a single-field identifier, this identifier is used.
     *
     * Otherwise a new integer is generated.
     *
     * @param mixed $model The choice to create an index for
     *
     * @return integer|string A unique index containing only ASCII letters,
     *                        digits and underscores.
     */
    protected function createIndex($model)
    {
        if ($this->identifierAsIndex) {
            return current($this->getIdentifierValues($model));
        }

        return parent::createIndex($model);
    }

    /**
     * {@inheritdoc}
     */
    protected function canOptimizeIdentifier()
    {
        return 1 === count($this->identifier);
    }

    /**
     * {@inheritdoc}
     */
    protected function load()
    {
        $models = (array) $this->query->find();

        $preferred = array();
        if ($this->preferredQuery instanceof ModelCriteria) {
            $preferred = (array) $this->preferredQuery->find();
        }

        try {
            // The second parameter $labels is ignored by ObjectChoiceList
            parent::initialize($models, array(), $preferred);
        } catch (StringCastException $e) {
            throw new StringCastException(str_replace('argument $labelPath', 'option "property"', $e->getMessage()), null, $e);
        }

        $this->loaded = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdentifierValues($model)
    {
        if ($model instanceof Persistent) {
            return array($model->getPrimaryKey());
        }

        // readonly="true" models do not implement Persistent.
        if ($model instanceof BaseObject && method_exists($model, 'getPrimaryKey')) {
            return array($model->getPrimaryKey());
        }

        return $model->getPrimaryKeys();
    }

    /**
     * Whether this column in an integer
     *
     * @param \ColumnMap $column
     *
     * @return Boolean
     */
    private function isInteger(\ColumnMap $column)
    {
        return $column->getPdoType() === \PDO::PARAM_INT;
    }
}

