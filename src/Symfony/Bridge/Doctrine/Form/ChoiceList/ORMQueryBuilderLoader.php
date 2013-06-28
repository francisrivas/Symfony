<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Doctrine\Form\ChoiceList;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Connection;

/**
 * Getting Entities through the ORM QueryBuilder
 */
class ORMQueryBuilderLoader implements EntityLoaderInterface
{
    /**
     * Contains the query builder that builds the query for fetching the
     * entities
     *
     * This property should only be accessed through queryBuilder.
     *
     * @var QueryBuilder
     */
    private $queryBuilder;
    
    /**
     * Contains the query hints to be used with the query builder
     *
     * @var array
     */
    private $queryHints;

    /**
     * Construct an ORM Query Builder Loader
     *
     * @param QueryBuilder|\Closure $queryBuilder
     * @param EntityManager         $manager
     * @param string                $class
     *
     * @throws UnexpectedTypeException
     */
    public function __construct($queryBuilder, $manager = null, $class = null, array $hints = array())
    {
        // If a query builder was passed, it must be a closure or QueryBuilder
        // instance
        if (!($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder or \Closure');
        }

        if ($queryBuilder instanceof \Closure) {
            $queryBuilder = $queryBuilder($manager->getRepository($class));

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
            }
        }

        $this->queryBuilder = $queryBuilder;
        $this->hints = $hints;
    }

    /**
     * {@inheritDoc}
     */
    public function getEntities()
    {
        $query = $this->queryBuilder->getQuery();

        foreach ($this->hints as $name => $value) {
            $query->setHint($name, $value);
        }

        return $query->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        $qb = clone ($this->queryBuilder);
        $alias = current($qb->getRootAliases());
        $parameter = 'ORMQueryBuilderLoader_getEntitiesByIds_'.$identifier;
        $where = $qb->expr()->in($alias.'.'.$identifier, ':'.$parameter);

        $query = $qb->andWhere($where)
                  ->getQuery()
                  ->setParameter($parameter, $values, Connection::PARAM_STR_ARRAY);

        foreach ($this->hints as $name => $value) {
            $query->setHint($name, $value);
        }

        return $query->getResult();
    }
}
