<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Doctrine\ORM\Util;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Bundle\SecurityBundle\Exception\RuntimeException;

/**
 * Utils for doctrine ORM.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DoctrineUtils
{
    /**
     * @var array
     */
    private static $cacheIdentifiers = array();

    /**
     * @var array
     */
    private static $cacheZeroIds = array();

    /**
     * @var array
     */
    private static $cacheCastIdentifiers = array();

    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Get the identifier of entity.
     *
     * @param ClassMetadata $targetEntity The target entity
     *
     * @return string
     */
    public static function getIdentifier(ClassMetadata $targetEntity)
    {
        if (!isset(static::$cacheIdentifiers[$targetEntity->getName()])) {
            $identifier = $targetEntity->getIdentifierFieldNames();
            static::$cacheIdentifiers[$targetEntity->getName()] = 0 < count($identifier)
                ? $id = $identifier[0]
                : 'id';
        }

        return static::$cacheIdentifiers[$targetEntity->getName()];
    }

    /**
     * Get the mock id for entity identifier.
     *
     * @param ClassMetadata $targetEntity The target entity
     *
     * @return int|string|null
     */
    public static function getMockZeroId(ClassMetadata $targetEntity)
    {
        if (!isset(static::$cacheZeroIds[$targetEntity->getName()])) {
            $type = static::getIdentifierType($targetEntity);
            $value = null;

            if ($type instanceof GuidType) {
                $value = '00000000-0000-0000-0000-000000000000';
            } elseif ($type instanceof IntegerType || $type instanceof SmallIntType
                    || $type instanceof BigIntType || $type instanceof DecimalType
                    || $type instanceof FloatType) {
                $value = 0;
            } elseif ($type instanceof StringType) {
                $value = '';
            }

            static::$cacheZeroIds[$targetEntity->getName()] = $value;
        }

        return static::$cacheZeroIds[$targetEntity->getName()];
    }

    /**
     * Cast the identifier.
     *
     * @param ClassMetadata $targetEntity The target entity
     * @param Connection    $connection   The doctrine connection
     *
     * @return string
     */
    public static function castIdentifier(ClassMetadata $targetEntity, Connection $connection)
    {
        if (!isset(static::$cacheCastIdentifiers[$targetEntity->getName()])) {
            $cast = '';

            if ('postgresql' === $connection->getDatabasePlatform()->getName()) {
                $type = static::getIdentifierType($targetEntity);
                $cast = '::'.$type->getSQLDeclaration($targetEntity->getIdentifierFieldNames(),
                                                      $connection->getDatabasePlatform());
            }

            static::$cacheCastIdentifiers[$targetEntity->getName()] = $cast;
        }

        return static::$cacheCastIdentifiers[$targetEntity->getName()];
    }

    /**
     * Get the dbal identifier type.
     *
     * @param ClassMetadata $targetEntity The target entity
     *
     * @return Type
     *
     * @throws RuntimeException When the doctrine dbal type is not found
     */
    protected static function getIdentifierType(ClassMetadata $targetEntity)
    {
        $identifier = static::getIdentifier($targetEntity);
        $type = $targetEntity->getTypeOfField($identifier);

        if ($type instanceof Type) {
            return $type;
        }

        if (is_string($type)) {
            return Type::getType($type);
        }

        $msg = 'The Doctrine DBAL type is not found for "%s::%s" identifier';
        throw new RuntimeException(sprintf($msg, $targetEntity->getName(), $identifier));
    }
}
