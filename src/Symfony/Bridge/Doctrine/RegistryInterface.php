<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

/**
 * References Doctrine connections and entity managers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface RegistryInterface
{
    /**
     * Gets the default connection name.
     *
     * @return string The default connection name
     */
    public function getDefaultConnectionName();

    /**
     * Gets the named connection.
     *
     * @param string $name The connection name (null for the default one)
     *
     * @return Connection
     */
    public function getConnection($name = null);

    /**
     * Gets an array of all registered connections
     *
     * @return array An array of Connection instances
     */
    public function getConnections();

    /**
     * Gets all connection names.
     *
     * @return array An array of connection names
     */
    public function getConnectionNames();

    /**
     * Gets the default entity manager name.
     *
     * @return string The default entity manager name
     */
    public function getDefaultEntityManagerName();

    /**
     * Gets a named entity manager.
     *
     * @param string $name The entity manager name (null for the default one)
     *
     * @return EntityManager
     */
    public function getEntityManager($name = null);

    /**
     * Gets an array of all registered entity managers
     *
     * @return EntityManager[] An array of EntityManager instances
     */
    public function getEntityManagers();

    /**
     * Resets a named entity manager.
     *
     * This method is useful when an entity manager has been closed
     * because of a rollbacked transaction AND when you think that
     * it makes sense to get a new one to replace the closed one.
     *
     * Be warned that you will get a brand new entity manager as
     * the existing one is not useable anymore. This means that any
     * other object with a dependency on this entity manager will
     * hold an obsolete reference. You can inject the registry instead
     * to avoid this problem.
     *
     * @param string $name The entity manager name (null for the default one)
     *
     * @return EntityManager
     */
    public function resetEntityManager($name = null);

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * This method looks for the alias in all registered entity managers.
     *
     * @param string $alias The alias
     *
     * @return string The full namespace
     *
     * @see Configuration::getEntityNamespace
     */
    public function getEntityNamespace($alias);

    /**
     * Gets all connection names.
     *
     * @return array An array of connection names
     */
    public function getEntityManagerNames();

    /**
     * Gets the EntityRepository for an entity.
     *
     * @param string $entityName        The name of the entity.
     * @param string $entityManagerName The entity manager name (null for the default one)
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($entityName, $entityManagerName = null);

    /**
     * Gets the entity manager associated with a given class.
     *
     * @param string $class A Doctrine Entity class name
     *
     * @return EntityManager|null
     */
    public function getEntityManagerForClass($class);
}
